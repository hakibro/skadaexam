<?php


namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class GuruImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsOnError,
    SkipsOnFailure
{
    private $successCount = 0;
    private $errorCount = 0;
    private $errors = [];

    public function collection(Collection $rows)
    {
        $validRoles = array_keys(Guru::getRoleOptions());

        foreach ($rows as $index => $row) {
            try {
                // Create user first
                $user = \App\Models\User::create([
                    'name' => $row['nama'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password']),
                ]);

                // Create guru linked to user
                $guru = Guru::create([
                    'nama' => $row['nama'],
                    'nip' => $row['nip'] ?? null,
                    'email' => $row['email'],
                    'user_id' => $user->id,
                    'password' => null, // Set to null since we store passwords in the users table
                ]);

                // Assign role to user
                $role = $row['role'];
                if (in_array($role, $validRoles)) {
                    $user->assignRole($role);
                } else {
                    $user->assignRole('guru'); // Default role
                }

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $index + 2, // +2 for heading row and 0-index
                    'errors' => [$e->getMessage()]
                ];
            }
        }
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
}
