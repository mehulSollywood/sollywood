<?php

namespace App\Services\RecipeService;

use App\Helpers\ResponseError;
use App\Models\Recipe;
use App\Services\CoreService;

class RecipeService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getModelClass()
    {
        return Recipe::class;
    }

    public function create($collection): array
    {
        $recipe = $this->model()->create($this->setParams($collection));
        $this->setRecipeItems($recipe, $collection);

        $this->setTranslations($recipe, $collection);
        if ($recipe && isset($collection->images)) {
            $recipe->update(['image' => $collection->images[0]]);
            $recipe->uploads($collection->images);
        }
        return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $recipe];
    }

    /**
     * @param int $id
     * @param $collection
     * @return array
     */
    public function update(int $id, $collection): array
    {
        $recipe = $this->model()->find($id);
        if ($recipe)
        {
            $recipe->update($this->setParams($collection));

            $this->setRecipeItems($recipe, $collection);

            $this->setTranslations($recipe, $collection);
            if (isset($collection->images)) {
                $recipe->update(['image' => $collection->images[0]]);
                $recipe->uploads($collection->images);
            }
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $recipe];
        }
        return ['status' => false, 'code' => ResponseError::ERROR_404];

    }

    /**
     * Set Category params for Create & Update function
     * @param $collection
     * @return array
     */
    private function setParams($collection): array
    {
        return [
            'recipe_category_id' => $collection['recipe_category_id'],
            'user_id' => auth('sanctum')->id(),
            'image' => $collection['image'] ?? null,
            'active_time' => $collection['active_time'] ?? null,
            'total_time' => $collection['total_time'] ?? null,
            'calories' => $collection['calories'] ?? null,
            'shop_id' => $collection['shop_id'] ?? null,
        ];
    }

    public function setTranslations($model, $collection)
    {
        $model->translations()->delete();
        foreach ($collection['title'] as $index => $value) {
            $model->translation()->create([
                'title' => $value,
                'locale' => $index,
            ]);
        }
    }

    public function setInstruction($model, $collection)
    {
        $model->instructions()->delete();
        foreach ($collection as $instruction) {
            $recipeInstruction = $model->instruction()->create($instruction);
            $recipeInstruction->translations()->delete();
            foreach ($instruction['title'] as $key => $value) {
                $recipeInstruction->translation()->create([
                    'title' => $value,
                    'locale' => $key,
                ]);
            }
        }
    }

    public function setNutrition($model, $collection)
    {
        $model->nutritions()->delete();
        foreach ($collection as $nutrition) {
            $recipeNutrition = $model->nutritions()->create($nutrition);
            $recipeNutrition->translations()->delete();
            foreach ($nutrition['title'] as $key => $value) {
                $recipeNutrition->translation()->create([
                    'title' => $value,
                    'locale' => $key,
                ]);
            }

        }
    }

    public function setRecipeItems($model, $collection)
    {
        if (isset($collection['nutrition'])) {
            $this->setNutrition($model, $collection['nutrition']);
        }

        if (isset($collection['products'])) {
            $this->setProduct($model, $collection['products']);
        }

        if (isset($collection['instruction'])) {
            $this->setInstruction($model, $collection['instruction']);
        }
    }

    public function setProduct($model, $collection)
    {
        $model->products()->delete();
        $model->products()->createMany($collection);
    }

}
