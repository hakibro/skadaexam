<?php

namespace App\Http\Controllers\Features\Naskah;

use App\Http\Controllers\Controller;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MapelRecoveryController extends Controller
{
    /**
     * Display a listing of soft deleted mapel records.
     */
    public function index()
    {
        $trashedMapels = Mapel::onlyTrashed()->get();

        return view('features.naskah.mapel.trashed', [
            'trashedMapels' => $trashedMapels
        ]);
    }

    /**
     * Restore a soft-deleted mapel record
     */
    public function restore($id)
    {
        $mapel = Mapel::withTrashed()->findOrFail($id);
        $mapel->restore();

        return redirect()->route('naskah.mapel.trashed')
            ->with('success', "Mata pelajaran '{$mapel->nama_mapel}' berhasil dipulihkan");
    }

    /**
     * Permanently delete a soft-deleted mapel record
     */
    public function forceDelete($id)
    {
        try {
            // Start transaction to ensure data consistency
            DB::beginTransaction();

            $mapel = Mapel::withTrashed()->findOrFail($id);
            $mapelName = $mapel->nama_mapel;

            Log::info("Starting permanent deletion of mapel ID {$mapel->id} ({$mapelName})");

            // Check for JadwalUjian relationships
            if ($mapel->jadwalUjians()->exists()) {
                $jadwalCount = $mapel->jadwalUjians()->count();
                Log::info("Mapel ID {$mapel->id} has {$jadwalCount} jadwal relationships - detaching");

                // Set the mapel_id to NULL in jadwal_ujian table (breaking the relationship)
                $mapel->jadwalUjians()->update(['mapel_id' => null]);
            }

            // Check for related bank soal
            if ($mapel->bankSoals()->exists()) {
                $bankCount = $mapel->bankSoals()->count();
                Log::info("Mapel ID {$mapel->id} has {$bankCount} bank soal relationships - processing");

                // Delete related bank soals and their soals
                foreach ($mapel->bankSoals as $bankSoal) {
                    // Delete soals related to this bank soal
                    if ($bankSoal->soals()->exists()) {
                        $soalCount = $bankSoal->soals()->count();
                        Log::info("- Bank Soal ID {$bankSoal->id} has {$soalCount} soal records - deleting");
                        $bankSoal->soals()->delete();
                    }

                    // Check if this bank soal is used in jadwal ujian
                    if ($bankSoal->jadwalUjians()->exists()) {
                        $jadwalCount = $bankSoal->jadwalUjians()->count();
                        Log::info("- Bank Soal ID {$bankSoal->id} has {$jadwalCount} jadwal records - detaching");
                        $bankSoal->jadwalUjians()->update(['bank_soal_id' => null]);
                    }

                    // Now delete the bank soal
                    $bankSoal->delete();
                }
            }

            // Now we can safely force delete the mapel
            $mapel->forceDelete();
            Log::info("Successfully force deleted Mapel ID {$mapel->id}");

            DB::commit();

            return redirect()->route('naskah.mapel.trashed')
                ->with('success', "Mata pelajaran '{$mapelName}' berhasil dihapus permanen");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during permanent deletion of mapel ID ' . $id . ': ' . $e->getMessage());

            return redirect()->route('naskah.mapel.trashed')
                ->with('error', "Gagal menghapus permanen mata pelajaran: " . $e->getMessage());
        }
    }

    /**
     * Restore all soft-deleted mapel records
     */
    public function restoreAll()
    {
        $count = Mapel::onlyTrashed()->count();
        Mapel::onlyTrashed()->restore();

        return redirect()->route('naskah.mapel.trashed')
            ->with('success', "{$count} mata pelajaran berhasil dipulihkan");
    }

    /**
     * Permanently delete all soft-deleted mapel records
     */
    public function forceDeleteAll()
    {
        try {
            // Start transaction to ensure data consistency
            DB::beginTransaction();

            $count = Mapel::onlyTrashed()->count();

            if ($count === 0) {
                return redirect()->route('naskah.mapel.index')
                    ->with('info', "Tidak ada mata pelajaran terhapus yang perlu dihapus permanen.");
            }

            // Get all soft-deleted mapels
            $mapels = Mapel::onlyTrashed()->get();

            // Log the process
            Log::info('Starting permanent deletion of ' . $count . ' soft-deleted mapel records');

            // Process each mapel record
            foreach ($mapels as $mapel) {
                // Check for JadwalUjian relationships
                if ($mapel->jadwalUjians()->exists()) {
                    $jadwalCount = $mapel->jadwalUjians()->count();
                    Log::info("Mapel ID {$mapel->id} has {$jadwalCount} jadwal relationships - deleting or detaching");

                    // Set the mapel_id to NULL in jadwal_ujian table (breaking the relationship)
                    $mapel->jadwalUjians()->update(['mapel_id' => null]);
                }

                // Check for BankSoal relationships
                if ($mapel->bankSoals()->exists()) {
                    $bankCount = $mapel->bankSoals()->count();
                    Log::info("Mapel ID {$mapel->id} has {$bankCount} bank soal relationships - processing");

                    // For each bank soal, delete related soals first, then the bank soal itself
                    foreach ($mapel->bankSoals as $bankSoal) {
                        // Delete soals related to this bank soal
                        if ($bankSoal->soals()->exists()) {
                            $soalCount = $bankSoal->soals()->count();
                            Log::info("- Bank Soal ID {$bankSoal->id} has {$soalCount} soal records - deleting");
                            $bankSoal->soals()->delete();
                        }

                        // Check if this bank soal is used in jadwal ujian
                        if ($bankSoal->jadwalUjians()->exists()) {
                            $jadwalCount = $bankSoal->jadwalUjians()->count();
                            Log::info("- Bank Soal ID {$bankSoal->id} has {$jadwalCount} jadwal records - detaching");
                            $bankSoal->jadwalUjians()->update(['bank_soal_id' => null]);
                        }

                        // Now delete the bank soal
                        $bankSoal->delete();
                    }
                }

                // Now we can safely force delete the mapel
                $mapel->forceDelete();
                Log::info("Successfully force deleted Mapel ID {$mapel->id}");
            }

            DB::commit();

            return redirect()->route('naskah.mapel.index')
                ->with('success', "{$count} mata pelajaran terhapus berhasil dihapus permanen");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during permanent deletion of mapel records: ' . $e->getMessage());

            return redirect()->route('naskah.mapel.trashed')
                ->with('error', "Gagal menghapus permanen mata pelajaran: " . $e->getMessage());
        }
    }
}
