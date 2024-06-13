<?php

namespace App\Repositories\ShopCategoryRepository;


use App\Models\Category;
use App\Models\ShopCategory;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShopCategoryRepository extends CoreRepository
{

    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return ShopCategory::class;
    }


    public function paginate($perPage, int $shop_id)
    {
        $perPage = $perPage ?? 10;
        return $this->model()->with([
            'category.translation',
            'category.children.translation:id,locale,title,category_id',
            'category.children.children.translation:id,locale,title,category_id'
        ])
            ->whereHas('category', function ($q) {
                $q->where('parent_id', null);
            })
            ->where('shop_id', $shop_id)
            ->orderByDesc('id')->paginate($perPage);
    }

    public function children($perPage, int $parent_id): LengthAwarePaginator
    {
        $perPage = $perPage ?? 10;

        return Category::with([
            'translation:id,locale,title,category_id',
        ])
            ->where('parent_id', $parent_id)
            ->orderByDesc('id')->paginate($perPage);
    }

    public function shopCategoryPaginate($perPage = 10)
    {
        return $this->model()->with('category.translation')->orderByDesc('id')->paginate($perPage);
    }

    public function getById(int $category_id, int $shop_id)
    {
        return $this->model()->where('category_id', $category_id)->where('shop_id', $shop_id)->first();
    }
}
