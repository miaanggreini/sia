<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiSiswa extends Model
{
    protected $table = 'presensi';

    protected $fillable = [
        'sesi_presensi_id',
        'siswa_id',
        'status', // 'hadir','izin','sakit','alfa'
        'dipindai_pada',
        'ip_address',
        'device_fingerprint',
        'dibuat_oleh',
        'diperbarui_oleh',
    ];

    protected $casts = [
        'dipindai_pada' => 'datetime',
    ];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(SesiPresensi::class, 'sesi_presensi_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}