<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiswaMenuPeriode extends Model
{
    protected $table = 'pilihan_rombel';

    protected $fillable = [
        'periode_id',
        'siswa_id',
        'pilihan_1_menu_id',
        'pilihan_2_menu_id',
        'pilihan_3_menu_id',
        'menu_diterima_id',
        'status',
        'waktu_pengajuan',
    ];

    public function periode()
    {
        return $this->belongsTo(PeriodePemilihanMenu::class, 'periode_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

       public function pilihan1()
    {
        return $this->belongsTo(MenuRombel::class, 'pilihan_1_menu_id');
    }

    public function pilihan2()
    {
        return $this->belongsTo(MenuRombel::class, 'pilihan_2_menu_id');
    }

    public function pilihan3()
    {
        return $this->belongsTo(MenuRombel::class, 'pilihan_3_menu_id');
    }

    // Menu yang akhirnya DITERIMA siswa
    public function menuDiterima()
    {
        return $this->belongsTo(MenuRombel::class, 'menu_diterima_id');
    }
}
