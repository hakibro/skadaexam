<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kelas = Kelas::all();
        return view('features.data.kelas.index', compact('kelas')); // Updated path
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('features.data.kelas.create'); // Updated path
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:kelas,name',
        ]);
        Kelas::create($request->all());
        return redirect()->route('data.kelas.index')->with('success', 'Kelas berhasil ditambahkan.'); // Updated route
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kelas $kelas)
    {
        return view('features.data.kelas.edit', compact('kelas')); // Updated path
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kelas $kelas)
    {
        $request->validate([
            'name' => 'required|unique:kelas,name,' . $kelas->id,
        ]);
        $kelas->update($request->all());
        return redirect()->route('data.kelas.index')->with('success', 'Kelas berhasil diupdate.'); // Updated route
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->route('data.kelas.index')->with('success', 'Kelas berhasil dihapus.'); // Updated route
    }
}
