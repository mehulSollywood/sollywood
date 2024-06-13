<?php

namespace App\Observers;

use App\Helpers\ResponseError;
use App\Models\Product;
use App\Services\ProjectService\ProjectService;
use Exception;
use Illuminate\Support\Str;
use Log;
use Throwable;

class ProductObserver
{
    /**
     * Handle the Product "creating" event.
     *
     * @param Product $product
     * @return void
     * @throws Exception
     */
    public function creating(Product $product)
    {
        $product->uuid = Str::uuid();
        $this->projectStatus();

    }

    /**
     * Handle the Product "created" event.
     *
     * @param Product $product
     * @return void
     */
    public function created(Product $product)
    {
        //
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param Product $product
     * @return void
     */
    public function updated(Product $product)
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function deleted(Product $product)
    {

        try {

            $product->update([
                'category_id' => null
            ]);

        } catch (Throwable $e) {

          Log::error($e->getMessage(), [$e->getFile(), $e->getLine(), $e->getTrace()]);

        }

    }

    /**
     * Handle the Product "restored" event.
     *
     * @param Product $product
     * @return void
     */
    public function restored(Product $product)
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function forceDeleted(Product $product)
    {
        //
    }

    private function projectStatus(){
        if (!cache()->has('project.status') || cache('project.status')->active != 1){
            return (new ProjectService())->activationError();
        }
    }
}
