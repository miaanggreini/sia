<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Jadwal;
use App\Models\Pengumuman;
use App\Models\TahunAjaran;
use App\Models\PresensiSiswa;
use App\Models\SesiPresensi;
use Illuminate\Support\Carbon;
use App\Models\RuangKelas;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // ====== TOTAL DATA ======
        $totalGuru  = Guru::count();
        $totalSiswa = Siswa::count();
        $totalKelas = RuangKelas::count();


        // ====== PRESENSI HARI INI (presensi + sesi_presensi) ======
        $tz      = 'Asia/Jakarta';
        $hariIni = Carbon::now($tz)->toDateString();

        // 1) Deteksi kolom status di presensi: 'status' atau 'kehadiran'
        $kolomStatus = Schema::hasColumn('presensi', 'status')
            ? 'status'
            : (Schema::hasColumn('presensi', 'kehadiran') ? 'kehadiran' : null);

        // 2) Deteksi kolom tanggal di sesi_presensi (prioritas berurutan)
        $kandidatTanggal = ['tanggal', 'tanggal_sesi', 'tanggal_mulai', 'dibuka_pada', 'waktu_mulai', 'created_at'];
        $kolomTanggalSesi = null;
        foreach ($kandidatTanggal as $k) {
            if (Schema::hasColumn('sesi_presensi', $k)) { $kolomTanggalSesi = $k; break; }
        }

        // Jika kolom tak ditemukan, fallback aman ke 0 agar tidak error
        $presensiHadirToday = 0;
        if ($kolomStatus && $kolomTanggalSesi) {
            // gunakan whereDate terhadap kolom tanggal yang berhasil dideteksi
            $presensiHadirToday = PresensiSiswa::where($kolomStatus, 'hadir')
                ->whereHas('sesi', function ($q) use ($hariIni, $kolomTanggalSesi) {
                    $q->whereDate($kolomTanggalSesi, $hariIni);
                })
                ->count();
        }

        // Basis pembanding total (sederhana: total siswa)
        $presensiTotalToday = $totalSiswa;

        // ====== KOMPOSISI GENDER SISWA ======
        $genderCounts = [
            'L' => Siswa::where('jenis_kelamin', 'L')->count(),
            'P' => Siswa::where('jenis_kelamin', 'P')->count(),
        ];

        // ====== JADWAL HARI INI ======
        $english = strtolower(Carbon::now($tz)->dayName);
        $indo    = strtolower(Carbon::now($tz)->locale('id')->dayName);

        $rels = ['mataPelajaran', 'guru', 'rombel'];
        $jadwalHariIni = Jadwal::with($rels)
            ->whereIn('hari', [$indo, $english])
            ->orderBy('jam_mulai')
            ->get();

        // ====== TAHUN AJARAN AKTIF ======
        $ta = method_exists(TahunAjaran::class, 'aktif')
            ? (TahunAjaran::aktif() ?? TahunAjaran::orderByDesc('updated_at')->first())
            : TahunAjaran::orderByDesc('updated_at')->first();

        $tahunAjaranAktif = optional($ta)->label ?? optional($ta)->nama_tahun ?? '—';

        // ====== INFO PENGUMUMAN ======
        $pengumumanPendingCount = Pengumuman::where('status', Pengumuman::STATUS_PENDING)->count();
        $pengumumanSiapPublishCnt = Pengumuman::where('status', Pengumuman::STATUS_APPROVED)
            ->whereNull('published_at')
            ->count();

        return view('admin.index', compact(
            'totalGuru',
            'totalSiswa',
            'totalKelas',
            'presensiHadirToday',
            'presensiTotalToday',
            'genderCounts',
            'jadwalHariIni',
            'tahunAjaranAktif',
            'pengumumanPendingCount',
            'pengumumanSiapPublishCnt'
        ));
    }
}
