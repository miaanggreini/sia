<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class WaliMonitoringController extends Controller
{
    protected function rombelWaliAktif(): ?Rombel
    {
        $user = auth()->user();

        $guruId = optional($user->guru)->id
            ?? Guru::where('user_id', $user->id)->value('id');

        if (!$guruId) {
            return null;
        }

        $taAktifId = TahunAjaran::where('status', 'aktif')->value('id');

        return Rombel::where('guru_id', $guruId)
            ->when($taAktifId, fn ($q) => $q->where('tahun_ajaran_id', $taAktifId))
            ->first();
    }

    protected function predikatFromNilai($nilai): ?string
    {
        if ($nilai === null) {
            return null;
        }

        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        return 'D';
    }

public function kelasSaya(Request $request)
{
    $rombel = $this->rombelWaliAktif();
    $taAktif = \App\Models\TahunAjaran::where('status', 'aktif')->value('nama_tahun');

    if (!$rombel) {
        return view('guru.wali_kelas.kelas_saya', [
            'rombels' => collect(),
            'taAktif' => $taAktif,
        ]);
    }

    $q = trim((string) $request->get('q'));

    // Eager load siswa aktif pada rombel wali
    $rombel->load([
        'siswa' => function ($query) use ($q) {
            $query->wherePivot('aktif', 1)
                ->when($q !== '', function ($sub) use ($q) {
                    $sub->where(function ($x) use ($q) {
                        $x->where('siswa.nama', 'like', "%{$q}%")
                          ->orWhere('siswa.nis', 'like', "%{$q}%")
                          ->orWhere('siswa.nisn', 'like', "%{$q}%");
                    });
                })
                ->orderBy('siswa.nama');
        },
        'tahunAjaran',
    ]);

    return view('guru.wali_kelas.kelas_saya', [
        'rombels' => collect([$rombel]),
        'taAktif' => $taAktif,
    ]);
}
    /**
     * Monitoring presensi wali kelas
     * Layout: rekap per siswa dalam bulan terpilih
     */
    public function presensi(Request $request)
    {
        $rombel = $this->rombelWaliAktif();

        $bulan   = $request->input('bulan', now('Asia/Jakarta')->format('Y-m'));
        $mapelId = $request->input('mapel_id');

        if (!$rombel) {
            return view('guru.wali_kelas.monitoring_presensi', [
                'rombel'      => null,
                'bulan'       => $bulan,
                'mapelId'     => $mapelId,
                'daftarMapel' => collect(),
                'rows'        => collect(),
            ]);
        }

        $daftarMapel = DB::table('jadwal as j')
            ->join('mata_pelajaran as mp', 'mp.id', '=', 'j.mata_pelajaran_id')
            ->where('j.rombel_id', $rombel->id)
            ->select('mp.id', 'mp.nama_mapel')
            ->distinct()
            ->orderBy('mp.nama_mapel')
            ->get();

        try {
            $start = Carbon::createFromFormat('Y-m', $bulan, 'Asia/Jakarta')->startOfMonth();
        } catch (\Throwable $e) {
            $start = now('Asia/Jakarta')->startOfMonth();
            $bulan = $start->format('Y-m');
        }

        $end = $start->copy()->endOfMonth();

        $sesiQuery = DB::table('sesi_presensi')
            ->where('rombel_id', $rombel->id)
            ->whereBetween(DB::raw('DATE(mulai_pada)'), [
                $start->toDateString(),
                $end->toDateString(),
            ]);

        if (!empty($mapelId)) {
            $sesiQuery->where('mata_pelajaran_id', $mapelId);
        }

        $sesiList = $sesiQuery->get(['id']);
        $sesiIds  = $sesiList->pluck('id')->all();
        $totalSesi = count($sesiIds);

        $siswaList = DB::table('siswa as s')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.aktif', 1)
            ->select('s.id', 's.nama', 's.nis')
            ->orderBy('s.nama')
            ->get();

        $rekapBySiswa = collect();

        if (!empty($sesiIds)) {
            $rekapBySiswa = DB::table('presensi')
                ->whereIn('sesi_presensi_id', $sesiIds)
                ->selectRaw("
                    siswa_id,
                    SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN status = 'izin'  THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN status = 'alfa'  THEN 1 ELSE 0 END) as alfa
                ")
                ->groupBy('siswa_id')
                ->get()
                ->keyBy('siswa_id');
        }

        $rows = $siswaList->map(function ($siswa) use ($rekapBySiswa, $totalSesi, $rombel, $mapelId, $bulan) {
            $rec = $rekapBySiswa->get($siswa->id);

            $hadir = (int) ($rec->hadir ?? 0);
            $izin  = (int) ($rec->izin ?? 0);
            $sakit = (int) ($rec->sakit ?? 0);
            $alfaRecorded = (int) ($rec->alfa ?? 0);

            $counted = $hadir + $izin + $sakit + $alfaRecorded;
            $missing = max(0, $totalSesi - $counted);
            $alfa    = $alfaRecorded + $missing;

            $persen = $totalSesi > 0
                ? round(($hadir / $totalSesi) * 100, 1)
                : 0;

            return (object) [
                'siswa_id'     => $siswa->id,
                'nama'         => $siswa->nama,
                'nis'          => $siswa->nis,
                'hadir'        => $hadir,
                'izin'         => $izin,
                'sakit'        => $sakit,
                'alfa'         => $alfa,
                'total'        => $totalSesi,
                'persentase'   => $persen,
                'status_label' => $totalSesi === 0
                    ? 'Belum ada data'
                    : ($persen < 90 ? 'Perlu perhatian' : 'Aman'),
                'detail_url'   => !empty($mapelId)
                    ? route('guru.wali.monitoring-presensi.siswa', [
                        'rombel' => $rombel->id,
                        'mapel'  => $mapelId,
                        'siswa'  => $siswa->id,
                        'bulan'  => $bulan,
                    ])
                    : null,
            ];
        });

        return view('guru.wali_kelas.monitoring_presensi', [
            'rombel'      => $rombel,
            'bulan'       => $bulan,
            'mapelId'     => $mapelId,
            'daftarMapel' => $daftarMapel,
            'rows'        => $rows,
        ]);
    }

    /**
     * Detail sesi lama (tetap dipertahankan kalau route masih dipakai)
     */
    public function presensiDetail($sesiId)
    {
        $rombel = $this->rombelWaliAktif();
        if (!$rombel) {
            abort(404, 'Rombel wali tidak ditemukan.');
        }

        $sesi = DB::table('sesi_presensi as sp')
            ->leftJoin('mata_pelajaran as mp', 'mp.id', '=', 'sp.mata_pelajaran_id')
            ->where('sp.id', $sesiId)
            ->where('sp.rombel_id', $rombel->id)
            ->select(
                'sp.*',
                'mp.nama_mapel'
            )
            ->first();

        abort_unless($sesi, 404, 'Sesi presensi tidak ditemukan.');

        $items = DB::table('siswa as s')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
            ->leftJoin('presensi as p', function ($join) use ($sesiId) {
                $join->on('p.siswa_id', '=', 's.id')
                    ->where('p.sesi_presensi_id', '=', $sesiId);
            })
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.aktif', 1)
            ->select(
                's.id',
                's.nama',
                's.nis',
                DB::raw("COALESCE(p.status, 'alfa') as status")
            )
            ->orderBy('s.nama')
            ->get();

        return view('guru.wali_kelas.monitoring_presensi_detail', [
            'rombel' => $rombel,
            'sesi'   => $sesi,
            'items'  => $items,
        ]);
    }

    /**
     * Detail presensi per siswa dan per mapel
     */
    public function presensiSiswa(Request $request, $rombelId, $mapelId, $siswaId)
    {
        $rombel = $this->rombelWaliAktif();
        abort_unless($rombel && (int) $rombel->id === (int) $rombelId, 404, 'Rombel wali tidak ditemukan.');

        $siswa = DB::table('siswa')
            ->where('id', $siswaId)
            ->first();

        abort_unless($siswa, 404, 'Siswa tidak ditemukan.');

        $mapel = DB::table('mata_pelajaran')
            ->where('id', $mapelId)
            ->first();

        abort_unless($mapel, 404, 'Mata pelajaran tidak ditemukan.');

        $bulan = $request->input('bulan', now('Asia/Jakarta')->format('Y-m'));
        try {
            $start = Carbon::createFromFormat('Y-m', $bulan, 'Asia/Jakarta')->startOfMonth();
        } catch (\Throwable $e) {
            $start = now('Asia/Jakarta')->startOfMonth();
            $bulan = $start->format('Y-m');
        }
        $end = $start->copy()->endOfMonth();

        $sesiList = DB::table('sesi_presensi as sp')
            ->where('sp.rombel_id', $rombelId)
            ->where('sp.mata_pelajaran_id', $mapelId)
            ->whereBetween(DB::raw('DATE(sp.mulai_pada)'), [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->orderBy('sp.mulai_pada')
            ->get();

        $rows = $sesiList->map(function ($sesi) use ($siswaId) {
            $presensi = DB::table('presensi')
                ->where('sesi_presensi_id', $sesi->id)
                ->where('siswa_id', $siswaId)
                ->first();

            return (object) [
                'tanggal' => Carbon::parse($sesi->mulai_pada)->timezone('Asia/Jakarta')->translatedFormat('d M Y'),
                'jam'     => Carbon::parse($sesi->mulai_pada)->timezone('Asia/Jakarta')->format('H:i'),
                'status'  => $presensi->status ?? 'alfa',
            ];
        });

        $count = [
            'hadir' => $rows->where('status', 'hadir')->count(),
            'izin'  => $rows->where('status', 'izin')->count(),
            'sakit' => $rows->where('status', 'sakit')->count(),
            'alfa'  => $rows->where('status', 'alfa')->count(),
        ];

        $total = $rows->count();
        $persenHadir = $total > 0 ? round(($count['hadir'] / $total) * 100, 1) : 0;

        return view('guru.wali_kelas.monitoring_presensi_siswa', [
            'rombel'      => $rombel,
            'mapel'       => $mapel,
            'siswa'       => $siswa,
            'bulan'       => $bulan,
            'rows'        => $rows,
            'count'       => $count,
            'total'       => $total,
            'persenHadir' => $persenHadir,
        ]);
    }

    public function penilaian(Request $request)
    {
        $rombel = $this->rombelWaliAktif();
        $semesterAktif = TahunAjaran::where('status', 'aktif')->value('semester');
        $semester      = $request->input('semester', 'all');

        if (!$rombel) {
            return view('guru.wali_kelas.monitoring_penilaian', [
                'rombel'        => null,
                'rows'          => collect(),
                'semester'      => $semester,
                'semesterAktif' => $semesterAktif,
            ]);
        }

        $jadwal = Jadwal::with('mataPelajaran')
            ->where('rombel_id', $rombel->id)
            ->get();

        $jadwalIds = $jadwal->pluck('id');

        if ($jadwal->isEmpty()) {
            return view('guru.wali_kelas.monitoring_penilaian', [
                'rombel'        => $rombel,
                'rows'          => collect(),
                'semester'      => $semester,
                'semesterAktif' => $semesterAktif,
            ]);
        }

        if ($semester !== 'all' && $semesterAktif && $semester !== $semesterAktif) {
            return view('guru.wali_kelas.monitoring_penilaian', [
                'rombel'        => $rombel,
                'rows'          => collect(),
                'semester'      => $semester,
                'semesterAktif' => $semesterAktif,
            ]);
        }

        $nilaiRows = Nilai::query()
            ->join('siswa', 'siswa.id', '=', 'nilai.siswa_id')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.aktif', 1)
            ->whereIn('nilai.jadwal_id', $jadwalIds)
            ->get([
                'nilai.*',
                'siswa.id   as siswa_id',
                'siswa.nama as siswa_nama',
                'siswa.nis  as siswa_nis',
            ]);

        $bySiswa = $nilaiRows->groupBy('siswa_id');

        $rows = $bySiswa->map(function ($items, $siswaId) use ($semesterAktif) {
            $first = $items->first();

            return (object) [
                'siswa_id'    => $siswaId,
                'siswa_nama'  => $first->siswa_nama,
                'siswa_nis'   => $first->siswa_nis,
                'semester'    => $semesterAktif ?: null,
            ];
        })->values();

        $rows = $rows->sortBy(fn ($row) => mb_strtolower($row->siswa_nama))->values();

        return view('guru.wali_kelas.monitoring_penilaian', [
            'rombel'        => $rombel,
            'rows'          => $rows,
            'semester'      => $semester,
            'semesterAktif' => $semesterAktif,
        ]);
    }

    public function penilaianDetail($siswaId)
    {
        $rombel = $this->rombelWaliAktif();
        if (!$rombel) {
            abort(404, 'Rombel wali tidak ditemukan.');
        }

        $taAktif = TahunAjaran::where('status', 'aktif')->value('nama_tahun');
        $semesterAktif = TahunAjaran::where('status', 'aktif')->value('semester');

        $jadwal = Jadwal::with('mataPelajaran')
            ->where('rombel_id', $rombel->id)
            ->get();

        $jadwalIds = $jadwal->pluck('id');

        $nilaiList = Nilai::with(['siswa', 'jadwal.mataPelajaran'])
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('siswa_id', $siswaId)
            ->get();

        $rows = $nilaiList->map(function ($n) {
            $mapel = $n->jadwal?->mataPelajaran;

            $lm1 = $n->lm1_nilai ?? $n->lm1_calculated;
            $lm2 = $n->lm2_nilai ?? $n->lm2_calculated;
            $lm3 = $n->lm3_nilai ?? $n->lm3_calculated;
            $lm4 = $n->lm4_nilai ?? $n->lm4_calculated;
            $akhir = $n->nilai_akhir ?? $n->nilai_akhir_calculated;

            return (object) [
                'mapel_nama'   => $mapel->nama_mapel ?? '-',

                'lm1_tp1'      => $n->lm1_tp1,
                'lm1_tp2'      => $n->lm1_tp2,
                'lm1_tp3'      => $n->lm1_tp3,
                'lm1_tp4'      => $n->lm1_tp4,
                'lm1_nilai'    => $lm1,

                'lm2_tp1'      => $n->lm2_tp1,
                'lm2_tp2'      => $n->lm2_tp2,
                'lm2_tp3'      => $n->lm2_tp3,
                'lm2_tp4'      => $n->lm2_tp4,
                'lm2_nilai'    => $lm2,

                'lm3_tp1'      => $n->lm3_tp1,
                'lm3_tp2'      => $n->lm3_tp2,
                'lm3_tp3'      => $n->lm3_tp3,
                'lm3_tp4'      => $n->lm3_tp4,
                'lm3_nilai'    => $lm3,

                'lm4_tp1'      => $n->lm4_tp1,
                'lm4_tp2'      => $n->lm4_tp2,
                'lm4_tp3'      => $n->lm4_tp3,
                'lm4_tp4'      => $n->lm4_tp4,
                'lm4_nilai'    => $lm4,

                'nilai_akhir'  => $akhir,
                'status'       => $n->status,
                'predikat'     => $this->predikatFromNilai($akhir),
            ];
        })->sortBy('mapel_nama')->values();

        $siswa = optional($nilaiList->first())->siswa;

        return view('guru.wali_kelas.monitoring_penilaian_detail', [
            'rombel'        => $rombel,
            'rows'          => $rows,
            'siswa'         => $siswa,
            'semesterAktif' => $semesterAktif,
            'taAktif'       => $taAktif,
        ]);
    }
}