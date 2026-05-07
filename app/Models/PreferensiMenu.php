<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreferensiMenu extends Model
{
    // pakai tabel yang sudah ada
    protected $table = 'pilihan_rombel';

    // kalau tidak butuh guarded khusus:
    protected $guarded = [];

    // relasi ke menu rombel
    public function menuRombel()
    {
        // GANTI 'menu_id' sesuai nama kolom foreign key di tabel pilihan_rombel
        return $this->belongsTo(MenuRombel::class, 'menu_id');
    }

    public function periode()
    {
        return $this->belongsTo(PeriodePemilihanMenu::class, 'periode_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
