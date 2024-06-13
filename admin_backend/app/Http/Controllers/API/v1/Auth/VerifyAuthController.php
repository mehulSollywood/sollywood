<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AfterVerifyRequest;
use App\Http\Requests\Auth\ResendVerifyRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use App\Traits\Notification;
use Illuminate\Http\JsonResponse;
use App\Events\Mails\SendEmailVerification;
use Illuminate\Http\Request;
use Throwable;
use Symfony\Component\HttpFoundation\Response;
use App\Services\UserServices\UserWalletService;

class VerifyAuthController extends Controller
{
    use ApiResponse, Notification;

//    public function verifyEmail(Request $request): \Illuminate\Http\JsonResponse
//    {
//        return (new AuthByEmail())->confirmOPTCode($request->all());
//    }

    public function verifyPhone(Request $request)
    {
        return (new AuthByMobilePhone())->confirmOPTCode($request->all());
    }

    public function resendVerify(ResendVerifyRequest $request): JsonResponse
    {
        $user = User::where('email', $request->input('email'))
            ->whereNotNull('verify_token')
            ->whereNull('email_verified_at')
            ->first();

        if (!$user) {
            return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND);
        }

        event((new SendEmailVerification($user)));

        return $this->successResponse('Verify code send');
    }

    public function verifyEmail(?string $verifyToken): JsonResponse
    {
        $user = User::withTrashed()->where('verify_token', $verifyToken)
            ->whereNull('email_verified_at')
            ->first();

        if (empty($user)) {
            return $this->errorResponse(ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], request()->lang),
                Response::HTTP_NOT_FOUND);
        }

        try {
            $user->update([
                'email_verified_at' => now(),
                'verify_token'      => null,
                'deleted_at'        => null,
            ]);

            return $this->successResponse('Email successfully verified', [
                'email' => $user->email
            ]);
        } catch (Throwable $e) {
            return $this->errorResponse(ResponseError::ERROR_501, trans('errors.' . ResponseError::ERROR_501, [], request()->lang),
                Response::HTTP_BAD_REQUEST);
        }
    }

    public function afterVerifyEmail(AfterVerifyRequest $request): JsonResponse
    {
    
        $user = User::where('phone', $request->input('phone'))->first();

        if (!$user){
            $user = User::create([
                'phone'             => $request->input('phone'),
                'email'             => $request->input('email'),
                'firstname'         => $request->input('firstname', $request->input('firstname')),
                'lastname'          => $request->input('lastname', $request->input('lastname')),
                'referral'          => $request->input('referral', $request->input('referral')),
                'birthday'          => $request->input('birthday', $request->input('birthday')),
                'gender'            => $request->input('gender','male'),
                'password'          => bcrypt($request->input('password', 'password')),
            ]);
        }else{
            $user->update([
                'phone'             => $request->input('phone'),
                'email'             => $request->input('email'),
                'firstname'         => $request->input('firstname', $user->firstname),
                'lastname'          => $request->input('lastname', $user->lastname),
                'referral'          => $request->input('referral', $user->referral),
                'birthday'          => $request->input('birthday', $user->birthday),
                'gender'            => $request->input('gender','male'),
                'password'          => bcrypt($request->input('password', 'password')),
            ]);
        }


        $referral = User::where('my_referral', $request->input('referral', $user->referral))
            ->first();

//        if (!empty($referral) && !empty($referral->firebase_token)) {
//            $this->sendNotification(
//                [$referral->firebase_token],
//                "By your referral registered new user. $user->name_or_email",
//                "Congratulations!",
//                [
//                    'type' => 'new_user_by_referral'
//                ],
//            );
//        }
//
//
//        $user->notifications()->sync(
//            \App\Models\Notification::where('type', \App\Models\Notification::PUSH)
//                ->select(['id', 'type'])
//                ->first()
//                ->pluck('id')
//                ->toArray()
//        );
//
//        $id = \App\Models\Notification::where('type', \App\Models\Notification::PUSH)->select(['id', 'type'])->first()?->id;
//
//        if ($id) {
//            $user->notifications()->sync([$id]);
//        } else {
//            $user->notifications()->forceDelete();
//        }

        $user->emailSubscription()->updateOrCreate([
            'user_id' => $user->id
        ], [
            'active' => true
        ]);

        if(empty($user->wallet)) {
            (new UserWalletService())->create($user);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        $user->syncRoles('user');

        return $this->successResponse(__('web.user_successfully_registered'), [
            'token' => $token,
            'user'  => UserResource::make($user->load('wallet')),
        ]);
    }

}
