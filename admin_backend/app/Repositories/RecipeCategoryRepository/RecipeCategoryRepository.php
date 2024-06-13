<?php

namespace App\Repositories\RecipeCategoryRepository;

use App\Models\RecipeCategory;
use App\Repositories\CoreRepository;

class RecipeCategoryRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return RecipeCategory::class;
    }

    // get list product
    public function list($array)
    {
        return $this->model()->filter($array)->orderByDesc('id')->get();
    }

    public function paginateForRest($perPage, $array, $active = true)
    {
        return $this->model()->whereHas('child.recipes')->whereHas(
            'child.recipes', function ($q) use ($array) {
            $q->where('shop_id', $array['shop_id']);
        })
            ->with([
                'translation:id,locale,recipe_category_id,title',
                'child.translation',
            ])
            ->whereHas('translation')
            ->whereHas('child.translation')
            ->when(isset($active), function ($q) use ($active) {
                $q->where('status', $active);
            })->when(isset($array['category_id']), function ($q) use ($array) {
                $q->whereHas('child', function ($query) use ($array) {
                    $query->where('id', $array['category_id']);
                });
            })->where('parent_id', null)->orderByDesc('id')->paginate($perPage);
    }

    public function paginate($perPage, $array)
    {
        return $this->model()
            ->with([
                'translation:id,locale,recipe_category_id,title',
                'child.translation',
            ])
            ->whereHas('translation')
            ->whereHas('child.translation')
            ->when(isset($array['category_id']), function ($q) use ($array) {
                $q->whereHas('child', function ($query) use ($array) {
                    $query->where('id', $array['category_id']);
                });
            })->where('parent_id', null)->orderByDesc('id')->paginate($perPage);
    }


    public function getById(int $id, $active = null)
    {
        return $this->model()->with([
            'translation',
            'child.translation',
            'child.recipes.translation',
            'recipes.translation',
            'recipes.user',
            'child.recipes.user',
            'translations'
        ])->when(isset($active), function ($q) use ($active) {
            $q->where('status', $active);
        })->find($id);
    }


}
