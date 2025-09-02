<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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

        $guru = Guru::create([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'] ?? null,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role ke guru (Spatie)
        $guru->assignRole($validated['role']);

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

        $guru->syncRoles([$validated['role']]);

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
            // Create an instance of the import class
            $import = new GuruImport();

            // Import the file
            Excel::import($import, $request->file('file'));

            // Get import statistics
            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            // Handle partial success
            if ($errorCount > 0) {
                return redirect()->route('data.guru.import')
                    ->with('importResults', [
                        'success' => $successCount,
                        'errors' => $errorCount,
                        'errorDetails' => $errors
                    ])
                    ->with('warning', "Import completed with issues: {$successCount} successful, {$errorCount} failed.");
            }

            // Full success
            return redirect()->route('data.guru.index')
                ->with('success', "Successfully imported {$successCount} guru records!");
        } catch (\Exception $e) {
            return redirect()->route('data.guru.import')
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
        // Debug log untuk troubleshooting
        Log::info('Guru search called with params: ' . json_encode($request->all()));

        // Get filter parameters
        $query = $request->get('q', '');
        $perPage = $request->get('per_page', 10);
        $roleFilter = $request->get('role', '');

        // Build query
        $gurusQuery = Guru::query();

        // Apply search filter if provided
        if (!empty($query)) {
            $gurusQuery->where(function ($q) use ($query) {
                $q->where('nama', 'like', "%{$query}%")
                    ->orWhere('nip', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            });
        }

        // Apply role filter if provided
        if (!empty($roleFilter)) {
            $gurusQuery->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        // Execute query with pagination
        $gurus = $gurusQuery->orderBy('created_at', 'desc')->paginate($perPage);
        $gurus->appends($request->all());

        // Handle AJAX request
        if ($request->ajax() || $request->wantsJson()) {
            $tableHtml = view('features.data.guru.partials.table', compact('gurus'))->render();
            $paginationHtml = view('features.data.guru.partials.pagination', compact('gurus'))->render();

            return response()->json([
                'success' => true,
                'html' => $tableHtml,
                'pagination' => $paginationHtml,
                'total' => $gurus->total(),
                'current_page' => $gurus->count(),
                'per_page' => $gurus->perPage()
            ]);
        }

        // Handle regular request
        return view('features.data.guru.index', compact('gurus'));
    }

    /**
     * Bulk delete selected gurus
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guru,id'
        ]);

        try {
            $count = count($request->ids);
            Guru::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => "{$count} guru berhasil dihapus."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal menghapus guru: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update role for selected gurus
     */
    public function bulkUpdateRole(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guru,id',
            'role' => 'required|in:guru,data,naskah,pengawas,koordinator,ruangan'
        ]);

        try {
            $count = 0;

            foreach ($request->ids as $id) {
                $guru = Guru::findOrFail($id);
                $guru->syncRoles([$request->role]);
                $count++;
            }

            return response()->json([
                'success' => true,
                'message' => "Role untuk {$count} guru berhasil diupdate."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Gagal mengupdate role: " . $e->getMessage()
            ], 500);
        }
    }
}
