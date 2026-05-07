<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users'; // pastikan tabelnya ini

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function guru(){ return $this->hasOne(\App\Models\Guru::class, 'user_id'); }
    public function siswa(){ return $this->hasOne(\App\Models\Siswa::class, 'user_id'); }

    public function kelas(){ return $this->belongsTo(\App\Models\Kelas::class); }
    public function mapel(){ return $this->belongsTo(\App\Models\MataPelajaran::class, 'mapel_id'); }

}
