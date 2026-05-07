<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use App\Models\Pengumuman;
use App\Models\PresensiSiswa;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today('Asia/Jakarta');
        $todayStr = $today->toDateString();

        // ===== TAHUN AJARAN AKTIF =====
        $taAktif = TahunAjaran::aktif();

        // ===== DATA UMUM =====
        $totalRombel = $taAktif
            ? Rombel::where('tahun_ajaran_id', $taAktif->id)->count()
            : Rombel::count();

        $totalGuru = Guru::count();

        $totalSiswa = Siswa::where('status', 'aktif')->count();

        $pendingPengumuman = Pengumuman::where('status', 'pending')->count();

        // ===== RINGKASAN PRESENSI HARI INI =====
        // Filter tahun ajaran lewat rombel, bukan lewat sesi_presensi.
        $rekapHariIni = PresensiSiswa::join('sesi_presensi as sp', 'sp.id', '=', 'presensi.sesi_presensi_id')
            ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
            ->when($taAktif, function ($query) use ($taAktif) {
                $query->where('r.tahun_ajaran_id', $taAktif->id);
            })
            ->whereDate('sp.mulai_pada', $todayStr)
            ->selectRaw("
                SUM(CASE WHEN presensi.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN presensi.status = 'izin' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN presensi.status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN presensi.status = 'alfa' THEN 1 ELSE 0 END) as alfa,
                COUNT(*) as total
            ")
            ->first();

        $totalPresensi = $rekapHariIni->total ?? 0;

        $persenHadir = $totalPresensi > 0
            ? round((($rekapHariIni->hadir ?? 0) / $totalPresensi) * 100)
            : null;

        // ===== PROGRES FINALISASI NILAI =====
        /*
         * Total mapel dihitung dari kombinasi rombel + mata pelajaran
         * pada jadwal rombel tahun ajaran aktif.
         *
         * Jadi kalau satu mapel punya lebih dari satu jadwal,
         * tetap dihitung 1 progress finalisasi.
         */
        $totalMapelQuery = DB::table('jadwal as j')
            ->join('rombel as r', 'r.id', '=', 'j.rombel_id');

        if ($taAktif && !empty($taAktif->id)) {
            $totalMapelQuery->where('r.tahun_ajaran_id', $taAktif->id);
        }

        $totalMapel = (int) $totalMapelQuery
            ->selectRaw("COUNT(DISTINCT CONCAT(j.rombel_id, '-', j.mata_pelajaran_id)) as total")
            ->value('total');

        /*
         * Mapel final dihitung dari nilai berstatus final,
         * tetap berdasarkan kombinasi rombel + mata pelajaran
         * pada tahun ajaran aktif.
         */
        $finalMapelQuery = DB::table('nilai as n')
            ->join('jadwal as j', 'j.id', '=', 'n.jadwal_id')
            ->join('rombel as r', 'r.id', '=', 'j.rombel_id')
            ->where('n.status_penilaian', 'final');

        if ($taAktif && !empty($taAktif->id)) {
            $finalMapelQuery->where('r.tahun_ajaran_id', $taAktif->id);
            $finalMapelQuery->where('n.tahun_ajaran_id', $taAktif->id);
        }

        if ($taAktif && !empty($taAktif->semester)) {
            $finalMapelQuery->where('n.semester', $taAktif->semester);
        }

        $finalMapel = (int) $finalMapelQuery
            ->selectRaw("COUNT(DISTINCT CONCAT(j.rombel_id, '-', j.mata_pelajaran_id)) as total")
            ->value('total');

        $draftMapel = max($totalMapel - $finalMapel, 0);

        // ===== KELAS DENGAN ALFA TERTINGGI 7 HARI TERAKHIR =====
        $startWeek = $today->copy()->subDays(6)->toDateString();

        $kelasAlfaTinggi = DB::table('presensi as ps')
            ->join('sesi_presensi as sp', 'sp.id', '=', 'ps.sesi_presensi_id')
            ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
            ->whereBetween(DB::raw('DATE(sp.mulai_pada)'), [$startWeek, $todayStr])
            ->where('ps.status', 'alfa')
            ->when($taAktif, function ($query) use ($taAktif) {
                $query->where('r.tahun_ajaran_id', $taAktif->id);
            })
            ->groupBy('sp.rombel_id', 'r.nama_rombel')
            ->selectRaw('
                sp.rombel_id,
                r.nama_rombel,
                COUNT(*) as total_alfa
            ')
            ->orderByDesc('total_alfa')
            ->limit(5)
            ->get();

        return view('kepsek.index', [
            'today'             => $today,
            'taAktif'           => $taAktif,

            'totalRombel'       => $totalRombel,
            'totalGuru'         => $totalGuru,
            'totalSiswa'        => $totalSiswa,
            'pendingPengumuman' => $pendingPengumuman,

            'rekapHariIni'      => $rekapHariIni,
            'persenHadir'       => $persenHadir,

            'totalMapel'        => $totalMapel,
            'finalMapel'        => $finalMapel,
            'draftMapel'        => $draftMapel,

            'kelasAlfaTinggi'   => $kelasAlfaTinggi,
        ]);
    }
}