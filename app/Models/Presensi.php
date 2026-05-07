<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
        protected $table = 'presensi'; // 👈 ini penting

    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'tanggal',
        'status',
    ];
}