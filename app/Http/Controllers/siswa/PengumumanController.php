<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengumuman;

class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $today = now()->toDateString();

        $data = Pengumuman::query()
            // Siswa hanya boleh melihat pengumuman yang sudah dipublikasikan admin
            ->whereIn('status', ['publik', 'published'])

            // Sudah mulai tayang
            ->whereDate('tanggal_mulai', '<=', $today)

            // Masih dalam masa tayang
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhereDate('tanggal_selesai', '>=', $today);
            })

            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('judul', 'like', "%{$q}%")
                      ->orWhere('isi', 'like', "%{$q}%");
                });
            })

            // Urutkan dari yang paling baru dipublikasikan
            ->orderByDesc('published_at')
            ->orderByDesc('tanggal_mulai')
            ->paginate(12)
            ->withQueryString();

        return view('siswa.pengumuman.index', compact('data', 'q'));
    }

    public function show(Pengumuman $pengumuman)
    {
        $today = now()->toDateString();

        $bolehDilihat =
            in_array($pengumuman->status, ['publik', 'published'], true)
            && $pengumuman->tanggal_mulai
            && $pengumuman->tanggal_mulai->toDateString() <= $today
            && (
                !$pengumuman->tanggal_selesai
                || $pengumuman->tanggal_selesai->toDateString() >= $today
            );

        abort_unless($bolehDilihat, 404);

        return view('siswa.pengumuman.show', [
            'p' => $pengumuman,
        ]);
    }
}