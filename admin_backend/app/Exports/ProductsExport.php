<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport extends BaseExport implements FromCollection, WithHeadings
{
    public function __construct(protected $shopId = null)
    {
        $this->lang = request('lang') ?? null;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $shopId = $this->shopId;
        $model = Product::with([
            'category.translation',
            'category.parent.translation',
            'unit.translation',
            'translation',
            'brand',
        ])->when($shopId, function ($q) use ($shopId) {
            $q->whereHas('shopProduct', function ($b) use ($shopId) {
                $b->where('shop_id', $shopId);
            });
        })->get();
        return $model->map(function ($model) {
            return $this->productModel($model);
        });
    }

    public function headings(): array
    {
        $data = [
            'Id',
            'Parent category name',
            'Category name',
            'Brand name',
            'Unit name',
            'Product name',
            'Picture',
            'Qr code',
        ];
        if ($this->shopId) {
            $shopProduct = [
                'Price',
                'Quantity',
            ];
            $data = array_merge($data, $shopProduct);
        }
        return $data;
    }

    private function productModel($item): array
    {
        $data = [
            'Id' => $item->id,
            'Parent category name' => $item?->category?->parent?->translation?->title,
            'Category name' => $item->category?->translation?->title,
            'Brand name' => $item->brand?->title,
            'Unit name' => $item->unit?->translation?->title,
            'Product name' => $item->translation?->title,
            'Picture' => $item->galleries ? $this->imageUrl($item->galleries) : '',
            'Qr code' => $item->qr_code,
        ];

        if ($this->shopId) {
            $shopProduct = [
                'Id' => $item->shopProduct?->id,
                'Price' => $item->shopProduct?->price,
                'Quantity' => $item->shopProduct?->quantity,
            ];
            $data = array_merge($data, $shopProduct);
        }
        return $data;
    }

}
