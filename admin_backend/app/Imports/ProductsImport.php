<?php

namespace App\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Gallery;
use App\Models\Language;
use App\Models\Product;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use App\Models\Unit;
use App\Traits\ApiResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    use Importable, ApiResponse;

    public function __construct(protected $shopId = null)
    {

    }

    /**
     * @param Collection $collection
     * @return bool
     */
    public function collection(Collection $collection)
    {

        $lang = Language::where('default', 1)->first();

        foreach ($collection as $row) {
            $parentCategory = Category::query()->whereHas('translations', fn($q) => $q->where('locale', $lang->locale)
                ->where('title', $row['parent_category_name']))->first();

            if (!$parentCategory) {
                $parentCategory = Category::create([
                    'parent_id' => null
                ]);

                $parentCategory->translation()->create([
                    'locale' => $lang->locale,
                    'title' => $row['parent_category_name']
                ]);
            }


            $category = Category::query()->whereHas('translations', fn($q) => $q->where('locale', $lang->locale)
                ->where('title', $row['category_name']))->where('parent_id', $parentCategory->id)->first();

            if (!$category) {
                $category = Category::create([
                    'parent_id' => $parentCategory->id
                ]);

                $category->translation()->create([
                    'locale' => $lang->locale,
                    'title' => $row['category_name']
                ]);
            }

            $brand = Brand::query()->where('title', $row['brand_name'])->first();

            if (!$brand) {
                $brand = Brand::create([
                    'title' => $row['brand_name']
                ]);
            }

            $unit = Unit::query()->whereHas('translations', fn($q) => $q->where('locale', $lang->locale)
                ->where('title', $row['unit_name']))->first();

            if (!$unit) {
                $unit = Unit::create([
                    'position' => 'after',
                    'active' => true,
                ]);

                $unit->translation()->create([
                    'locale' => $lang->locale,
                    'title' => $row['unit_name']
                ]);
            }

            $product = Product::whereHas('translations', fn($q) => $q->where('locale', $lang->locale)
                ->where('qr_code', $row['qr_code']))->first();

            if (!$product) {
                $product = Product::create([
                    'category_id' => $category->id,
                    'brand_id' => $brand->id,
                    'unit_id' => $unit->id,
                    'qr_code' => $row['qr_code']
                ]);

                $product->translation()->create([
                    'locale' => $lang->locale,
                    'title' => $row['product_name'],
                ]);
            }

            if (isset($row['picture'])) {

                $product->galleries()->delete();

                $images = explode(',', $row['picture']);

                foreach ($images as $image) {
                    if (empty($image)) {
                        continue;
                    }
                    try {
                        Gallery::create([
                            'title' => $image,
                            'path' => $image,
                            'type' => 'products',
                            'loadable_type' => 'App\Models\Product',
                            'loadable_id' => $product->id,
                        ]);

                    } catch (\Throwable $e) {
                        Log::error('failed img upload', [
                            'url' => $image,
                            'message' => $e->getMessage(),
                        ]);
                    }
                }

                $product->update(['img' => data_get($product->galleries->first(), 'path')]);
            }

            if ($this->shopId) {
                $shopProduct = ShopProduct::where('product_id', $product->id)
                    ->where('shop_id', $this->shopId)
                    ->first();

                if (!$shopProduct) {
                    ShopProduct::create([
                        'product_id' => $product->id,
                        'shop_id' => $this->shopId,
                        'min_qty' => 1,
                        'max_qty' => 100,
                        'quantity' => $row['quantity'],
                        'price' => $row['price'],
                        'tax' => 0
                    ]);
                }

                $shopCategory = ShopCategory::where('category_id', $parentCategory->id)
                    ->where('shop_id', $this->shopId)->first();

                if (!$shopCategory) {
                    ShopCategory::create([
                        'category_id' => $parentCategory->id,
                        'shop_id' => $this->shopId
                    ]);
                }

            }

        }
        return true;
    }

    public function rules(): array
    {
        return [
            'category_name' => ['required', 'string'],
            'brand_name' => ['required', 'string'],
            'unit_name' => ['required', 'string'],
            'product_name' => ['required', 'string'],
            'qr_code' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'max:255'],
            'quantity' => ['required', 'numeric'],
            'picture' => ['required', 'string']
        ];
    }


    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 500;
    }

}
