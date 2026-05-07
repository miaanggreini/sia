<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Rombel;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// PDF & streamed CSV
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WaliKelasController extends Controller
{
    private function rombelOrderColumn(): string
    {
        if (Schema::hasColumn('rombel','nama_rombel')) return 'nama_rombel';
        if (Schema::hasColumn('rombel','nama_kelas'))  return 'nama_kelas';
        if (Schema::hasColumn('rombel','kode'))        return 'kode';
        return 'id';
    }

    private function progressForJadwal(Jadwal $j): array
    {
        $siswaIds = method_exists($j, 'sumberSiswaQuery')
            ? $j->sumberSiswaQuery()->pluck('siswa.id')
            : DB::table('siswa_rombel')->where('rombel_id', $j->rombel_id)->where('aktif', 1)->pluck('siswa_id');

        $total = $siswaIds->count();
        $rows  = DB::table('nilai')->where('jadwal_id', $j->id)->whereIn('siswa_id',$siswaIds)->get()->keyBy('siswa_id');

        $miss = ['UH'=>0,'Tugas'=>0,'UTS'=>0,'UAS'=>0];
        foreach ($siswaIds as $sid) {
            $r = $rows[$sid] ?? null;
            if (empty($r?->nilai_uh))    $miss['UH']++;
            if (empty($r?->nilai_tugas)) $miss['Tugas']++;
            if (empty($r?->nilai_uts))   $miss['UTS']++;
            if (empty($r?->nilai_uas))   $miss['UAS']++;
        }

        return [
            'total'   => $total,
            'done'    => [
                'UH'    => $total - $miss['UH'],
                'Tugas' => $total - $miss['Tugas'],
                'UTS'   => $total - $miss['UTS'],
                'UAS'   => $total - $miss['UAS'],
            ],
            'complete'=> array_sum($miss) === 0,
        ];
    }

    /** ======== Monitoring list (judul menampilkan TA aktif) ======== */
public function monitoringPenilaian(Request $request)
    {
        $guru = auth()->user()->guru;
        abort_if(! $guru, 403, 'Guru tidak ditemukan.');

        // Tahun ajaran aktif
        $taAktif = TahunAjaran::aktifKode()
            ?? TahunAjaran::where('status', 'aktif')->value('nama_tahun');

        // Rombel yang dia wali-kan di TA aktif
        $rombel = Rombel::where('guru_id', $guru->id)
            ->when($taAktif, fn($q) => $q->where('tahun_ajaran', $taAktif))
            ->first();

        // Kalau bukan wali kelas, tetap kirim rombel = null supaya view tidak error
        if (! $rombel) {
            return view('guru.wali_kelas.monitoring_penilaian', [
                'rombel'       => null,
                'mapelOptions' => collect(),
                'rows'         => collect(),
                'totalSiswa'   => 0,
                'mapelId'      => null,
                'q'            => null,
            ]);
        }

        $mapelId = $request->mapel_id;
        $q       = $request->q;

        // daftar mapel yg terkait rombel ini
        $mapelOptions = MataPelajaran::select('mata_pelajaran.id','mata_pelajaran.nama_mapel')
            ->join('jadwal','jadwal.mapel_id','=','mata_pelajaran.id')
            ->where('jadwal.rombel_id', $rombel->id)
            ->distinct()
            ->orderBy('nama_mapel')
            ->get();

        $rows = DB::table('siswa')
            ->join('siswa_rombel as sr', function ($j) use ($rombel, $taAktif) {
                $j->on('sr.siswa_id', '=', 'siswa.id')
                  ->where('sr.rombel_id', '=', $rombel->id)
                  ->where('sr.aktif', '=', 1);

                if ($taAktif) {
                    $j->where('sr.tahun_ajaran', '=', $taAktif);
                }
            })
            ->leftJoin('nilai', 'nilai.siswa_id', '=', 'siswa.id')
            ->leftJoin('jadwal', function ($j) use ($rombel) {
                $j->on('jadwal.id', '=', 'nilai.jadwal_id')
                  ->where('jadwal.rombel_id', '=', $rombel->id);
            })
            ->leftJoin('mata_pelajaran', 'mata_pelajaran.id', '=', 'jadwal.mapel_id')
            ->when($mapelId, fn($qr) => $qr->where('mata_pelajaran.id', $mapelId))
            ->when($q, function ($qr) use ($q) {
                $like = "%{$q}%";
                $qr->where(function ($w) use ($like) {
                    $w->where('siswa.nis','like',$like)
                      ->orWhere('siswa.nama','like',$like);
                });
            })
            ->groupBy('siswa.id', 'siswa.nis', 'siswa.nama')
            ->selectRaw('
                siswa.id   as siswa_id,
                siswa.nis  as nis,
                siswa.nama as nama,
                COUNT(nilai.id)        as jumlah_nilai,
                AVG(nilai.nilai_akhir) as rata_nilai
            ')
            ->orderBy('siswa.nama')
            ->get();

        $rows->transform(function ($row) {
            $n = $row->rata_nilai;
            if (is_null($n)) {
                $row->predikat = '-';
            } elseif ($n >= 90) {
                $row->predikat = 'A';
            } elseif ($n >= 80) {
                $row->predikat = 'B';
            } elseif ($n >= 70) {
                $row->predikat = 'C';
            } else {
                $row->predikat = 'D';
            }
            return $row;
        });

        $totalSiswa = $rows->count();

        return view('guru.wali_kelas.monitoring_penilaian', compact(
            'rombel',
            'mapelOptions',
            'rows',
            'totalSiswa',
            'mapelId',
            'q'
        ));
    }



    public function monitoringPresensi(Request $request)
{
    $guru = auth()->user()->guru;
    abort_if(! $guru, 403, 'Guru tidak ditemukan.');

    // TA aktif
    $taAktif = TahunAjaran::aktifKode()
        ?? TahunAjaran::where('status', 'aktif')->value('nama_tahun');

    // Rombel yang dia wali-kan di TA aktif
    $rombel = Rombel::where('guru_id', $guru->id)
        ->when($taAktif, fn($q) => $q->where('tahun_ajaran', $taAktif))
        ->firstOrFail();

    // Filter
    $tanggal = $request->tanggal;      // nullable
    $mapelId = $request->mapel_id;     // nullable

    // Daftar mapel di rombel itu saja (buat dropdown)
    $mapelOptions = MataPelajaran::select('mata_pelajaran.id','mata_pelajaran.nama_mapel')
        ->join('jadwal','jadwal.mapel_id','=','mata_pelajaran.id')
        ->where('jadwal.rombel_id', $rombel->id)
        ->distinct()
        ->orderBy('nama_mapel')
        ->get();

    // Query rekap presensi lama kamu boleh tetap dipakai,
    // hanya pastikan pakai $rombel->id, BUKAN request('rombel_id') lagi.
    $rekapQuery = \DB::table('presensi')
        ->join('sesi_presensi','sesi_presensi.id','=','presensi.sesi_id')
        ->join('jadwal','jadwal.id','=','sesi_presensi.jadwal_id')
        ->join('mata_pelajaran','mata_pelajaran.id','=','jadwal.mapel_id')
        ->where('jadwal.rombel_id', $rombel->id)
        ->selectRaw('
            sesi_presensi.id as sesi_id,
            sesi_presensi.tanggal,
            mata_pelajaran.id as mapel_id,
            mata_pelajaran.nama_mapel,
            SUM(CASE WHEN presensi.status = "hadir" THEN 1 ELSE 0 END) as stat_hadir,
            SUM(CASE WHEN presensi.status = "terlambat" THEN 1 ELSE 0 END) as stat_terlambat,
            SUM(CASE WHEN presensi.status = "izin" THEN 1 ELSE 0 END) as stat_izin,
            SUM(CASE WHEN presensi.status = "sakit" THEN 1 ELSE 0 END) as stat_sakit,
            SUM(CASE WHEN presensi.status = "alfa" THEN 1 ELSE 0 END) as stat_alfa,
            COUNT(*) as total
        ')
        ->groupBy(
            'sesi_presensi.id',
            'sesi_presensi.tanggal',
            'mata_pelajaran.id',
            'mata_pelajaran.nama_mapel'
        );

    if ($tanggal) {
        $rekapQuery->whereDate('sesi_presensi.tanggal', $tanggal);
    }

    if ($mapelId) {
        $rekapQuery->where('mata_pelajaran.id', $mapelId);
    }

    $rekap = $rekapQuery
        ->orderBy('sesi_presensi.tanggal','desc')
        ->get();

    $totalPertemuan = $rekap->count();

    return view('guru.wali_kelas.monitoring_presensi', compact(
        'rombel',
        'mapelOptions',
        'rekap',
        'totalPertemuan'
    ));
}

    /** ======== Detail nilai read-only untuk 1 jadwal ======== */
    public function monitoringDetail(Jadwal $jadwal)
    {
        // (opsional) pastikan user adalah wali rombel ini
        $user = auth()->user();
        $waliId = $user->guru->id ?? Guru::where('user_id',$user->id)->value('id');
        $rombelWaliId = Rombel::where('id',$jadwal->rombel_id)->value('guru_id');
        abort_if($waliId !== $rombelWaliId, 403);

        // ambil siswa & nilai
        $siswa = method_exists($jadwal,'sumberSiswaQuery')
            ? $jadwal->sumberSiswaQuery()->select('siswa.id','siswa.nis','siswa.nama')->orderBy('nama')->get()
            : DB::table('siswa')->select('id','nis','nama')
                ->join('siswa_rombel as sr','sr.siswa_id','=','siswa.id')
                ->where('sr.rombel_id',$jadwal->rombel_id)->where('sr.aktif',1)->orderBy('nama')->get();

        $nilai = DB::table('nilai')->where('jadwal_id',$jadwal->id)->get()->keyBy('siswa_id');

        $mapel = $jadwal->mataPelajaran->nama
              ?? $jadwal->mapel->nama
              ?? $jadwal->mapel->nama_mapel
              ?? 'Mapel';
        $rombel = Rombel::where('id',$jadwal->rombel_id)->value('nama_rombel');

        $taAktif = \App\Models\TahunAjaran::aktifKode()
            ?? \App\Models\TahunAjaran::where('status','aktif')->value('nama_tahun');

        return view('guru.wali_kelas.monitoring_detail', compact('jadwal','siswa','nilai','mapel','rombel','taAktif'));
    }

    /** ======== Kelas Saya: dengan pencarian + export CSV/PDF ======== */
public function kelasSaya(Request $request)
{
    $userId = auth()->id();

    $guruId = Guru::where('user_id', $userId)->value('id');

    abort_unless($guruId, 403, 'Akun ini belum terhubung dengan data guru.');

    $taAktif = TahunAjaran::where('status', 'aktif')->first();

    /*
    |--------------------------------------------------------------------------
    | Ambil rombel wali kelas
    |--------------------------------------------------------------------------
    | Prioritas:
    | 1. Rombel yang diwali guru pada tahun ajaran aktif.
    | 2. Jika tidak ada, ambil rombel terakhir yang pernah diwali guru.
    |
    | Ini penting karena guru bisa pernah menjadi wali kelas pada tahun ajaran
    | sebelumnya. Untuk histori, siswa_rombel biasanya sudah aktif = 0
    | setelah siswa naik kelas, jadi jangan bergantung pada sr.aktif = 1.
    */
    $rombelQuery = Rombel::with(['tahunAjaran', 'waliKelas', 'ruangKelas'])
        ->where('guru_id', $guruId);

    $rombel = null;

    if ($taAktif) {
        $rombel = (clone $rombelQuery)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->orderBy('nama_rombel')
            ->first();
    }

    if (!$rombel) {
        $rombel = (clone $rombelQuery)
            ->orderByDesc('tahun_ajaran_id')
            ->orderBy('nama_rombel')
            ->first();
    }

    $q = trim((string) $request->query('q', ''));

    $siswaList = collect();

    if ($rombel) {
        $tahunRombelId = $rombel->tahun_ajaran_id;

        $siswaList = DB::table('siswa as s')
            ->join('siswa_rombel as sr', function ($join) use ($rombel, $tahunRombelId) {
                $join->on('sr.siswa_id', '=', 's.id')
                    ->where('sr.rombel_id', '=', $rombel->id);

                if (!empty($tahunRombelId)) {
                    $join->where('sr.tahun_ajaran_id', '=', $tahunRombelId);
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($w) use ($q) {
                    $w->where('s.nama', 'like', "%{$q}%")
                        ->orWhere('s.nis', 'like', "%{$q}%")
                        ->orWhere('s.nisn', 'like', "%{$q}%");
                });
            })
            ->select('s.*', 'sr.aktif as status_keanggotaan')
            ->distinct()
            ->orderBy('s.nama')
            ->get();
    }

    if ($rombel) {
        $rombel->setRelation('siswa', $siswaList);
        $rombels = collect([$rombel]);
    } else {
        $rombels = collect();
    }

    $taLabel = $rombel?->tahunAjaran?->nama_tahun
        ?? $rombel?->tahunAjaran?->nama
        ?? $rombel?->tahunAjaran?->tahun
        ?? $rombel?->tahun_ajaran
        ?? $taAktif?->nama_tahun
        ?? $taAktif?->label
        ?? '-';

    return view('guru.wali_kelas.kelas_saya', [
        'rombels' => $rombels,
        'rombel' => $rombel,
        'kelas' => $rombel,
        'siswaList' => $siswaList,
        'taAktif' => $taLabel,
        'q' => $q,
    ]);
}

    /** ======== Detail profil siswa di rombel (wali) ======== */
    public function siswaShow(Rombel $rombel, Siswa $siswa)
    {
        $this->authorizeWali($rombel);

        // pastikan siswa memang anggota rombel ini (dan aktif)
        $isMember = DB::table('siswa_rombel')
            ->where('rombel_id', $rombel->id)
            ->where('siswa_id', $siswa->id)
            ->where('aktif', 1)
            ->exists();

        abort_unless($isMember, 404);

        return view('guru.wali_kelas.siswa_show', compact('rombel', 'siswa'));
    }

    public function siswaNilai(Rombel $rombel, Siswa $siswa)
    {
        $this->authorizeWali($rombel);

        $isMember = DB::table('siswa_rombel')
            ->where('rombel_id', $rombel->id)
            ->where('siswa_id', $siswa->id)
            ->where('aktif', 1)
            ->exists();
        abort_unless($isMember, 404);

        $jadwal = Jadwal::with(['guru','mataPelajaran','rombel'])
            ->where('rombel_id', $rombel->id)
            ->orderBy('hari')->orderBy('jam_mulai')
            ->get();

        $nilai = Nilai::where('siswa_id', $siswa->id)
            ->whereIn('jadwal_id', $jadwal->pluck('id'))
            ->get()
            ->keyBy('jadwal_id');

        $rows = $jadwal->map(function ($j) use ($nilai) {
            $n = $nilai->get($j->id);

            $arr = collect([
                optional($n)->nilai_uh,
                optional($n)->nilai_tugas,
                optional($n)->nilai_uts,
                optional($n)->nilai_uas,
            ])->filter(fn($v) => $v !== null);

            $avg  = $arr->isEmpty() ? null : round($arr->avg(), 2);
            $rata = optional($n)->rata_rata ?? $avg;

            return (object)[
                'mapel'       => $j->mataPelajaran->nama ?? $j->mataPelajaran->nama_mapel ?? '—',
                'guru'        => $j->guru->nama ?? '—',
                'nilai_uh'    => optional($n)->nilai_uh,
                'nilai_tugas' => optional($n)->nilai_tugas,
                'nilai_uts'   => optional($n)->nilai_uts,
                'nilai_uas'   => optional($n)->nilai_uas,
                'rata_rata'   => $rata,
                'status'      => $j->penilaian_final_at ? 'final' : 'draft',
                'final_at'    => $j->penilaian_final_at,
            ];
        });

        return view('guru.wali_kelas.siswa_nilai', compact('rombel','siswa','rows'));
    }

    /* ===================== Helpers ===================== */

    private function authorizeWali(Rombel $rombel): void
    {
        $guruId = optional(auth()->user()->guru)->id
            ?? Guru::where('user_id', auth()->id())->value('id');

        abort_unless($guruId && (int) $rombel->guru_id === (int) $guruId, 403);
    }

    /** Export CSV untuk 1 rombel (+opsional filter keyword) */
    protected function exportCsv(int $rombelId, string $keyword = ''): StreamedResponse
    {
        $rombel = Rombel::findOrFail($rombelId);

        $siswa = $rombel->siswa()
            ->wherePivot('aktif', 1)
            ->select('siswa.*')
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($w) use ($keyword) {
                    $w->where('siswa.nis', 'like', "%{$keyword}%")
                      ->orWhere('siswa.nama', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('nama')
            ->get();

        $filename = 'kelas-'.$rombel->nama_rombel.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($siswa, $rombel) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Kelas', $rombel->nama_rombel]);
            fputcsv($out, []); // baris kosong
            fputcsv($out, ['No','NIS','Nama','Jenis Kelamin','Tempat Lahir','Tanggal Lahir']);

            foreach ($siswa as $i => $s) {
                fputcsv($out, [
                    $i + 1,
                    $s->nis,
                    $s->nama,
                    $s->jk_label, // accessor di model Siswa
                    $s->tempat_lahir,
                    optional($s->tanggal_lahir)->format('d-m-Y'),
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /** Export PDF untuk 1 rombel (+opsional filter keyword) */
    protected function exportPdf(int $rombelId, string $keyword = '')
    {
        $rombel = Rombel::findOrFail($rombelId);

        $siswa = $rombel->siswa()
            ->wherePivot('aktif', 1)
            ->select('siswa.*')
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($w) use ($keyword) {
                    $w->where('siswa.nis', 'like', "%{$keyword}%")
                      ->orWhere('siswa.nama', 'like', "%{$keyword}%");
                });
            })
            ->orderBy('nama')
            ->get();

        $pdf = Pdf::loadView('guru.wali_kelas.pdf', [
            'rombel'  => $rombel,
            'siswa'   => $siswa,
            'tanggal' => now()->translatedFormat('d M Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('kelas-'.$rombel->nama_rombel.'-'.now()->format('Ymd-His').'.pdf');
    }
}
