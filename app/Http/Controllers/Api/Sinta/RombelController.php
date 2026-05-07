<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use Illuminate\Http\Request;

class RombelController extends Controller
{
    /**
     * LIST ROMBEL
     * GET /api/sinta/master/rombel
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $guruId = trim((string) $request->query('guru_id', ''));
        $aktif = $request->query('aktif');
        $tahunAjaranId = trim((string) $request->query('tahun_ajaran_id', ''));

        $rombels = Rombel::query()
            ->with([
                'waliKelas:id,nama,nip,nuptk,email',
                'ruangKelas:id,nama',
                'tahunAjaran:id,nama_tahun,semester,status',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_rombel', 'like', "%{$q}%")
                        ->orWhere('tingkat', 'like', "%{$q}%");
                });
            })
            ->when($guruId !== '', function ($query) use ($guruId) {
                $query->where('guru_id', $guruId);
            })
            ->when($aktif !== null && $aktif !== '', function ($query) use ($aktif) {
                $query->where('aktif', (int) $aktif);
            })
            ->when($tahunAjaranId !== '', function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->orderByDesc('aktif')
            ->orderByRaw("FIELD(tingkat, 'X', 'XI', 'XII')")
            ->orderBy('nama_rombel')
            ->get();

        $data = $rombels->map(function (Rombel $r) {
            return [
                'id' => $r->id,
                'tingkat' => $r->tingkat,
                'guru_id' => $r->guru_id,
                'menu_rombel_id' => $r->menu_rombel_id,
                'nama_rombel' => $r->nama_rombel,
                'tahun_ajaran_id' => $r->tahun_ajaran_id,
                'aktif' => (int) $r->aktif,
                'kapasitas' => $r->kapasitas,
                'ruang_kelas_id' => $r->ruang_kelas_id,
                'created_at' => optional($r->created_at)->toDateTimeString(),
                'updated_at' => optional($r->updated_at)->toDateTimeString(),
                'wali_kelas' => $r->waliKelas ? [
                    'id' => $r->waliKelas->id,
                    'nama' => $r->waliKelas->nama,
                    'nip' => $r->waliKelas->nip,
                    'nuptk' => $r->waliKelas->nuptk,
                    'email' => $r->waliKelas->email,
                ] : null,
                'ruang_kelas' => $r->ruangKelas ? [
                    'id' => $r->ruangKelas->id,
                    'nama' => $r->ruangKelas->nama,
                ] : null,
                'tahun_ajaran' => $r->tahunAjaran ? [
                    'id' => $r->tahunAjaran->id,
                    'nama_tahun' => $r->tahunAjaran->nama_tahun,
                    'semester' => $r->tahunAjaran->semester,
                    'status' => $r->tahunAjaran->status,
                ] : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL ROMBEL
     * GET /api/sinta/master/rombel/{id}
     * Mendukung ID numerik atau nama_rombel
     */
    public function show($id)
    {
        $query = Rombel::query()
            ->with([
                'waliKelas:id,nama,nip,nuptk,email',
                'ruangKelas:id,nama',
                'tahunAjaran:id,nama_tahun,semester,status',
                'siswaAktif' => function ($q) {
                    $q->select('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.jenis_kelamin');
                },
                'jadwal' => function ($q) {
                    $q->with([
                        'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                        'guru:id,nama,nip,nuptk',
                    ])
                    ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
                    ->orderBy('jam_mulai');
                },
            ]);

        $rombel = is_numeric($id)
            ? $query->where('id', (int) $id)->first()
            : $query->where('nama_rombel', $id)->first();

        if (!$rombel) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        $tahunAjaranRombel = $rombel->tahunAjaran ? [
            'id' => $rombel->tahunAjaran->id,
            'nama_tahun' => $rombel->tahunAjaran->nama_tahun,
            'semester' => $rombel->tahunAjaran->semester,
            'status' => $rombel->tahunAjaran->status,
        ] : null;

        $siswa = $rombel->siswaAktif->map(function ($s) {
            return [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama' => $s->nama,
                'jk' => $s->jenis_kelamin,
                'tahun_ajaran_id' => $s->pivot->tahun_ajaran_id ?? null,
                'aktif' => isset($s->pivot->aktif) ? (int) $s->pivot->aktif : null,
                'status_pilihan' => $s->pivot->status_pilihan ?? null,
            ];
        })->values();

        $jadwal = $rombel->jadwal->map(function ($j) use ($rombel, $tahunAjaranRombel) {
            return [
                'id' => $j->id,
                'rombel_id' => $j->rombel_id,
                'mata_pelajaran_id' => $j->mata_pelajaran_id,
                'guru_id' => $j->guru_id,
                'hari' => $j->hari,
                'slot_kode' => $j->slot_kode,
                'durasi_jp' => $j->durasi_jp,
                'jam_mulai' => $j->jam_mulai,
                'jam_selesai' => $j->jam_selesai,

                /*
                |--------------------------------------------------------------------------
                | Identitas periode rombel
                |--------------------------------------------------------------------------
                | Jadwal mengikuti periode rombel. Field ini wajib dikirim agar SINTA
                | bisa memastikan jadwal tidak tercampur dengan tahun ajaran lain.
                */
                'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                'tahun_ajaran' => $tahunAjaranRombel,
                'semester' => $tahunAjaranRombel['semester'] ?? null,
                'rombel_aktif' => (int) $rombel->aktif,

                'mapel' => $j->mataPelajaran ? [
                    'id' => $j->mataPelajaran->id,
                    'nama_mapel' => $j->mataPelajaran->nama_mapel,
                    'kelompok' => $j->mataPelajaran->kelompok,
                    'kkm' => $j->mataPelajaran->kkm,
                    'status' => $j->mataPelajaran->status,
                ] : null,
                'guru' => $j->guru ? [
                    'id' => $j->guru->id,
                    'nama' => $j->guru->nama,
                    'nip' => $j->guru->nip,
                    'nuptk' => $j->guru->nuptk,
                ] : null,
            ];
        })->values();

        $data = [
            'id' => $rombel->id,
            'tingkat' => $rombel->tingkat,
            'guru_id' => $rombel->guru_id,
            'menu_rombel_id' => $rombel->menu_rombel_id,
            'nama_rombel' => $rombel->nama_rombel,
            'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
            'aktif' => (int) $rombel->aktif,
            'kapasitas' => $rombel->kapasitas,
            'ruang_kelas_id' => $rombel->ruang_kelas_id,
            'created_at' => optional($rombel->created_at)->toDateTimeString(),
            'updated_at' => optional($rombel->updated_at)->toDateTimeString(),
            'wali_kelas' => $rombel->waliKelas ? [
                'id' => $rombel->waliKelas->id,
                'nama' => $rombel->waliKelas->nama,
                'nip' => $rombel->waliKelas->nip,
                'nuptk' => $rombel->waliKelas->nuptk,
                'email' => $rombel->waliKelas->email,
            ] : null,
            'ruang_kelas' => $rombel->ruangKelas ? [
                'id' => $rombel->ruangKelas->id,
                'nama' => $rombel->ruangKelas->nama,
            ] : null,
            'tahun_ajaran' => $tahunAjaranRombel,
            'siswa' => $siswa,
            'jadwal' => $jadwal,
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DAFTAR ANGGOTA ROMBEL
     * GET /api/sinta/master/rombel/{id}/anggota
     */
    public function anggota($id)
    {
        $rombel = Rombel::query()
            ->with([
                'siswaAktif' => function ($q) {
                    $q->select('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.jenis_kelamin');
                },
            ])
            ->find($id);

        if (!$rombel) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        $data = $rombel->siswaAktif->map(function ($s) {
            return [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama' => $s->nama,
                'jk' => $s->jenis_kelamin,
                'tahun_ajaran_id' => $s->pivot->tahun_ajaran_id ?? null,
                'aktif' => isset($s->pivot->aktif) ? (int) $s->pivot->aktif : null,
                'status_pilihan' => $s->pivot->status_pilihan ?? null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * JADWAL ROMBEL
     * GET /api/sinta/master/rombel/{id}/jadwal
     */
    public function jadwal(Request $request, $id)
    {
        $tahunAjaranId = trim((string) $request->query('tahun_ajaran_id', ''));
        $semester = trim((string) $request->query('semester', ''));

        $rombel = Rombel::query()
            ->with([
                'tahunAjaran:id,nama_tahun,semester,status',
            ])
            ->find($id);

        if (!$rombel) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi periode rombel
        |--------------------------------------------------------------------------
        | Jadwal melekat pada rombel. Jika SINTA meminta tahun_ajaran_id tertentu,
        | rombel yang tidak sesuai tidak boleh mengembalikan jadwal.
        */
        if ($tahunAjaranId !== '' && (string) $rombel->tahun_ajaran_id !== $tahunAjaranId) {
            return response()->json([
                'status' => true,
                'data' => [],
            ]);
        }

        if ($semester !== '') {
            $semesterRombel = strtolower(trim((string) optional($rombel->tahunAjaran)->semester));

            if ($semesterRombel !== strtolower($semester)) {
                return response()->json([
                    'status' => true,
                    'data' => [],
                ]);
            }
        }

        $tahunAjaranRombel = $rombel->tahunAjaran ? [
            'id' => $rombel->tahunAjaran->id,
            'nama_tahun' => $rombel->tahunAjaran->nama_tahun,
            'semester' => $rombel->tahunAjaran->semester,
            'status' => $rombel->tahunAjaran->status,
        ] : null;

        $data = $rombel->jadwal()
            ->with([
                'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'guru:id,nama,nip,nuptk',
            ])
            ->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(function ($j) use ($rombel, $tahunAjaranRombel) {
                return [
                    'id' => $j->id,
                    'rombel_id' => $j->rombel_id,
                    'mata_pelajaran_id' => $j->mata_pelajaran_id,
                    'guru_id' => $j->guru_id,
                    'hari' => $j->hari,
                    'slot_kode' => $j->slot_kode,
                    'durasi_jp' => $j->durasi_jp,
                    'jam_mulai' => $j->jam_mulai,
                    'jam_selesai' => $j->jam_selesai,

                    /*
                    |--------------------------------------------------------------------------
                    | Identitas periode rombel
                    |--------------------------------------------------------------------------
                    */
                    'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                    'tahun_ajaran' => $tahunAjaranRombel,
                    'semester' => $tahunAjaranRombel['semester'] ?? null,
                    'rombel_aktif' => (int) $rombel->aktif,

                    'mapel' => $j->mataPelajaran ? [
                        'id' => $j->mataPelajaran->id,
                        'nama_mapel' => $j->mataPelajaran->nama_mapel,
                        'kelompok' => $j->mataPelajaran->kelompok,
                        'kkm' => $j->mataPelajaran->kkm,
                        'status' => $j->mataPelajaran->status,
                    ] : null,
                    'guru' => $j->guru ? [
                        'id' => $j->guru->id,
                        'nama' => $j->guru->nama,
                        'nip' => $j->guru->nip,
                        'nuptk' => $j->guru->nuptk,
                    ] : null,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * ROMBEL BERDASARKAN GURU/WALI KELAS
     * GET /api/sinta/master/rombel-by-guru/{guruId}
     */
    public function byGuru($guruId)
    {
        $rombels = Rombel::query()
            ->with([
                'waliKelas:id,nama,nip,nuptk,email',
                'tahunAjaran:id,nama_tahun,semester,status',
            ])
            ->where('guru_id', $guruId)
            ->orderByDesc('aktif')
            ->orderByDesc('tahun_ajaran_id')
            ->orderBy('nama_rombel')
            ->get();

        $data = $rombels->map(function (Rombel $r) {
            return [
                'id' => $r->id,
                'nama_rombel' => $r->nama_rombel,
                'tingkat' => $r->tingkat,
                'guru_id' => $r->guru_id,
                'tahun_ajaran_id' => $r->tahun_ajaran_id,
                'aktif' => (int) $r->aktif,
                'wali_kelas' => $r->waliKelas ? [
                    'id' => $r->waliKelas->id,
                    'nama' => $r->waliKelas->nama,
                    'nip' => $r->waliKelas->nip,
                    'nuptk' => $r->waliKelas->nuptk,
                    'email' => $r->waliKelas->email,
                ] : null,
                'tahun_ajaran' => $r->tahunAjaran ? [
                    'id' => $r->tahunAjaran->id,
                    'nama_tahun' => $r->tahunAjaran->nama_tahun,
                    'semester' => $r->tahunAjaran->semester,
                    'status' => $r->tahunAjaran->status,
                ] : null,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}