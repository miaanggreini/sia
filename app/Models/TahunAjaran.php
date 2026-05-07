<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama_tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'status'
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    /**
     * Mengembalikan record tahun ajaran yang statusnya aktif.
     */
    public static function aktif(): ?self
    {
        return static::where('status', 'aktif')->first();
    }

    /**
     * Helper lama: mengembalikan kode / nama_tahun dari tahun ajaran aktif.
     * Dipakai di beberapa controller (misal GradeMonitorController).
     */
    public static function aktifKode(): ?string
    {
        $ta = static::aktif();   // pakai helper di atas
        return $ta?->nama_tahun; // null-safe: kalau nggak ada, hasilnya null
    }

    /** Teks format dd-mm-yyyy */
    public function getMulaiFormattedAttribute()
    {
        return $this->tanggal_mulai
            ? Carbon::parse($this->tanggal_mulai)->format('d-m-Y')
            : '-';
    }

    public function getSelesaiFormattedAttribute()
    {
        return $this->tanggal_selesai
            ? Carbon::parse($this->tanggal_selesai)->format('d-m-Y')
            : '-';
    }
}
