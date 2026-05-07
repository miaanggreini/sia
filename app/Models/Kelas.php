<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    // sesuaikan isi fillable dengan kolom yang memang ada
    protected $fillable = ['nama_kelas','tingkat','guru_id','tahun_ajaran','status'];

    // wali kelas (pakai kolom guru_id)
    public function wali()
    {
        return $this->belongsTo(\App\Models\Guru::class, 'guru_id');
    }

    // hitung jumlah baris aktif di pivot (tanpa join siswa)
    public function keanggotaanAktif()
    {
        return $this->hasMany(\App\Models\SiswaKelas::class, 'kelas_id')
                    ->where('aktif', 1);
    }

    // daftar siswa aktif (pivot siswa_id)
    public function anggotaAktif()
    {
        return $this->belongsToMany(\App\Models\Siswa::class, 'siswa_kelas',
                'kelas_id', 'siswa_id')
            ->wherePivot('aktif', 1);
    }
}
