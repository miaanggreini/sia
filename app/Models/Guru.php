<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'nama',
        'jk',
        'tempat_lahir',
        'tanggal_lahir',
        'status_kepegawaian',
        'no_hp',
        'email',
        'foto',
        'status',
        'alamat',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function jadwal()
    {
        return $this->hasMany(\App\Models\Jadwal::class, 'guru_id');
    }

    public function sesiPresensi()
    {
        return $this->hasMany(\App\Models\SesiPresensi::class, 'guru_id');
    }

    public function presensiEkskul()
    {
        return $this->hasMany(\App\Models\PresensiEkskul::class, 'pembina_id');
    }

    public function rombel()
    {
        return $this->hasMany(\App\Models\Rombel::class, 'guru_id');
    }

    public function getJkLabelAttribute(): string
    {
        return $this->jk === 'L' ? 'Laki-laki' : ($this->jk === 'P' ? 'Perempuan' : '-');
    }

    public function getTanggalLahirFormattedAttribute(): ?string
    {
        return $this->tanggal_lahir?->translatedFormat('d-m-Y');
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && Storage::disk('public')->exists($this->foto)) {
            return asset('storage/' . $this->foto);
        }

        return asset('images/no-photo.png');
    }
}