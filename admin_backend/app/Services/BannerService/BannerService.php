<?php

namespace App\Services\BannerService;

use App\Helpers\ResponseError;
use App\Models\Banner;
use App\Services\CoreService;

class BannerService extends CoreService
{
    protected function getModelClass(): string
    {
        return Banner::class;
    }

    public function create($collection, $shop_id = null): array
    {
        $collection['shop_id'] = $shop_id;
        $banner = $this->model()->create($collection);
        if ($banner) {
            $this->setTranslations($banner, $collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $banner];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];

    }

    public function update($collection, int $id, $shop_id = null): array
    {
        $blog = $this->model()->find($id);
        if ($blog) {
            $collection['shop_id'] = $shop_id;
            $blog->update($collection);
            $this->setTranslations($blog, $collection);
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $blog];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            $model->translation()->create([
                'locale' => $index,
                'title' => $value,
                'short_desc' => $collection['short_desc'][$index] ?? null,
                'description' => $collection['description'][$index] ?? null,
                'button_text' => $collection['button_text'][$index] ?? null,
            ]);
        }
    }
}
