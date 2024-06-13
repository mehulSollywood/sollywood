<?php

namespace App\Repositories\EmailTemplateRepository;

use App\Models\EmailTemplate;
use App\Repositories\CoreRepository;

class EmailTemplateRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return EmailTemplate::class;
    }

    public function paginate(array $filter) {
        return $this->model()->orderByDesc('id')->paginate(data_get($filter, 'perPage', 10));
    }

    public function show(EmailTemplate $emailTemplate, array $filter): EmailTemplate
    {
        return $emailTemplate->loadMissing(['emailSetting']);
    }
}
