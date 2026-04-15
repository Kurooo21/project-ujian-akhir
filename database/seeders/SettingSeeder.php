<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'outlet_address'],
            ['value' => 'Jl. Merdeka No. 123, Jakarta']
        );

        Setting::updateOrCreate(
            ['key' => 'admin_whatsapp_number'],
            ['value' => '6281336441994']
        );
    }
}
