<?php

namespace App\Repositories\TransactionRepository;

use App\Models\Order;
use App\Models\Transaction;
use App\Repositories\CoreRepository;
use Illuminate\Database\Eloquent\Builder;


class TransactionRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Transaction::class;
    }

    public function paginate($perPage, $array = [])
    {
        return $this->model()->with([
            'payable', 'user',
        ])->filter($array)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    public function show(int $id, int $shopId = null)
    {
        return $this->model()->with([
            'payable', 'user',
            'paymentSystem.payment.translation'
        ])->when($shopId, function (Builder $query, $shopId) {
            $query
                ->where('payable_type', Order::class)
                ->whereHas('payable', fn($payable) => $payable->where('shop_id', $shopId));
        })->find($id);
    }
}
