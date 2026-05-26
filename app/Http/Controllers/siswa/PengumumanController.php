<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $data = Pengumuman::query()
            // Siswa hanya boleh melihat pengumuman yang sudah disetujui Kepala Sekolah
            ->where('status', 'approved')

            // Harus sudah memiliki tanggal mulai publikasi
            ->whereNotNull('tanggal_mulai')

            // Sudah masuk tanggal tayang
            ->whereDate('tanggal_mulai', '<=', $today)

            // Masih dalam masa tayang
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhereDate('tanggal_selesai', '>=', $today);
            })

            // Pencarian judul atau isi
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('judul', 'like', "%{$q}%")
                        ->orWhere('isi', 'like', "%{$q}%");
                });
            })

            // Urutkan dari jadwal publikasi terbaru
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        return view('siswa.pengumuman.index', compact('data', 'q'));
    }

    public function show(Pengumuman $pengumuman)
    {
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        $tanggalMulai = $pengumuman->tanggal_mulai
            ? Carbon::parse($pengumuman->tanggal_mulai)->toDateString()
            : null;

        $tanggalSelesai = $pengumuman->tanggal_selesai
            ? Carbon::parse($pengumuman->tanggal_selesai)->toDateString()
            : null;

        $bolehDilihat =
            $pengumuman->status === 'approved'
            && $tanggalMulai
            && $tanggalMulai <= $today
            && (
                !$tanggalSelesai
                || $tanggalSelesai >= $today
            );

        abort_unless($bolehDilihat, 404);

        return view('siswa.pengumuman.show', [
            'p' => $pengumuman,
        ]);
    }
}