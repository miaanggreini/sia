<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\TahunAjaran;
use App\Models\SesiPresensi;
use App\Models\PresensiSiswa;
use App\Models\Jadwal;
use App\Models\Rombel;
use App\Models\MataPelajaran;
use Illuminate\Support\Str;

class PresensiMonitoringController extends Controller
{
    public function index(Request $request)
    {
        [$bulan, $start, $end, $days] = $this->resolveMonthRange($request->input('bulan'));

        $taAktif = $this->getTahunAjaranAktif();

        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        if (!$tahunAjaranId) {
            $tahunAjaranId = $taAktif?->id
                ?? TahunAjaran::orderByDesc('id')->value('id');
        }

        $tahunDipilih = TahunAjaran::find($tahunAjaranId);

        if (!$tahunDipilih) {
            $tahunDipilih = $taAktif ?? TahunAjaran::orderByDesc('id')->first();
            $tahunAjaranId = $tahunDipilih?->id;
        }

        $daftarTahunAjaran = TahunAjaran::orderByDesc('id')->get();

        /*
        |--------------------------------------------------------------------------
        | Rombel sesuai tahun ajaran dipilih
        |--------------------------------------------------------------------------
        | Untuk histori, siswa pada rombel tidak boleh hanya aktif=1 saja,
        | karena rombel tahun ajaran lama biasanya sudah tidak aktif.
        */
        $rombels = Rombel::with([
                'siswa' => function ($q) use ($tahunAjaranId) {
                    $q->wherePivot('tahun_ajaran_id', $tahunAjaranId)
                        ->orderBy('nama');
                },
                'tahunAjaran',
            ])
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->orderBy('nama_rombel')
            ->get();

        $rombelIds = $rombels->pluck('id')->map(fn ($id) => (int) $id)->all();

        /*
        |--------------------------------------------------------------------------
        | Data presensi pada bulan + tahun ajaran yang dipilih
        |--------------------------------------------------------------------------
        */
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
        $mapelGlobal = [];

        foreach ($rows as $r) {
            $rid  = (int) $r->rombel_id;
            $sid  = (int) $r->siswa_id;
            $mid  = (int) $r->mata_pelajaran_id;
            $kode = $r->kode_status;

            if (!$kode) {
                continue;
            }

            if (!isset($rekapBulananRombel[$rid])) {
                $rekapBulananRombel[$rid] = [
                    'H' => 0,
                    'I' => 0,
                    'S' => 0,
                    'A' => 0,
                ];
            }

            $rekapBulananRombel[$rid][$kode]++;

            if (!isset($perStudentMonthly[$rid][$sid])) {
                $perStudentMonthly[$rid][$sid] = [
                    'H' => 0,
                    'I' => 0,
                    'S' => 0,
                    'A' => 0,
                ];
            }

            $perStudentMonthly[$rid][$sid][$kode]++;

            if (!isset($mapelGlobal[$mid])) {
                $mapelGlobal[$mid] = [
                    'H' => 0,
                    'I' => 0,
                    'S' => 0,
                    'A' => 0,
                ];
            }

            $mapelGlobal[$mid][$kode]++;
        }

        $mapelById = MataPelajaran::pluck('nama_mapel', 'id');

        /*
        |--------------------------------------------------------------------------
        | Mapel per rombel sesuai tahun ajaran dipilih
        |--------------------------------------------------------------------------
        */
        $mapelPerRombel = collect();

        if (!empty($rombelIds)) {
            $mapelPerRombel = Jadwal::select('rombel_id', 'mata_pelajaran_id')
                ->with('mataPelajaran:id,nama_mapel')
                ->whereIn('rombel_id', $rombelIds)
                ->get()
                ->groupBy('rombel_id')
                ->map(function ($items) {
                    return $items->pluck('mataPelajaran')
                        ->filter()
                        ->unique('id')
                        ->values();
                });
        }

        /*
        |--------------------------------------------------------------------------
        | Summary global
        |--------------------------------------------------------------------------
        | Persentase kehadiran dihitung dari H dan A saja:
        | H / (H + A) * 100
        | Izin dan sakit tetap dicatat, tetapi tidak menurunkan persentase.
        */
        $summaryGlobal = [
            'total_rombel' => $rombels->count(),
            'total_siswa' => $rombels->sum(fn ($r) => $r->siswa->count()),
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
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Summary per rombel + alert siswa
        |--------------------------------------------------------------------------
        */
        $rombelSummaries = [];
        $studentAlerts = [];

        foreach ($rombels as $rombel) {
            $rid = (int) $rombel->id;
            $jumlahSiswa = $rombel->siswa->count();

            $bulanStats = $rekapBulananRombel[$rid] ?? [
                'H' => 0,
                'I' => 0,
                'S' => 0,
                'A' => 0,
            ];

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
                    : 0;

                if ($totalHadirAlfaSiswa > 0 && $persenSiswa < 90) {
                    $kurangDari90++;

                    $studentAlerts[] = [
                        'siswa_id' => $siswa->id,
                        'nama' => $siswa->nama,
                        'nis' => $siswa->nis,
                        'nisn' => $siswa->nisn,
                        'rombel_id' => $rid,
                        'rombel_nama' => $rombel->nama_rombel,
                        'stats' => $studentStat,
                        'persen' => $persenSiswa,
                        'status_label' => $persenSiswa < 80
                            ? 'Perlu Tindak Lanjut'
                            : 'Perlu Perhatian',
                        'status_class' => $persenSiswa < 80
                            ? 'bg-rose-100 text-rose-700 border-rose-200'
                            : 'bg-amber-100 text-amber-700 border-amber-200',
                    ];
                }
            }

            $rombelSummaries[] = [
                'id' => $rid,
                'nama_rombel' => $rombel->nama_rombel,
                'tingkat' => $rombel->tingkat ?? null,
                'tahun_ajaran' => $rombel->tahunAjaran->nama_tahun
                    ?? $rombel->tahunAjaran->tahun
                    ?? null,
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

        /*
        |--------------------------------------------------------------------------
        | Total siswa perlu perhatian
        |--------------------------------------------------------------------------
        | Count dilakukan sebelum take(10), supaya angka ringkasan tetap total semua.
        */
        $summaryGlobal['siswa_perlu_perhatian'] = count($studentAlerts);

        $studentAlerts = collect($studentAlerts)
            ->sortBy('persen')
            ->take(10)
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Summary mapel global
        |--------------------------------------------------------------------------
        | Persentase mapel juga dihitung dari H dan A saja.
        */
        $mapelSummaries = collect($mapelGlobal)
            ->map(function ($stats, $mid) use ($mapelById) {
                $hadirMapel = $stats['H'] ?? 0;
                $alfaMapel  = $stats['A'] ?? 0;

                $totalHadirAlfaMapel = $hadirMapel + $alfaMapel;

                return [
                    'mapel_id' => (int) $mid,
                    'nama_mapel' => $mapelById[$mid] ?? 'Mapel',
                    'stats' => $stats,
                    'persen' => $totalHadirAlfaMapel > 0
                        ? round(($hadirMapel / $totalHadirAlfaMapel) * 100, 1)
                        : 0,
                ];
            })
            ->sortBy('persen')
            ->values();

        return view('kepsek.presensi.index', compact(
            'bulan',
            'start',
            'end',
            'days',
            'taAktif',
            'tahunDipilih',
            'tahunAjaranId',
            'daftarTahunAjaran',
            'summaryGlobal',
            'rombelSummaries',
            'studentAlerts',
            'mapelSummaries'
        ));
    }

    public function rombelMapel(Request $request, Rombel $rombel, MataPelajaran $mapel)
    {
        [$bulan, $start, $end, $days] = $this->resolveMonthRange($request->input('bulan'));

        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        if (!$tahunAjaranId) {
            $tahunAjaranId = $rombel->tahun_ajaran_id;
        }

        $tahunDipilih = TahunAjaran::find($tahunAjaranId);

        $siswa = $this->siswaRombelSesuaiMapel($rombel, $mapel, $tahunAjaranId);

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
        $rekapPerSiswa = [];
        $rekapPerTanggal = [];

        foreach ($rows as $r) {
            $sid = (int) $r->siswa_id;
            $day = (int) Carbon::parse($r->tgl)->format('j');
            $tgl = Carbon::parse($r->tgl)->toDateString();
            $kode = $r->kode_status;

            if (!$kode) {
                continue;
            }

            $matrix[$sid][$day] = $kode;
            $rekapBulanan[$kode]++;

            if (!isset($rekapPerSiswa[$sid])) {
                $rekapPerSiswa[$sid] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            }

            $rekapPerSiswa[$sid][$kode]++;

            if (!isset($rekapPerTanggal[$tgl])) {
                $rekapPerTanggal[$tgl] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            }

            $rekapPerTanggal[$tgl][$kode]++;
        }

        $allTime = (object) [
            'hadir' => 0,
            'izin'  => 0,
            'sakit' => 0,
            'alfa'  => 0,
        ];

        if (!empty($siswaIds)) {
            $allTime = PresensiSiswa::selectRaw("
                    SUM(CASE WHEN presensi.status='hadir' THEN 1 ELSE 0 END) as hadir,
                    SUM(CASE WHEN presensi.status='izin'  THEN 1 ELSE 0 END) as izin,
                    SUM(CASE WHEN presensi.status='sakit' THEN 1 ELSE 0 END) as sakit,
                    SUM(CASE WHEN presensi.status='alfa'  THEN 1 ELSE 0 END) as alfa
                ")
                ->join('sesi_presensi as sp', 'sp.id', '=', 'presensi.sesi_presensi_id')
                ->where('sp.rombel_id', $rombel->id)
                ->where('sp.mata_pelajaran_id', $mapel->id)
                ->whereIn('presensi.siswa_id', $siswaIds)
                ->first();
        }

        $sesiHarian = SesiPresensi::selectRaw('DATE(mulai_pada) as tgl, COUNT(*) as jumlah_sesi')
            ->where('rombel_id', $rombel->id)
            ->where('mata_pelajaran_id', $mapel->id)
            ->whereBetween(DB::raw('DATE(mulai_pada)'), [
                $start->toDateString(),
                $end->toDateString(),
            ])
            ->groupBy(DB::raw('DATE(mulai_pada)'))
            ->pluck('jumlah_sesi', 'tgl');

        return view('kepsek.presensi.rombel-mapel', [
            'bulan'           => $bulan,
            'start'           => $start,
            'end'             => $end,
            'days'            => $days,
            'rombel'          => $rombel,
            'mapel'           => $mapel,
            'siswa'           => $siswa,
            'matrix'          => $matrix,
            'rekapBulanan'    => $rekapBulanan,
            'allTime'         => $allTime,
            'rekapPerSiswa'   => $rekapPerSiswa,
            'rekapPerTanggal' => $rekapPerTanggal,
            'sesiHarian'      => $sesiHarian,
            'tahunDipilih'    => $tahunDipilih,
            'tahunAjaranId'   => $tahunAjaranId,
        ]);
    }

    private function resolveMonthRange(?string $bulan): array
    {
        $bulan = $bulan ?: now()->format('Y-m');
        [$year, $month] = explode('-', $bulan);

        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $days = [];
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return [$bulan, $start, $end, $days];
    }

    private function siswaRombelSesuaiMapel(Rombel $rombel, MataPelajaran $mapel, ?int $tahunAjaranId = null)
    {
        $query = $rombel->siswa()
            ->when($tahunAjaranId, function ($q) use ($tahunAjaranId) {
                $q->wherePivot('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderBy('nama');

        if ($this->isMapelAgama($mapel->nama_mapel)) {
            $agama = $this->agamaDariMapel($mapel->nama_mapel);

            $query->whereRaw('LOWER(siswa.agama) = ?', [strtolower($agama)]);
        }

        return $query->get();
    }

    private function getTahunAjaranAktif()
    {
        if (method_exists(TahunAjaran::class, 'aktif')) {
            $taAktif = TahunAjaran::aktif();

            if ($taAktif instanceof \Illuminate\Database\Eloquent\Builder) {
                return $taAktif->first();
            }

            return $taAktif;
        }

        return TahunAjaran::where('status', 'aktif')
            ->orderByDesc('id')
            ->first();
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