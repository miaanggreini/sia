<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Siswa extends Model
{
    protected $table = 'siswa';

    /**
     * Kolom yang dapat diisi (fillable)
     * Lengkap sesuai struktur tabel SIA.
     */
    protected $fillable = [

        // Akun
        'user_id',

        // Identitas dasar
        'nis',
        'nisn',
        'nama',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'foto',

        // Kontak & Alamat
        'alamat',
        'email',
        'no_hp',

        // Informasi sekolah
        'status',
        'tahun_masuk',
        'jalur_penerimaan',
        'kebutuhan_khusus',

        // Data Ayah
        'nama_ayah',
        'nik_ayah',
        'status_ayah',
        'pekerjaan_ayah',
        'pendidikan_ayah',
        'no_hp_ayah',
        'alamat_ayah',

        // Data Ibu
        'nama_ibu',
        'nik_ibu',
        'status_ibu',
        'pekerjaan_ibu',
        'pendidikan_ibu',
        'no_hp_ibu',
        'alamat_ibu',
    ];

    /**
     * Casting otomatis
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
        'tahun_masuk'   => 'integer',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    /**
     * Relasi ke tabel user.
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Riwayat anggota rombel (pivot siswa_rombel)
     */
    public function keanggotaanRombel()
    {
        return $this->hasMany(\App\Models\SiswaRombel::class, 'siswa_id');
    }

    /**
     * Rombel aktif berdasarkan pivot siswa_rombel (pivot.aktif = 1)
     */
    public function rombelAktif()
    {
        return $this->belongsToMany(
            \App\Models\Rombel::class,
            'siswa_rombel',
            'siswa_id',
            'rombel_id'
        )->withPivot(['tahun_ajaran_id', 'aktif', 'status_pilihan'])
         ->wherePivot('aktif', 1);
    }

    /**
     * Accessor agar bisa dipanggil langsung:
     * $siswa->rombel
     *
     * Mengembalikan satu instance Rombel aktif (atau null jika tidak ada).
     */
    public function getRombelAttribute()
    {
        return $this->rombelAktif()->first();
    }

    /**
     * Label jenis kelamin (L/P → Laki-laki/Perempuan)
     */
    public function getJkLabelAttribute(): string
    {
        $val = $this->jenis_kelamin ?? $this->jk ?? null;

        return match (strtoupper((string) $val)) {
            'L' => 'Laki-laki',
            'P' => 'Perempuan',
            default => '-',
        };
    }

    /**
     * Accessor URL foto siswa
     */
/**
 * Accessor URL foto siswa
 */
public function getFotoUrlAttribute(): string
{
    if (empty($this->foto)) {
        return asset('images/no-photo.png');
    }

    $foto = ltrim($this->foto, '/');

    // Jika sudah berupa URL penuh
    if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
        return $foto;
    }

    // Jika database terlanjur menyimpan "storage/..."
    if (str_starts_with($foto, 'storage/')) {
        return asset($foto);
    }

    // Jika database terlanjur menyimpan "public/..."
    if (str_starts_with($foto, 'public/')) {
        $foto = substr($foto, strlen('public/'));
    }

    // Jika database terlanjur menyimpan "storage/app/public/..."
    if (str_starts_with($foto, 'storage/app/public/')) {
        $foto = substr($foto, strlen('storage/app/public/'));
    }

    // Format normal database: foto_siswa/nama-file.jpg
    return asset('storage/' . $foto);
}
}