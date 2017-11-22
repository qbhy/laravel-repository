<?php

namespace Qbhy\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{

    /**
     * @param $model
     * @return array
     */
    public function format($model): array;

    /**
     * @param array $data
     * @param $model
     * @return array
     */
    public function bind(array $data, $model): array;

    /**
     * @param $model
     * @return array
     */
    public function getDataFromCache($model): array;

    /**
     * @param $model
     * @return bool
     */
    public function removeCache($model): bool;

    /**
     * @param $model
     * @return array
     */
    public function updateCache($model): array;

    /**
     * @param $model
     * @return string
     */
    static public function getCacheKey($model): string;

    /**
     * @param $paginate
     * @return array
     */
    public function formatPaginate($paginate): array;

    /**
     * @param $list
     * @return array
     */
    public function formatList($list): array;

    /**
     * @param int $id
     * @return Model|null
     */
    public function find(int $id);

    /**
     * @param int $id
     * @return Model
     * @throws ModelNotFoundException
     */
    public function findOrFail(int $id);

    /**
     * 清空仓库缓存
     */
    public function flushCache(): void;

}