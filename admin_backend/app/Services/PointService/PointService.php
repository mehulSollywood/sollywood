<?php

namespace App\Services\PointService;

use App\Helpers\ResponseError;
use App\Models\Point;
use App\Services\CoreService;

class PointService extends CoreService
{

    protected function getModelClass(): string
    {
        return Point::class;
    }
    /**
     * @param array $ids
     * @return array
     */
    public function delete(array $ids): array
    {
        $items = $this->model()->find($ids);

        if ($items->isNotEmpty()) {

            foreach ($items as $item) {
                $item->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR];
        }

        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }


}
