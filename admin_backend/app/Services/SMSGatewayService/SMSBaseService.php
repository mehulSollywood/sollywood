<?php

namespace App\Services\SMSGatewayService;

use App\Models\SmsGateway;
use App\Services\CoreService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Twilio\Exceptions\ConfigurationException;

class SMSBaseService extends CoreService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return SmsGateway::class;
    }

    /**
     * @throws ConfigurationException
     */
    public function smsGateway($phone)
    {
        $gateway = $this->model()->where('active', 1)->first();
        $otp = $this->setOTP();

        switch ($gateway->type){
            case "nexmo":
                $result = (new NexmoService())->sendSms($gateway, $phone, $otp);
                if ($result['status']){
                    $this->setOTPToCache($phone, $otp);
                    return ['status' => true, 'verifyId' => $otp['verifyId'], 'phone' => Str::mask($phone, '*', -12, 8)];
                }
                return ['status' => false, 'message' => $result['message']];

            case "twilio":
                $result = (new TwilioService())->sendSms($gateway, $phone, $otp);
                if ($result['status']){
                    $this->setOTPToCache($phone, $otp);
                    return ['status' => true, 'verifyId' => $otp['verifyId'], 'phone' => Str::mask($phone, '*', -12, 8)];
                }
                return ['status' => false, 'message' => $result['message']];
        }
    }


    public function setOTP(): array
    {
        return ['verifyId' => Str::uuid(), 'otpCode' => rand(100000, 999999)];
    }

    public function setOTPToCache($phone, $otp){
        Cache::put('sms-'. $otp['verifyId'], [
            'phone' => $phone,
            'verifyId' => $otp['verifyId'],
            'OTPCode' => $otp['otpCode'],
            'expiredAt' => now()->addMinutes(5),
        ], 1800);

        Cache::put($otp['verifyId'], 3, 300);
    }
}
