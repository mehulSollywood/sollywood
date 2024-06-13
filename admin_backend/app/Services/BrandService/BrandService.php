<?php

namespace App\Services\BrandService;

use App\Helpers\ResponseError;
use App\Models\Brand;
use App\Services\CoreService;
use App\Services\Interfaces\BrandServiceInterface;

class BrandService extends CoreService implements BrandServiceInterface
{

    protected function getModelClass(): string
    {
        return Brand::class;
    }

    public function create($collection): array
    {
        $brand = $this->model()->create($this->setBrandParams($collection));
        if ($brand) {
            if (isset($collection->images)) {
                $brand->update(['img' => $collection->images[0]]);
                $brand->uploads($collection->images);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $brand];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];


    }

    public function update(int $id, $collection): array
    {
        $brand = $this->model()->find($id);
        if ($brand) {
            $brand->update($this->setBrandParams($collection));
            if (isset($collection->images)) {
                $brand->update(['img' => $collection->images[0]]);
                $brand->uploads($collection->images);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $brand];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];

    }

    public function destroy(array $ids): array
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

    private function setBrandParams($collection): array
    {
        return [
            'title' => $collection->title,
            'active' => $collection->active ?? 0,
        ];
    }
}
