<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Pengumuman;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $siswa = optional($request->user()->siswa);
        abort_unless($siswa, 403);

        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        // Samakan kapitalisasi hari dengan data jadwal: Senin, Selasa, dst.
        $hari = $now->copy()->locale('id')->isoFormat('dddd');
        $hari = ucfirst(strtolower($hari));

        // Jadwal hari ini berdasarkan rombel aktif siswa
        $jadwalHariIni = Jadwal::with(['mataPelajaran', 'guru', 'rombel'])
            ->whereHas('rombel.siswa', function ($q) use ($siswa) {
                $q->where('siswa.id', $siswa->id)
                    ->where('siswa_rombel.aktif', 1);
            })
            ->where('hari', $hari)
            ->orderBy('jam_mulai')
            ->get();

        /*
         * Pengumuman dashboard:
         * - hanya yang sudah disetujui kepala sekolah
         * - sudah masuk jadwal publikasi
         * - belum melewati tanggal selesai
         * - hanya pengumuman dengan tanggal mulai pada bulan berjalan
         */
        $pengumuman = Pengumuman::query()
            ->where('status', 'approved')
            ->whereNotNull('tanggal_mulai')
            ->whereDate('tanggal_mulai', '<=', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhereDate('tanggal_selesai', '>=', $today);
            })
            ->whereYear('tanggal_mulai', $now->year)
            ->whereMonth('tanggal_mulai', $now->month)
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        /*
         * Popup:
         * Karena alur baru tidak memakai published_at lagi,
         * popup diambil dari pengumuman approved yang mulai tayang hari ini.
         */
        $pengumumanBaru = Pengumuman::query()
            ->where('status', 'approved')
            ->whereNotNull('tanggal_mulai')
            ->whereDate('tanggal_mulai', $today)
            ->where(function ($query) use ($today) {
                $query->whereNull('tanggal_selesai')
                    ->orWhereDate('tanggal_selesai', '>=', $today);
            })
            ->orderByDesc('approved_at')
            ->orderByDesc('created_at')
            ->first();

        // Supaya popup tidak muncul terus saat refresh / balik dashboard
        if ($pengumumanBaru) {
            $sessionKey = 'popup_pengumuman_dilihat_' . auth()->id();
            $lastSeenId = session($sessionKey);

            if ((int) $lastSeenId === (int) $pengumumanBaru->id) {
                $pengumumanBaru = null;
            } else {
                session([$sessionKey => $pengumumanBaru->id]);
            }
        }

        return view('siswa.index', compact(
            'siswa',
            'jadwalHariIni',
            'pengumuman',
            'pengumumanBaru'
        ));
    }
}