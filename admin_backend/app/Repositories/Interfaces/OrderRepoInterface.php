<?php

namespace App\Repositories\Interfaces;

use App\Http\Resources\OrderResource;
use App\Models\Order;

interface OrderRepoInterface
{
    public function ordersList(array $array = []);

    public function ordersPaginate(int $perPage, int $userId = null, array $array = []);

    public function show(int $id, $shopId = null);

    public function reDataOrder(?Order $order): OrderResource|null;

}
