<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    protected $table = 'ekskul';

    protected $fillable = [
        'nama',
        'pembina_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
    ];

    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }

    public function anggota()
    {
        return $this->hasMany(EkskulAnggota::class, 'ekskul_id');
    }

    public function presensi()
{
    return $this->hasMany(\App\Models\EkskulPresensi::class, 'ekskul_id');
}

public function anggotaAktif()
{
    return $this->hasMany(EkskulAnggota::class, 'ekskul_id')
        ->where('status', 'aktif');
}
}
