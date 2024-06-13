<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Models\Order;
use App\Helpers\ResponseError;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportController extends UserBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function orderExportPDF($id)
    {
        $order = Order::with([
            'shop' => fn($q) => $q->withTrashed(),
            'orderDetails',
            'orderDetails.shopProduct' => fn($q) => $q->withTrashed(),
            'orderDetails.shopProduct.product' => fn($q) => $q->withTrashed()
        ])->find($id);
        try {
            if ($order) {
                $pdf = Pdf::loadView('order-invoice', compact('order'));

                $pdf->save(Storage::disk('public')->path('import-example') . '/order_invoice.pdf');

                return response(Storage::disk('public')->get('/import-example/order_invoice.pdf'), 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment',
                ]);
            }
        }catch (\Exception $exception){
            dd($exception);
        }

        return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language));
    }

}
