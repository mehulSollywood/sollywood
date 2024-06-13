<?php

namespace App\Repositories\PayoutRepository;

use App\Models\Payout;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PayoutRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Payout::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter = []): LengthAwarePaginator
    {
        return Payout::filter($filter)->with([
            'currency',
            'payment',
            'createdBy:id,uuid,firstname,lastname,img',
            'createdBy.wallet',
            'approvedBy:id,uuid,firstname,lastname,img',
            'approvedBy.wallet',
        ])
            ->orderByDesc('id')
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param Payout $payout
     * @param null $userId
     * @return Payout
     */
    public function show(Payout $payout, $userId = null): Payout
    {
        return $payout->when($userId, function ($q) use ($userId) {
            $q->where('created_by', $userId);
        })->load([
            'currency',
            'payment',
            'createdBy:id,uuid,firstname,lastname,img',
            'createdBy.wallet',
            'approvedBy:id,uuid,firstname,lastname,img',
            'approvedBy.wallet',
        ]);
    }
}
