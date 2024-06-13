<?php

namespace App\Services\EmailSettingService;

use App\Helpers\ResponseError;
use App\Models\EmailSetting;
use App\Services\CoreService;
use Exception;

class EmailSettingService extends CoreService
{
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return EmailSetting::class;
    }

    /**
     * @param array $data
     * @return array
     */
    public function create(array $data): array
    {
        try {

            $data['ssl'] = data_get($data, 'ssl', [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]);

            $data['ssl'] = [
                'ssl' => $data['ssl']
            ];

            $data['from_site'] = data_get($data, 'from_site', request()->getHost());

            $emailSetting = $this->model()->create($data);

            cache()->forget('email-settings-list');
            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $emailSetting];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }
    }

    /**
     * @param EmailSetting $emailSetting
     * @param array $data
     * @return array
     */
    public function update(EmailSetting $emailSetting, array $data): array
    {
        try {
            $data['ssl'] = data_get($data, 'ssl', [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]);

            $data['ssl'] = [
                'ssl' => $data['ssl']
            ];


            $emailSetting->update($data);

            try {
                cache()->forget('email-settings-list');
            } catch (Exception $e) {}

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => $emailSetting];
        } catch (Exception $e) {
            return ['status' => false, 'code' => ResponseError::ERROR_400];
        }
    }


    /**
     * @param int $id
     * @return void
     * @throws Exception
     */
    public function setActive(int $id): void
    {
        $emailSetting = EmailSetting::find($id);

        $emailSetting->update([
            'active' => !$emailSetting->active
        ]);

        cache()->forget('email-settings-list');
    }
}
