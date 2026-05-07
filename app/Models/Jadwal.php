<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Rombel;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Guru;
use App\Models\Siswa;

class Jadwal extends Model
{
    protected $table = 'jadwal';

    /**
     * Gunakan mata_pelajaran_id sebagai acuan utama.
     * mapel_id dipertahankan untuk kompatibilitas kode lama
     * jika masih ada bagian sistem yang mengirim field itu.
     */
    protected $fillable = [
        'rombel_id',
        'mata_pelajaran_id',
        'mapel_id', // legacy compatibility
        'guru_id',
        'hari',
        'slot_kode',
        'durasi_jp',
        'jam_mulai',
        'jam_selesai',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function rombel()
    {
        return $this->belongsTo(Rombel::class, 'rombel_id');
    }

    /**
     * Relasi utama yang benar.
     */
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    /**
     * Alias lama untuk backward compatibility.
     * Jangan dihapus agar fitur lama yang memanggil mapel() tetap aman.
     */
    public function mapel()
    {
        return $this->mataPelajaran();
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class, 'jadwal_id');
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function sumberSiswaQuery()
    {
        return Siswa::select('siswa.*')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $this->rombel_id)
            ->where('sr.aktif', 1);
    }

    /**
     * Accessor legacy:
     * jika ada kode lama yang membaca $jadwal->mapel_id,
     * arahkan ke mata_pelajaran_id bila mapel_id fisik tidak digunakan.
     */
    public function getMapelIdAttribute()
    {
        return $this->attributes['mapel_id'] ?? $this->attributes['mata_pelajaran_id'] ?? null;
    }
}