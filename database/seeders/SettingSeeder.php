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
        $defaults = [
            'outlet_address' => 'Jl. Merdeka No. 123, Jakarta',
            'admin_whatsapp_number' => '6281336441994',
            'payment_qris_label' => 'Demo QRIS Chi-Pok',
            'payment_qris_image_url' => null,
            'payment_qris_note' => 'Ini hanya QRIS demo untuk simulasi checkout, bukan pembayaran sungguhan.',
            'payment_bank_name' => 'BCA',
            'payment_bank_account_number' => '1234567890',
            'payment_bank_account_name' => 'Chi Pok Indonesia',
            'payment_bank_note' => 'Ini hanya rekening demo untuk simulasi pembayaran manual di aplikasi.',
        ];

        foreach ($defaults as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
