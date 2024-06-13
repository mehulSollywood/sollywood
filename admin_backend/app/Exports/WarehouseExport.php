<?php

namespace App\Exports;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class WarehouseExport extends BaseExport implements FromCollection, WithHeadings
{
    public function __construct(protected $shopId = null)
    {
        $this->lang = request('lang') ?? null;
    }

    /**
    * @return array|Collection|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function collection(): \Illuminate\Database\Eloquent\Collection|array|Collection
    {
        $shopId = $this->shopId;
        $model = Warehouse::with([
            'shopProduct.product.translation',
            'user',
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
        return [
            'Id',
            'Shop product name',
            'User name',
            'Quantity',
            'Note',
        ];
    }

    private function productModel($item): array
    {
        return [
            'Id' => $item->id,
            'Shop product name' => $item?->shopProduct?->product?->translation?->title,
            'User email' => $item->user?->email,
            'Quantity' => $item->quantity,
            'Note' => $item->note,
        ];
    }
}
