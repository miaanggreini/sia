<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengumuman;
use App\Models\Jadwal;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $siswa = optional($request->user()->siswa);
        abort_unless($siswa, 403);

        // Samakan kapitalisasi hari dengan data jadwal: Senin, Selasa, dst.
        $hari = Carbon::now()->locale('id')->isoFormat('dddd');
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

        // Pengumuman dashboard: 1 bulan terakhir, urut dari yang terbaru dipublikasikan
        $pengumuman = Pengumuman::query()
            ->whereIn('status', ['approved', 'publik', 'published'])
            ->whereRaw('COALESCE(published_at, approved_at, updated_at, created_at) >= ?', [
                now()->subMonth(),
            ])
            ->orderByRaw('COALESCE(published_at, approved_at, updated_at, created_at) DESC')
            ->limit(5)
            ->get();

        // Popup: hanya pengumuman terbaru yang dipublikasikan hari ini
        $pengumumanBaru = Pengumuman::query()
            ->whereIn('status', ['approved', 'publik', 'published'])
            ->whereNotNull('published_at')
            ->whereDate('published_at', Carbon::today())
            ->latest('published_at')
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