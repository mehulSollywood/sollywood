<?php

namespace App\Exports;

use App\Models\Category;
use App\Models\Shop;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class ShopExport implements FromCollection
{
    public function __construct()
    {
        $this->lang = request('lang') ?? null;
    }
    /**
     * @return Collection
     */
    public function collection()
    {
        $model = Shop::with([
            'seller',
            'translation',
        ])->get();
        return $model->map(function ($model){
            return $this->tableBody($model);
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'User name',
            'Tax',
            'Delivery range',
            'Percentage',
            'Location',
            'Phone',
            'Show type',
            'Open',
            'Visibility',
            'Min amount',
            'Status',
            'Status Note',
            'Created at',
        ];
    }

    private function tableBody($item): array
    {
        return [
            'id' => $item->id,
            'Seller name' => $item->seller->firstname ?? null,
            'Tax' => $item->tax,
            'Delivery range' => $item->delivery_range,
            'Percentage' => $item->percentage,
            'Location' => $item->location,
            'Phone' => $item->phone,
            'Show type' => $item->show_type,
            'Open' => $item->open,
            'Visibility' => $item->visibility,
            'Min amount' => $item->min_amount,
            'Status Note' => $item->status_note,
            'Status' => $item->status,
            'Created at' => $item->created_at,
        ];
    }
}
