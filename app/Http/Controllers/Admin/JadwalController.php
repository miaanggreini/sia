<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class JadwalController extends Controller
{
    private array $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    private array $slotMap = [
        1  => ['JP1',  '07:00', '07:45'],
        2  => ['JP2',  '07:45', '08:30'],
        3  => ['JP3',  '08:30', '09:15'],
        4  => ['JP4',  '09:30', '10:15'],
        5  => ['JP5',  '10:15', '11:00'],
        6  => ['JP6',  '11:00', '11:45'],
        7  => ['JP7',  '12:30', '13:15'],
        8  => ['JP8',  '13:15', '14:00'],
        9  => ['JP9',  '14:00', '14:45'],
        10 => ['JP10', '14:45', '15:30'],
        11 => ['JP11', '15:30', '16:15'],
    ];

    public function index(Request $request)
    {
        $q        = trim((string) $request->query('q'));
        $rombelId = $request->query('rombel_id');
        $guruId   = $request->query('guru_id');
        $hari     = $request->query('hari');

        $daftarTahunAjaran = TahunAjaran::query()
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->query('tahun_ajaran_id')
            : (int) (
                optional($tahunAjaranAktif)->id
                ?? optional($daftarTahunAjaran->first())->id
            );

        $items = Jadwal::with(['rombel.tahunAjaran', 'mataPelajaran', 'guru'])
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->select('jadwal.*')
            ->when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('rombel.tahun_ajaran_id', $tahunAjaranId);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('rombel.nama_rombel', 'like', "%{$q}%")
                        ->orWhereHas('mataPelajaran', fn ($m) => $m->where('nama_mapel', 'like', "%{$q}%"))
                        ->orWhereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%"))
                        ->orWhere('jadwal.hari', 'like', "%{$q}%");
                });
            })
            ->when(filled($rombelId), fn ($qr) => $qr->where('jadwal.rombel_id', $rombelId))
            ->when(filled($guruId), fn ($qr) => $qr->where('jadwal.guru_id', $guruId))
            ->when(filled($hari), fn ($qr) => $qr->where('jadwal.hari', $hari))
            ->orderByRaw("
                CASE rombel.tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('rombel.nama_rombel')
            ->orderByRaw("FIELD(jadwal.hari, '" . implode("','", $this->dayOrder) . "')")
            ->orderBy('jadwal.jam_mulai')
            ->paginate(10)
            ->withQueryString();

        $daftarRombel = Rombel::with('tahunAjaran')
            ->when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderByRaw("
                CASE tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tingkat', 'tahun_ajaran_id']);

        $daftarGuru = Guru::orderBy('nama')->get(['id', 'nama']);
        $hariOptions = $this->dayOrder;

        return view('admin.jadwal.index', compact(
            'items',
            'q',
            'rombelId',
            'guruId',
            'hari',
            'daftarRombel',
            'daftarGuru',
            'hariOptions',
            'daftarTahunAjaran',
            'tahunAjaranAktif',
            'tahunAjaranId'
        ));
    }

    public function create(Request $request)
    {
        $daftarTahunAjaran = TahunAjaran::query()
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->query('tahun_ajaran_id')
            : (int) (
                old('tahun_ajaran_id')
                ?? optional($tahunAjaranAktif)->id
                ?? optional($daftarTahunAjaran->first())->id
            );

        $daftarRombel = Rombel::when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderByRaw("
                CASE tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tingkat', 'tahun_ajaran_id']);

        $daftarGuru = Guru::orderBy('nama')->get(['id', 'nama']);
        $hariOptions = $this->dayOrder;
        $slotOptions = $this->slotMap;

        $mapelRombel = collect();

        if (old('rombel_id')) {
            $rombel = Rombel::find(old('rombel_id'));

            if ($rombel) {
                $mapelRombel = $rombel->mataPelajaran()
                    ->select('mata_pelajaran.id as id', 'mata_pelajaran.nama_mapel')
                    ->orderBy('mata_pelajaran.nama_mapel')
                    ->get();
            }
        }

        return view('admin.jadwal.create', compact(
            'daftarRombel',
            'daftarGuru',
            'hariOptions',
            'slotOptions',
            'mapelRombel',
            'daftarTahunAjaran',
            'tahunAjaranAktif',
            'tahunAjaranId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rombel_id'           => 'required|exists:rombel,id',
            'mapel_id'            => 'required|exists:mata_pelajaran,id',
            'guru_id'             => 'required|exists:guru,id',

            'entries'             => 'required|array|min:1',
            'entries.*.hari'      => 'required|string',
            'entries.*.slot_kode' => 'required',
            'entries.*.durasi_jp' => 'required|integer|min:1|max:12',
            'entries.*.mapel_id'  => 'nullable|exists:mata_pelajaran,id',
            'entries.*.guru_id'   => 'nullable|exists:guru,id',
        ]);

        try {
            $rows = $this->normalizeEntries($data);

            $this->validateBentrokDalamPreview($rows);
            $this->validateBentrokDatabase($rows);

            DB::transaction(function () use ($rows) {
                foreach ($rows as $row) {
                    Jadwal::create([
                        'rombel_id'         => $row['rombel_id'],
                        'mata_pelajaran_id' => $row['mata_pelajaran_id'],
                        'guru_id'           => $row['guru_id'],
                        'hari'              => $row['hari'],
                        'slot_kode'         => $row['slot_kode'],
                        'durasi_jp'         => $row['durasi_jp'],
                        'jam_mulai'         => $row['jam_mulai'],
                        'jam_selesai'       => $row['jam_selesai'],
                    ]);
                }
            });

            return redirect()
                ->route('admin.jadwal.index', [
                    'tahun_ajaran_id' => Rombel::where('id', $data['rombel_id'])->value('tahun_ajaran_id'),
                ])
                ->with('ok', 'Semua jadwal berhasil disimpan.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'msg' => 'Gagal menyimpan jadwal: ' . $e->getMessage(),
                ]);
        }
    }

    public function edit(Jadwal $jadwal)
    {
        $jadwal->load('rombel');

        $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id ?? null;

        $daftarRombel = Rombel::when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderByRaw("
                CASE tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tingkat', 'tahun_ajaran_id']);

        $daftarGuru = Guru::orderBy('nama')->get(['id', 'nama']);
        $slotOptions = $this->slotMap;
        $hariOptions = $this->dayOrder;

        $mapelRombel = $jadwal->rombel
            ? $jadwal->rombel->mataPelajaran()
                ->select('mata_pelajaran.id as id', 'mata_pelajaran.nama_mapel')
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get()
            : collect();

        return view('admin.jadwal.edit', compact(
            'jadwal',
            'daftarRombel',
            'daftarGuru',
            'slotOptions',
            'hariOptions',
            'mapelRombel',
            'tahunAjaranId'
        ));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $v = $request->validate([
            'rombel_id'          => 'required|exists:rombel,id',
            'mata_pelajaran_id'  => 'required|exists:mata_pelajaran,id',
            'guru_id'            => 'required|exists:guru,id',
            'hari'               => 'required|string',
            'slot_kode'          => 'required',
            'durasi_jp'          => 'required|integer|min:1|max:12',
        ]);

        try {
            [$jamMulai, $jamSelesai] = $this->hitungJam(
                (int) $v['slot_kode'],
                (int) $v['durasi_jp']
            );

            $row = [
                'id'                => $jadwal->id,
                'rombel_id'         => (int) $v['rombel_id'],
                'mata_pelajaran_id' => (int) $v['mata_pelajaran_id'],
                'guru_id'           => (int) $v['guru_id'],
                'hari'              => $v['hari'],
                'slot_kode'         => (int) $v['slot_kode'],
                'durasi_jp'         => (int) $v['durasi_jp'],
                'jam_mulai'         => $jamMulai,
                'jam_selesai'       => $jamSelesai,
                'baris'             => 1,
            ];

            $this->validateBentrokDatabase([$row], $jadwal->id);

            $jadwal->update([
                'rombel_id'         => $row['rombel_id'],
                'mata_pelajaran_id' => $row['mata_pelajaran_id'],
                'guru_id'           => $row['guru_id'],
                'hari'              => $row['hari'],
                'slot_kode'         => $row['slot_kode'],
                'durasi_jp'         => $row['durasi_jp'],
                'jam_mulai'         => $row['jam_mulai'],
                'jam_selesai'       => $row['jam_selesai'],
            ]);

            return redirect()
                ->route('admin.jadwal.index', [
                    'tahun_ajaran_id' => Rombel::where('id', $row['rombel_id'])->value('tahun_ajaran_id'),
                    'rombel_id'       => $row['rombel_id'],
                ])
                ->with('ok', 'Jadwal diperbarui.');
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'msg' => 'Gagal memperbarui jadwal: ' . $e->getMessage(),
                ]);
        }
    }

    public function show(Jadwal $jadwal)
    {
        $jadwal->load(['rombel.tahunAjaran', 'mataPelajaran', 'guru']);

        return view('admin.jadwal.show', [
            'item'  => $jadwal,
            'title' => 'Detail Jadwal',
        ]);
    }

    public function destroy(Jadwal $jadwal)
    {
        $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id ?? null;
        $rombelId = $jadwal->rombel_id;

        $jadwal->delete();

        return redirect()
            ->route('admin.jadwal.index', [
                'tahun_ajaran_id' => $tahunAjaranId,
                'rombel_id'       => $rombelId,
            ])
            ->with('ok', 'Jadwal berhasil dihapus.');
    }

    public function exportPdf(Request $request)
    {
        $rombelId = $request->rombel_id;
        $guruId   = $request->guru_id;
        $hari     = $request->hari;
        $q        = trim((string) $request->q);

        $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->query('tahun_ajaran_id')
            : optional($tahunAjaranAktif)->id;

        $items = Jadwal::with(['rombel.tahunAjaran', 'mataPelajaran', 'guru'])
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->select('jadwal.*')
            ->when($tahunAjaranId, fn ($qr) => $qr->where('rombel.tahun_ajaran_id', $tahunAjaranId))
            ->when($rombelId, fn ($qr) => $qr->where('jadwal.rombel_id', $rombelId))
            ->when($guruId, fn ($qr) => $qr->where('jadwal.guru_id', $guruId))
            ->when($hari, fn ($qr) => $qr->where('jadwal.hari', $hari))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('rombel.nama_rombel', 'like', "%{$q}%")
                        ->orWhereHas('mataPelajaran', fn ($m) => $m->where('nama_mapel', 'like', "%{$q}%"))
                        ->orWhereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%"))
                        ->orWhere('jadwal.hari', 'like', "%{$q}%");
                });
            })
            ->orderBy('rombel.nama_rombel')
            ->orderByRaw("FIELD(jadwal.hari, '" . implode("','", $this->dayOrder) . "')")
            ->orderBy('jadwal.jam_mulai')
            ->get();

        $filters = compact('rombelId', 'guruId', 'hari', 'q', 'tahunAjaranId');

        $pdf = Pdf::loadView('admin.jadwal.pdf', [
            'items'   => $items,
            'filters' => $filters,
        ])->setPaper('a4', 'portrait');

        $filename = 'jadwal-' . now()->format('Ymd-His') . '.pdf';

        return $request->boolean('download')
            ? $pdf->download($filename)
            : $pdf->stream($filename);
    }

    private function normalizeEntries(array $data): array
    {
        $rows = [];

        foreach ($data['entries'] as $i => $entry) {
            $mapelId = $entry['mapel_id'] ?? $data['mapel_id'] ?? null;
            $guruId  = $entry['guru_id'] ?? $data['guru_id'] ?? null;

            if (!$mapelId || !$guruId) {
                throw new \Exception('Mapel atau guru belum dipilih pada baris ke-' . ($i + 1) . '.');
            }

            [$jamMulai, $jamSelesai] = $this->hitungJam(
                (int) $entry['slot_kode'],
                (int) $entry['durasi_jp']
            );

            $rows[] = [
                'rombel_id'         => (int) $data['rombel_id'],
                'mata_pelajaran_id' => (int) $mapelId,
                'guru_id'           => (int) $guruId,
                'hari'              => $entry['hari'],
                'slot_kode'         => (int) $entry['slot_kode'],
                'durasi_jp'         => (int) $entry['durasi_jp'],
                'jam_mulai'         => $jamMulai,
                'jam_selesai'       => $jamSelesai,
                'baris'             => $i + 1,
            ];
        }

        return $rows;
    }

    private function hitungJam(int $slotKode, int $durasi): array
    {
        $keys = array_keys($this->slotMap);
        $startIdx = array_search($slotKode, $keys, true);

        if ($startIdx === false) {
            throw new \Exception('Slot mulai tidak valid.');
        }

        $picked = [];

        for ($k = $startIdx; $k < count($keys) && count($picked) < $durasi; $k++) {
            $picked[] = $this->slotMap[$keys[$k]];
        }

        if (count($picked) < $durasi) {
            throw new \Exception('Durasi melebihi slot yang tersedia.');
        }

        return [
            $picked[0][1],
            $picked[count($picked) - 1][2],
        ];
    }

    private function validateBentrokDalamPreview(array $rows): void
    {
        foreach ($rows as $i => $a) {
            foreach ($rows as $j => $b) {
                if ($i >= $j) {
                    continue;
                }

                if ($a['hari'] !== $b['hari']) {
                    continue;
                }

                if (!$this->jamBentrok($a['jam_mulai'], $a['jam_selesai'], $b['jam_mulai'], $b['jam_selesai'])) {
                    continue;
                }

                if ((int) $a['guru_id'] === (int) $b['guru_id']) {
                    $guru = Guru::where('id', $a['guru_id'])->value('nama') ?? 'Guru';

                    throw new \Exception(
                        "{$guru} memiliki jadwal bentrok pada baris {$a['baris']} dan {$b['baris']} ({$a['hari']} {$a['jam_mulai']}-{$a['jam_selesai']})."
                    );
                }

                if ((int) $a['rombel_id'] === (int) $b['rombel_id']) {
                    $samaSamaAgama = $this->isMapelAgamaId((int) $a['mata_pelajaran_id'])
                        && $this->isMapelAgamaId((int) $b['mata_pelajaran_id']);

                    if (!$samaSamaAgama) {
                        throw new \Exception(
                            "Preview bentrok pada rombel untuk baris {$a['baris']} dan {$b['baris']} ({$a['hari']} {$a['jam_mulai']}-{$a['jam_selesai']})."
                        );
                    }
                }
            }
        }
    }

    private function validateBentrokDatabase(array $rows, ?int $ignoreId = null): void
    {
        foreach ($rows as $row) {
            $targetTahunAjaranId = Rombel::where('id', $row['rombel_id'])->value('tahun_ajaran_id');

            $bentrokGuru = Jadwal::with(['mataPelajaran', 'guru', 'rombel'])
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->whereHas('rombel', function ($q) use ($targetTahunAjaranId) {
                    $q->where('tahun_ajaran_id', $targetTahunAjaranId);
                })
                ->where('guru_id', $row['guru_id'])
                ->where('hari', $row['hari'])
                ->where('jam_mulai', '<', $row['jam_selesai'])
                ->where('jam_selesai', '>', $row['jam_mulai'])
                ->first();

            if ($bentrokGuru) {
                $guru   = $bentrokGuru->guru->nama ?? 'Guru';
                $mapel  = $bentrokGuru->mataPelajaran->nama_mapel ?? 'mapel lain';
                $rombel = $bentrokGuru->rombel->nama_rombel ?? 'rombel lain';

                throw new \Exception(
                    "{$guru} sudah memiliki jadwal {$mapel} di {$rombel} pada {$row['hari']} {$bentrokGuru->jam_mulai}-{$bentrokGuru->jam_selesai}."
                );
            }

            $bentrokRombelList = Jadwal::with(['mataPelajaran', 'guru', 'rombel'])
                ->when($ignoreId, fn ($q) => $q->where('id', '<>', $ignoreId))
                ->where('rombel_id', $row['rombel_id'])
                ->where('hari', $row['hari'])
                ->where('jam_mulai', '<', $row['jam_selesai'])
                ->where('jam_selesai', '>', $row['jam_mulai'])
                ->get();

            foreach ($bentrokRombelList as $bentrokRombel) {
                $samaSamaAgama = $this->isMapelAgamaId((int) $row['mata_pelajaran_id'])
                    && $this->isMapelAgama($bentrokRombel->mataPelajaran);

                if ($samaSamaAgama) {
                    continue;
                }

                $mapel  = $bentrokRombel->mataPelajaran->nama_mapel ?? 'mapel lain';
                $rombel = $bentrokRombel->rombel->nama_rombel ?? 'rombel ini';

                throw new \Exception(
                    "Rombel {$rombel} sudah memiliki jadwal {$mapel} pada {$row['hari']} {$bentrokRombel->jam_mulai}-{$bentrokRombel->jam_selesai}."
                );
            }
        }
    }

    private function jamBentrok(string $mulaiA, string $selesaiA, string $mulaiB, string $selesaiB): bool
    {
        return $mulaiA < $selesaiB && $selesaiA > $mulaiB;
    }

    private function isMapelAgamaId(int $mapelId): bool
    {
        $nama = MataPelajaran::where('id', $mapelId)->value('nama_mapel');

        return str_contains(strtolower($nama ?? ''), 'agama');
    }

    private function isMapelAgama($mapel): bool
    {
        $nama = strtolower($mapel->nama_mapel ?? '');

        return str_contains($nama, 'agama');
    }

    public function cekBentrok(Request $request)
    {
        $data = $request->validate([
            'rombel_id'          => 'required|exists:rombel,id',
            'mata_pelajaran_id'  => 'required|exists:mata_pelajaran,id',
            'guru_id'            => 'required|exists:guru,id',
            'hari'               => 'required|string',
            'slot_kode'          => 'required',
            'durasi_jp'          => 'required|integer|min:1|max:12',
        ]);

        try {
            [$jamMulai, $jamSelesai] = $this->hitungJam(
                (int) $data['slot_kode'],
                (int) $data['durasi_jp']
            );

            $row = [
                'rombel_id'         => (int) $data['rombel_id'],
                'mata_pelajaran_id' => (int) $data['mata_pelajaran_id'],
                'guru_id'           => (int) $data['guru_id'],
                'hari'              => $data['hari'],
                'slot_kode'         => (int) $data['slot_kode'],
                'durasi_jp'         => (int) $data['durasi_jp'],
                'jam_mulai'         => $jamMulai,
                'jam_selesai'       => $jamSelesai,
                'baris'             => 1,
            ];

            $this->validateBentrokDatabase([$row]);

            return response()->json([
                'bentrok' => false,
                'message' => 'Jadwal tersedia.',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'bentrok' => true,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}