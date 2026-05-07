<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRombel extends Model
{
    protected $table = 'menu_rombel';

    protected $fillable = [
        'nama',
        'tahun_ajaran_id',
        'tingkat',
        'kapasitas_total',
        'aktif',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function mapel()
    {
        return $this->belongsToMany(
            MataPelajaran::class,
            'menu_rombel_mapel',
            'menu_rombel_id',
            'mata_pelajaran_id'
        );
    }

    // === BARU: semua siswa yang ditempatkan ke menu ini ===
    public function penempatan()
    {
        // foreign key = menu_diterima_id di tabel pilihan_rombel
        return $this->hasMany(SiswaMenuPeriode::class, 'menu_diterima_id');
    }

    public function siswaMenuPeriode()
{
    return $this->hasMany(\App\Models\SiswaMenuPeriode::class, 'menu_diterima_id');
}
}
