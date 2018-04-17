<?php

namespace Qbhy\Repository;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class Repository implements RepositoryInterface
{
    /**
     * 缓存前缀
     */
    const CACHE_PREFIX = 'cache_prefix';

    /**
     * 模型中文
     */
    const MODEL_CN = 'model_name';

    /**
     * 模型的类名
     */
    const MODEL = 'model';

    /**
     * 缓存实例
     * @var \Illuminate\Cache\TaggedCache
     */
    protected $cache = null;

    /**
     * 缓存标签
     * @var array
     */
    protected $tags = [];

    /**
     * @var array
     */
    protected $fillable = [];

    protected $primary_key = 'id';

    /** @var static */
    protected static $instance;

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * 获取标签缓存器
     *
     * @return \Illuminate\Cache\TaggedCache
     */
    public function getTaggedCache(): \Illuminate\Cache\TaggedCache
    {
        if (is_null($this->cache)) {
            $tags = count($this->tags) > 0 ? $this->tags : [static::CACHE_PREFIX];

            $this->cache = app('cache.store')->tags($tags);
        }

        return $this->cache;
    }

    /**
     * 格式化待缓存的数据
     *
     * @param Model $model
     *
     * @return array
     */
    public function format($model): array
    {
        if ($this->fillable) {
            return $model->only(
                $this->fillable
            );
        }

        return $model->toArray();
    }

    /**
     * @param string $primary_key
     *
     * @return $this
     */
    public function setPrimaryKey(string $primary_key)
    {
        $this->primary_key = $primary_key;

        return $this;
    }

    /**
     * 清空该仓库缓存
     */
    public function flushCache(): void
    {
        $this->getTaggedCache()->flush();
    }

    /**
     * 后期模型绑定
     *
     * @param array     $data
     * @param Model|int $model
     *
     * @return array
     */
    public function bind(array $data, $model): array
    {
        return $data;
    }

    /**
     * 从数据库中获取模型
     *
     * @param int $id
     *
     * @return Model|null
     */
    public function find($id)
    {
        /** @var Model $class */
        $class = static::MODEL;

        return $class::query()->where($this->primary_key, $id)->first();
    }

    /**
     * 获取列表
     *
     * @param $list
     *
     * @return array
     */
    public function formatList($list): array
    {
        $results = [];
        foreach ($list as $item) {
            $results[] = $this->getDataFromCache($item);
        }

        return $results;
    }

    /**
     * 格式化成分页
     *
     * @param LengthAwarePaginator $paginate
     *
     * @return array
     */
    public function formatPaginate($paginate): array
    {
        return [
            'page'      => $paginate->currentPage(),
            'page_size' => $paginate->perPage(),
            'total'     => $paginate->total(),
            'list'      => $this->formatList($paginate),
        ];
    }

    /**
     * 获取缓存的 key
     *
     * @param $model
     *
     * @return string
     */
    public static function getCacheKey($model): string
    {
        $key = static::getModelKey($model);

        return static::CACHE_PREFIX . $key;
    }

    protected static function getModelKey($model): string
    {
        $key = $model instanceof Model ? $model->{static::getInstance()->primary_key} : $model;

        return $key;
    }

    /**
     * 优先从缓存中获取数据
     *
     * @param string|Model|null $model
     *
     * @return array|null
     */
    public function getDataFromCache($model)
    {
        if (is_null($model)) {
            return null;
        }

        $key = $this->getModelKey($model);

        $cache_key = static::getCacheKey($key);
        $data      = $this->getTaggedCache()->get($cache_key);

        if (is_null($data)) {
            $model = $key === $model ? $this->find($key) : $model;
            if (is_null($model)) {
                return null;
            }
            $data = $this->format($model);
            $this->getTaggedCache()->forever($cache_key, $data);
        }

        $data = $this->bind($data, $model);

        return $data;
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function updateCache($model): array
    {
        $this->removeCache($model);

        return $this->getDataFromCache($model);
    }

    /**
     * @param $model
     *
     * @return bool
     */
    public function removeCache($model): bool
    {
        return $this->getTaggedCache()->forget($this->getCacheKey($model));
    }

    /**
     * @param        $id
     * @param string $field
     *
     * @return Model|mixed|null
     */
    public function findOrFail($id)
    {
        $model = $this->find($id);

        if (is_null($model)) {
            throw new ModelNotFoundException(static::MODEL_CN . '没有找到!');
        }

        return $model;
    }

    /**
     * @param $model
     *
     * @return array
     */
    public function formatData($model)
    {
        $data = $this->format($model);

        return $this->bindWithoutCache($data, $model);
    }

    /**
     * @param array $data
     * @param       $model
     *
     * @return array
     */
    public function bindWithoutCache(array $data, $model)
    {
        return $this->bind($data, $model);
    }

}