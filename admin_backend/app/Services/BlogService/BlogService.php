<?php

namespace App\Services\BlogService;

use App\Helpers\ResponseError;
use App\Models\Blog;
use App\Services\CoreService;
use Illuminate\Support\Str;

class BlogService extends CoreService
{
    protected function getModelClass(): string
    {
        return Blog::class;
    }

    public function create($collection): array
    {
        $blog = $this->model()->create([
            'uuid' => Str::uuid(),
            'user_id' => auth('sanctum')->id(),
            'type' => Blog::TYPES[$collection->type],
            'active' => $collection->active ?? 1
        ]);
        if ($blog) {
            $this->setTranslations($blog, $collection);
            if (isset($collection->images)) {
                $blog->uploads($collection->images);
                $blog->update(['img' => $collection->images[0]]);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $blog];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_501];
    }

    public function update(string $uuid, $collection): array
    {
        $blog = $this->model()->firstWhere('uuid', $uuid);
        if ($blog) {
            $blog->update([
                'type' => Blog::TYPES[$collection->type],
                'active' => $collection->active ?? 1,
                ]);
            $this->setTranslations($blog, $collection);
            if (isset($collection->images)) {
                $blog->galleries()->delete();
                $blog->update(['img' => $collection->images[0]]);
                $blog->uploads($collection->images);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $blog];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];
    }

    private function setTranslations($model, $collection)
    {
        $model->translations()->delete();

        foreach ($collection->title as $index => $value) {
            $model->translation()->create([
                'locale' => $index,
                'title' => $value,
                'short_desc' => $collection->short_desc[$index] ?? null,
                'description' => $collection->description[$index] ?? null,
            ]);
        }
    }
}
