<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rombel extends Model
{
    protected $table = 'rombel';

    protected $fillable = [
        'tingkat',
        'guru_id',
        'menu_rombel_id',
        'ruang_kelas_id',
        'nama_rombel',
        'tahun_ajaran_id',
        'aktif',
        'kapasitas',
    ];

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function menuRombel(): BelongsTo
    {
        return $this->belongsTo(MenuRombel::class, 'menu_rombel_id');
    }

    public function ruangKelas(): BelongsTo
    {
        return $this->belongsTo(RuangKelas::class, 'ruang_kelas_id');
    }

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function mapel(): BelongsToMany
    {
        return $this->belongsToMany(
            MataPelajaran::class,
            'rombel_mapel',
            'rombel_id',
            'mata_pelajaran_id'
        )->withTimestamps();
    }

    public function mataPelajaran(): BelongsToMany
    {
        return $this->mapel();
    }

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(
            Siswa::class,
            'siswa_rombel',
            'rombel_id',
            'siswa_id'
        )
        ->withPivot(['tahun_ajaran_id', 'aktif', 'status_pilihan'])
        ->withTimestamps();
    }

    public function siswaAktif(): BelongsToMany
    {
        return $this->siswa()->wherePivot('aktif', 1);
    }

    public function anggota(): BelongsToMany
    {
        return $this->siswaAktif();
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class, 'rombel_id');
    }

    public function getNamaMapelListAttribute(): string
    {
        $mapels = $this->mataPelajaran->pluck('nama_mapel');

        if ($mapels->isEmpty() && $this->menuRombel) {
            $mapels = $this->menuRombel->mapel->pluck('nama_mapel');
        }

        return $mapels->filter()->values()->join(', ');
    }
}