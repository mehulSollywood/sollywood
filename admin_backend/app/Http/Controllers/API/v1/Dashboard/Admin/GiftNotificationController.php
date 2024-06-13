<?php

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

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
use Carbon\Carbon;

class GiftNotificationController extends AdminBaseController
{
    use ApiResponse;

    /**
     * @throws ConfigurationException
     */
    public function giftNotification()
    {
        $users = User::all();
        $today = Carbon::now();
        $birthdayMatches = [];
        
        foreach ($users as $user) {
            $birthday = strtotime($user->birthday);
            $birthdayMonth = date('m', $birthday);
            $birthdayDate = date('d', $birthday);
            
            if ($today->month == $birthdayMonth && $today->day == $birthdayDate) {
                // If the month and day match, add the user's name to the array
                $birthdayMatches[] = $user;
            }
        }
       
        foreach ($birthdayMatches as $user) {
          
           $firebaseToken = $user->firebase_token;
           // dd($firebaseToken);
            // Send notification only if a valid Firebase token is present
            if ($firebaseToken) {
                $url = 'https://fcm.googleapis.com/fcm/send';
                $data = array(
                    'to' => $firebaseToken,
                    'notification' => array(
                        'body' => "Wish You Happy Birthday {$user->firstname}  You Got 100 Rs Cashback Today !",
                        'title' => "Birthday Greetings"
                    )
                );
                
                // Initialize cURL session
                $curl = curl_init();
                
                // Set cURL options
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_CAINFO => '/path/to/cacert.pem',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: key=AAAAjWgdQIc:APA91bHhDQ6wMcL1qHseBqfJ3LlIu9p81cniD3FBqo8N9dtmTKVlmGtPXu1dro0V8uMt-NeS1vnRfg-9eK19vOHS-ZuSbUBqPwm6cqyTZpovqJNKBsvflqh7Ym73k9P9AqYTZUbOoxEB' // Replace with your server key
                    ),
                ));
                
                // Execute cURL request
                $response = curl_exec($curl);
                
                // Handle errors
                if ($response === false) {
                    $error = curl_error($curl);
                    // Handle the error appropriately, such as logging it
                    echo "cURL Error: " . $error;
                }
                
                // Close cURL session
                curl_close($curl);
            }
        }
    }
    
       
}
