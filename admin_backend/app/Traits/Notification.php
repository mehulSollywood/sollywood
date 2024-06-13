<?php

namespace App\Traits;

use App\Models\Settings;
use App\Services\PushNotificationService\PushNotificationService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

trait Notification
{
    private string $url = 'https://fcm.googleapis.com/fcm/send';

    public function sendNotification(
        array   $receivers = [],
        ?string $message = '',
        ?string $title = null,
        mixed   $data = [],
        array   $userIds = [],
        ?string $firebaseTitle = ''): string
    {
        $server_key = $this->firebaseKey();
        $fields = [
            'registration_ids' => $receivers,
            'notification' => [
                'body'  => $message,
                'title' => $title,
            ],
            'data' => $data
        ];

        $headers = [
            'Authorization' => 'key=' . $server_key,
            'Content-Type'  => 'application/json'
        ];

        $type = data_get($data, 'order.type');

        if (is_array($userIds) && count($userIds) > 0) {
            (new PushNotificationService)->storeMany([
                'type' => $type ?? data_get($data, 'type'),
                'title' => $title,
                'body' => $message,
                'data' => $data,
            ], $userIds);
        }

        $response = Http::withHeaders($headers)->post($this->url, $fields);
        $response = $response->body();

        Log::info('NOTIFICATION LOG REQUEST', [$fields, $headers]);
        Log::info('NOTIFICATION LOG RESPONSE', [$response]);
        return $response;
    }

    private function firebaseKey()
    {
        return Settings::adminSettings()->where('key', 'server_key')->pluck('value')->first();
    }
}
