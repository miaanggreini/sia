<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama_mapel',
        'kelompok',
        'kkm',
        'status',
    ];

    // Relasi ke ROMBEL
    public function rombel(): BelongsToMany
    {
        return $this->belongsToMany(
            Rombel::class,
            'rombel_mapel',
            'mata_pelajaran_id',
            'rombel_id'
        )->withTimestamps();
    }

    // Relasi ke GURU melalui tabel jadwal
    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(
            Guru::class,
            'jadwal',
            'mata_pelajaran_id',
            'guru_id'
        )->distinct();
    }
}