<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulPresensi extends Model
{
    protected $table = 'presensi_ekskul';

    protected $fillable = [
        'ekskul_id',
        'siswa_id',
        'tahun_ajaran_id',
        'pembina_id',
        'tanggal',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class, 'ekskul_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }
}
