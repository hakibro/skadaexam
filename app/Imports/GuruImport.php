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

        // Kumpulkan data user & guru per batch
        $userBatch = [];
        $guruBatch = [];
        $rolesBatch = [];

        foreach ($rows as $index => $row) {
            try {
                $userData = [
                    'name' => $row['nama'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $userBatch[] = $userData;

                $guruData = [
                    'nama' => $row['nama'],
                    'nip' => $row['nip'] ?? null,
                    'email' => $row['email'],
                    'password' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $guruBatch[] = $guruData;

                // Simpan role sementara sesuai index
                $role = in_array($row['role'], $validRoles) ? $row['role'] : 'guru';
                $rolesBatch[] = $role;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $index + 2,
                    'errors' => [$e->getMessage()]
                ];
            }
        }

        // Insert user dan guru secara batch
        DB::transaction(function () use ($userBatch, $guruBatch, $rolesBatch) {
            $insertedUsers = [];
            foreach ($userBatch as $userData) {
                $insertedUsers[] = User::create($userData);
            }

            foreach ($guruBatch as $i => $guruData) {
                $guruData['user_id'] = $insertedUsers[$i]->id;
                Guru::create($guruData);

                // Assign role
                $insertedUsers[$i]->assignRole($rolesBatch[$i]);

                $this->successCount++;
            }
        });
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:guru,email|unique:users,email',
            'password' => 'required|string|min:6',
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
