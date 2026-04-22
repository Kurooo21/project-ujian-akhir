<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingController extends Controller
{
    public function getSettings()
    {
        $settings = Setting::pluck('value', 'key');
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'outlet_address' => 'nullable|string|max:500',
            'admin_whatsapp_number' => 'nullable|string|max:30',
            'payment_qris_label' => 'nullable|string|max:100',
            'payment_qris_image_url' => 'nullable|string|max:500',
            'payment_qris_note' => 'nullable|string|max:500',
            'payment_bank_name' => 'nullable|string|max:100',
            'payment_bank_account_number' => 'nullable|string|max:50',
            'payment_bank_account_name' => 'nullable|string|max:100',
            'payment_bank_note' => 'nullable|string|max:500',
        ]);

        $fields = [
            'outlet_address',
            'admin_whatsapp_number',
            'payment_qris_label',
            'payment_qris_image_url',
            'payment_qris_note',
            'payment_bank_name',
            'payment_bank_account_number',
            'payment_bank_account_name',
            'payment_bank_note',
        ];

        foreach ($fields as $field) {
            if (! $request->has($field)) {
                continue;
            }

            Setting::updateOrCreate(
                ['key' => $field],
                ['value' => $this->normalizeSettingValue($field, $request->input($field))]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan outlet dan pembayaran berhasil diperbarui!',
            'data' => Setting::pluck('value', 'key')
        ]);
    }

    private function normalizeSettingValue(string $key, mixed $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (in_array($key, ['admin_whatsapp_number', 'payment_bank_account_number'], true)) {
            $digits = preg_replace('/\D+/', '', $value);
            return $digits !== '' ? $digits : null;
        }

        return $value;
    }
}
