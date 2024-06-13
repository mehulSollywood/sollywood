<?php

namespace App\Http\Controllers\API\v1\Dashboard\Deliveryman;

use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

abstract class DeliverymanBaseController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(['sanctum.check', 'role:deliveryman']);
    }
}
