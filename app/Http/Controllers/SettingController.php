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
        ]);

        if ($request->filled('outlet_address')) {
            Setting::updateOrCreate(
                ['key' => 'outlet_address'],
                ['value' => $request->outlet_address]
            );
        }

        if ($request->has('admin_whatsapp_number')) {
            $digits = preg_replace('/\D+/', '', (string) $request->admin_whatsapp_number);

            Setting::updateOrCreate(
                ['key' => 'admin_whatsapp_number'],
                ['value' => $digits]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan outlet berhasil diperbarui!',
            'data' => Setting::pluck('value', 'key')
        ]);
    }
}
