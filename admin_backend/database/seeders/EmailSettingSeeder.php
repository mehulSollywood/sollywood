<?php

namespace Database\Seeders;

use App\Models\EmailSetting;
use Illuminate\Database\Seeder;

class EmailSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $emailSettings = [
            [
                'id' => 1,
                'smtp_auth' => 1,
                'smtp_debug' => 0,
                'host' => 'ssl://smtp.gmail.com',
                'port' => 465,
                'username' => 'username',
                'password' => '123456',
                'from_to' => 'seller@gmail.com',
                'from_site' => 'example.org',
                'ssl' => '{
                            "ssl": {
                                "verify_peer": false,
                                "verify_peer_name": false,
                                "allow_self_signed": true
                            }
                        }',
                'active' => 1,
            ]
        ];

        foreach ($emailSettings as $emailSetting) {
            EmailSetting::updateOrInsert(['id' => data_get($emailSetting, 'id')], $emailSetting);
        }
    }
}
