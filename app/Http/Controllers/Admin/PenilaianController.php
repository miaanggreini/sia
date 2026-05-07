<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $taAktif = TahunAjaran::where('status', 'aktif')->latest('id')->first();

        $daftarTahunAjaran = TahunAjaran::query()
            ->orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? (int) $request->get('tahun_ajaran_id')
            : (int) (
                optional($taAktif)->id
                ?? optional($daftarTahunAjaran->first())->id
            );

        $taDipilih = $daftarTahunAjaran->firstWhere('id', $tahunAjaranId);

        $isTahunAjaranAktif = $taAktif
            && (int) $tahunAjaranId === (int) $taAktif->id;

        $q = trim((string) $request->get('q', ''));
        $rombelId = $request->get('rombel_id');
        $statusFinal = $request->get('status_final');

        $rombels = Rombel::query()
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

        $jadwalQuery = Jadwal::query()
            ->with([
                'rombel:id,nama_rombel,tingkat,tahun_ajaran_id',
                'rombel.tahunAjaran',
                'mataPelajaran:id,nama_mapel',
                'guru:id,nama',
            ])
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->select('jadwal.*')
            ->where('rombel.tahun_ajaran_id', $tahunAjaranId)
            ->when($rombelId, function ($q2) use ($rombelId) {
                $q2->where('jadwal.rombel_id', $rombelId);
            })
            ->when($q !== '', function ($q2) use ($q) {
                $q2->where(function ($sub) use ($q) {
                    $sub->whereHas('mataPelajaran', function ($m) use ($q) {
                        $m->where('nama_mapel', 'like', "%{$q}%");
                    })
                    ->orWhereHas('guru', function ($g) use ($q) {
                        $g->where('nama', 'like', "%{$q}%");
                    })
                    ->orWhere('rombel.nama_rombel', 'like', "%{$q}%");
                });
            })
            ->orderByRaw("
                CASE rombel.tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('rombel.nama_rombel')
            ->orderBy('jadwal.mata_pelajaran_id');

        $jadwals = $jadwalQuery->paginate(10)->withQueryString();

        $items = $jadwals->through(function ($jadwal) use ($tahunAjaranId, $isTahunAjaranAktif) {
            /*
             * Tahun ajaran aktif:
             *   hitung siswa aktif saja.
             *
             * Tahun ajaran lama:
             *   hitung riwayat siswa pada rombel tersebut, meskipun aktif = 0.
             */
            $totalSiswaQuery = Siswa::join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
                ->where('sr.rombel_id', $jadwal->rombel_id)
                ->where('sr.tahun_ajaran_id', $tahunAjaranId);

            if ($isTahunAjaranAktif) {
                $totalSiswaQuery->where('sr.aktif', 1);
            }

            $totalSiswa = $totalSiswaQuery->count();

            $nilaiRows = Nilai::query()
                ->where('jadwal_id', $jadwal->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get();

            $progress = [
                'lm1' => $nilaiRows->whereNotNull('lm1_nilai')->count(),
                'lm2' => $nilaiRows->whereNotNull('lm2_nilai')->count(),
                'lm3' => $nilaiRows->whereNotNull('lm3_nilai')->count(),
                'lm4' => $nilaiRows->whereNotNull('lm4_nilai')->count(),
            ];

            $finalCount = $nilaiRows->where('status_penilaian', 'final')->count();

            $status = ($totalSiswa > 0 && $finalCount >= $totalSiswa)
                ? 'final'
                : 'draft';

            $avg = $nilaiRows->whereNotNull('nilai_akhir')->avg('nilai_akhir');
            $avg = $avg !== null ? round($avg, 2) : null;

            $jadwal->siswa_total = $totalSiswa;
            $jadwal->progress = $progress;
            $jadwal->status_final = $status;
            $jadwal->avg = $avg;

            return $jadwal;
        });

        if ($statusFinal) {
            $filtered = collect($items->items())
                ->filter(function ($row) use ($statusFinal) {
                    return ($row->status_final ?? 'draft') === $statusFinal;
                })
                ->values();

            $jadwals->setCollection($filtered);
        }

        return view('admin.penilaian.index', [
            'items' => $jadwals,
            'rombels' => $rombels,
            'taAktif' => $taAktif,
            'taDipilih' => $taDipilih,
            'daftarTahunAjaran' => $daftarTahunAjaran,
            'tahunAjaranId' => $tahunAjaranId,
            'isTahunAjaranAktif' => $isTahunAjaranAktif,
            'q' => $q,
            'rombel_id' => $rombelId,
            'statusFinal' => $statusFinal,
        ]);
    }

public function show(Request $request, $id)
{
    $jadwal = Jadwal::with([
        'rombel:id,nama_rombel,tingkat,tahun_ajaran_id',
        'rombel.tahunAjaran',
        'mataPelajaran:id,nama_mapel,kkm',
        'guru:id,nama',
    ])->findOrFail($id);

    $taAktif = TahunAjaran::where('status', 'aktif')->latest('id')->first();

    /*
     * Tahun ajaran rombel/jadwal.
     * Ini dipakai untuk mengambil daftar siswa dari siswa_rombel.
     */
    $tahunAjaranRombelId = (int) ($jadwal->rombel->tahun_ajaran_id ?? 0);
    $taRombel = TahunAjaran::find($tahunAjaranRombelId);

    /*
     * Semester yang dipilih untuk monitoring.
     * Karena tabel tahun_ajaran kamu hanya 1 record per tahun ajaran,
     * maka semester tidak dicari dari id tahun ajaran lain,
     * tapi langsung dari request semester.
     */
    $semesterDefault = $taRombel->semester
        ?? $taAktif->semester
        ?? 'Ganjil';

    $semesterDipilih = $request->filled('semester')
        ? $request->get('semester')
        : $semesterDefault;

    $semesterDipilih = ucfirst(strtolower(trim((string) $semesterDipilih)));

    if (!in_array($semesterDipilih, ['Ganjil', 'Genap'], true)) {
        $semesterDipilih = 'Ganjil';
    }

    $isTahunAjaranAktif = $taAktif
        && (int) $tahunAjaranRombelId === (int) $taAktif->id;

    /*
     * Daftar siswa tetap mengikuti rombel/jadwal.
     * Jadi saat admin memilih semester Ganjil/Genap,
     * daftar siswa tidak berubah.
     */
    $siswaQuery = Siswa::join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
        ->where('sr.rombel_id', $jadwal->rombel_id)
        ->where('sr.tahun_ajaran_id', $tahunAjaranRombelId)
        ->select('siswa.id', 'siswa.nama', 'siswa.nis', 'siswa.nisn')
        ->orderBy('siswa.nama');

    /*
     * Kalau rombel masih pada tahun ajaran aktif, tampilkan siswa aktif saja.
     * Kalau rombel sudah histori, tampilkan riwayat anggota rombel tersebut.
     */
    if ($isTahunAjaranAktif) {
        $siswaQuery->where('sr.aktif', 1);
    }

    $siswa = $siswaQuery->get();

    /*
     * Nilai mengikuti tahun ajaran rombel + semester yang dipilih.
     */
    $nilaiMap = Nilai::query()
        ->where('jadwal_id', $jadwal->id)
        ->where('tahun_ajaran_id', $tahunAjaranRombelId)
        ->where('semester', $semesterDipilih)
        ->get()
        ->keyBy('siswa_id');

    $kkm = $jadwal->mataPelajaran->kkm ?? null;

    $nilai = $siswa->map(function ($s) use ($nilaiMap, $kkm) {
        $row = $nilaiMap->get($s->id);

        $nilaiAkhir = $row->nilai_akhir ?? null;

        $statusKetuntasan = null;

        if ($nilaiAkhir !== null && $kkm !== null) {
            $statusKetuntasan = ((float) $nilaiAkhir >= (float) $kkm)
                ? 'tuntas'
                : 'tidak_tuntas';
        }

        return (object) [
            'siswa_id' => $s->id,
            'nis' => $s->nis,
            'nisn' => $s->nisn,
            'nama' => $s->nama,

            'lm1_tp1' => $row->lm1_tp1 ?? null,
            'lm1_tp2' => $row->lm1_tp2 ?? null,
            'lm1_tp3' => $row->lm1_tp3 ?? null,
            'lm1_tp4' => $row->lm1_tp4 ?? null,

            'lm2_tp1' => $row->lm2_tp1 ?? null,
            'lm2_tp2' => $row->lm2_tp2 ?? null,
            'lm2_tp3' => $row->lm2_tp3 ?? null,
            'lm2_tp4' => $row->lm2_tp4 ?? null,

            'lm3_tp1' => $row->lm3_tp1 ?? null,
            'lm3_tp2' => $row->lm3_tp2 ?? null,
            'lm3_tp3' => $row->lm3_tp3 ?? null,
            'lm3_tp4' => $row->lm3_tp4 ?? null,

            'lm4_tp1' => $row->lm4_tp1 ?? null,
            'lm4_tp2' => $row->lm4_tp2 ?? null,
            'lm4_tp3' => $row->lm4_tp3 ?? null,
            'lm4_tp4' => $row->lm4_tp4 ?? null,

            'lm1_nilai' => $row->lm1_nilai ?? null,
            'lm2_nilai' => $row->lm2_nilai ?? null,
            'lm3_nilai' => $row->lm3_nilai ?? null,
            'lm4_nilai' => $row->lm4_nilai ?? null,

            'nilai_akhir' => $nilaiAkhir,

            /*
             * Status ketuntasan untuk kolom Status.
             */
            'status' => $statusKetuntasan,
            'status_ketuntasan' => $statusKetuntasan,

            /*
             * Status finalisasi untuk kolom Finalisasi.
             */
            'status_penilaian' => $row->status_penilaian ?? 'draft',
            'finalized_at' => $row->finalized_at ?? null,
        ];
    });

    return view('admin.penilaian.show', [
        'jadwal' => $jadwal,
        'nilai' => $nilai,
        'taAktif' => $taAktif,
        'taDipilih' => $taRombel,
        'taRombel' => $taRombel,
        'tahunAjaranId' => $tahunAjaranRombelId,
        'tahunAjaranRombelId' => $tahunAjaranRombelId,
        'semesterDipilih' => $semesterDipilih,
        'isTahunAjaranAktif' => $isTahunAjaranAktif,
    ]);
}
}