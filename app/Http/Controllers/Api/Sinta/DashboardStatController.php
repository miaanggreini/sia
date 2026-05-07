<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Nilai;
use App\Models\Presensi;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Guru;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardStatController extends Controller
{
    public function summary(): JsonResponse
    {
        $tahunAjaranAktif = TahunAjaran::query()
            ->where('status', 'aktif')
            ->latest('id')
            ->first();

        $totalGuru = Guru::query()
            ->where('status', 'aktif')
            ->count();

        $totalSiswa = Siswa::query()
            ->where('status', 'aktif')
            ->count();

        $totalRombel = Rombel::query()
            ->where('aktif', 1)
            ->count();

        $genderL = Siswa::query()
            ->where('status', 'aktif')
            ->where('jenis_kelamin', 'L')
            ->count();

        $genderP = Siswa::query()
            ->where('status', 'aktif')
            ->where('jenis_kelamin', 'P')
            ->count();

        return response()->json([
            'status' => true,
            'message' => 'Ringkasan dashboard berhasil diambil.',
            'data' => [
                'tahun_ajaran_aktif' => $tahunAjaranAktif ? [
                    'id' => $tahunAjaranAktif->id,
                    'nama_tahun' => $tahunAjaranAktif->nama_tahun,
                    'semester' => $tahunAjaranAktif->semester,
                    'status' => $tahunAjaranAktif->status,
                ] : null,
                'total_guru' => $totalGuru,
                'total_siswa' => $totalSiswa,
                'total_rombel' => $totalRombel,
                'gender' => [
                    'L' => $genderL,
                    'P' => $genderP,
                ],
            ],
        ]);
    }

    public function presensiToday(): JsonResponse
    {
        $dateColumn = $this->resolvePresensiDateColumn();

        if (!$dateColumn) {
            return response()->json([
                'status' => false,
                'message' => 'Kolom tanggal pada tabel presensi tidak ditemukan.',
                'data' => [],
            ], 500);
        }

        $today = Carbon::today()->toDateString();

        $rows = Presensi::query()
            ->whereDate($dateColumn, $today)
            ->get();

        $total = $rows->count();
        $hadir = $rows->where('status', 'hadir')->count();
        $persen = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'tanggal' => $today,
                'total' => $total,
                'hadir' => $hadir,
                'persen' => $persen,
            ],
        ]);
    }

    public function presensiTrend7(): JsonResponse
    {
        $dateColumn = $this->resolvePresensiDateColumn();

        if (!$dateColumn) {
            return response()->json([
                'status' => false,
                'message' => 'Kolom tanggal pada tabel presensi tidak ditemukan.',
                'data' => [],
            ], 500);
        }

        $start = Carbon::today()->subDays(6);
        $end = Carbon::today();

        $rows = Presensi::query()
            ->selectRaw("DATE($dateColumn) as tanggal")
            ->selectRaw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw('COUNT(*) as total')
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->groupBy(DB::raw("DATE($dateColumn)"))
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $data = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $tanggal = $cursor->toDateString();
            $item = $rows->get($tanggal);

            $hadir = (int) ($item->hadir ?? 0);
            $total = (int) ($item->total ?? 0);
            $persen = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            $data->push([
                'tanggal' => $tanggal,
                'label' => $cursor->translatedFormat('d M'),
                'hadir' => $hadir,
                'total' => $total,
                'persen' => $persen,
            ]);

            $cursor->addDay();
        }

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $data->values(),
        ]);
    }

    public function presensiTrendMonthly(): JsonResponse
    {
        $dateColumn = $this->resolvePresensiDateColumn();

        if (!$dateColumn) {
            return response()->json([
                'status' => false,
                'message' => 'Kolom tanggal pada tabel presensi tidak ditemukan.',
                'data' => [],
            ], 500);
        }

        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $end = Carbon::now()->endOfMonth();

        $rows = Presensi::query()
            ->selectRaw("YEAR($dateColumn) as tahun")
            ->selectRaw("MONTH($dateColumn) as bulan")
            ->selectRaw("SUM(CASE WHEN status = 'hadir' THEN 1 ELSE 0 END) as hadir")
            ->selectRaw('COUNT(*) as total')
            ->whereBetween($dateColumn, [$start->toDateString(), $end->toDateString()])
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get()
            ->keyBy(fn($r) => $r->tahun . '-' . str_pad($r->bulan, 2, '0', STR_PAD_LEFT));

        $labels = [];
        $series = [];

        $cursor = $start->copy()->startOfMonth();
        $last = $end->copy()->startOfMonth();

        while ($cursor->lte($last)) {
            $key = $cursor->format('Y-m');
            $item = $rows->get($key);

            $hadir = (int) ($item->hadir ?? 0);
            $total = (int) ($item->total ?? 0);
            $persen = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            $labels[] = $cursor->translatedFormat('M Y');
            $series[] = $persen;

            $cursor->addMonth();
        }

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'labels' => $labels,
                'series' => $series,
            ],
        ]);
    }

    public function rekapNilaiPerKelas(): JsonResponse
    {
        $rows = Nilai::query()
            ->join('jadwal', 'jadwal.id', '=', 'nilai.jadwal_id')
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->where('nilai.status_penilaian', 'final')
            ->whereNotNull('nilai.nilai_akhir')
            ->selectRaw('
                rombel.id,
                rombel.nama_rombel,
                rombel.tingkat,
                ROUND(AVG(nilai.nilai_akhir), 2) as rata_rata
            ')
            ->groupBy('rombel.id', 'rombel.nama_rombel', 'rombel.tingkat')
            ->orderByRaw("FIELD(rombel.tingkat, 'X', 'XI', 'XII')")
            ->orderBy('rombel.nama_rombel')
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'nama_rombel' => $row->nama_rombel,
                    'tingkat' => $row->tingkat,
                    'rata_rata' => (float) $row->rata_rata,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $rows,
        ]);
    }

    public function rekapNilaiPerTingkat(): JsonResponse
    {
        $rows = Nilai::query()
            ->join('jadwal', 'jadwal.id', '=', 'nilai.jadwal_id')
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->where('nilai.status_penilaian', 'final')
            ->whereNotNull('nilai.nilai_akhir')
            ->selectRaw('
                rombel.tingkat,
                ROUND(AVG(nilai.nilai_akhir), 2) as rata_rata
            ')
            ->groupBy('rombel.tingkat')
            ->orderByRaw("FIELD(rombel.tingkat, 'X', 'XI', 'XII')")
            ->get()
            ->map(function ($row) {
                return [
                    'tingkat' => $row->tingkat,
                    'rata_rata' => (float) $row->rata_rata,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $rows,
        ]);
    }

    public function rataRataNilaiGlobal(): JsonResponse
    {
        $avg = Nilai::query()
            ->where('status_penilaian', 'final')
            ->whereNotNull('nilai_akhir')
            ->avg('nilai_akhir');

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'rata_rata' => round((float) $avg, 2),
            ],
        ]);
    }

    public function top5Siswa(): JsonResponse
    {
        $rows = Nilai::query()
            ->join('siswa', 'siswa.id', '=', 'nilai.siswa_id')
            ->join('jadwal', 'jadwal.id', '=', 'nilai.jadwal_id')
            ->join('rombel', 'rombel.id', '=', 'jadwal.rombel_id')
            ->where('nilai.status_penilaian', 'final')
            ->whereNotNull('nilai.nilai_akhir')
            ->selectRaw('
                siswa.id as siswa_id,
                siswa.nama as siswa_nama,
                rombel.id as rombel_id,
                rombel.nama_rombel,
                rombel.tingkat,
                ROUND(AVG(nilai.nilai_akhir), 2) as rata_rata
            ')
            ->groupBy('siswa.id', 'siswa.nama', 'rombel.id', 'rombel.nama_rombel', 'rombel.tingkat')
            ->orderByDesc('rata_rata')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'siswa' => [
                        'id' => $row->siswa_id,
                        'nama' => $row->siswa_nama,
                    ],
                    'rombel' => [
                        'id' => $row->rombel_id,
                        'nama_rombel' => $row->nama_rombel,
                        'tingkat' => $row->tingkat,
                    ],
                    'rata_rata' => (float) $row->rata_rata,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => $rows,
        ]);
    }

    private function resolvePresensiDateColumn(): ?string
    {
        foreach (['tanggal', 'tanggal_presensi', 'tgl_presensi', 'created_at'] as $column) {
            if (Schema::hasColumn('presensi', $column)) {
                return $column;
            }
        }

        return null;
    }
}