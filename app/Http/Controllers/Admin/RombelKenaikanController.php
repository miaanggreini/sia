<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class RombelKenaikanController extends Controller
{
    public function form(Request $request, Rombel $rombel)
    {
        $rombel->loadMissing(['tahunAjaran', 'waliKelas', 'guru']);

        $tingkatAsal = strtoupper(trim((string) $rombel->tingkat));
        $tingkatTujuan = $this->tingkatTujuan($tingkatAsal);

        /*
         * Tahun ajaran tujuan adalah tahun ajaran baru setelah tahun ajaran rombel asal.
         * Tahun ajaran tujuan TIDAK harus aktif dulu.
         */
        $tahunAjaranTujuan = $this->tahunAjaranTujuan($rombel);

        /*
         * Anggota yang dievaluasi adalah anggota aktif pada rombel asal
         * dan tahun ajaran rombel asal.
         */
        $anggota = $rombel->siswa()
            ->wherePivot('aktif', 1)
            ->wherePivot('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->orderBy('nama')
            ->get();

        $rombelsTujuan = collect();

        /*
         * Rombel tujuan hanya dibutuhkan untuk kelas XI -> XII.
         * Kelas X tidak memilih rombel tujuan di halaman kenaikan,
         * karena rombel XI ditentukan dari proses pemilihan menu rombel.
         */
        if ($tingkatAsal === 'XI' && $tingkatTujuan && $tahunAjaranTujuan) {
            $rombelsTujuan = Rombel::with('tahunAjaran')
                ->where('id', '!=', $rombel->id)
                ->where('tingkat', $tingkatTujuan)
                ->where('tahun_ajaran_id', $tahunAjaranTujuan->id)
                ->orderBy('nama_rombel')
                ->get();
        }

        $evaluasiRows = $this->buildEvaluasiRows($rombel, $anggota);

        return view('admin.rombel.kenaikan', [
            'rombel'             => $rombel,
            'anggota'            => $anggota,
            'rombelsTujuan'      => $rombelsTujuan,
            'evaluasiRows'       => $evaluasiRows,
            'tahunAjaranTujuan'  => $tahunAjaranTujuan,
            'tahunAjaranAktif'   => $tahunAjaranTujuan, // fallback kalau view lama masih pakai nama ini
            'tingkatAsal'        => $tingkatAsal,
            'tingkatTujuan'      => $tingkatTujuan,
        ]);
    }

    public function proses(Request $request, Rombel $rombel)
    {
        $rombel->loadMissing(['tahunAjaran']);

        $tingkatAsal = strtoupper(trim((string) $rombel->tingkat));
        $isKelasX = $tingkatAsal === 'X';
        $isKelasXI = $tingkatAsal === 'XI';

        if (!$isKelasX && !$isKelasXI) {
            throw ValidationException::withMessages([
                'rombel' => 'Kenaikan hanya berlaku untuk kelas X dan XI. Kelas XII diproses melalui menu kelulusan.',
            ]);
        }

        $rules = [
            'siswa_ids'   => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['exists:siswa,id'],
            'sikap'       => ['nullable', 'array'],
        ];

        /*
         * Hanya kelas XI yang butuh rombel tujuan.
         * Kelas X lanjut ke pemilihan menu rombel XI dulu.
         */
        if ($isKelasXI) {
            $rules['rombel_tujuan_id'] = ['required', 'exists:rombel,id'];
        }

        $data = $request->validate($rules, [
            'rombel_tujuan_id.required' => 'Pilih rombel tujuan terlebih dahulu.',
            'siswa_ids.required'        => 'Pilih minimal satu siswa yang akan diproses.',
            'siswa_ids.min'             => 'Pilih minimal satu siswa yang akan diproses.',
        ]);

        /*
         * Tahun ajaran tujuan tidak harus aktif.
         * Justru alur yang benar: tahun ajaran baru disiapkan dulu, baru diaktifkan setelah semua proses selesai.
         */
        $tahunAjaranTujuan = $this->tahunAjaranTujuan($rombel);

        if (!$tahunAjaranTujuan) {
            throw ValidationException::withMessages([
                'tahun_ajaran' => 'Tahun ajaran tujuan belum tersedia. Buat tahun ajaran baru terlebih dahulu, tetapi tidak perlu diaktifkan dulu.',
            ]);
        }

        if ((int) $tahunAjaranTujuan->id === (int) $rombel->tahun_ajaran_id) {
            throw ValidationException::withMessages([
                'tahun_ajaran' => 'Tahun ajaran tujuan tidak boleh sama dengan tahun ajaran rombel asal.',
            ]);
        }

        $rombelTujuan = null;

        if ($isKelasXI) {
            $rombelTujuan = Rombel::findOrFail($data['rombel_tujuan_id']);

            if ((int) $rombelTujuan->id === (int) $rombel->id) {
                throw ValidationException::withMessages([
                    'rombel_tujuan_id' => 'Rombel tujuan tidak boleh sama dengan rombel asal.',
                ]);
            }

            if (strtoupper(trim((string) $rombelTujuan->tingkat)) !== 'XII') {
                throw ValidationException::withMessages([
                    'rombel_tujuan_id' => 'Rombel tujuan untuk kelas XI harus tingkat XII.',
                ]);
            }

            if ((int) $rombelTujuan->tahun_ajaran_id !== (int) $tahunAjaranTujuan->id) {
                throw ValidationException::withMessages([
                    'rombel_tujuan_id' => 'Rombel tujuan harus berada pada tahun ajaran tujuan.',
                ]);
            }
        }

        $siswaIds = collect($data['siswa_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $sikapInput = $request->input('sikap', []);

        /*
         * Pastikan siswa yang dipilih benar-benar anggota aktif
         * pada rombel asal dan tahun ajaran rombel asal.
         */
        $anggota = $rombel->siswa()
            ->wherePivot('aktif', 1)
            ->wherePivot('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->whereIn('siswa.id', $siswaIds)
            ->orderBy('nama')
            ->get();

        if ($anggota->count() !== count($siswaIds)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Beberapa siswa yang dipilih bukan anggota aktif pada rombel asal ini.',
            ]);
        }

        /*
         * Untuk kelas X, siswa yang sudah punya rombel aktif di tahun ajaran tujuan
         * tidak boleh diproses lagi.
         */
        if ($isKelasX) {
            $sudahPunyaRombelTujuan = DB::table('siswa_rombel as sr')
                ->join('siswa as s', 's.id', '=', 'sr.siswa_id')
                ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
                ->whereIn('sr.siswa_id', $siswaIds)
                ->where('sr.tahun_ajaran_id', $tahunAjaranTujuan->id)
                ->where('sr.aktif', 1)
                ->select('s.nama', 'r.nama_rombel')
                ->get();

            if ($sudahPunyaRombelTujuan->isNotEmpty()) {
                $daftar = $sudahPunyaRombelTujuan
                    ->map(fn ($row) => "{$row->nama} ({$row->nama_rombel})")
                    ->implode(', ');

                throw ValidationException::withMessages([
                    'siswa_ids' => 'Beberapa siswa sudah memiliki rombel aktif pada tahun ajaran tujuan: ' . $daftar,
                ]);
            }
        }

        $evaluasiRows = $this->buildEvaluasiRows($rombel, $anggota)->keyBy('siswa_id');

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
                'siswa_ids' => 'Beberapa siswa belum memenuhi syarat kenaikan kelas: ' . implode(', ', $gagal),
            ]);
        }

        DB::transaction(function () use (
            $rombel,
            $rombelTujuan,
            $siswaIds,
            $tahunAjaranTujuan,
            $isKelasX,
            $isKelasXI
        ) {
            foreach ($siswaIds as $siswaId) {
                /*
                 * Kelas X:
                 * siswa dikeluarkan dari rombel X lama sebagai tanda sudah diproses layak naik.
                 * Setelah ini siswa bisa mengikuti pemilihan menu rombel XI.
                 *
                 * Kelas XI:
                 * siswa dikeluarkan dari rombel XI lama,
                 * lalu langsung dimasukkan ke rombel XII tujuan.
                 */
                DB::table('siswa_rombel')
                    ->where('rombel_id', $rombel->id)
                    ->where('siswa_id', $siswaId)
                    ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
                    ->where('aktif', 1)
                    ->update([
                        'aktif'      => 0,
                        'updated_at' => now(),
                    ]);

                if ($isKelasXI && $rombelTujuan) {
                    /*
                     * Pastikan siswa tidak punya rombel aktif lain
                     * pada tahun ajaran tujuan.
                     */
                    DB::table('siswa_rombel')
                        ->where('siswa_id', $siswaId)
                        ->where('tahun_ajaran_id', $tahunAjaranTujuan->id)
                        ->where('aktif', 1)
                        ->update([
                            'aktif'      => 0,
                            'updated_at' => now(),
                        ]);

                    $existing = DB::table('siswa_rombel')
                        ->where('rombel_id', $rombelTujuan->id)
                        ->where('siswa_id', $siswaId)
                        ->where('tahun_ajaran_id', $tahunAjaranTujuan->id)
                        ->first();

                    if ($existing) {
                        DB::table('siswa_rombel')
                            ->where('id', $existing->id)
                            ->update([
                                'aktif'          => 1,
                                'status_pilihan' => 'utama',
                                'updated_at'     => now(),
                            ]);
                    } else {
                        DB::table('siswa_rombel')->insert([
                            'rombel_id'       => $rombelTujuan->id,
                            'siswa_id'        => $siswaId,
                            'tahun_ajaran_id' => $tahunAjaranTujuan->id,
                            'aktif'           => 1,
                            'status_pilihan'  => 'utama',
                            'created_at'      => now(),
                            'updated_at'      => now(),
                        ]);
                    }
                }

                if ($isKelasX) {
                    DB::table('siswa')
                        ->where('id', $siswaId)
                        ->update([
                            'status'     => 'aktif',
                            'updated_at' => now(),
                        ]);
                }
            }

            /*
             * Khusus kelas XI -> XII:
             * Salin mapel rombel asal ke rombel tujuan.
             * Yang disalin hanya daftar mapel, bukan jadwal.
             */
            if ($isKelasXI && $rombelTujuan) {
                $mapelIds = DB::table('rombel_mapel')
                    ->where('rombel_id', $rombel->id)
                    ->pluck('mata_pelajaran_id')
                    ->unique()
                    ->values()
                    ->all();

                if (!empty($mapelIds)) {
                    $existingMapelIds = DB::table('rombel_mapel')
                        ->where('rombel_id', $rombelTujuan->id)
                        ->pluck('mata_pelajaran_id')
                        ->unique()
                        ->values()
                        ->all();

                    $insertMapelIds = array_values(array_diff($mapelIds, $existingMapelIds));

                    foreach ($insertMapelIds as $mapelId) {
                        DB::table('rombel_mapel')->insert([
                            'rombel_id'         => $rombelTujuan->id,
                            'mata_pelajaran_id' => $mapelId,
                            'created_at'        => now(),
                            'updated_at'        => now(),
                        ]);
                    }
                }
            }
        });

        $message = $isKelasX
            ? 'Kenaikan kelas X berhasil diproses. Siswa yang dipilih sudah ditandai layak naik dan dapat mengikuti pemilihan rombel XI saat periode dibuka.'
            : 'Kenaikan kelas XI ke XII berhasil diproses. Daftar mapel rombel asal juga disalin ke rombel tujuan.';

        return redirect()
            ->route('admin.rombel.anggota', $rombel)
            ->with('ok', $message);
    }

    private function tahunAjaranTujuan(Rombel $rombel)
    {
        $rombel->loadMissing('tahunAjaran');

        $query = TahunAjaran::query()
            ->where('id', '!=', $rombel->tahun_ajaran_id);

        if (!empty($rombel->tahunAjaran?->tanggal_mulai)) {
            $query->where('tanggal_mulai', '>', $rombel->tahunAjaran->tanggal_mulai)
                ->orderBy('tanggal_mulai')
                ->orderBy('id');

            $tahunTujuan = $query->first();

            if ($tahunTujuan) {
                return $tahunTujuan;
            }
        }

        /*
         * Fallback kalau tanggal_mulai belum rapi.
         * Ambil tahun ajaran dengan id lebih besar dari tahun asal.
         */
        $tahunTujuan = TahunAjaran::where('id', '>', $rombel->tahun_ajaran_id)
            ->orderBy('id')
            ->first();

        if ($tahunTujuan) {
            return $tahunTujuan;
        }

        return TahunAjaran::where('id', '!=', $rombel->tahun_ajaran_id)
            ->orderByDesc('id')
            ->first();
    }

    private function tingkatTujuan(?string $tingkat): ?string
    {
        return match (strtoupper(trim((string) $tingkat))) {
            'X'  => 'XI',
            'XI' => 'XII',
            default => null,
        };
    }

    private function buildEvaluasiRows(Rombel $rombel, $anggota)
    {
        $jadwalIds = DB::table('jadwal')
            ->where('rombel_id', $rombel->id)
            ->pluck('id');

        $jumlahMapel = $jadwalIds->count();

        $hasNilaiAkhirColumn = Schema::hasColumn('nilai', 'nilai_akhir');
        $hasTahunAjaranIdOnNilai = Schema::hasColumn('nilai', 'tahun_ajaran_id');

        $nilaiPerSiswa = collect();

        if ($jumlahMapel > 0) {
            $nilaiQuery = DB::table('nilai')
                ->select('siswa_id')
                ->whereIn('jadwal_id', $jadwalIds);

            if ($hasTahunAjaranIdOnNilai && !empty($rombel->tahun_ajaran_id)) {
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

        /*
         * Presensi dihitung berdasarkan sesi unik.
         * Karena presensi sistem kamu berbasis sesi/mapel, maka total pertemuan
         * adalah total sesi_presensi dari semua mata pelajaran pada rombel tersebut.
         */
        $presensiPerSiswa = DB::table('presensi as p')
            ->join('sesi_presensi as sp', 'sp.id', '=', 'p.sesi_presensi_id')
            ->where('sp.rombel_id', $rombel->id)
            ->select(
                'p.siswa_id',
                DB::raw("
                    COUNT(DISTINCT CASE
                        WHEN LOWER(p.status) IN ('hadir', 'h')
                        THEN p.sesi_presensi_id
                    END) as hadir
                "),
                DB::raw("
                    COUNT(DISTINCT CASE
                        WHEN LOWER(p.status) IN ('hadir', 'h', 'izin', 'i', 'sakit', 's', 'alfa', 'a')
                        THEN p.sesi_presensi_id
                    END) as total
                ")
            )
            ->groupBy('p.siswa_id')
            ->get()
            ->keyBy('siswa_id');

        return $anggota->map(function ($siswa) use ($nilaiPerSiswa, $presensiPerSiswa, $jumlahMapel) {
            $totalNilai = (int) ($nilaiPerSiswa[$siswa->id] ?? 0);
            $nilaiLengkap = $jumlahMapel > 0 ? $totalNilai >= $jumlahMapel : false;

            $presensi = $presensiPerSiswa->get($siswa->id);
            $hadir = (int) ($presensi->hadir ?? 0);
            $total = (int) ($presensi->total ?? 0);
            $persenHadir = $total > 0 ? round(($hadir / $total) * 100, 2) : 0;

            return (object) [
                'siswa_id'           => $siswa->id,
                'nis'                => $siswa->nis,
                'nama'               => $siswa->nama,
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