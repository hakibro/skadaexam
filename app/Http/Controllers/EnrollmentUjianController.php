<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnrollmentUjianController extends Controller
{
    public function index()
    {
        return view('enrollment-ujian.index');
    }

    public function create()
    {
        return view('enrollment-ujian.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('enrollment-ujian.index')->with('success', 'Enrollment ujian berhasil dibuat');
    }

    public function show($id)
    {
        return view('enrollment-ujian.show', compact('id'));
    }

    public function edit($id)
    {
        return view('enrollment-ujian.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('enrollment-ujian.index')->with('success', 'Enrollment ujian berhasil diupdate');
    }

    public function destroy($id)
    {
        return redirect()->route('enrollment-ujian.index')->with('success', 'Enrollment ujian berhasil dihapus');
    }
}
