<?php

namespace Qbhy\Repository;

use Cache;
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
    protected $fillable = [
        'id',
    ];

    /**
     * 获取标签缓存器
     *
     * @return \Illuminate\Cache\TaggedCache
     */
    public function getTaggedCache(): \Illuminate\Cache\TaggedCache
    {
        if (is_null($this->cache)) {
            $tags = count($this->tags) > 0 ? $this->tags : [static::CACHE_PREFIX];
            $this->cache = Cache::tags($tags);
        }
        return $this->cache;
    }

    /**
     * 格式化待缓存的数据
     *
     * @param Model $model
     * @return array
     */
    public function format($model): array
    {
        return $model->only(
            $this->fillable
        );
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
     * @param array $data
     * @param Model|int $model
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
     * @return Model|null
     */
    public function find(int $id)
    {
        $class = static::MODEL;
        return $class::find($id);
    }

    /**
     * 获取列表
     *
     * @param $list
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
     * @return array
     */
    public function formatPaginate($paginate): array
    {
        return [
            'page' => $paginate->currentPage(),
            'page_size' => $paginate->perPage(),
            'total' => $paginate->total(),
            'list' => $this->formatList($paginate)
        ];
    }

    /**
     * 获取缓存的 key
     *
     * @param $model
     * @return string
     */
    static public function getCacheKey($model): string
    {
        $id = is_numeric($model) ? $model : $model->id;
        return static::CACHE_PREFIX . $id;
    }

    /**
     * 优先从缓存中获取数据
     *
     * @param int|Model|null $model
     * @return array|null
     */
    public function getDataFromCache($model)
    {
        if (is_null($model)) {
            return null;
        }

        $id = is_numeric($model) ? $model : $model->id;
        $cache_key = static::getCacheKey($id);
        $data = $this->getTaggedCache()->get($cache_key);

        if (is_null($data)) {
            $model = $id === $model ? $this->find($id) : $model;
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
     * @return array
     */
    public function updateCache($model): array
    {
        $this->removeCache($model);
        return $this->getDataFromCache($model);
    }

    /**
     * @param $model
     * @return bool
     */
    public function removeCache($model): bool
    {
        return $this->getTaggedCache()->forget($this->getCacheKey($model));
    }

    /**
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id)
    {
        $model = $this->find($id);

        if (is_null($model)) {
            throw new ModelNotFoundException(static::MODEL_CN . '没有找到!');
        }

        return $model;
    }

}