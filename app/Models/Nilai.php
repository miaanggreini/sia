<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Jadwal;
use App\Models\Siswa;
use App\Models\Rombel;
use App\Models\MataPelajaran;
use App\Models\Guru;

class Nilai extends Model
{
    protected $table = 'nilai';

    protected $guarded = [];

    protected $casts = [
        'lm1_tp1'    => 'float',
        'lm1_tp2'    => 'float',
        'lm1_tp3'    => 'float',
        'lm1_tp4'    => 'float',
        'lm1_nilai'  => 'float',

        'lm2_tp1'    => 'float',
        'lm2_tp2'    => 'float',
        'lm2_tp3'    => 'float',
        'lm2_tp4'    => 'float',
        'lm2_nilai'  => 'float',

        'lm3_tp1'    => 'float',
        'lm3_tp2'    => 'float',
        'lm3_tp3'    => 'float',
        'lm3_tp4'    => 'float',
        'lm3_nilai'  => 'float',

        'lm4_tp1'    => 'float',
        'lm4_tp2'    => 'float',
        'lm4_tp3'    => 'float',
        'lm4_tp4'    => 'float',
        'lm4_nilai'  => 'float',

        'nilai_akhir' => 'float',
        'finalized_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    /**
     * Relasi langsung ini dipertahankan demi kompatibilitas.
     * Untuk fitur baru, utamakan ambil rombel dari jadwal->rombel.
     */
    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    /**
     * Legacy compatibility:
     * jika kolom mapel_id masih ada dan dipakai fitur lama, tetap aman.
     * Namun untuk integrasi fitur baru, utamakan jadwal->mataPelajaran.
     */
    public function mapel()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id');
    }

    /**
     * Relasi langsung dipertahankan demi kompatibilitas.
     * Untuk fitur baru, utamakan jadwal->guru.
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR / HELPER PER LM
    |--------------------------------------------------------------------------
    */

    public function getLm1CalculatedAttribute(): ?float
    {
        return $this->calculateAverage([
            $this->lm1_tp1,
            $this->lm1_tp2,
            $this->lm1_tp3,
            $this->lm1_tp4,
        ]);
    }

    public function getLm2CalculatedAttribute(): ?float
    {
        return $this->calculateAverage([
            $this->lm2_tp1,
            $this->lm2_tp2,
            $this->lm2_tp3,
            $this->lm2_tp4,
        ]);
    }

    public function getLm3CalculatedAttribute(): ?float
    {
        return $this->calculateAverage([
            $this->lm3_tp1,
            $this->lm3_tp2,
            $this->lm3_tp3,
            $this->lm3_tp4,
        ]);
    }

    public function getLm4CalculatedAttribute(): ?float
    {
        return $this->calculateAverage([
            $this->lm4_tp1,
            $this->lm4_tp2,
            $this->lm4_tp3,
            $this->lm4_tp4,
        ]);
    }

    public function getNilaiAkhirCalculatedAttribute(): ?float
    {
        return $this->calculateAverage([
            $this->lm1_nilai ?? $this->lm1_calculated,
            $this->lm2_nilai ?? $this->lm2_calculated,
            $this->lm3_nilai ?? $this->lm3_calculated,
            $this->lm4_nilai ?? $this->lm4_calculated,
        ]);
    }

    /**
     * Sinkronkan hasil hitung LM dan nilai akhir ke kolom tabel.
     */
    public function syncNilai(): void
    {
        $this->lm1_nilai = $this->lm1_calculated;
        $this->lm2_nilai = $this->lm2_calculated;
        $this->lm3_nilai = $this->lm3_calculated;
        $this->lm4_nilai = $this->lm4_calculated;
        $this->nilai_akhir = $this->nilai_akhir_calculated;
    }

    /**
     * Tentukan status berdasarkan KKM mapel.
     */
    public function syncStatus(): void
    {
        $kkm = $this->getKkmResolvedAttribute();

        if ($kkm === null || $this->nilai_akhir === null) {
            $this->status = 'tidak_tuntas';
            return;
        }

        $this->status = $this->nilai_akhir >= $kkm ? 'tuntas' : 'tidak_tuntas';
    }

    protected function calculateAverage(array $values): ?float
    {
        $filtered = collect($values)->filter(fn ($v) => $v !== null);

        return $filtered->isEmpty() ? null : round($filtered->avg(), 2);
    }

    /**
     * Ambil KKM dari mapel yang resolved.
     */
    public function getKkmResolvedAttribute(): ?float
    {
        $mapel = $this->mapel_resolved;

        return $mapel?->kkm !== null ? (float) $mapel->kkm : null;
    }

    /**
     * Gunakan ini di fitur/API baru untuk mapel yang benar.
     */
    public function getMapelResolvedAttribute()
    {
        if ($this->relationLoaded('jadwal') && $this->jadwal && $this->jadwal->relationLoaded('mataPelajaran')) {
            return $this->jadwal->mataPelajaran;
        }

        if ($this->jadwal && method_exists($this->jadwal, 'mataPelajaran')) {
            return $this->jadwal->mataPelajaran;
        }

        return $this->mapel;
    }

    /**
     * Gunakan ini di fitur/API baru untuk guru yang benar.
     */
    public function getGuruResolvedAttribute()
    {
        if ($this->relationLoaded('jadwal') && $this->jadwal && $this->jadwal->relationLoaded('guru')) {
            return $this->jadwal->guru;
        }

        if ($this->jadwal && method_exists($this->jadwal, 'guru')) {
            return $this->jadwal->guru;
        }

        return $this->guru;
    }

    /**
     * Gunakan ini di fitur/API baru untuk rombel yang benar.
     */
    public function getRombelResolvedAttribute()
    {
        if ($this->relationLoaded('jadwal') && $this->jadwal && $this->jadwal->relationLoaded('rombel')) {
            return $this->jadwal->rombel;
        }

        if ($this->jadwal && method_exists($this->jadwal, 'rombel')) {
            return $this->jadwal->rombel;
        }

        return $this->rombel;
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPE
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($query)
    {
        return $query->where('status_penilaian', 'draft');
    }

    public function scopeFinal($query)
    {
        return $query->where('status_penilaian', 'final');
    }

    public function scopeBySemester($query, string $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeByLingkupMateri($query, int $lm)
    {
        return match ($lm) {
            1 => $query->whereNotNull('lm1_nilai'),
            2 => $query->whereNotNull('lm2_nilai'),
            3 => $query->whereNotNull('lm3_nilai'),
            4 => $query->whereNotNull('lm4_nilai'),
            default => $query,
        };
    }
}