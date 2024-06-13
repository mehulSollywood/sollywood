<?php

namespace App\Exports;

use App\Models\ShopProduct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ShopProductExport extends BaseExport  implements FromCollection,WithHeadings
{

    public function __construct(private $shopId)
    {
    }
    /**
    * @return Collection
    */
    public function collection()
    {
        $model = ShopProduct::with([
            'product.category.translation',
            'product.category.parent.translation',
            'product.unit.translation',
            'product.translation',
            'product.brand',
        ])->whereHas('product')->where('shop_id', $this->shopId->id)->get();

        return $model->map(function ($model){
            return $this->productModel($model);
        });
    }


    public function headings(): array
    {
        return [
            'Id',
            'Parent category name',
            'Category name',
            'Brand name',
            'Unit name',
            'Product name',
            'Picture',
            'Qr code',
            'Price',
            'Quantity',
        ];
    }

    private function productModel($item): array
    {
        return [
            'Id' => $item->id,
            'Parent category name' => $item?->product?->category?->parent?->translation?->title,
            'Category name' => $item?->product?->category?->translation?->title,
            'Brand name' => $item?->product?->brand?->title,
            'Unit name' => $item?->product?->unit?->translation?->title,
            'Product name' => $item?->product?->translation?->title,
            'Picture' => $item?->product?->galleries ? $this->imageUrl($item->galleries) : '',
            'Qr code' => $item?->product?->qr_code,
            'Price' => $item->price,
            'Quantity' => $item->quantity,

        ];

    }
}
