<?php

namespace App\Repositories\RecipeRepository;


use App\Models\Recipe;
use App\Repositories\CoreRepository;

class RecipeRepository extends CoreRepository
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass(): string
    {
        return Recipe::class;
    }
    // get list product
    public function list($array)
    {
        return $this->model()->filter($array)->orderByDesc('id')->get();
    }

    public function paginate($perPage, $array = [], $shop = null, $active = null)
    {
        return $this->model()->with([
            'translation',
            'user'
        ])
            ->when(isset($shop), function ($q) use ($shop) {
                $q->where('shop_id', $shop);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('status', $active);
            })
            ->when(isset($array['category_id']), function($q) use ($array){
                $q->where('recipe_category_id', $array['category_id']);
            })
            ->whereHas('translation')
            ->orderByDesc('id')->paginate($perPage);
    }


    public function getById(int $id, $shop = null)
    {
        return $this->model()->with([

            'nutritions.translation',

            'instructions.translation',

            'products.shopProduct.product.translation',

            'recipeCategory.translation',

            'translation',

            'nutritions.translations',

            'instructions.translations',

            'products.shopProduct.product.translation',

            'products.shopProduct.shop',

            'recipeCategory.translations',

            'translations',

            'user'
        ])
            ->when(isset($shop), function ($q) use($shop) {
                $q->where('shop_id', $shop);
            })->find($id);
    }
}
