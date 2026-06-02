<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;

class GuruImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure,
    WithChunkReading
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        $validRoles = array_keys(Guru::getRoleOptions());

        $userBatch = [];
        $guruBatch = [];
        $rolesBatch = [];

        // OPTIMIZATION: Pre-hash password once (avoid repeated Hash::make calls)
        // Default password = 'password123'
        $defaultPassword = Hash::make('password123');

        // Ambil last user id
        $lastId = DB::table('users')->max('id') ?? 0;
        $nextId = $lastId + 1;

        foreach ($rows as $index => $row) {
            try {
                $userBatch[] = [
                    'id' => $nextId,
                    'name' => $row['nama'],
                    'email' => $row['email'],
                    'password' => $defaultPassword,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $guruBatch[] = [
                    'nama' => $row['nama'],
                    'nip' => $row['nip'] ?? null,
                    'email' => $row['email'],
                    'user_id' => $nextId,
                    'password' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $rolesBatch[$nextId] = in_array($row['role'], $validRoles) ? $row['role'] : 'guru';

                $nextId++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        // Batch insert
        DB::transaction(function () use ($userBatch, $guruBatch, $rolesBatch) {
            User::insert($userBatch);
            Guru::insert($guruBatch);

            // OPTIMIZATION: Batch assign roles using raw SQL instead of per-user queries
            $roleInserts = [];
            foreach ($rolesBatch as $userId => $roleName) {
                // Get role ID from roles table
                $role = DB::table('roles')->where('name', $roleName)->where('guard_name', 'web')->first();
                if ($role) {
                    $roleInserts[] = [
                        'role_id' => $role->id,
                        'model_type' => User::class,
                        'model_id' => $userId,
                    ];
                    $this->successCount++;
                }
            }

            // Batch insert all role assignments at once
            if (!empty($roleInserts)) {
                DB::table('model_has_roles')->insert($roleInserts);
            }
        });
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:guru,email|unique:users,email',
            'password' => 'nullable|string|min:6',  // Password is optional, default = password123
            'role' => 'required|string',
        ];
    }

    public function onError(\Throwable $e)
    {
        $this->errorCount++;
        $this->errors[] = [
            'row' => 'Unknown',
            'errors' => [$e->getMessage()]
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->errorCount++;
            $this->errors[] = [
                'row' => $failure->row(),
                'errors' => $failure->errors()
            ];
        }
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function chunkSize(): int
    {
        return 20;
    }
}
