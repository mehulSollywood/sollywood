<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use Illuminate\Http\Request;
use App\Http\Resources\PaymentResource;
use App\Repositories\Interfaces\PaymentRepoInterface;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentController extends SellerBaseController
{

    public function __construct(protected PaymentRepoInterface $paymentRepository)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function paginate(Request $request): AnonymousResourceCollection
    {
        $products = $this->paymentRepository->paginate($request->input('perPage', 15), $request->all());
        return PaymentResource::collection($products);
    }

}
