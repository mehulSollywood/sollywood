<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnotherTable extends Model
{
	protected $table = 'wallet_transaction_history';
    protected $fillable = [
        'type','amount','order_id','created_at','updated_at'
    ];

    /**
     * Define the relationship between Order and AnotherTable.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Save the order record into AnotherTable.
     *
     * @param \App\Models\Order $order
     * @return bool
     */
    public function saveRecord(Order $order)
    {
        // Assuming you want to save the order's data into AnotherTable
        $this->fill($order->toArray());
        return $this->save();
    }
}
