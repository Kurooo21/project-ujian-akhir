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
            'outlet_address' => 'required|string|max:500'
        ]);

        Setting::updateOrCreate(
            ['key' => 'outlet_address'],
            ['value' => $request->outlet_address]
        );

        return response()->json([
            'success' => true,
            'message' => 'Alamat outlet berhasil diperbarui!',
            'data' => Setting::pluck('value', 'key')
        ]);
    }
}
