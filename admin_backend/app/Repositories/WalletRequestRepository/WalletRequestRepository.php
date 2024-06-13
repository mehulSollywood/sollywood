<?php

namespace App\Repositories\WalletRequestRepository;

use App\Models\WalletRequest;
use App\Repositories\CoreRepository;

class WalletRequestRepository extends CoreRepository
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return WalletRequest::class;
    }

    public function index(array $collection)
    {
        return $this->model()->with(['requestUser:id,firstname,lastname,phone,email', 'responseUser:id,firstname,lastname,phone,email'])
            ->where('response_user_id', auth('sanctum')->user()->id)
            ->orWhere('request_user_id', auth('sanctum')->user()->id)
            ->paginate($collection['perPage'] ?? 15);
    }

    public function show(int $id)
    {
        return $this->model()->with(['requestUser:id,firstname,lastname,phone,email', 'responseUser:id,firstname,lastname,phone,email'])
            ->where('id', $id)
            ->first();
    }

}
