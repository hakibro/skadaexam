<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KioskSetting;
use Illuminate\Http\Request;

class KioskSettingController extends Controller
{
    public function edit()
    {
        $settings = KioskSetting::allAsArray();

        return view('admin.kiosk-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'exit_password' => 'required|string|min:4|max:50',
            'password_expires_at' => 'required|date|after:now',
        ]);

        KioskSetting::setMany($validated);

        return redirect()->route('admin.kiosk-settings.edit')
            ->with('success', 'Pengaturan mode kiosk berhasil disimpan.');
    }
}
