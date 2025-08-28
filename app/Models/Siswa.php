<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'idyayasan', // This will store idperson from SISDA
        'nama',
        'nama_from_sisda',
        'email',
        'password',
        'kelas',
        'rekomendasi',
        'catatan_rekomendasi',
        'status_pembayaran',
        'payment_api_cache',
        'payment_last_check',
        'payment_total_credit',
        'payment_total_debit',
        'payment_paid_items',
        'payment_unpaid_items',
        'sync_status',
        'sync_error',
        'user_id'
    ];

    protected $casts = [
        'payment_last_check' => 'datetime',
        'payment_api_cache' => 'array', // Auto JSON decode/encode
        'payment_total_credit' => 'decimal:2',
        'payment_total_debit' => 'decimal:2',
        'payment_paid_items' => 'integer',
        'payment_unpaid_items' => 'integer',
    ];

    protected $hidden = [
        'password',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get nama lengkap attribute - ADD THIS
     */
    public function getNamaLengkapAttribute()
    {
        return $this->nama ?? $this->idyayasan ?? 'Unknown';
    }

    /**
     * Get periode from SISDA data - ADD THIS  
     */
    public function getPeriodeAttribute()
    {
        if ($this->sisda_data && isset($this->sisda_data['PERIODE'])) {
            return $this->sisda_data['PERIODE'];
        }

        return $this->attributes['periode'] ?? null;
    }

    /**
     * Get SISDA URL for payment - ADD THIS
     */
    public function getSisdaUrlAttribute()
    {
        return "https://sisda.daruttaqwa.or.id/payment/{$this->idyayasan}";
    }

    /**
     * Get formatted rekomendasi attribute - ADD THIS
     */
    public function getFormattedRekomendasiAttribute()
    {
        switch ($this->rekomendasi) {
            case 'ya':
                return [
                    'text' => 'Ya',
                    'class' => 'bg-green-100 text-green-800',
                    'icon' => 'fa-check-circle'
                ];
            case 'tidak':
                return [
                    'text' => 'Tidak',
                    'class' => 'bg-red-100 text-red-800',
                    'icon' => 'fa-times-circle'
                ];
            default:
                return [
                    'text' => 'Unknown',
                    'class' => 'bg-gray-100 text-gray-800',
                    'icon' => 'fa-question-circle'
                ];
        }
    }

    /**
     * Generate email from nama - Static method
     */
    public static function generateEmailFromNama($nama)
    {
        if (empty($nama)) return null;

        $email = strtolower($nama);
        $email = str_replace(' ', '.', $email);
        $email = preg_replace('/[^a-z0-9.]/', '', $email);
        $email .= '@skada.test';

        return $email;
    }

    /**
     * Get SISDA data details
     */
    public function getSisdaDetailsAttribute()
    {
        if (!$this->sisda_data) return null;

        return [
            'gender' => $this->sisda_data['gender'] ?? null,
            'lahirtempat' => $this->sisda_data['lahirtempat'] ?? null,
            'lahirtanggal' => $this->sisda_data['lahirtanggal'] ?? null,
            'phone' => $this->sisda_data['phone'] ?? null,
            'unit_formal' => $this->sisda_data['UnitFormal'] ?? null,
            'kelas_formal' => $this->sisda_data['KelasFormal'] ?? null,
            'asrama_pondok' => $this->sisda_data['AsramaPondok'] ?? null,
            'kelas_pondok' => $this->sisda_data['KelasPondok'] ?? null,
            'tingkat_diniyah' => $this->sisda_data['TingkatDiniyah'] ?? null,
            'kelas_diniyah' => $this->sisda_data['KelasDiniyah'] ?? null,
        ];
    }

    /**
     * Get formatted payment status for display
     */
    public function getFormattedPaymentStatusAttribute()
    {
        if (!$this->status_pembayaran) {
            return [
                'text' => 'Unknown',
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'fa-question-circle'
            ];
        }

        switch (strtolower($this->status_pembayaran)) {
            case 'lunas':
                return [
                    'text' => 'Lunas',
                    'class' => 'bg-green-100 text-green-800',
                    'icon' => 'fa-check-circle'
                ];
            case 'belum lunas':
                return [
                    'text' => 'Belum Lunas',
                    'class' => 'bg-red-100 text-red-800',
                    'icon' => 'fa-times-circle'
                ];
            case 'cicilan':
                return [
                    'text' => 'Cicilan',
                    'class' => 'bg-yellow-100 text-yellow-800',
                    'icon' => 'fa-clock'
                ];
            default:
                return [
                    'text' => $this->status_pembayaran,
                    'class' => 'bg-gray-100 text-gray-800',
                    'icon' => 'fa-info-circle'
                ];
        }
    }

    /**
     * Check if student can be recommended
     */
    public function canBeRecommended()
    {
        return $this->status_pembayaran === 'Lunas';
    }

    /**
     * Get formatted sync status
     */
    public function getFormattedSyncStatusAttribute()
    {
        if (!Schema::hasColumn('siswa', 'sync_status')) {
            return [
                'text' => 'Unknown',
                'class' => 'bg-gray-100 text-gray-800',
                'icon' => 'fa-question-circle'
            ];
        }

        switch ($this->sync_status) {
            case 'synced':
                return [
                    'text' => 'Synced',
                    'class' => 'bg-green-100 text-green-800',
                    'icon' => 'fa-check-circle'
                ];
            case 'failed':
                return [
                    'text' => 'Failed',
                    'class' => 'bg-red-100 text-red-800',
                    'icon' => 'fa-times-circle'
                ];
            case 'pending':
            default:
                return [
                    'text' => 'Pending',
                    'class' => 'bg-yellow-100 text-yellow-800',
                    'icon' => 'fa-clock'
                ];
        }
    }

    /**
     * Get display name with sync indicator
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->nama ?: $this->idyayasan;

        if ($this->nama_from_sisda) {
            return $name . ' ✓';  // Checkmark to indicate synced from SISDA
        }

        return $name;
    }

    /**
     * Update student data from SISDA API
     */
    public function updateFromSisdaData($sisdaData)
    {
        $updated = false;
        $originalName = $this->nama;

        // Update name if available and different
        if (isset($sisdaData['nama']) && !empty($sisdaData['nama'])) {
            $newName = trim($sisdaData['nama']);
            if ($this->nama !== $newName) {
                $this->nama = $newName;
                $this->nama_from_sisda = true;
                $updated = true;
            }
        }

        // Update other fields if available
        if (isset($sisdaData['kelas']) && !empty($sisdaData['kelas'])) {
            $newKelas = trim($sisdaData['kelas']);
            if ($this->kelas !== $newKelas) {
                $this->kelas = $newKelas;
                $updated = true;
            }
        }

        // Store raw SISDA data for reference
        $this->sisda_data_cache = $sisdaData;
        $this->sisda_last_sync = now();

        if ($updated) {
            $this->sync_status = 'synced';
            $this->sync_error = null;
        }

        return [
            'updated' => $updated,
            'name_changed' => $originalName !== $this->nama,
            'original_name' => $originalName,
            'new_name' => $this->nama
        ];
    }
}
