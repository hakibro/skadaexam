<?php

namespace App\Imports;

use App\Models\Guru;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class GuruImport implements ToCollection, WithHeadingRow
{
    protected $errors = [];
    protected $successCount = 0;
    protected $errorCount = 0;

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $rowData = $this->prepareRowData($row);

            // Validate each row
            $validator = $this->validateRow($rowData);

            if ($validator->fails()) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $this->getCurrentRowNumber($collection, $row),
                    'errors' => $validator->errors()->all(),
                    'data' => $rowData
                ];
                continue;
            }

            try {
                // Create guru if validation passes
                Guru::create([
                    'nama' => $rowData['nama'],
                    'nip' => $rowData['nip'],
                    'email' => $rowData['email'],
                    'password' => Hash::make($rowData['password']),
                    'role' => $rowData['role'],
                ]);

                $this->successCount++;
            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = [
                    'row' => $this->getCurrentRowNumber($collection, $row),
                    'errors' => ['Database error: ' . $e->getMessage()],
                    'data' => $rowData
                ];
            }
        }
    }

    protected function prepareRowData($row)
    {
        return [
            'nama' => trim($row['nama'] ?? ''),
            'nip' => trim($row['nip'] ?? ''),
            'email' => trim($row['email'] ?? ''),
            'password' => trim($row['password'] ?? ''),
            'role' => strtolower(trim($row['role'] ?? 'guru')),
        ];
    }

    protected function validateRow($data)
    {
        return Validator::make($data, [
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|unique:guru,nip',
            'email' => 'required|email|unique:guru,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:guru,data,naskah,pengawas,koordinator,ruangan',
        ]);
    }

    protected function getCurrentRowNumber($collection, $currentRow)
    {
        return $collection->search(function ($item) use ($currentRow) {
            return $item === $currentRow;
        }) + 2; // +2 because of 1-based indexing and header row
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }
}
