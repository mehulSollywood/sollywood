<?php

namespace App\Imports;

use App\Models\ShopProduct;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ShopProductImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    public function __construct()
    {
        $this->lang = request('lang') ?? null;
    }

    /**
     * @param Collection $collection
     * @return bool
     */
    public function collection(Collection $collection)
    {
        $shop_id = auth('sanctum')->user()->shop->id;
            foreach ($collection as $row) {
                ShopProduct::updateOrCreate(['shop_id' => $shop_id,'product_id' => $row['product_id']],[
                    'quantity' => $row['quantity'],
                    'price' => $row['price'],
                    'min_qty' => $row['min_qty'],
                    'max_qty' => $row['max_qty'],
                    'product_id' => $row['product_id'],
                    'active' => 1,
                    'shop_id' => $shop_id
                ]);
            }
            return true;
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
