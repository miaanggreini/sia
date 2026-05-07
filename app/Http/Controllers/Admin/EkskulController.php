<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EkskulController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $items = Ekskul::with('pembina')
            ->when($q, function ($qr) use ($q) {
                $like = '%' . $q . '%';
                $qr->where(function ($s) use ($like) {
                    $s->where('nama', 'like', $like)
                        ->orWhere('lokasi', 'like', $like)
                        ->orWhere('hari', 'like', $like);
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.ekskul.index', compact('items', 'q'));
    }

    public function create()
    {
        $ekskul = new Ekskul();
        $daftarGuru = Guru::orderBy('nama')->get();

        return view('admin.ekskul.create', compact('ekskul', 'daftarGuru'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Ekskul::create($data);

        return redirect()
            ->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil ditambahkan.');
    }

    public function show(Ekskul $ekskul)
    {
        $ekskul->load('pembina');
        return view('admin.ekskul.show', compact('ekskul'));
    }

    public function edit(Ekskul $ekskul)
    {
        $daftarGuru = Guru::orderBy('nama')->get();
        return view('admin.ekskul.edit', compact('ekskul', 'daftarGuru'));
    }

    public function update(Request $request, Ekskul $ekskul)
    {
        $data = $this->validated($request, $ekskul->id);
        $ekskul->update($data);

        return redirect()
            ->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil diperbarui.');
    }

    public function destroy(Ekskul $ekskul)
    {
        $ekskul->delete();

        return redirect()
            ->route('admin.ekskul.index')
            ->with('success', 'Ekskul berhasil dihapus.');
    }

   public function anggota(Request $request, Ekskul $ekskul)
{
    $ekskul->load('pembina');

    $tahunList = TahunAjaran::orderByDesc('tanggal_mulai')->get();

    $tahunDipilih = null;
    if ($request->filled('tahun_ajaran_id')) {
        $tahunDipilih = $tahunList->firstWhere('id', $request->tahun_ajaran_id);
    }

    if (!$tahunDipilih) {
        $tahunDipilih = $tahunList->firstWhere('status', 'aktif')
            ?? $tahunList->firstWhere('is_aktif', 1)
            ?? $tahunList->first();
    }

    $tab = $request->get('tab', 'anggota');

    $anggota = EkskulAnggota::with('siswa')
        ->where('ekskul_id', $ekskul->id)
        ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
            $q->where('tahun_ajaran_id', $tahunDipilih->id);
        })
        ->where('status', 'aktif')
        ->orderBy('tanggal_gabung')
        ->get();

    $riwayatAnggota = EkskulAnggota::with('siswa')
        ->where('ekskul_id', $ekskul->id)
        ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
            $q->where('tahun_ajaran_id', $tahunDipilih->id);
        })
        ->where('status', 'nonaktif')
        ->orderByDesc('tanggal_keluar')
        ->orderBy('tanggal_gabung')
        ->get();

    $presensiSummary = collect();
    $presensiTanggalList = collect();
    $presensiMatrixRows = collect();

    $bulanOptions = collect();
    $bulanDipilih = $request->get('bulan');

    if ($tahunDipilih) {
        $allTanggal = DB::table('presensi_ekskul')
            ->where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunDipilih->id)
            ->select('tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal');

        $bulanOptions = $allTanggal
            ->map(function ($tanggal) {
                $c = \Carbon\Carbon::parse($tanggal);
                return [
                    'value' => $c->format('Y-m'),
                    'label' => $c->translatedFormat('F Y'),
                ];
            })
            ->unique('value')
            ->values();

        if (!$bulanDipilih && $bulanOptions->isNotEmpty()) {
            $bulanDipilih = $bulanOptions->last()['value'];
        }

        $summaryQuery = DB::table('presensi_ekskul as pe')
            ->where('pe.ekskul_id', $ekskul->id)
            ->where('pe.tahun_ajaran_id', $tahunDipilih->id);

        if ($bulanDipilih) {
            [$tahunFilter, $bulanFilter] = explode('-', $bulanDipilih);
            $summaryQuery
                ->whereYear('pe.tanggal', (int) $tahunFilter)
                ->whereMonth('pe.tanggal', (int) $bulanFilter);
        }

        $presensiSummary = $summaryQuery
            ->selectRaw("
                pe.tanggal,
                SUM(CASE WHEN pe.status = 'H' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN pe.status = 'I' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN pe.status = 'S' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN pe.status = 'A' THEN 1 ELSE 0 END) as alfa,
                COUNT(*) as total
            ")
            ->groupBy('pe.tanggal')
            ->orderBy('pe.tanggal')
            ->get();

        $presensiTanggalList = $presensiSummary->pluck('tanggal')->values();

        $detailQuery = DB::table('presensi_ekskul as pe')
            ->join('siswa as s', 's.id', '=', 'pe.siswa_id')
            ->where('pe.ekskul_id', $ekskul->id)
            ->where('pe.tahun_ajaran_id', $tahunDipilih->id);

        if ($bulanDipilih) {
            [$tahunFilter, $bulanFilter] = explode('-', $bulanDipilih);
            $detailQuery
                ->whereYear('pe.tanggal', (int) $tahunFilter)
                ->whereMonth('pe.tanggal', (int) $bulanFilter);
        }

        $detailRows = $detailQuery
            ->select([
                'pe.tanggal',
                'pe.siswa_id',
                's.nama as nama_siswa',
                's.nis',
                's.nisn',
                'pe.status',
            ])
            ->orderBy('s.nama')
            ->orderBy('pe.tanggal')
            ->get();

        $groupedBySiswa = $detailRows->groupBy('siswa_id');

        $presensiMatrixRows = $groupedBySiswa->map(function ($rows) use ($presensiTanggalList) {
            $first = $rows->first();

            $statusMap = $rows->keyBy(function ($r) {
                return $r->tanggal;
            });

            $statuses = [];
            foreach ($presensiTanggalList as $tgl) {
                $statuses[$tgl] = optional($statusMap->get($tgl))->status;
            }

            return (object) [
                'siswa_id' => $first->siswa_id,
                'nama_siswa' => $first->nama_siswa,
                'nis' => $first->nis,
                'nisn' => $first->nisn,
                'statuses' => $statuses,
                'hadir' => $rows->where('status', 'H')->count(),
                'izin'  => $rows->where('status', 'I')->count(),
                'sakit' => $rows->where('status', 'S')->count(),
                'alfa'  => $rows->where('status', 'A')->count(),
            ];
        })->values();
    }

    return view('admin.ekskul.anggota', [
        'ekskul'               => $ekskul,
        'tahunList'            => $tahunList,
        'tahunDipilih'         => $tahunDipilih,
        'anggota'              => $anggota,
        'riwayatAnggota'       => $riwayatAnggota,
        'tab'                  => $tab,
        'presensiSummary'      => $presensiSummary,
        'presensiTanggalList'  => $presensiTanggalList,
        'presensiMatrixRows'   => $presensiMatrixRows,
        'bulanOptions'         => $bulanOptions,
        'bulanDipilih'         => $bulanDipilih,
    ]);
}

   public function keluarkanAnggota(Request $request, Ekskul $ekskul, EkskulAnggota $anggota)
{
    if ($anggota->ekskul_id !== $ekskul->id) {
        return back()->with('error', 'Data anggota tidak sesuai dengan ekskul.');
    }

    if ($anggota->status !== 'aktif') {
        return back()->with('error', 'Anggota ini sudah tidak berstatus aktif.');
    }

    $anggota->status = 'nonaktif';
    $anggota->tanggal_keluar = now()->toDateString();
    $anggota->save();

    return back()->with('success', 'Siswa berhasil dikeluarkan dari ekskul.');
}

    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'nama'        => ['required', 'string', 'max:120', Rule::unique('ekskul', 'nama')->ignore($ignoreId)],
            'pembina_id'  => ['required', 'exists:guru,id'],
            'hari'        => ['nullable', 'string', 'max:20'],
            'jam_mulai'   => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after_or_equal:jam_mulai'],
            'lokasi'      => ['nullable', 'string', 'max:100'],
        ], [], [
            'pembina_id' => 'pembina',
        ]);
    }
}