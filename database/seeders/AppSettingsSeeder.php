<?php

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'id' => 1,
                'version' => '1.0.0',
                'force_update' => 0,
                'show_review_controller' => 0,
                'app_url' => env('APP_STORE_URL'),
            ],
        ];

        foreach ($settings as $key => $setting) {
            AppSetting::updateOrCreate(['id' => $setting['id']], $setting);
        }
    }
}
