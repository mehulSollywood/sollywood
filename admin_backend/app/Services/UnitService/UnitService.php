<?php

namespace App\Services\UnitService;

use App\Helpers\ResponseError;
use App\Models\Unit;
use App\Services\CoreService;

class UnitService extends CoreService
{

    protected function getModelClass(): string
    {
        return Unit::class;
    }

    /**
     * @param array $ids
     * @return array
     */
    public function delete(array $ids): array
    {
        $items = $this->model()->whereDoesntHave('products')->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_511];
    }
}
