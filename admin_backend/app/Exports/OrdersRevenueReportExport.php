<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersRevenueReportExport implements FromCollection, WithHeadings
{
    protected Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows->map(fn($row) => $this->tableBody($row));
    }

    public function headings(): array
    {
        return [
            'Date',
            'Orders',
            'Items sold',
            'Shipping',
            'Taxes',
            'Total sales',
        ];
    }

    private function tableBody($row): array
    {
        return [
            'time'           => data_get($row, 'time', 0),
            'count'          => data_get($row, 'count', 0),
            'total_quantity' => data_get($row, 'total_quantity', 0),
            'delivery_fee'   => data_get($row, 'delivery_fee', 0),
            'tax'            => data_get($row, 'tax', 0),
            'total_price'    => data_get($row, 'price', 0),
        ];
    }
}
