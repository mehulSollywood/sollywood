<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Traits\Loggable;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\{FromCollection,
    ShouldAutoSize,
    WithBatchInserts,
    WithChunkReading,
    WithHeadings,
    WithMapping,
};
use Throwable;

class OrderReportExport implements FromCollection, WithMapping, ShouldAutoSize, WithBatchInserts, WithChunkReading, WithHeadings
{
    use Loggable;

    private Collection $rows;

    /**
     * BookingExport constructor.
     *
     * @param Collection $rows
     */
    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        return $this->rows;
    }

    public function orderProductsTitle($order): string
    {
        $names = '';

        $orderDetails = OrderDetail::where('order_id',$order->id)->get();
        foreach ($orderDetails as $orderDetail) {

                try {
                    $title = $orderDetail->shopProduct->product->translation->title;
                } catch (Throwable $e) {
                    $title = '';
                }

                $names .= "$title ;";

        }

        return $names;
    }

    public function moneyFormatter($number): string
    {
        [$whole, $decimal] = sscanf($number, '%d.%d');

        $money = number_format($number, 0, ',', ' ');

        return $decimal ? $money . ",$decimal" : $money;
    }

    public function map($row): array
    {
        /** @var Order $row */

        return [
            $row->created_at,
            $row->id,
            $row->status,
            $row->firstname . ' ' . $row->lastname,
            '',
            $this->orderProductsTitle($row),
            $row->quantity,
            '',
            $this->moneyFormatter($row->price)
        ];
    }

    public function headings(): array
    {
        return [
            'Date',
            '#',
            'Status',
            'Customer',
            'Customer type',
            'Products',
            'Item sold',
            'coupons',
            'Net sales',
        ];
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
