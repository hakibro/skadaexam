<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\KioskSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class KioskSettingController extends Controller
{
    /**
     * Get current kiosk settings
     */
    public function index()
    {
        $settings = KioskSetting::allAsArray();

        return response()->json([
            'success' => true,
            'data' => [
                'exit_password' => $settings['exit_password'],
                'password_expires_at' => $settings['password_expires_at'],
                'is_expired' => $settings['password_expires_at'] ?
                    now()->greaterThan($settings['password_expires_at']) : true,
            ],
        ]);
    }

    /**
     * Update kiosk settings via API
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exit_password' => 'required|string|min:4|max:50',
            'password_expires_at' => 'required|date|after:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            KioskSetting::setMany($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Pengaturan mode kiosk berhasil disimpan',
                'data' => KioskSetting::allAsArray(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan pengaturan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify exit password
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Password diperlukan',
            ], 422);
        }

        $settings = KioskSetting::allAsArray();
        $isExpired = $settings['password_expires_at'] ?
            now()->greaterThan($settings['password_expires_at']) : true;

        if ($isExpired) {
            return response()->json([
                'success' => false,
                'message' => 'Password telah kadaluarsa',
            ], 403);
        }

        $isValid = $request->password === $settings['exit_password'];

        return response()->json([
            'success' => $isValid,
            'message' => $isValid ? 'Password benar' : 'Password salah',
        ]);
    }
}
