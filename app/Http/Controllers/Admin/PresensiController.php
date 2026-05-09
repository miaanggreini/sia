<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PresensiSiswa;
use App\Models\Jadwal;
use App\Models\Rombel;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->input('bulan') ?: now()->format('Y-m');
        [$year, $month] = explode('-', $bulan);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        $daftarTahunAjaran = TahunAjaran::query()
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $taAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : (int) (
                optional($taAktif)->id
                ?? optional($daftarTahunAjaran->first())->id
            );

        $taDipilih = $daftarTahunAjaran->firstWhere('id', $tahunAjaranId);
        $isTahunAjaranAktif = $taAktif && (int) $tahunAjaranId === (int) $taAktif->id;

        /*
         * Jika tahun ajaran yang dipilih adalah tahun ajaran aktif,
         * siswa yang dihitung hanya siswa_rombel aktif = 1.
         *
         * Jika tahun ajaran lama, siswa yang dihitung adalah semua riwayat anggota
         * pada rombel tersebut, agar histori tetap bisa dilihat.
         */
        $rombels = Rombel::with([
                'tahunAjaran',
                'siswa' => function ($q) use ($isTahunAjaranAktif) {
                    if ($isTahunAjaranAktif) {
                        $q->wherePivot('aktif', 1);
                    }

                    $q->orderBy('nama');
                },
            ])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->orderByRaw("
                CASE tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('nama_rombel')
            ->get();

        $rombelIds = $rombels->pluck('id')->map(fn ($id) => (int) $id)->all();

        $rows = collect();

        if (!empty($rombelIds)) {
            $rows = PresensiSiswa::selectRaw("
                    sp.rombel_id,
                    sp.mata_pelajaran_id,
                    presensi.siswa_id,
                    DATE(sp.mulai_pada) as tgl,
                    MAX(
                        CASE presensi.status
                            WHEN 'hadir' THEN 'H'
                            WHEN 'izin'  THEN 'I'
                            WHEN 'sakit' THEN 'S'
                            WHEN 'alfa'  THEN 'A'
                            ELSE NULL
                        END
                    ) as kode_status
                ")
                ->join('sesi_presensi as sp', 'sp.id', '=', 'presensi.sesi_presensi_id')
                ->whereIn('sp.rombel_id', $rombelIds)
                ->whereBetween(DB::raw('DATE(sp.mulai_pada)'), [
                    $start->toDateString(),
                    $end->toDateString(),
                ])
                ->groupBy(
                    'sp.rombel_id',
                    'sp.mata_pelajaran_id',
                    'presensi.siswa_id',
                    DB::raw('DATE(sp.mulai_pada)')
                )
                ->get();
        }

        $rekapBulananRombel = [];
        $perStudentMonthly = [];

        foreach ($rows as $r) {
            $rid  = (int) $r->rombel_id;
            $sid  = (int) $r->siswa_id;
            $kode = $r->kode_status;

            if (!$kode) {
                continue;
            }

            if (!isset($rekapBulananRombel[$rid])) {
                $rekapBulananRombel[$rid] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            }

            $rekapBulananRombel[$rid][$kode]++;

            if (!isset($perStudentMonthly[$rid][$sid])) {
                $perStudentMonthly[$rid][$sid] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            }

            $perStudentMonthly[$rid][$sid][$kode]++;
        }

        $mapelPerRombel = Jadwal::select('jadwal.rombel_id', 'jadwal.mata_pelajaran_id')
            ->with('mataPelajaran:id,nama_mapel')
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->where('rombel.tahun_ajaran_id', $tahunAjaranId)
            ->when(!empty($rombelIds), fn ($q) => $q->whereIn('jadwal.rombel_id', $rombelIds))
            ->get()
            ->groupBy('rombel_id')
            ->map(function ($items) {
                return $items->pluck('mataPelajaran')->filter()->unique('id')->values();
            });

        $summaryGlobal = [
            'H' => 0,
            'I' => 0,
            'S' => 0,
            'A' => 0,
            'persen_kehadiran' => 0,
            'siswa_perlu_perhatian' => 0,
        ];

        foreach ($rekapBulananRombel as $stats) {
            foreach (['H', 'I', 'S', 'A'] as $key) {
                $summaryGlobal[$key] += (int) ($stats[$key] ?? 0);
            }
        }

            $totalGlobalHadirAlfa = $summaryGlobal['H'] + $summaryGlobal['A'];

            $summaryGlobal['persen_kehadiran'] = $totalGlobalHadirAlfa > 0
                ? round(($summaryGlobal['H'] / $totalGlobalHadirAlfa) * 100, 1)
                : 100;
        $rombelSummaries = [];
        $studentAlertsAll = [];

        foreach ($rombels as $rombel) {
            $rid = (int) $rombel->id;

            $jumlahSiswa = $rombel->siswa->count();

            $bulanStats = $rekapBulananRombel[$rid] ?? ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];

            $hadirRombel = $bulanStats['H'] ?? 0;
            $alfaRombel  = $bulanStats['A'] ?? 0;

            $totalHadirAlfaRombel = $hadirRombel + $alfaRombel;

            $persen = $totalHadirAlfaRombel > 0
                ? round(($hadirRombel / $totalHadirAlfaRombel) * 100, 1)
                : 100;

            $kurangDari90 = 0;

foreach ($rombel->siswa as $siswa) {
    $studentStat = $perStudentMonthly[$rid][$siswa->id] ?? [
        'H' => 0,
        'I' => 0,
        'S' => 0,
        'A' => 0,
    ];

    $hadirSiswa = $studentStat['H'] ?? 0;
    $alfaSiswa  = $studentStat['A'] ?? 0;

    $totalHadirAlfaSiswa = $hadirSiswa + $alfaSiswa;

    $persenSiswa = $totalHadirAlfaSiswa > 0
        ? round(($hadirSiswa / $totalHadirAlfaSiswa) * 100, 1)
        : 100;

    /*
     * Logika alert berbasis alfa:
     * - Izin dan sakit tetap dicatat, tapi tidak menurunkan persentase.
     * - Alfa menurunkan persentase karena tanpa keterangan.
     *
     * >= 90%      : Aman
     * 80% - 89.9% : Perlu Perhatian
     * < 80%       : Perlu Tindak Lanjut
     */
    if ($totalHadirAlfaSiswa > 0 && $persenSiswa < 90) {
        $kurangDari90++;

        if ($persenSiswa < 80) {
            $statusLabel = 'Perlu Tindak Lanjut';
            $statusClass = 'bg-rose-100 text-rose-700 border-rose-200';
        } else {
            $statusLabel = 'Perlu Perhatian';
            $statusClass = 'bg-amber-100 text-amber-700 border-amber-200';
        }

        $studentAlertsAll[] = [
            'siswa_id' => $siswa->id,
            'nama' => $siswa->nama,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'rombel_id' => $rid,
            'rombel_nama' => $rombel->nama_rombel,
            'stats' => $studentStat,
            'persen' => $persenSiswa,
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
        ];
    }
}
            $rombelSummaries[] = [
                'id' => $rid,
                'nama_rombel' => $rombel->nama_rombel,
                'tingkat' => $rombel->tingkat,
                'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                'jumlah_siswa' => $jumlahSiswa,
                'stats' => $bulanStats,
                'persen' => $persen,
                'perlu_perhatian' => $kurangDari90,
                'mapel_list' => $mapelPerRombel[$rid] ?? collect(),
            ];
        }

        $rombelSummaries = collect($rombelSummaries)
            ->sortBy([
                ['persen', 'asc'],
                ['perlu_perhatian', 'desc'],
                ['nama_rombel', 'asc'],
            ])
            ->values()
            ->all();

        $summaryGlobal['siswa_perlu_perhatian'] = count($studentAlertsAll);

        $studentAlerts = collect($studentAlertsAll)
            ->sortBy('persen')
            ->take(10)
            ->values()
            ->all();

        return view('admin.presensi.index', compact(
            'bulan',
            'start',
            'end',
            'days',
            'taAktif',
            'taDipilih',
            'tahunAjaranId',
            'daftarTahunAjaran',
            'isTahunAjaranAktif',
            'summaryGlobal',
            'rombelSummaries',
            'studentAlerts'
        ));
    }

    public function rombelMapel(Request $request, Rombel $rombel, MataPelajaran $mapel)
    {
        $bulan = $request->input('bulan') ?: now()->format('Y-m');
        [$year, $month] = explode('-', $bulan);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        $taAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->input('tahun_ajaran_id')
            : (int) $rombel->tahun_ajaran_id;

        /*
         * Detail mapel harus tetap mengikuti tahun ajaran rombel yang dibuka.
         */
        if ((int) $tahunAjaranId !== (int) $rombel->tahun_ajaran_id) {
            $tahunAjaranId = (int) $rombel->tahun_ajaran_id;
        }

        $taDipilih = TahunAjaran::find($tahunAjaranId);
        $isTahunAjaranAktif = $taAktif && (int) $tahunAjaranId === (int) $taAktif->id;

        $siswa = $this->siswaRombelSesuaiMapel($rombel, $mapel, $isTahunAjaranAktif);

        $siswaIds = $siswa->pluck('id')->map(fn ($id) => (int) $id)->all();

        $rows = collect();

        if (!empty($siswaIds)) {
            $rows = PresensiSiswa::selectRaw("
                    presensi.siswa_id,
                    DATE(sp.mulai_pada) as tgl,
                    MAX(
                        CASE presensi.status
                            WHEN 'hadir' THEN 'H'
                            WHEN 'izin'  THEN 'I'
                            WHEN 'sakit' THEN 'S'
                            WHEN 'alfa'  THEN 'A'
                            ELSE NULL
                        END
                    ) as kode_status
                ")
                ->join('sesi_presensi as sp', 'sp.id', '=', 'presensi.sesi_presensi_id')
                ->where('sp.rombel_id', $rombel->id)
                ->where('sp.mata_pelajaran_id', $mapel->id)
                ->whereIn('presensi.siswa_id', $siswaIds)
                ->whereBetween(DB::raw('DATE(sp.mulai_pada)'), [
                    $start->toDateString(),
                    $end->toDateString(),
                ])
                ->groupBy('presensi.siswa_id', DB::raw('DATE(sp.mulai_pada)'))
                ->get();
        }

        $matrix = [];
        $rekapBulanan = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];

        foreach ($rows as $r) {
            $sid  = (int) $r->siswa_id;
            $day  = (int) Carbon::parse($r->tgl)->format('j');
            $kode = $r->kode_status;

            if (!$kode) {
                continue;
            }

            $matrix[$sid][$day] = $kode;
            $rekapBulanan[$kode]++;
        }

        return view('admin.presensi.rombel-mapel', [
            'bulan'        => $bulan,
            'start'        => $start,
            'end'          => $end,
            'days'         => $days,
            'rombel'       => $rombel,
            'mapel'        => $mapel,
            'siswa'        => $siswa,
            'matrix'       => $matrix,
            'rekapBulanan' => $rekapBulanan,
            'taDipilih'    => $taDipilih,
            'tahunAjaranId'=> $tahunAjaranId,
        ]);
    }

private function siswaRombelSesuaiMapel(Rombel $rombel, MataPelajaran $mapel, bool $isTahunAjaranAktif)
{
    $query = \App\Models\Siswa::query()
        ->select('siswa.*')
        ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
        ->where('sr.rombel_id', $rombel->id)
        ->where('sr.tahun_ajaran_id', $rombel->tahun_ajaran_id)
        ->when($isTahunAjaranAktif, function ($q) {
            $q->where('sr.aktif', 1);
        })
        ->where('siswa.status', 'aktif')
        ->orderBy('siswa.nama');

    if ($this->isMapelAgama($mapel->nama_mapel)) {
        $agama = $this->agamaDariMapel($mapel->nama_mapel);

        $query->whereRaw('LOWER(siswa.agama) = ?', [strtolower($agama)]);
    }

    return $query->get();
}

    private function isMapelAgama(?string $namaMapel): bool
    {
        return $namaMapel && Str::startsWith($namaMapel, 'Pendidikan Agama');
    }

    private function agamaDariMapel(?string $namaMapel): string
    {
        return trim(Str::after(trim((string) $namaMapel), 'Pendidikan Agama'));
    }
}