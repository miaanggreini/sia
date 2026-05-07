<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\TahunAjaran;

class PenilaianController extends Controller
{
public function index()
{
    $user = auth()->user();

    $guruId = optional($user->guru)->id
        ?? Guru::where('user_id', $user->id)->value('id');

    $taAktif = $this->getTahunAjaranAktif();
    $taLabel = null;
    $semester = null;

    if ($taAktif) {
        $taLabel = $taAktif->nama_tahun
            ?? $taAktif->tahun
            ?? (($taAktif->mulai ?? '') . (($taAktif->mulai && $taAktif->akhir) ? '/' : '') . ($taAktif->akhir ?? ''));

        $semester = $taAktif->semester ?? $taAktif->periode ?? null;
    }

    $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /*
    |--------------------------------------------------------------------------
    | Jadwal guru hanya dari tahun ajaran aktif
    |--------------------------------------------------------------------------
    | Jangan hanya filter guru_id, karena guru bisa punya jadwal di TA lama.
    | Jadwal dikunci melalui rombel.tahun_ajaran_id.
    */
    $teaching = Jadwal::with(['rombel', 'mapel', 'mataPelajaran'])
        ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
        ->when($taAktif, function ($q) use ($taAktif) {
            $q->whereHas('rombel', function ($r) use ($taAktif) {
                $r->where('tahun_ajaran_id', $taAktif->id)
                  ->where(function ($w) {
                      $w->where('aktif', 1)
                        ->orWhereNull('aktif');
                  });
            });
        })
        ->orderByRaw("FIELD(hari, '" . implode("','", $dayOrder) . "')")
        ->orderBy('jam_mulai')
        ->get();

    $riwayat = $teaching->map(function ($j) use ($taAktif) {
        $mapel = $j->mataPelajaran->nama_mapel
            ?? $j->mapel->nama_mapel
            ?? $j->mapel->nama
            ?? 'Mapel';

        $siswaIds = $this->siswaQueryUntukJadwal($j)
            ->pluck('s.id')
            ->map(fn ($v) => (int) $v);

        $total = $siswaIds->count();

        $rows = DB::table('nilai')
            ->where('jadwal_id', $j->id)
            ->whereIn('siswa_id', $siswaIds)
            ->when($taAktif, function ($q) use ($taAktif) {
                $q->where('tahun_ajaran_id', $taAktif->id)
                    ->where('semester', $taAktif->semester);
            })
            ->get();

        $doneLM1 = $rows->whereNotNull('lm1_nilai')->count();
        $doneLM2 = $rows->whereNotNull('lm2_nilai')->count();
        $doneLM3 = $rows->whereNotNull('lm3_nilai')->count();
        $doneLM4 = $rows->whereNotNull('lm4_nilai')->count();

        $statusFinal = $total > 0
            && $rows->count() === $total
            && $rows->where('status_penilaian', 'final')->count() === $total;

        $finalAt = $rows->where('status_penilaian', 'final')->max('finalized_at');

        return [
            'id'                => $j->id,
            'rombel_id'         => $j->rombel_id,
            'mata_pelajaran_id' => $j->mata_pelajaran_id,
            'mapel'             => $mapel,
            'rombel'            => $j->rombel->nama_rombel ?? '-',
            'hari'              => $j->hari,
            'jam'               => ($j->jam_mulai ? substr($j->jam_mulai, 0, 5) : '') . '–' . ($j->jam_selesai ? substr($j->jam_selesai, 0, 5) : ''),
            'total'             => $total,
            'done'              => [
                'LM1' => $doneLM1,
                'LM2' => $doneLM2,
                'LM3' => $doneLM3,
                'LM4' => $doneLM4,
            ],
            'status'            => $statusFinal ? 'final' : 'draft',
            'final_at'          => $finalAt,
        ];
    });

    return view('guru.penilaian.index', compact(
        'teaching',
        'riwayat',
        'taAktif',
        'taLabel',
        'semester'
    ));
}
    public function create(Request $request)
    {
        $data = $request->validate([
            'rombel_id'         => ['required', 'integer'],
            'mata_pelajaran_id' => ['required', 'integer'],
            'komponen'          => ['required', 'in:LM1,LM2,LM3,LM4'],
        ]);

        $guru = auth()->user()->guru
            ?? Guru::where('user_id', auth()->id())->first();

        $taAktif = $this->getTahunAjaranAktif();

        if (!$taAktif) {
            return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
        }

$jadwal = Jadwal::with(['rombel', 'mapel', 'mataPelajaran'])
    ->where('guru_id', $guru->id ?? 0)
    ->where('rombel_id', $data['rombel_id'])
    ->where('mata_pelajaran_id', $data['mata_pelajaran_id'])
    ->whereHas('rombel', function ($r) use ($taAktif) {
        $r->where('tahun_ajaran_id', $taAktif->id)
          ->where(function ($w) {
              $w->where('aktif', 1)
                ->orWhereNull('aktif');
          });
    })
    ->firstOrFail();

        $siswa = $this->siswaQueryUntukJadwal($jadwal)
            ->orderBy('s.nama')
            ->get();

        $nilai = DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->where('semester', $taAktif->semester)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $progress = $this->buildProgress($jadwal, $taAktif->id, $taAktif->semester);

        $statusInfo = $this->getStatusPenilaianSemester($jadwal->id, $taAktif->id, $taAktif->semester);
        $readOnly = $statusInfo['status'] === 'final';

        $kkm = $jadwal->mataPelajaran->kkm
            ?? $jadwal->mapel->kkm
            ?? null;

        return view('guru.penilaian.create', [
            'guru'          => $guru,
            'jadwal'        => $jadwal,
            'siswa'         => $siswa,
            'komponen'      => $data['komponen'],
            'nilai'         => $nilai,
            'progress'      => $progress,
            'readOnly'      => $readOnly,
            'taAktif'       => $taAktif,
            'semesterAktif' => $taAktif->semester,
            'statusInfo'    => $statusInfo,
            'kkm'           => $kkm,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'jadwal_id'         => ['required', 'integer'],
            'rombel_id'         => ['required', 'integer'],
            'mata_pelajaran_id' => ['required', 'integer'],
            'komponen'          => ['required', 'in:LM1,LM2,LM3,LM4'],
            'nilai'             => ['array'],

            'jenis_tp1'         => ['required', 'in:praktik,teori'],
            'jenis_tp2'         => ['required', 'in:praktik,teori'],
            'jenis_tp3'         => ['required', 'in:praktik,teori'],
            'jenis_tp4'         => ['required', 'in:praktik,teori'],

            'bobot_praktik'     => ['required', 'numeric', 'min:0', 'max:100'],
            'bobot_teori'       => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $taAktif = $this->getTahunAjaranAktif();

        if (!$taAktif) {
            return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.'])->withInput();
        }

        $statusInfo = $this->getStatusPenilaianSemester((int) $data['jadwal_id'], (int) $taAktif->id, $taAktif->semester);

        if ($statusInfo['status'] === 'final') {
            return back()->withErrors(['msg' => 'Nilai semester ini sudah difinalisasi dan terkunci.'])->withInput();
        }

        $guru = auth()->user()->guru
            ?? Guru::where('user_id', auth()->id())->first();

$jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
    ->where('id', (int) $data['jadwal_id'])
    ->where('guru_id', $guru->id ?? 0)
    ->where('rombel_id', (int) $data['rombel_id'])
    ->where('mata_pelajaran_id', (int) $data['mata_pelajaran_id'])
    ->whereHas('rombel', function ($r) use ($taAktif) {
        $r->where('tahun_ajaran_id', $taAktif->id)
          ->where(function ($w) {
              $w->where('aktif', 1)
                ->orWhereNull('aktif');
          });
    })
    ->firstOrFail();

        $kkm = $jadwal->mataPelajaran->kkm
            ?? $jadwal->mapel->kkm
            ?? null;

        if ($kkm === null || $kkm === '') {
            return back()->withErrors([
                'msg' => 'KKM mata pelajaran belum diatur. Silakan lengkapi KKM pada data mata pelajaran terlebih dahulu.',
            ])->withInput();
        }

        $bobotPraktik = (float) $data['bobot_praktik'];
        $bobotTeori = (float) $data['bobot_teori'];
        $totalBobot = $bobotPraktik + $bobotTeori;

        if (abs($totalBobot - 100) > 0.01) {
            return back()->withErrors([
                'msg' => 'Total bobot praktik dan teori harus 100%.',
            ])->withInput();
        }

        $jenisTp = [
            'tp1' => $data['jenis_tp1'],
            'tp2' => $data['jenis_tp2'],
            'tp3' => $data['jenis_tp3'],
            'tp4' => $data['jenis_tp4'],
        ];

        if ($bobotPraktik > 0 && !in_array('praktik', $jenisTp, true)) {
            return back()->withErrors([
                'msg' => 'Bobot praktik lebih dari 0%, maka minimal satu TP harus dipilih sebagai praktik.',
            ])->withInput();
        }

        if ($bobotTeori > 0 && !in_array('teori', $jenisTp, true)) {
            return back()->withErrors([
                'msg' => 'Bobot teori lebih dari 0%, maka minimal satu TP harus dipilih sebagai teori.',
            ])->withInput();
        }

        $validSiswaIds = $this->siswaQueryUntukJadwal($jadwal)
            ->pluck('s.id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $submittedIds = collect(array_keys($data['nilai'] ?? []))
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($submittedIds as $siswaId) {
            abort_unless(in_array($siswaId, $validSiswaIds, true), 403, 'Ada siswa yang tidak valid untuk penilaian mapel ini.');
        }

        $prefixMap = [
            'LM1' => 'lm1',
            'LM2' => 'lm2',
            'LM3' => 'lm3',
            'LM4' => 'lm4',
        ];

        $prefix = $prefixMap[$data['komponen']];

        DB::beginTransaction();

        try {
            foreach (($data['nilai'] ?? []) as $siswaId => $item) {
                $tp1 = $this->normalizeNilai($item['tp1'] ?? null);
                $tp2 = $this->normalizeNilai($item['tp2'] ?? null);
                $tp3 = $this->normalizeNilai($item['tp3'] ?? null);
                $tp4 = $this->normalizeNilai($item['tp4'] ?? null);

                $allNull = $tp1 === null && $tp2 === null && $tp3 === null && $tp4 === null;

                if ($allNull) {
                    continue;
                }

                $lmNilai = $this->hitungNilaiLmBerbobot(
                    [
                        'tp1' => $tp1,
                        'tp2' => $tp2,
                        'tp3' => $tp3,
                        'tp4' => $tp4,
                    ],
                    $jenisTp,
                    $bobotPraktik,
                    $bobotTeori
                );

                DB::table('nilai')->updateOrInsert(
                    [
                        'jadwal_id'       => (int) $data['jadwal_id'],
                        'siswa_id'        => (int) $siswaId,
                        'tahun_ajaran_id' => (int) $taAktif->id,
                        'semester'        => $taAktif->semester,
                    ],
                    [
                        "{$prefix}_tp1"    => $tp1,
                        "{$prefix}_tp2"    => $tp2,
                        "{$prefix}_tp3"    => $tp3,
                        "{$prefix}_tp4"    => $tp4,
                        "{$prefix}_nilai"  => $lmNilai,
                        'status_penilaian' => 'draft',
                        'updated_at'       => now(),
                        'created_at'       => now(),
                    ]
                );

                $row = DB::table('nilai')
                    ->where([
                        'jadwal_id'       => (int) $data['jadwal_id'],
                        'siswa_id'        => (int) $siswaId,
                        'tahun_ajaran_id' => (int) $taAktif->id,
                        'semester'        => $taAktif->semester,
                    ])
                    ->first();

                $nilaiAkhir = $this->averageNullable([
                    $row->lm1_nilai ?? null,
                    $row->lm2_nilai ?? null,
                    $row->lm3_nilai ?? null,
                    $row->lm4_nilai ?? null,
                ]);

                $statusKetuntasan = 'tidak_tuntas';

                if ($nilaiAkhir !== null) {
                    $statusKetuntasan = $nilaiAkhir >= (float) $kkm ? 'tuntas' : 'tidak_tuntas';
                }

                DB::table('nilai')
                    ->where([
                        'jadwal_id'       => (int) $data['jadwal_id'],
                        'siswa_id'        => (int) $siswaId,
                        'tahun_ajaran_id' => (int) $taAktif->id,
                        'semester'        => $taAktif->semester,
                    ])
                    ->update([
                        'nilai_akhir'      => $nilaiAkhir,
                        'status'           => $statusKetuntasan,
                        'status_penilaian' => 'draft',
                        'updated_at'       => now(),
                    ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors([
                'msg' => 'Gagal menyimpan nilai: ' . $e->getMessage(),
            ])->withInput();
        }

        return redirect()->route('guru.penilaian.create', [
            'rombel_id'         => $data['rombel_id'],
            'mata_pelajaran_id' => $data['mata_pelajaran_id'],
            'komponen'          => $data['komponen'],
        ])->with('success', 'Nilai berhasil disimpan dengan perhitungan berbobot.');
    }

    public function finalize(Request $request)
    {
        $data = $request->validate([
            'jadwal_id' => ['required', 'integer'],
        ]);

$taAktif = $this->getTahunAjaranAktif();

if (!$taAktif) {
    return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
}

$jadwal = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
    ->where('id', $data['jadwal_id'])
    ->whereHas('rombel', function ($r) use ($taAktif) {
        $r->where('tahun_ajaran_id', $taAktif->id)
          ->where(function ($w) {
              $w->where('aktif', 1)
                ->orWhereNull('aktif');
          });
    })
    ->firstOrFail();

        $guru = auth()->user()->guru ?? Guru::where('user_id', auth()->id())->first();

        if (($jadwal->guru_id ?? null) !== ($guru->id ?? null)) {
            abort(403);
        }


        if (!$taAktif) {
            return back()->withErrors(['msg' => 'Tahun ajaran aktif belum diatur.']);
        }

        $p = $this->buildProgress($jadwal, $taAktif->id, $taAktif->semester);

        if ($p['missing_any'] > 0) {
            return back()->withErrors([
                'msg' => 'Finalisasi gagal: masih ada nilai yang kosong pada LM1/LM2/LM3/LM4.',
            ]);
        }

        $siswaIds = $this->siswaQueryUntukJadwal($jadwal)
            ->pluck('s.id');

        DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->where('semester', $taAktif->semester)
            ->whereIn('siswa_id', $siswaIds)
            ->update([
                'status_penilaian' => 'final',
                'finalized_at'     => now(),
                'finalized_by'     => auth()->id(),
                'updated_at'       => now(),
            ]);

        return back()->with('success', 'Finalisasi berhasil untuk semester ' . ucfirst($taAktif->semester) . '.');
    }

    private function buildProgress(Jadwal $jadwal, int $tahunAjaranId, string $semester): array
    {
        $siswa = $this->siswaQueryUntukJadwal($jadwal)
            ->orderBy('s.nama')
            ->get();

        $nilai = DB::table('nilai')
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswa->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $total = $siswa->count();

        $miss = [
            'LM1' => 0,
            'LM2' => 0,
            'LM3' => 0,
            'LM4' => 0,
        ];

        $missingAny = 0;

        foreach ($siswa as $s) {
            $row = $nilai[$s->id] ?? null;
            $hasMiss = false;

            if ($row?->lm1_nilai === null) {
                $miss['LM1']++;
                $hasMiss = true;
            }

            if ($row?->lm2_nilai === null) {
                $miss['LM2']++;
                $hasMiss = true;
            }

            if ($row?->lm3_nilai === null) {
                $miss['LM3']++;
                $hasMiss = true;
            }

            if ($row?->lm4_nilai === null) {
                $miss['LM4']++;
                $hasMiss = true;
            }

            if ($hasMiss) {
                $missingAny++;
            }
        }

        return [
            'total'       => $total,
            'missing'     => $miss,
            'missing_any' => $missingAny,
        ];
    }

    private function getStatusPenilaianSemester(int $jadwalId, int $tahunAjaranId, string $semester): array
    {
        $jadwal = Jadwal::with(['mataPelajaran', 'mapel'])->find($jadwalId);

        if (!$jadwal) {
            return [
                'status'       => 'draft',
                'finalized_at' => null,
                'finalized_by' => null,
            ];
        }

        $siswaIds = $this->siswaQueryUntukJadwal($jadwal)
            ->pluck('s.id');

        $rows = DB::table('nilai')
            ->where('jadwal_id', $jadwalId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds)
            ->get();

        if ($rows->isEmpty()) {
            return [
                'status'       => 'draft',
                'finalized_at' => null,
                'finalized_by' => null,
            ];
        }

        $totalSiswa = $siswaIds->count();

        $allFinal = $totalSiswa > 0
            && $rows->count() === $totalSiswa
            && $rows->where('status_penilaian', 'final')->count() === $totalSiswa;

        return [
            'status'       => $allFinal ? 'final' : 'draft',
            'finalized_at' => $rows->where('status_penilaian', 'final')->max('finalized_at'),
            'finalized_by' => $rows->where('status_penilaian', 'final')->max('finalized_by'),
        ];
    }

    private function getTahunAjaranAktif()
    {
        $taQuery = TahunAjaran::query();
        $hasAny = false;

        $taQuery->where(function ($w) use (&$hasAny) {
            if (Schema::hasColumn('tahun_ajaran', 'is_aktif')) {
                $w->orWhere('is_aktif', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'aktif')) {
                $w->orWhere('aktif', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'is_active')) {
                $w->orWhere('is_active', 1);
                $hasAny = true;
            }

            if (Schema::hasColumn('tahun_ajaran', 'status')) {
                $w->orWhere('status', 'aktif');
                $hasAny = true;
            }
        });

        return ($hasAny ? $taQuery : TahunAjaran::query())
            ->orderByDesc('id')
            ->first();
    }

private function siswaQueryUntukJadwal(Jadwal $jadwal)
{
    $mapel = $jadwal->mataPelajaran->nama_mapel
        ?? $jadwal->mapel->nama_mapel
        ?? $jadwal->mapel->nama
        ?? null;

    $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id
        ?? DB::table('rombel')
            ->where('id', $jadwal->rombel_id)
            ->value('tahun_ajaran_id');

    $query = DB::table('siswa as s')
        ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
        ->where('sr.rombel_id', $jadwal->rombel_id)
        ->where('sr.aktif', 1)
        ->when($tahunAjaranId, function ($q) use ($tahunAjaranId) {
            $q->where('sr.tahun_ajaran_id', $tahunAjaranId);
        })
        ->select('s.*');

    if ($this->isMapelAgama($mapel)) {
        $agama = $this->agamaDariMapel($mapel);

        $query->whereRaw('LOWER(s.agama) = ?', [strtolower($agama)]);
    }

    return $query;
}

    private function isMapelAgama(?string $namaMapel): bool
    {
        return $namaMapel && Str::startsWith($namaMapel, 'Pendidikan Agama');
    }

    private function agamaDariMapel(?string $namaMapel): string
    {
        $namaMapel = trim((string) $namaMapel);

        return trim(Str::after($namaMapel, 'Pendidikan Agama'));
    }

    private function normalizeNilai($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = (float) $value;

        return max(0, min(100, $value));
    }

    private function averageNullable(array $values): ?float
    {
        $filtered = collect($values)->filter(fn ($v) => $v !== null && $v !== '');

        return $filtered->isEmpty() ? null : round($filtered->avg(), 2);
    }

    private function hitungNilaiLmBerbobot(array $tp, array $jenisTp, float $bobotPraktik, float $bobotTeori): ?float
    {
        $nilaiPraktik = [];
        $nilaiTeori = [];

        foreach ([1, 2, 3, 4] as $i) {
            $key = "tp{$i}";
            $nilai = $tp[$key] ?? null;
            $jenis = $jenisTp[$key] ?? null;

            if ($nilai === null || $nilai === '') {
                continue;
            }

            if ($jenis === 'praktik') {
                $nilaiPraktik[] = (float) $nilai;
            }

            if ($jenis === 'teori') {
                $nilaiTeori[] = (float) $nilai;
            }
        }

        $adaNilai = count($nilaiPraktik) > 0 || count($nilaiTeori) > 0;

        if (!$adaNilai) {
            return null;
        }

        if ($bobotPraktik > 0 && count($nilaiPraktik) === 0) {
            return null;
        }

        if ($bobotTeori > 0 && count($nilaiTeori) === 0) {
            return null;
        }

        $rataPraktik = count($nilaiPraktik) > 0
            ? array_sum($nilaiPraktik) / count($nilaiPraktik)
            : 0;

        $rataTeori = count($nilaiTeori) > 0
            ? array_sum($nilaiTeori) / count($nilaiTeori)
            : 0;

        $nilaiLm = ($rataPraktik * ($bobotPraktik / 100))
            + ($rataTeori * ($bobotTeori / 100));

        return round($nilaiLm, 2);
    }
}