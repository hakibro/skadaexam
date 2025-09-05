<?php

namespace App\Http\Controllers\Features\Ruangan;

use App\Http\Controllers\Controller;
use App\Models\SesiTemplate;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SesiTemplateController extends Controller
{
    /**
     * Display a listing of the templates
     */
    public function index()
    {
        $templates = SesiTemplate::withCount([
            'sesiRuangan',
            'sesiRuangan as active_count' => function ($query) {
                $query->where('status', '!=', 'selesai')
                    ->where('status', '!=', 'dibatalkan');
            }
        ])->orderBy('is_active', 'desc')->get();

        return view('features.ruangan.template.index', compact('templates'));
    }

    /**
     * Show the form for creating a new template
     */
    public function create()
    {
        return view('features.ruangan.template.create');
    }

    /**
     * Store a newly created template
     */
    public function store(Request $request)
    {
        // Format time fields to ensure they're in the correct format
        if ($request->has('waktu_mulai')) {
            $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
        }
        if ($request->has('waktu_selesai')) {
            $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
        }

        $request->validate([
            'nama_sesi' => 'required|string|max:191',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'status' => 'required|in:belum_mulai,berlangsung',
            'is_active' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $template = SesiTemplate::create([
                'nama_sesi' => $request->nama_sesi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => $request->status,
                'is_active' => $request->is_active ?? true,
                'pengaturan' => $request->pengaturan ?? []
            ]);

            DB::commit();

            return redirect()->route('ruangan.template.index')
                ->with('success', 'Template sesi berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal membuat template sesi: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified template
     */
    public function show(SesiTemplate $template)
    {
        $template->load(['sesiRuangan' => function ($query) {
            $query->orderBy('tanggal', 'desc')->limit(50);
        }]);

        $roomCounts = DB::table('sesi_ruangan')
            ->select('ruangan_id', DB::raw('count(*) as session_count'))
            ->where('template_id', $template->id)
            ->groupBy('ruangan_id')
            ->get();

        $rooms = Ruangan::whereIn('id', $roomCounts->pluck('ruangan_id'))->get()
            ->map(function ($room) use ($roomCounts) {
                $count = $roomCounts->firstWhere('ruangan_id', $room->id)->session_count ?? 0;
                return [
                    'id' => $room->id,
                    'name' => $room->nama_ruangan,
                    'code' => $room->kode_ruangan,
                    'count' => $count
                ];
            });

        // Get active sessions using this template
        $activeSessions = $template->sesiRuangan()
            ->with('ruangan')
            ->where(function ($query) {
                $query->where('status', 'belum_mulai')
                    ->orWhere('status', 'berlangsung');
            })
            ->orderBy('tanggal')
            ->orderBy('waktu_mulai')
            ->get();

        return view('features.ruangan.template.show', compact('template', 'rooms', 'activeSessions'));
    }

    /**
     * Show the form for editing the template
     */
    public function edit(SesiTemplate $template)
    {
        return view('features.ruangan.template.edit', compact('template'));
    }

    /**
     * Update the specified template
     */
    public function update(Request $request, SesiTemplate $template)
    {
        // Format time fields to ensure they're in the correct format
        if ($request->has('waktu_mulai')) {
            $request->merge(['waktu_mulai' => date('H:i', strtotime($request->waktu_mulai))]);
        }
        if ($request->has('waktu_selesai')) {
            $request->merge(['waktu_selesai' => date('H:i', strtotime($request->waktu_selesai))]);
        }

        $request->validate([
            'nama_sesi' => 'required|string|max:191',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
            'status' => 'required|in:belum_mulai,berlangsung',
            'is_active' => 'boolean',
            'update_existing' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $template->update([
                'nama_sesi' => $request->nama_sesi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'status' => $request->status,
                'is_active' => $request->is_active ?? true,
                'pengaturan' => $request->pengaturan ?? []
            ]);

            // Update existing sessions if requested
            if ($request->update_existing) {
                $count = $template->updateAllSessions();
                $message = "Template sesi berhasil diperbarui dan diterapkan ke $count sesi yang ada";
            } else {
                $message = "Template sesi berhasil diperbarui";
            }

            DB::commit();

            return redirect()->route('ruangan.template.show', $template)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal memperbarui template sesi: ' . $e->getMessage());
        }
    }

    /**
     * Apply template to rooms
     */
    public function apply(Request $request, SesiTemplate $template)
    {
        $request->validate([
            'ruangan_ids' => 'nullable|array',
            'ruangan_ids.*' => 'exists:ruangan,id',
            'date' => 'required|date|after_or_equal:today',
            'apply_all' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $ruanganIds = $request->apply_all ? null : $request->ruangan_ids;
            $count = $template->applyToRuangan($ruanganIds, $request->date);

            DB::commit();

            return redirect()->route('ruangan.template.show', $template)
                ->with('success', "Template sesi berhasil diterapkan ke $count ruangan");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Gagal menerapkan template sesi: ' . $e->getMessage());
        }
    }

    /**
     * Toggle template active status
     */
    public function toggleActive(SesiTemplate $template)
    {
        try {
            $template->update([
                'is_active' => !$template->is_active
            ]);

            $status = $template->is_active ? 'diaktifkan' : 'dinonaktifkan';

            return redirect()->back()
                ->with('success', "Template sesi berhasil $status");
        } catch (\Exception $e) {
            Log::error('Error toggling sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal mengubah status template: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified template
     */
    public function destroy(SesiTemplate $template)
    {
        try {
            // Check if template is being used
            $sessionCount = $template->sesiRuangan()->count();
            if ($sessionCount > 0) {
                return redirect()->back()
                    ->with('error', "Template tidak dapat dihapus karena masih digunakan oleh $sessionCount sesi");
            }

            $template->delete();

            return redirect()->route('ruangan.template.index')
                ->with('success', 'Template sesi berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Error deleting sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus template: ' . $e->getMessage());
        }
    }

    /**
     * Force delete the template and remove template references from all sessions
     */
    public function forceDelete(SesiTemplate $template)
    {
        try {
            DB::beginTransaction();

            // Get count of sessions using this template
            $sessionCount = $template->sesiRuangan()->count();

            // Remove template reference from all sessions
            $template->sesiRuangan()->update(['template_id' => null]);

            // Delete the template
            $template->delete();

            DB::commit();

            return redirect()->route('ruangan.template.index')
                ->with('success', "Template sesi berhasil dihapus paksa dan referensi dihapus dari $sessionCount sesi");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error force deleting sesi template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menghapus paksa template: ' . $e->getMessage());
        }
    }

    /**
     * Show form to apply template to rooms
     */
    public function showApplyForm(SesiTemplate $template)
    {
        // Get all active rooms
        $rooms = Ruangan::where('status', 'aktif')->orderBy('nama_ruangan')->get();

        return view('features.ruangan.template.apply', compact('template', 'rooms'));
    }

    /**
     * Apply template to rooms (alias method that routes to apply)
     */
    public function applyTemplate(Request $request, SesiTemplate $template)
    {
        return $this->apply($request, $template);
    }
}
