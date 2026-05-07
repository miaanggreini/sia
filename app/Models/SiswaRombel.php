<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaRombel extends Model
{
    protected $table = 'siswa_rombel';

    protected $fillable = [
        'siswa_id',
        'rombel_id',
        'tahun_ajaran_id',
        'tahun_ajaran', // legacy compatibility
        'aktif',
        'status_pilihan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    /**
     * Helper aman untuk fitur baru/lama.
     */
    public function getTahunAjaranResolvedAttribute()
    {
        return $this->tahun_ajaran_id ?? $this->tahun_ajaran ?? null;
    }
}