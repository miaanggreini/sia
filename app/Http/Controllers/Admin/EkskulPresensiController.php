<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\EkskulPresensi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EkskulPresensiController extends Controller
{
    /**
     * Pilih tahun ajaran (pakai query ?tahun_ajaran_id=...,
     * kalau tidak ada ambil yang status=aktif, kalau belum ada ambil yang terbaru).
     */
    private function pilihTahunAjaran(Request $request): array
    {
        $daftar = TahunAjaran::orderBy('tanggal_mulai', 'desc')->get();

        if ($daftar->isEmpty()) {
            return [null, $daftar];
        }

        if ($request->filled('tahun_ajaran_id')) {
            $selected = $daftar->firstWhere('id', (int) $request->tahun_ajaran_id) ?? $daftar->first();
        } else {
            $selected = $daftar->firstWhere('status', 'aktif') ?? $daftar->first();
        }

        return [$selected, $daftar];
    }

    /**
     * Rekap presensi ekskul per tanggal (untuk 1 ekskul).
     */
    public function index(Request $request, Ekskul $ekskul)
    {
        [$tahunAjaran, $daftarTahun] = $this->pilihTahunAjaran($request);

        if (!$tahunAjaran) {
            return back()->with('error', 'Data tahun ajaran belum tersedia.');
        }

        $rekapTanggal = EkskulPresensi::selectRaw("
                tanggal,
                SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) AS hadir,
                SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) AS izin,
                SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) AS sakit,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) AS alfa,
                COUNT(*) AS total
            ")
            ->where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('admin.ekskul.presensi_index', [
            'ekskul'       => $ekskul,
            'tahunAjaran'  => $tahunAjaran,
            'daftarTahun'  => $daftarTahun,
            'rekapTanggal' => $rekapTanggal,
        ]);
    }

    /**
     * Detail presensi ekskul pada 1 tanggal + rekap kehadiran per siswa
     * (semua pertemuan).
     */
    public function detail(Request $request, Ekskul $ekskul, string $tanggal)
    {
        [$tahunAjaran, $daftarTahun] = $this->pilihTahunAjaran($request);

        if (!$tahunAjaran) {
            return back()->with('error', 'Data tahun ajaran belum tersedia.');
        }

        try {
            $tanggalObj   = Carbon::parse($tanggal);
            $tanggalQuery = $tanggalObj->toDateString();
        } catch (\Exception $e) {
            abort(404);
        }

        // Anggota aktif tahun ajaran ini
        $anggota = EkskulAnggota::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('status', 'aktif')
            ->get()
            ->sortBy(function ($row) {
                return $row->siswa->nama ?? '';
            })
            ->values();

        // Presensi di tanggal tsb
        $presensiHariIni = EkskulPresensi::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->whereDate('tanggal', $tanggalQuery)
            ->orderBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        // Ringkasan tanggal itu
        $ringkasan = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
        foreach ($presensiHariIni as $row) {
            if (isset($ringkasan[$row->status])) {
                $ringkasan[$row->status]++;
            }
        }
        $ringkasan['total'] = array_sum($ringkasan);

        // Rekap kehadiran per siswa (semua pertemuan ekskul)
        $rekapSiswaRaw = EkskulPresensi::selectRaw('siswa_id, status, COUNT(*) as jumlah')
            ->where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->groupBy('siswa_id', 'status')
            ->get()
            ->groupBy('siswa_id');

        $rekapSiswa = [];
        foreach ($rekapSiswaRaw as $siswaId => $rows) {
            $rekapSiswa[$siswaId] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
            foreach ($rows as $row) {
                if (isset($rekapSiswa[$siswaId][$row->status])) {
                    $rekapSiswa[$siswaId][$row->status] = $row->jumlah;
                }
            }
        }

        return view('admin.ekskul.presensi_detail', [
            'ekskul'          => $ekskul,
            'tahunAjaran'     => $tahunAjaran,
            'tanggalObj'      => $tanggalObj,
            'daftarTahun'     => $daftarTahun,
            'anggota'         => $anggota,
            'presensiHariIni' => $presensiHariIni,
            'ringkasan'       => $ringkasan,
            'rekapSiswa'      => $rekapSiswa,
        ]);
    }
}
