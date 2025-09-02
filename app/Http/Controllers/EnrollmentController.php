<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index()
    {
        return view('enrollment.index');
    }

    public function create($jadwal)
    {
        return view('enrollment.create', compact('jadwal'));
    }

    public function store(Request $request)
    {
        return redirect()->route('enrollment.index')->with('success', 'Enrollment berhasil dibuat');
    }

    public function show($jadwal)
    {
        return view('enrollment.show', compact('jadwal'));
    }

    public function generateTokens(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Tokens generated']);
    }
}
