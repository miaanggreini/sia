<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NilaiController extends Controller
{
public function index(Request $request)
{
    $user = auth()->user();

    $siswa = $user->siswa ?? Siswa::where('user_id', $user->id)->firstOrFail();
    $taAktif = $this->getTahunAjaranAktif();

    $rows = $this->baseNilaiQuery($siswa)->get();

    $taAktifLabel = $taAktif?->nama_tahun ?? '-';

    $overallAvg = $rows->pluck('rata')->filter(fn ($v) => $v !== null)->avg();
    $overallAvg = $overallAvg !== null ? round($overallAvg, 2) : null;

    $groups = $rows
        ->groupBy(function ($row) {
            return implode('|', [
                $row->ta ?? '-',
                $row->semester ?? '-',
                $row->tingkat ?? '-',
            ]);
        })
        ->map(function (Collection $items) {
            $first = $items->first();

            $avg = $items->pluck('rata')->filter(fn ($v) => $v !== null)->avg();
            $avg = $avg !== null ? round($avg, 2) : null;

            return (object) [
                'ta'       => $first->ta ?? '-',
                'semester' => $first->semester ?? '-',
                'tingkat'  => $first->tingkat ?? '-',
                'avg'      => $avg,
                'rows'     => $items->values(),
            ];
        })
        ->values();

    $chartLabels = $groups->map(function ($g) {
        $semester = $this->normalizeSemester($g->semester);
        return 'TA ' . ($g->ta ?? '-') . ' - ' . ($g->tingkat ?? '-') . '/' . ($semester ?? '-');
    })->values()->all();

    $chartValues = $groups->map(function ($g) {
        return $g->avg ?? 0;
    })->values()->all();

    return view('siswa.nilai.index', [
        'siswa'       => $siswa,
        'taAktif'     => $taAktifLabel,
        'overallAvg'  => $overallAvg,
        'groups'      => $groups,
        'chartLabels' => $chartLabels,
        'chartValues' => $chartValues,
    ]);
}
public function detail(Request $request)
{
    $user = auth()->user();

    $siswa = $user->siswa ?? Siswa::where('user_id', $user->id)->firstOrFail();

    $tahunAjaran = $request->query('tahun_ajaran');
    $semester = $request->query('semester');
    $tingkat = $request->query('tingkat');

    $query = $this->pdfNilaiQuery($siswa);

    if ($tahunAjaran) {
        $query->where('ta.nama_tahun', $tahunAjaran);
    }

    if ($semester) {
        $query->where('n.semester', $semester);
    }

    if ($tingkat) {
        $query->where('r.tingkat', $tingkat);
    }

    $rows = $query
        ->orderBy('mp.nama_mapel')
        ->get();

    $rataSemester = $rows->pluck('nilai_akhir')
        ->filter(fn ($v) => $v !== null)
        ->avg();

    $rataSemester = $rataSemester !== null ? round($rataSemester, 2) : null;

    return view('siswa.nilai.detail', [
        'siswa' => $siswa,
        'rows' => $rows,
        'tahunAjaran' => $tahunAjaran,
        'semester' => $semester,
        'tingkat' => $tingkat,
        'rataSemester' => $rataSemester,
    ]);
}
    public function downloadPdf(Request $request)
    {
        $user = auth()->user();
        $siswa = $user->siswa ?? Siswa::where('user_id', $user->id)->firstOrFail();

        $tahunAjaran = $request->get('tahun_ajaran');
        $semester    = $request->get('semester');
        $tingkat     = $request->get('tingkat');

        if (!$tahunAjaran || !$semester) {
            return redirect()->route('siswa.nilai.index')
                ->withErrors(['msg' => 'Tahun ajaran dan semester harus dipilih untuk mencetak PDF.']);
        }

        $nilai = $this->pdfNilaiQuery($siswa)
            ->where('ta.nama_tahun', $tahunAjaran)
            ->where('n.semester', $semester)
            ->when($tingkat && $tingkat !== '—', fn ($q) => $q->where('r.tingkat', $tingkat))
            ->orderBy('mp.nama_mapel')
            ->get()
            ->values();

        $nilai = $nilai->map(function ($row, $index) {
            $row->no = $index + 1;
            return $row;
        });

        $rataSemester = $nilai->pluck('nilai_akhir')->filter(fn ($v) => $v !== null)->avg();
        $rataSemester = $rataSemester !== null ? round($rataSemester, 2) : null;

        $ekskulQuery = DB::table('anggota_ekskul as ae')
            ->join('ekskul as e', 'e.id', '=', 'ae.ekskul_id')
            ->select(
                'e.nama as nama_ekskul',
                'ae.nilai_akhir',
                'ae.predikat',
                'ae.deskripsi'
            )
            ->where('ae.siswa_id', $siswa->id);

        if (Schema::hasColumn('anggota_ekskul', 'tahun_ajaran_id')) {
            $taId = DB::table('tahun_ajaran')->where('nama_tahun', $tahunAjaran)->value('id');
            if ($taId) {
                $ekskulQuery->where('ae.tahun_ajaran_id', $taId);
            }
        }

        if (Schema::hasColumn('anggota_ekskul', 'semester')) {
            $ekskulQuery->where('ae.semester', $semester);
        }

        $ekskul = $ekskulQuery
            ->orderBy('e.nama')
            ->get();

        $kelas = $this->resolveKelasSiswa($siswa->id, $tahunAjaran, $tingkat);
        $logoPath = public_path('images/logo-sman2.png');

        $pdf = Pdf::loadView('siswa.nilai.pdf', [
            'siswa'         => $siswa,
            'nilai'         => $nilai,
            'ekskul'        => $ekskul,
            'rataSemester'  => $rataSemester,
            'semester'      => $semester,
            'tahunAjaran'   => $tahunAjaran,
            'kelas'         => $kelas,
            'namaSekolah'   => 'SMA NEGERI 2 TEMANGGUNG',
            'alamatSekolah' => 'Jalan Pahlawan, Giyanti, Temanggung, Jawa Tengah',
            'logoPath'      => $logoPath,
        ])->setPaper('a4', 'landscape');

        $filename = 'laporan-nilai-' . ($siswa->nis ?? $siswa->id) . '-' . str_replace('/', '-', $tahunAjaran) . '-' . strtolower($semester) . '.pdf';

        return $pdf->download($filename);
    }

 private function baseNilaiQuery(Siswa $siswa)
{
    return DB::table('nilai as n')
        ->join('jadwal as j', 'j.id', '=', 'n.jadwal_id')
        ->leftJoin('mata_pelajaran as mp', 'mp.id', '=', 'j.mata_pelajaran_id')
        ->leftJoin('guru as g', 'g.id', '=', 'j.guru_id')
        ->leftJoin('rombel as r', 'r.id', '=', 'j.rombel_id')
        ->leftJoin('tahun_ajaran as ta', 'ta.id', '=', 'n.tahun_ajaran_id')
        ->select([
            'n.id',
            'n.semester',
            'n.status',
            'n.status_penilaian',
            'n.finalized_at',

            'n.lm1_tp1',
            'n.lm1_tp2',
            'n.lm1_tp3',
            'n.lm1_tp4',
            'n.lm1_nilai',

            'n.lm2_tp1',
            'n.lm2_tp2',
            'n.lm2_tp3',
            'n.lm2_tp4',
            'n.lm2_nilai',

            'n.lm3_tp1',
            'n.lm3_tp2',
            'n.lm3_tp3',
            'n.lm3_tp4',
            'n.lm3_nilai',

            'n.lm4_tp1',
            'n.lm4_tp2',
            'n.lm4_tp3',
            'n.lm4_tp4',
            'n.lm4_nilai',

            'n.nilai_akhir as rata',
            'n.nilai_akhir',

            DB::raw('COALESCE(mp.nama_mapel, "-") as mapel'),
            DB::raw('COALESCE(mp.kkm, 0) as kkm'),
            DB::raw('COALESCE(g.nama, "-") as guru'),
            DB::raw('COALESCE(r.tingkat, "-") as tingkat'),
            DB::raw('COALESCE(ta.nama_tahun, "-") as ta'),
        ])
        ->where('n.siswa_id', $siswa->id)
        ->where(function ($q) {
            $q->whereNotNull('n.lm1_nilai')
              ->orWhereNotNull('n.lm2_nilai')
              ->orWhereNotNull('n.lm3_nilai')
              ->orWhereNotNull('n.lm4_nilai')
              ->orWhereNotNull('n.nilai_akhir');
        })
        ->where(function ($q) use ($siswa) {
            $q->where('mp.nama_mapel', 'not like', 'Pendidikan Agama%');

            if (!empty($siswa->agama)) {
                $q->orWhere('mp.nama_mapel', 'like', '%' . $siswa->agama . '%');
            }
        })
        ->orderBy('ta.nama_tahun')
        ->orderByRaw("FIELD(n.semester, 'Ganjil', 'Genap')")
        ->orderBy('mp.nama_mapel');
}
  private function pdfNilaiQuery(Siswa $siswa)
{
    return DB::table('nilai as n')
        ->join('jadwal as j', 'j.id', '=', 'n.jadwal_id')
        ->leftJoin('mata_pelajaran as mp', 'mp.id', '=', 'j.mata_pelajaran_id')
        ->leftJoin('guru as g', 'g.id', '=', 'j.guru_id')
        ->leftJoin('rombel as r', 'r.id', '=', 'j.rombel_id')
        ->leftJoin('tahun_ajaran as ta', 'ta.id', '=', 'n.tahun_ajaran_id')
        ->select([
            'n.*',
            DB::raw('COALESCE(mp.nama_mapel, "-") as mapel'),
            DB::raw('COALESCE(mp.kkm, 0) as kkm'),
            DB::raw('COALESCE(g.nama, "-") as guru'),
            DB::raw('COALESCE(r.tingkat, "-") as tingkat'),
            DB::raw('COALESCE(ta.nama_tahun, "-") as ta'),
        ])
        ->where('n.siswa_id', $siswa->id)
        ->where(function ($q) {
            $q->whereNotNull('n.lm1_nilai')
              ->orWhereNotNull('n.lm2_nilai')
              ->orWhereNotNull('n.lm3_nilai')
              ->orWhereNotNull('n.lm4_nilai')
              ->orWhereNotNull('n.nilai_akhir');
        })
        ->where(function ($q) use ($siswa) {
            $q->where('mp.nama_mapel', 'not like', 'Pendidikan Agama%');

            if (!empty($siswa->agama)) {
                $q->orWhere('mp.nama_mapel', 'like', '%' . $siswa->agama . '%');
            }
        });
}

    private function getTahunAjaranAktif()
    {
        return TahunAjaran::where('status', 'aktif')->latest('id')->first();
    }

    private function normalizeSemester(?string $semester): ?string
    {
        if (!$semester) {
            return null;
        }

        $s = strtolower(trim($semester));

        if (in_array($s, ['1', 'ganjil', 'odd', 'semester 1', 'smt 1', 'sm 1'])) {
            return 'Ganjil';
        }

        if (in_array($s, ['2', 'genap', 'even', 'semester 2', 'smt 2', 'sm 2'])) {
            return 'Genap';
        }

        return ucfirst($semester);
    }

    private function resolveKelasSiswa(int $siswaId, ?string $tahunAjaran, ?string $tingkat): string
    {
        $q = DB::table('siswa_rombel as sr')
            ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
            ->leftJoin('tahun_ajaran as ta', 'ta.id', '=', 'sr.tahun_ajaran_id')
            ->where('sr.siswa_id', $siswaId)
            ->orderByDesc('sr.id');

        if ($tahunAjaran) {
            $q->where('ta.nama_tahun', $tahunAjaran);
        }

        if ($tingkat && $tingkat !== '—') {
            $q->where('r.tingkat', $tingkat);
        }

        $row = $q->select('r.nama_rombel', 'r.tingkat')->first();

        if (!$row) {
            return '-';
        }

        return $row->nama_rombel ?? (($row->tingkat ?? '-') . '');
    }
}