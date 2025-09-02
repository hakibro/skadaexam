<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UjianController extends Controller
{
    public function index()
    {
        return view('ujian.index');
    }

    public function show($id)
    {
        return view('ujian.show', compact('id'));
    }

    public function start(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Ujian dimulai']);
    }

    public function submit(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Jawaban disimpan']);
    }

    public function finish(Request $request)
    {
        return response()->json(['success' => true, 'message' => 'Ujian selesai']);
    }
}
