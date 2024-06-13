<?php

namespace App\Services\UserServices;

use App\Helpers\ResponseError;
use App\Models\UserAddress;
use App\Services\CoreService;
use Exception;
use Illuminate\Support\Str;

class UserAddressService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return UserAddress::class;
    }

    public function create($collection): array
    {
        try {
            $address = $this->model()->create($this->setAddressParams($collection));
            $this->setDefault($address->id, $address->default);
            if ($address) {
                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $address];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_501];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    public function update($id, $collection): array
    {
        try {
            $model = $this->model()->find($id);
            if ($model) {
                $model->update($this->setAddressParams($collection));
                $default =  $model->default ?: $collection->default;
                $this->setDefault($id, $default);

                return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $model];
            }
            return ['status' => false, 'code' => ResponseError::ERROR_404];

        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400, 'message' => $e->getMessage()];
        }
    }

    /**
     * Set User Address model parameters for actions
     */
    private function setAddressParams($collection): array
    {
        return [
            'user_id' => $collection->user_id,
            'title' => $collection->title,
            'address' => $collection->address,
            'location' => [
                'latitude' => $collection['location'] ? Str::of($collection['location'])->before(',') : null,
                'longitude' => $collection['location'] ? Str::of($collection['location'])->after(',') : null,
            ],
            'active' => $collection->active ?? 0,
        ];
    }

    public function setAddressDefault(int $id = null, int $default = null): array
    {

        $item = $this->model()->where(['user_id' => auth('sanctum')->id(), 'id' => $id])->first();
        if ($item) {
            return $this->setDefault($id, $default, auth('sanctum')->id());
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }
}
