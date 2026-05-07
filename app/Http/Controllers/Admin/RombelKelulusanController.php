<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RombelKelulusanController extends Controller
{
    /**
     * Form kelulusan untuk 1 rombel.
     */
    public function form(Request $request, Rombel $rombel)
    {
        $q = $request->query('q');

        $siswaQuery = Siswa::select('siswa.*')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.aktif', 1);

        if ($q) {
            $siswaQuery->where(function ($sub) use ($q) {
                $sub->where('siswa.nama', 'like', "%{$q}%")
                    ->orWhere('siswa.nis', 'like', "%{$q}%")
                    ->orWhere('siswa.nisn', 'like', "%{$q}%");
            });
        }

        $siswa = $siswaQuery
            ->orderBy('siswa.nama')
            ->get();

        $evaluasiRows = $this->buildEvaluasiRows($rombel, $siswa);

        return view('admin.rombel.kelulusan', [
            'rombel'       => $rombel,
            'siswa'        => $siswa,
            'evaluasiRows' => $evaluasiRows,
            'q'            => $q,
        ]);
    }

    /**
     * Proses kelulusan siswa yang dipilih.
     */
    public function proses(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'siswa_ids'   => ['required', 'array'],
            'siswa_ids.*' => ['exists:siswa,id'],
            'sikap'       => ['nullable', 'array'],
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa yang akan diluluskan.',
        ]);

        $siswaIds   = collect($data['siswa_ids'])->map(fn ($id) => (int) $id)->values()->all();
        $sikapInput = $request->input('sikap', []);

        $siswa = Siswa::select('siswa.*')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.aktif', 1)
            ->whereIn('siswa.id', $siswaIds)
            ->orderBy('siswa.nama')
            ->get();

        if ($siswa->isEmpty()) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Siswa yang dipilih tidak ditemukan pada rombel aktif ini.',
            ]);
        }

        $evaluasiRows = $this->buildEvaluasiRows($rombel, $siswa)->keyBy('siswa_id');

        $gagal = [];

        foreach ($siswaIds as $siswaId) {
            $row = $evaluasiRows->get($siswaId);

            if (!$row) {
                continue;
            }

            $sikap = strtolower(trim((string) ($sikapInput[$siswaId] ?? '')));

            if ($sikap === '') {
                $gagal[] = "{$row->nama} (sikap belum dipilih)";
                continue;
            }

            if ($sikap !== 'baik') {
                $gagal[] = "{$row->nama} (sikap harus minimal Baik)";
                continue;
            }

            if (!$row->nilai_lengkap) {
                $gagal[] = "{$row->nama} (nilai belum lengkap)";
                continue;
            }

            if (!$row->kehadiran_memenuhi) {
                $gagal[] = "{$row->nama} (kehadiran {$row->persen_hadir}% < 90%)";
                continue;
            }
        }

        if (!empty($gagal)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Beberapa siswa belum memenuhi syarat kelulusan: ' . implode(', ', $gagal),
            ]);
        }

        DB::transaction(function () use ($siswaIds, $rombel) {
            Siswa::whereIn('id', $siswaIds)
                ->update([
                    'status'     => 'lulus',
                    'updated_at' => now(),
                ]);

            DB::table('siswa_rombel')
                ->where('rombel_id', $rombel->id)
                ->whereIn('siswa_id', $siswaIds)
                ->where('aktif', 1)
                ->update([
                    'aktif'      => 0,
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.rombel.kelulusan.form', $rombel)
            ->with('ok', 'Proses kelulusan berhasil disimpan.');
    }

    /**
     * Evaluasi runtime nilai + kehadiran.
     */
    private function buildEvaluasiRows(Rombel $rombel, $siswa)
    {
        $jadwalIds = DB::table('jadwal')
            ->where('rombel_id', $rombel->id)
            ->pluck('id');

        $jumlahMapel = $jadwalIds->count();

        $hasNilaiAkhirColumn   = Schema::hasColumn('nilai', 'nilai_akhir');
        $hasTahunAjaranIdNilai = Schema::hasColumn('nilai', 'tahun_ajaran_id');

        $nilaiPerSiswa = collect();

        if ($jumlahMapel > 0) {
            $nilaiQuery = DB::table('nilai')
                ->select('siswa_id')
                ->whereIn('jadwal_id', $jadwalIds);

            if ($hasTahunAjaranIdNilai && !empty($rombel->tahun_ajaran_id)) {
                $nilaiQuery->where('tahun_ajaran_id', $rombel->tahun_ajaran_id);
            }

            if ($hasNilaiAkhirColumn) {
                $nilaiQuery->whereNotNull('nilai_akhir');
            }

            $nilaiPerSiswa = $nilaiQuery
                ->selectRaw('COUNT(DISTINCT jadwal_id) as total_nilai')
                ->groupBy('siswa_id')
                ->pluck('total_nilai', 'siswa_id');
        }

$presensiPerSiswa = DB::table('presensi as p')
    ->join('sesi_presensi as sp', 'sp.id', '=', 'p.sesi_presensi_id')
    ->where('sp.rombel_id', $rombel->id)
    ->select(
        'p.siswa_id',
        DB::raw("
            SUM(
                CASE
                    WHEN LOWER(p.status) IN ('hadir','h') THEN 1
                    ELSE 0
                END
            ) as hadir
        "),
        DB::raw("
            SUM(
                CASE
                    WHEN LOWER(p.status) IN ('hadir','izin','sakit','alfa','h','i','s','a') THEN 1
                    ELSE 0
                END
            ) as total
        ")
    )
    ->groupBy('p.siswa_id')
    ->get()
    ->keyBy('siswa_id');

        return $siswa->map(function ($item) use ($nilaiPerSiswa, $presensiPerSiswa, $jumlahMapel) {
            $totalNilai   = (int) ($nilaiPerSiswa[$item->id] ?? 0);
            $nilaiLengkap = $jumlahMapel > 0 ? $totalNilai >= $jumlahMapel : false;

            $presensi    = $presensiPerSiswa->get($item->id);
            $hadir       = (int) ($presensi->hadir ?? 0);
            $total       = (int) ($presensi->total ?? 0);
            $persenHadir = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            return (object) [
                'siswa_id'           => $item->id,
                'nis'                => $item->nis,
                'nisn'               => $item->nisn,
                'nama'               => $item->nama,
                'jumlah_mapel'       => $jumlahMapel,
                'jumlah_nilai'       => $totalNilai,
                'nilai_lengkap'      => $nilaiLengkap,
                'hadir'              => $hadir,
                'total_presensi'     => $total,
                'persen_hadir'       => $persenHadir,
                'kehadiran_memenuhi' => $persenHadir >= 90,
            ];
        });
    }
}