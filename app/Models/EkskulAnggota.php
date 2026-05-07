<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulAnggota extends Model
{
    protected $table = 'anggota_ekskul';

    protected $fillable = [
        'ekskul_id',
        'siswa_id',
        'tahun_ajaran_id',
        'status',
        'tanggal_gabung',
        'tanggal_keluar',
        'nilai_akhir',
        'predikat',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_gabung' => 'date',
        'tanggal_keluar' => 'date',
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

    public function scopeAktif($query)
{
    return $query->where('status', 'aktif');
}

public function scopeTahunAjaran($query, $tahunAjaranId)
{
    return $query->where('tahun_ajaran_id', $tahunAjaranId);
}
}
