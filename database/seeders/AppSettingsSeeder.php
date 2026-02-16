<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    /**
     * Seed app settings for production test (white-label, welcome page).
     */
    public function run(): void
    {
        $settings = [
            'app_name' => 'PharmaOS',
            'tagline' => 'Sistem manajemen apotek',
            'logo_path' => null,
            'favicon_path' => null,
        ];

        foreach ($settings as $key => $value) {
            DB::table('app_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
