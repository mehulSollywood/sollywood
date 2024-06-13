<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrderExport extends BaseExport implements FromCollection, WithHeadings
{
    protected array $filter;

    public function __construct(array $filter)
    {
        $this->filter = $filter;
    }

    public function collection(): Collection
    {
        $orders = Order::filter($this->filter)
            ->with([
                'user:id,firstname',
                'shop:id',
                'shop.translation',
                'deliveryMan:id,firstname',
            ])
            ->orderBy('id')
            ->get();

        return $orders->map(fn(Order $order) => $this->tableBody($order));
    }

    public function headings(): array
    {
        return [
            '#',
            'User Id',
            'Username',
            'Total Price',
            'Currency Id',
            'Currency Title',
            'Rate',
            'Note',
            'Shop Id',
            'Shop Title',
            'Tax',
            'Commission Fee',
            'Status',
            'Delivery Fee',
            'Deliveryman',
            'Deliveryman Firstname',
            'Delivery Date',
            'Delivery Time',
            'Total Discount',
            'Phone',
            'Created At',
        ];
    }

    private function tableBody(Order $order): array
    {
        $currencyTitle  = data_get($order->currency, 'title');
        $currencySymbol = data_get($order->currency, 'symbol');

        return [
            'id'                     => $order->id,//0
            'user_id'                => $order->user_id,//1
            'username'               => optional($order->user)->firstname,//2
            'total_price'            => $order->price,//3
            'currency_id'            => $order->currency_id,//4
            'currency_title'         => "$currencyTitle($currencySymbol)",//5
            'rate'                   => $order->rate,//6
            'note'                   => $order->note,//7
            'shop_id'                => $order->shop_id,//8
            'shop_title'             => data_get(optional($order->shop)->translation, 'title'),//9
            'tax'                    => $order->tax,//10
            'commission_fee'         => $order->commission_fee,//11
            'status'                 => $order->status,//12
            'delivery_fee'           => $order->delivery_fee,//13
            'deliveryman'            => $order->deliveryman,//14
            'deliveryman_firstname'  => optional($order->deliveryMan)->firstname,//15
            'delivery_date'          => $order->delivery_date,//16
            'delivery_time'          => $order->delivery_time,//17
            'total_discount'         => $order->total_discount,//18
            'phone'                  => $order->phone,//22
            'created_at'             => $order->created_at ?? date('Y-m-d H:i:s'),//23
        ];
    }
}
