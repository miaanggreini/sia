<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PeriodePemilihanMenu extends Model
{
    protected $table = 'periode_pemilihan';
    protected $fillable = ['nama_periode','tahun_ajaran_id','tingkat','tanggal_mulai','tanggal_selesai','status'];
    protected $casts = ['tanggal_mulai'=>'datetime','tanggal_selesai'=>'datetime'];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function scopeAktifDibuka($q)
    {
        return $q->where('status','dibuka');
    }
}
