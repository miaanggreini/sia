<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiPresensi extends Model
{
    protected $table = 'sesi_presensi';

    protected $fillable = [
        'guru_id',
        'rombel_id',
        'mata_pelajaran_id',
        'mulai_pada',
        'ditutup_pada',
        'status', // 'terbuka' | 'tertutup'
        'masa_aktif_detik',
        'secret_salt',
    ];

    protected $casts = [
        'mulai_pada'   => 'datetime',
        'ditutup_pada' => 'datetime',
    ];

    public function presensi(): HasMany
    {
        return $this->hasMany(PresensiSiswa::class, 'sesi_presensi_id');
    }

    public function rombel(): BelongsTo
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function terbuka(): bool
    {
        return $this->status === 'terbuka';
    }

    public function getRekapAttribute(): array
    {
        $g = $this->presensi->groupBy('status')->map->count();
        $val = fn ($k) => $g[$k] ?? 0;

        return [
            'hadir' => $val('hadir'),
            'izin'  => $val('izin'),
            'sakit' => $val('sakit'),
            'alfa'  => $val('alfa'),
        ];
    }
}