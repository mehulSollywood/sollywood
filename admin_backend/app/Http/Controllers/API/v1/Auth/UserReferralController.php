<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\ConfigurationException;

class UserReferralController extends Controller
{
    use ApiResponse;

    /**
     * @throws ConfigurationException
     */
    public function userReferral(RegisterRequest $request): JsonResponse
    {
       $user = User::where('uuid',$request->uuid)->first();
       $referral_to = User::where('referral',$user->my_referral)->get(['firstname','phone'])->toArray();
       $referral_from = User::where('my_referral',$user->referral)->get(['firstname','phone'])->toArray();
       // return $this->successResponse("ssss",$data);
      
       $firstReferralFrom = reset($referral_from);
       if(empty($firstReferralFrom)){
        $firstReferralFrom = null;
       }
        return $this->successResponse("fetch referral user succesfully",["my_referral"=>$user->my_referral, "referral_from" =>$firstReferralFrom,
        "referral_to" => array_map(function($user) {
            return [
                "firstname" => $user['firstname'],
                "phone" => $user['phone']
            ];
        }, $referral_to),]);
    }
}
