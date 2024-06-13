<?php

namespace App\Services\Interfaces;

interface BrandServiceInterface
{
    /**
     * @param $collection
     * @return mixed
     */
    public function create($collection): mixed;

    /**
     * @param int $id
     * @param $collection
     * @return mixed
     */
    public function update(int $id, $collection): mixed;


}
