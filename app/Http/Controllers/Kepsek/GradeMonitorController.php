<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rombel;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Siswa;
use App\Models\TahunAjaran;

class GradeMonitorController extends Controller
{
    public function index(Request $request)
    {
        $taAktifModel = $this->getTahunAjaranAktif();

        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        if (!$tahunAjaranId) {
            $tahunAjaranId = $taAktifModel?->id
                ?? TahunAjaran::orderByDesc('id')->value('id');
        }

        $tahunDipilih = TahunAjaran::find($tahunAjaranId);

        if (!$tahunDipilih) {
            $tahunDipilih = $taAktifModel ?? TahunAjaran::orderByDesc('id')->first();
            $tahunAjaranId = $tahunDipilih?->id;
        }

        $semester = $request->query('semester');

        if (!$semester) {
            if ($tahunDipilih && !empty($tahunDipilih->semester)) {
                $semester = $tahunDipilih->semester;
            } elseif ($taAktifModel && !empty($taAktifModel->semester)) {
                $semester = $taAktifModel->semester;
            } else {
                $semester = 'Ganjil';
            }
        }

        $q = trim((string) $request->query('q', ''));
        $rombelId = $request->query('rombel_id');
        $statusFinal = $request->query('status_final');

        $daftarTahunAjaran = TahunAjaran::orderByDesc('id')->get();

        $rombels = Rombel::query()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tingkat', 'tahun_ajaran_id']);

        $jadwalQuery = Jadwal::query()
            ->with([
                'rombel:id,nama_rombel,tingkat,tahun_ajaran_id',
'rombel.tahunAjaran:id,nama_tahun,semester,status',                'mataPelajaran:id,nama_mapel,kkm',
                'guru:id,nama',
            ])
            ->whereHas('rombel', function ($rombel) use ($tahunAjaranId) {
                $rombel->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->when($rombelId, fn ($qr) => $qr->where('rombel_id', $rombelId))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->whereHas('rombel', fn ($r) => $r->where('nama_rombel', 'like', "%{$q}%"))
                        ->orWhereHas('mataPelajaran', fn ($m) => $m->where('nama_mapel', 'like', "%{$q}%"))
                        ->orWhereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%"));
                });
            })
            ->orderBy('rombel_id')
            ->orderBy('mata_pelajaran_id');

        $items = $jadwalQuery->get()->map(function ($jadwal) use ($tahunAjaranId, $semester) {
            $totalSiswa = Siswa::join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
                ->where('sr.rombel_id', $jadwal->rombel_id)
                ->where('sr.tahun_ajaran_id', $tahunAjaranId)
                ->count();

            $nilaiRows = Nilai::query()
                ->where('jadwal_id', $jadwal->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('semester', $semester)
                ->get();

            $progress = [
                'lm1' => $nilaiRows->whereNotNull('lm1_nilai')->count(),
                'lm2' => $nilaiRows->whereNotNull('lm2_nilai')->count(),
                'lm3' => $nilaiRows->whereNotNull('lm3_nilai')->count(),
                'lm4' => $nilaiRows->whereNotNull('lm4_nilai')->count(),
            ];

            $finalCount = $nilaiRows->where('status_penilaian', 'final')->count();

            $isFinal = $totalSiswa > 0
                && $nilaiRows->count() >= $totalSiswa
                && $finalCount >= $totalSiswa;

            $avg = $nilaiRows->whereNotNull('nilai_akhir')->avg('nilai_akhir');
            $avg = $avg !== null ? round($avg, 2) : null;

            $jadwal->siswa_total = $totalSiswa;
            $jadwal->progress = $progress;
            $jadwal->status_final = $isFinal ? 'final' : 'draft';
            $jadwal->avg = $avg;
            $jadwal->diinput = max($progress);

            return $jadwal;
        });

        if ($statusFinal) {
            $items = $items->filter(function ($row) use ($statusFinal) {
                return ($row->status_final ?? 'draft') === $statusFinal;
            })->values();
        }

        $tahunLabel = $this->formatTahunAjaran($tahunDipilih);

        return view('kepsek.nilai.index', [
            'items' => $items,
            'rombels' => $rombels,
            'daftarTahunAjaran' => $daftarTahunAjaran,
            'taAktif' => $taAktifModel,
            'tahunDipilih' => $tahunDipilih,
            'tahunAjaranId' => $tahunAjaranId,
            'tahunLabel' => $tahunLabel,
            'semester' => $semester,
            'q' => $q,
            'rombel_id' => $rombelId,
            'statusFinal' => $statusFinal,
        ]);
    }

    public function show(Request $request, Jadwal $jadwal)
    {
        $taAktif = $this->getTahunAjaranAktif();

        $jadwal->load([
            'rombel:id,nama_rombel,tahun_ajaran_id',
'rombel.tahunAjaran:id,nama_tahun,semester,status',            'mataPelajaran:id,nama_mapel,kkm',
            'guru:id,nama',
        ]);

        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        if (!$tahunAjaranId) {
            $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id
                ?? $taAktif?->id
                ?? TahunAjaran::orderByDesc('id')->value('id');
        }

        $tahunDipilih = TahunAjaran::find($tahunAjaranId);

        if (!$tahunDipilih) {
            $tahunDipilih = $taAktif ?? TahunAjaran::orderByDesc('id')->first();
            $tahunAjaranId = $tahunDipilih?->id;
        }

        $semester = $request->query('semester');

        if (!$semester) {
            if ($tahunDipilih && !empty($tahunDipilih->semester)) {
                $semester = $tahunDipilih->semester;
            } elseif ($taAktif && !empty($taAktif->semester)) {
                $semester = $taAktif->semester;
            } else {
                $semester = 'Ganjil';
            }
        }

        $daftarTahunAjaran = TahunAjaran::orderByDesc('id')->get();

        $siswa = Siswa::join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $jadwal->rombel_id)
            ->where('sr.tahun_ajaran_id', $tahunAjaranId)
            ->select('siswa.id', 'siswa.nis', 'siswa.nisn', 'siswa.nama')
            ->orderBy('siswa.nama')
            ->get();

        $nilaiMap = Nilai::query()
            ->where('jadwal_id', $jadwal->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->get()
            ->keyBy('siswa_id');

        $rombel = $jadwal->rombel->nama_rombel ?? '-';
        $mapel = $jadwal->mataPelajaran->nama_mapel ?? '-';
        $guruNama = $jadwal->guru->nama ?? '-';

        $tahunLabel = $this->formatTahunAjaran($tahunDipilih);

        $taLabel = $tahunLabel . ' / ' . $semester;

        return view('kepsek.nilai.show', [
            'jadwal' => $jadwal,
            'siswa' => $siswa,
            'nilai' => $nilaiMap,
            'rombel' => $rombel,
            'mapel' => $mapel,
            'guruNama' => $guruNama,
            'taAktif' => $taLabel,
            'taLabel' => $taLabel,
            'taAktifModel' => $taAktif,
            'tahunDipilih' => $tahunDipilih,
            'tahunAjaranId' => $tahunAjaranId,
            'tahunLabel' => $tahunLabel,
            'semester' => $semester,
            'daftarTahunAjaran' => $daftarTahunAjaran,
        ]);
    }

    private function getTahunAjaranAktif()
    {
        if (method_exists(TahunAjaran::class, 'aktif')) {
            $result = TahunAjaran::aktif();

            if ($result instanceof \Illuminate\Database\Eloquent\Builder) {
                return $result->first();
            }

            return $result;
        }

        return TahunAjaran::where('status', 'aktif')
            ->orderByDesc('id')
            ->first();
    }

    private function formatTahunAjaran($tahunAjaran): string
    {
        if (!$tahunAjaran) {
            return '-';
        }

        return $tahunAjaran->nama_tahun
            ?? $tahunAjaran->tahun
            ?? '-';
    }
}