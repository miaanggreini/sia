<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaKelas extends Model
{
    protected $table = 'siswa_kelas';
    protected $guarded = [];

    // Tambahkan ini:
    protected $casts = [
        // jika kolom di DB type DATE -> pakai 'date', jika DATETIME/TIMESTAMP -> 'datetime'
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function siswa(){ return $this->belongsTo(\App\Models\Siswa::class, 'siswa_id'); }
    public function kelas(){ return $this->belongsTo(\App\Models\Kelas::class, 'kelas_id'); }
}
