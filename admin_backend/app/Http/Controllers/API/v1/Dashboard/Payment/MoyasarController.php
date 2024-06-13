<?php

namespace App\Http\Controllers\API\v1\Dashboard\Payment;

use Log;
use App\Models\Order;
use App\Traits\ApiResponse;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MoyasarController extends Controller
{
    use ApiResponse;
    public function paymentWebHook(Request $request){

        Log::error('paymentWebHook', [$request->all()]);

        if ($request->input('type') == 'payment_paid'){

            $transaction = Transaction::where('payment_trx_id',$request->input('data.invoice_id'))->first();

                $transaction?->update([
                    'status' => Transaction::PAID
                ]);
            }

        if ($request->input('type') == 'payment_failed'){
            $transaction = Transaction::where('payment_trx_id',$request->input('data.invoice_id'))->first();

            $transaction?->update([
                'status' => Transaction::CANCELED
            ]);

            $order = Order::where('id',$transaction->payable_id)->first();

            $order?->update([
                'status' => Order::CANCELED
            ]);
        }
        return $this->successResponse(trans('web.successfully_update',  [], \request()->lang), []);
    }
}
