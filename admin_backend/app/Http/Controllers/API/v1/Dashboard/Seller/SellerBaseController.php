<?php

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Traits\Loggable;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;

abstract class SellerBaseController extends Controller
{
    use ApiResponse, Loggable;
    protected $shop;

    public function __construct()
    {
        parent::__construct();
        $user = auth('sanctum')->user();
        if (isset($user->shop)) {
            $this->shop = $user->shop;
        } elseif (isset($user->moderatorShop) && (($user)->role == 'moderator' || ($user)->role == 'deliveryman')) {
            $this->shop = $user->moderatorShop;
        } else {
            $this->shop = false;
        }
    }

}
