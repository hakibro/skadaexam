<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GuideRepository;

class GuideController extends Controller
{
    public function index(GuideRepository $guides)
    {
        return response()->json($guides->all());
    }

    public function show(string $role, GuideRepository $guides)
    {
        $guide = $guides->forRole($role);

        if (! $guide) {
            return response()->json([
                'message' => 'Panduan role tidak ditemukan.',
                'role' => $role,
            ], 404);
        }

        return response()->json($guide);
    }
}
