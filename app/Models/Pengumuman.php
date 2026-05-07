<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pengumuman extends Model
{
    protected $table = 'pengumuman';

protected $fillable = [
    'judul','isi',
    'tanggal_mulai','tanggal_selesai',
    'status','kategori', 'approved_at','alasan_tolak',
    'published_at','created_by','approved_by','rejected_by',
];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
        'published_at'    => 'datetime',
        'approved_at'     => 'datetime',
    ];

    // ===== KONSTANTA STATUS =====
    public const STATUS_DRAFT     = 'draft';
    public const STATUS_PENDING   = 'pending';
    public const STATUS_APPROVED  = 'approved';
    public const STATUS_REJECTED  = 'rejected';
    public const STATUS_PUBLIK    = 'publik';

    // ===== SCOPE UNTUK SISWA =====
    public function scopeAktif($query)
    {
        $today = now()->toDateString();
        return $query->where('status', self::STATUS_PUBLIK)
            ->where(function($q) use ($today) {
                $q->whereNull('tanggal_mulai')->orWhere('tanggal_mulai','<=',$today);
            })
            ->where(function($q) use ($today) {
                $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai','>=',$today);
            });
    }

    public function creator()
{
    return $this->belongsTo(\App\Models\User::class, 'created_by');
}

public function approver()
{
    return $this->belongsTo(\App\Models\User::class, 'approved_by');
}

public function rejector()
{
    return $this->belongsTo(\App\Models\User::class, 'rejected_by');
}
}
