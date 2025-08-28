<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Imports\GuruImport;
use Maatwebsite\Excel\Facades\Excel;

class GuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gurus = Guru::paginate(10);
        return view('features.data.guru.index', compact('gurus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roleOptions = Guru::getRoleOptions();
        return view('features.data.guru.create', compact('roleOptions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip',
            'email' => 'required|email|unique:guru,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:guru,data,naskah,pengawas,koordinator,ruangan',
        ]);

        // Hash password sebelum save
        $validated['password'] = Hash::make($validated['password']);

        Guru::create($validated);

        return redirect()->route('data.guru.index')
            ->with('success', 'Guru berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Guru $guru)
    {
        return view('features.data.guru.show', compact('guru'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Guru $guru)
    {
        $roleOptions = Guru::getRoleOptions();
        return view('features.data.guru.edit', compact('guru', 'roleOptions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip,' . $guru->id,
            'email' => 'required|email|unique:guru,email,' . $guru->id,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:guru,data,naskah,pengawas,koordinator,ruangan',
        ]);

        // Jika password diisi, hash password baru
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            // Jika password kosong, jangan update password
            unset($validated['password']);
        }

        $guru->update($validated);

        return redirect()->route('data.guru.index')
            ->with('success', 'Guru "' . $guru->nama . '" berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guru $guru)
    {
        $guru->delete();

        return redirect()->route('data.guru.index')
            ->with('success', 'Guru berhasil dihapus!');
    }

    /**
     * Show the form for importing resources.
     */
    public function import()
    {
        return view('features.data.guru.import');
    }

    /**
     * Process the import of resources.
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new GuruImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            if ($errorCount > 0) {
                return redirect()->back()
                    ->with('importResults', [
                        'success' => $successCount,
                        'errors' => $errorCount,
                        'errorDetails' => $errors
                    ])
                    ->with('warning', "Import completed with issues: {$successCount} successful, {$errorCount} failed.");
            }

            return redirect()->route('data.guru.index')
                ->with('success', "Successfully imported {$successCount} guru records!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    /**
     * Download the template for importing resources.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="guru-template.xlsx"',
        ];

        // Create sample data
        $sampleData = [
            ['nama', 'nip', 'email', 'password', 'role'],
            ['John Doe', '123456789', 'john@example.com', 'password123', 'guru'],
            ['Jane Smith', '987654321', 'jane@example.com', 'password123', 'data'],
            ['Bob Johnson', '', 'bob@example.com', 'password123', 'naskah'],
        ];

        return Excel::download(new class($sampleData) implements \Maatwebsite\Excel\Concerns\FromArray {
            protected $data;

            public function __construct($data)
            {
                $this->data = $data;
            }

            public function array(): array
            {
                return $this->data;
            }
        }, 'guru-template.xlsx', \Maatwebsite\Excel\Excel::XLSX, $headers);
    }

    /**
     * Search for resources - AJAX endpoint
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $perPage = $request->get('per_page', 25);

            $gurus = Guru::query()
                ->when($query, function ($q) use ($query) {
                    $q->where('nama', 'LIKE', "%{$query}%")
                        ->orWhere('nip', 'LIKE', "%{$query}%")
                        ->orWhere('email', 'LIKE', "%{$query}%")
                        ->orWhere('role', 'LIKE', "%{$query}%");
                })
                ->latest()
                ->paginate($perPage);

            // For AJAX requests, return JSON with HTML
            if ($request->expectsJson() || $request->ajax()) {
                $tableHtml = view('features.data.guru.partials.table', compact('gurus'))->render();
                $paginationHtml = view('features.data.guru.partials.pagination', compact('gurus'))->render();

                return response()->json([
                    'success' => true,
                    'html' => $tableHtml,
                    'pagination' => $paginationHtml,
                    'count' => $gurus->total(),
                    'showing' => $gurus->count()
                ]);
            }

            // For regular requests, return view
            return view('features.data.guru.index', compact('gurus'));
        } catch (\Exception $e) {
            \Log::error('Guru search error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
