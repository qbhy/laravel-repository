<?php

namespace Qbhy\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface RepositoryInterface
{
    /**
     * @param $model
     *
     * @return array
     */
    public function format($model): array;

    /**
     * @param array $data
     * @param       $model
     *
     * @return array
     */
    public function bind(array $data, $model): array;

    /**
     * @param $model
     *
     * @return mixed
     */
    public function getDataFromCache($model);

    /**
     * @param $model
     *
     * @return bool
     */
    public function removeCache($model): bool;

    /**
     * @param $model
     *
     * @return array
     */
    public function updateCache($model): array;

    /**
     * @param $model
     *
     * @return string
     */
    public static function getCacheKey($model): string;

    /**
     * @param $paginate
     *
     * @return array
     */
    public function formatPaginate($paginate): array;

    /**
     * @param $list
     *
     * @return array
     */
    public function formatList($list): array;

    /**
     * @param $id
     *
     * @return mixed
     */
    public function find($id);

    /**
     * @param $id
     *
     * @return mixed
     */
    public function findOrFail($id);

    public function flushCache(): void;

}