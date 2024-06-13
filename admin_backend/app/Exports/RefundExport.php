<?php

namespace App\Exports;

use App\Models\Refund;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RefundExport implements FromCollection, WithHeadings
{
    /**
     * @var mixed|null
     */
    private $shop_id;

    public function __construct($shop_id = null)
    {
        $this->shop_id = $shop_id;
    }
    /**
    * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    public function collection()
    {
        $shopId = $this->shop_id;
        $refunds = Refund::with('user')
            ->when($shopId,function ($q) use ($shopId){
                $q->whereHas('order',function ($q) use ($shopId){
                    $q->where('shop_id',$shopId);
                });
            })
            ->get();

        return $refunds->map(fn(Refund $refund) => $this->tableBody($refund));
    }

    public function headings(): array
    {
        return [
            'Id',
            'Order Id',
            'User Id',
            'Message Seller',
            'Message User',
            'Status',
            'Image',
        ];
    }

    protected function tableBody(Refund $refund): array
    {
        return [
            'Id' => $refund->id,
            'Order Id' => $refund->order_id,
            'User Id' => $refund->user_id,
            'Message Seller' => $refund->message_seller,
            'Message User' => $refund->message_user,
            'Status' => $refund->status,
            'Image' => $refund->image,
        ];
    }
}
