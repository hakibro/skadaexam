<?php

namespace App\Http\Controllers\Features\Data;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
            'nip' => 'required|string|max:255|unique:guru,nip',
            'email' => 'required|email|max:255|unique:guru,email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', array_keys(Guru::getRoleOptions())),
        ]);

        // First create a user
        $user = \App\Models\User::create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign role to user
        $user->assignRole($validated['role']);

        // Create guru linked to the user
        $guru = Guru::create([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'email' => $validated['email'],
            'user_id' => $user->id,
            'password' => null, // Set to null since we're storing the password in the users table
        ]);

        return redirect()->route('data.guru.index')
            ->with('success', 'Data guru berhasil ditambahkan.');
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
        // Get user from guru if exists
        $user = $guru->user;

        // Email validation rules
        $emailRules = 'required|email|max:255|unique:guru,email,' . $guru->id;
        if ($user) {
            $emailRules .= '|unique:users,email,' . $user->id;
        } else {
            $emailRules .= '|unique:users,email';
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'required|string|max:255|unique:guru,nip,' . $guru->id,
            'email' => $emailRules,
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|in:' . implode(',', array_keys(Guru::getRoleOptions())),
        ]);

        // Update guru info
        $guru->update([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'email' => $validated['email'],
        ]);

        // Update or create associated user
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'],
            ]);

            if (!empty($validated['password'])) {
                $user->update([
                    'password' => Hash::make($validated['password']),
                ]);
            }

            // Update role
            $user->syncRoles([$validated['role']]);
        } else {
            // Create new user if doesn't exist
            $user = \App\Models\User::create([
                'name' => $validated['nama'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password'] ?? \Illuminate\Support\Str::random(16)),
            ]);

            $user->assignRole($validated['role']);

            // Link user to guru
            $guru->update(['user_id' => $user->id]);
        }

        return redirect()->route('data.guru.index')
            ->with('success', 'Data guru berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Guru $guru)
    {
        // Backup user ID before deleting guru
        $userId = $guru->user_id;

        // Delete the guru record
        $guru->delete();

        // Delete the associated user if it exists
        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user) {
                $user->delete();
            }
        }

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
        // Enhanced debug logging
        Log::info('Guru search called with params: ' . json_encode($request->all()));

        // Get filter parameters
        $query = $request->get('q', '');
        $perPage = $request->get('per_page', 10);
        $roleFilter = $request->get('role', '');

        Log::info('Filter parameters:', [
            'query' => $query,
            'perPage' => $perPage,
            'roleFilter' => $roleFilter
        ]);

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

        // Apply role filter if provided - FIXED for Spatie roles
        if (!empty($roleFilter)) {
            Log::info("Applying role filter: {$roleFilter}");

            // Include debug SQL logging
            DB::enableQueryLog();

            $gurusQuery->whereHas('user.roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });

            $queries = DB::getQueryLog();
            Log::info('Role filter query:', end($queries));
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
            // Get all selected gurus with their user IDs
            $gurus = Guru::whereIn('id', $request->ids)->get();
            $count = $gurus->count();

            // Collect user IDs that need to be deleted
            $userIds = $gurus->pluck('user_id')->filter()->values()->all();

            // Delete the guru records
            Guru::whereIn('id', $request->ids)->delete();

            // Delete associated users
            if (!empty($userIds)) {
                \App\Models\User::whereIn('id', $userIds)->delete();
            }

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

                // Update role on user model - FIXED
                if ($guru->user) {
                    $guru->user->syncRoles([$request->role]);
                    $count++;
                }
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
