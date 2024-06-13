<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emailTemplates = [
            [
                'id' => 1,
                'email_setting_id' => 1,
                'subject' => 'Verify code',
                'body' => '<b>Verify code is  $verify_code</b>',
                'alt_body' => 'Verify code is  $verify_code',
                'status' => '1',
                'type' => 'verify',
                'send_to' => now()
            ]
        ];

        foreach ($emailTemplates as $emailTemplate) {
            EmailTemplate::updateOrInsert(['id' => data_get($emailTemplate, 'id')], $emailTemplate);
        }
    }
}
