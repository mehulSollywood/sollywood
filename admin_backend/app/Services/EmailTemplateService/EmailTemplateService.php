<?php

namespace App\Services\EmailTemplateService;

use App\Events\Mails\EmailSendByTemplate;
use App\Helpers\ResponseError;
use App\Models\EmailTemplate;
use App\Services\CoreService;
use App\Traits\Loggable;
use Log;
use Throwable;

class EmailTemplateService extends CoreService
{
    use Loggable;
    /**
     * @return string
     */
    protected function getModelClass(): string
    {
        return EmailTemplate::class;
    }

    public function create(array $data): array
    {
        try {
            /** @var EmailTemplate $emailTemplate */
            $data['status'] = 0;
            $verify         = EmailTemplate::TYPE_VERIFY;

            if (
                $data['type'] === $verify && (
                    !stristr($data['body'], '$verify_code') ||
                    !stristr($data['alt_body'], '$verify_code')
                )
            ) {
                $message = 'when status: ' . $verify . ' you should add text $verify_code on body and alt body';

                return [
                    'status'    => false,
                    'message'   => $message,
                    'code'      => ResponseError::ERROR_501
                ];
            }

            if ($data['type'] === $verify) {
                $this->model()->where('type', $verify)->delete();
            }

            $emailTemplate = $this->model()->create($data);

            Log::error('date', [
                date('Y-m-d H', strtotime($emailTemplate->send_to)),
                date('Y-m-d H')
            ]);

            if (
                date('Y-m-d H', strtotime($emailTemplate->send_to)) === date('Y-m-d H') &&
                $emailTemplate->type == EmailTemplate::TYPE_SUBSCRIBE
            ) {
                event((new EmailSendByTemplate(EmailTemplate::find($emailTemplate->id))));
            }

            return [
                'status'    => true,
                'code'      => ResponseError::NO_ERROR,
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
            ];
        }
    }

    public function update(EmailTemplate $emailTemplate, array $data): array
    {
        try {
            $data['status'] = 0;
            $verify         = EmailTemplate::TYPE_VERIFY;

            if (
                $data['type'] === $verify && (
                    !stristr($data['body'], '$verify_code') ||
                    !stristr($data['alt_body'], '$verify_code')
                )
            ) {
                $message = 'when status: ' . $verify . ' you should add text $verify_code on body and alt body';

                return [
                    'status'    => false,
                    'message'   => $message,
                    'code'      => ResponseError::ERROR_501
                ];
            }

            $emailTemplate->update($data);

            if (
                date('Y-m-d H', strtotime($emailTemplate->send_to)) === date('Y-m-d H') &&
                $emailTemplate->type == EmailTemplate::TYPE_SUBSCRIBE
            ) {
                event((new EmailSendByTemplate(EmailTemplate::find($emailTemplate->id))));
            }

            return [
                'status'    => true,
                'code'      => ResponseError::NO_ERROR,
            ];
        } catch (Throwable $e) {

            $this->error($e);

            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_501,
            ];
        }
    }

    public function destroy(?array $ids = []): array
    {
        $emailTemplates = $this->model()->whereIn('id', $ids)->get();
        $count = $this->model()->count();

        if ($emailTemplates->count() > 0){
            if (!($count == 1)){
                foreach ($this->model()->whereIn('id', is_array($ids) ? $ids : [])->get() as $emailTemplate) {
                    $emailTemplate->delete();
                }
                return [
                    'status'    => true,
                    'code'      => ResponseError::NO_ERROR,
                ];
            }
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_505,
            ];
        }
            return [
                'status'    => false,
                'code'      => ResponseError::ERROR_404,
            ];

    }
}
