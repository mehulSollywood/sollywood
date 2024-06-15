<?php

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\AnotherTable;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\ConfigurationException;

class GiftCashbackController extends UserBaseController
{
    use ApiResponse;

    /**
     * @throws ConfigurationException
     */
    public function GiftHistory(RegisterRequest $request): JsonResponse
    {
      
       $user = User::where('uuid',$request->uuid)->get('id')->toArray();
       $giftUser = AnotherTable::where('user_id',$user)->where('type','gift_out')->get()->toArray();
        return $this->successResponse("data",$giftUser);
       
    }

    public function CashbackHistory(RegisterRequest $request): JsonResponse
    {
      
       $user = User::where('uuid',$request->uuid)->get('id')->toArray();
       $giftUser = AnotherTable::where('user_id',$user)->where('type','cashback_out')->get()->toArray();
        return $this->successResponse("data",$giftUser);
       
    }
}
