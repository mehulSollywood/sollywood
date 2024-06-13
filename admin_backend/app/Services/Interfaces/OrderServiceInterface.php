<?php

namespace App\Services\Interfaces;

interface OrderServiceInterface
{
    public function create($collection);

    public function update(int $id, $collection);

    public function createAutoOrder($orderTemplate, array $collection);
}
