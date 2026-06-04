<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolSettingController extends Controller
{
    public function edit()
    {
        $settings = SchoolSetting::allAsArray();

        return view('admin.school-settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'alamat' => 'nullable|string|max:1000',
            'npsn' => 'nullable|string|max:100',
            'nss' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:50',
            'telepon' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'info_lain' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|max:2048',
            'hapus_logo' => 'nullable|boolean',
            'sync_siswa_enabled' => 'nullable|boolean',
            'sync_siswa_interval_minutes' => 'required|integer|min:1|max:1440',
            'sync_siswa_date_start' => 'nullable|date',
            'sync_siswa_date_end' => 'nullable|date|after_or_equal:sync_siswa_date_start',
            'sync_siswa_time_start' => 'nullable|date_format:H:i',
            'sync_siswa_time_end' => 'nullable|date_format:H:i',
        ]);

        $settings = SchoolSetting::allAsArray();

        if ($request->boolean('hapus_logo') && !empty($settings['logo_path'])) {
            Storage::disk('public')->delete($settings['logo_path']);
            $validated['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if (!empty($settings['logo_path'])) {
                Storage::disk('public')->delete($settings['logo_path']);
            }

            $validated['logo_path'] = $request->file('logo')->store('school', 'public');
        }

        unset($validated['logo'], $validated['hapus_logo']);
        $validated['sync_siswa_enabled'] = $request->boolean('sync_siswa_enabled') ? '1' : '0';

        SchoolSetting::setMany($validated);

        return redirect()->route('admin.school-settings.edit')
            ->with('success', 'Setting sekolah berhasil disimpan.');
    }
}
