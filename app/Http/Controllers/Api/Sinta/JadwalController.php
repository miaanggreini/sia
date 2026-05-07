<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal;

class JadwalController extends Controller
{
    /**
     * LIST JADWAL
     * GET /api/sinta/master/jadwal
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $rombel = trim((string) $request->query('rombel', ''));
        $guru = trim((string) $request->query('guru', ''));
        $hari = trim((string) $request->query('hari', ''));

        $query = Jadwal::query()
            ->with([
                'rombel' => function ($q) {
                    $q->select(
                        'id',
                        'nama_rombel',
                        'tingkat',
                        'ruang_kelas_id',
                        'tahun_ajaran_id'
                    )->with([
                        'ruangKelas:id,nama',
                        'tahunAjaran:id,nama_tahun,semester,status',
                    ]);
                },
                'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'guru:id,nama,nip,nuptk',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('mataPelajaran', function ($m) use ($q) {
                    $m->where('nama_mapel', 'like', "%{$q}%")
                        ->orWhere('kelompok', 'like', "%{$q}%");
                })->orWhereHas('rombel', function ($r) use ($q) {
                    $r->where('nama_rombel', 'like', "%{$q}%")
                        ->orWhere('tingkat', 'like', "%{$q}%");
                })->orWhereHas('guru', function ($g) use ($q) {
                    $g->where('nama', 'like', "%{$q}%");
                });
            });
        }

        if ($rombel !== '') {
            $query->where(function ($sub) use ($rombel) {
                if (is_numeric($rombel)) {
                    $sub->where('rombel_id', (int) $rombel);
                } else {
                    $sub->whereHas('rombel', function ($r) use ($rombel) {
                        $r->where('nama_rombel', $rombel)
                            ->orWhere('nama_rombel', 'like', "%{$rombel}%");
                    });
                }
            });
        }

        if ($guru !== '') {
            $query->where(function ($sub) use ($guru) {
                if (is_numeric($guru)) {
                    $sub->where('guru_id', (int) $guru);
                } else {
                    $sub->whereHas('guru', function ($g) use ($guru) {
                        $g->where('nama', 'like', "%{$guru}%");
                    });
                }
            });
        }

        if ($hari !== '') {
            $query->where('hari', $hari);
        }

        $data = $query
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (Jadwal $j) {
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
                    'rombel' => $j->rombel ? [
                        'id' => $j->rombel->id,
                        'nama_rombel' => $j->rombel->nama_rombel,
                        'tingkat' => $j->rombel->tingkat,
                        'ruang_kelas' => $j->rombel->ruangKelas ? [
                            'id' => $j->rombel->ruangKelas->id,
                            'nama' => $j->rombel->ruangKelas->nama,
                        ] : null,
                        'tahun_ajaran' => $j->rombel->tahunAjaran ? [
                            'id' => $j->rombel->tahunAjaran->id,
                            'nama_tahun' => $j->rombel->tahunAjaran->nama_tahun,
                            'semester' => $j->rombel->tahunAjaran->semester,
                            'status' => $j->rombel->tahunAjaran->status,
                        ] : null,
                    ] : null,
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
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL JADWAL
     * GET /api/sinta/master/jadwal/{id}
     */
    public function show($id)
    {
        $jadwal = Jadwal::query()
            ->with([
                'rombel' => function ($q) {
                    $q->select(
                        'id',
                        'nama_rombel',
                        'tingkat',
                        'ruang_kelas_id',
                        'tahun_ajaran_id'
                    )->with([
                        'ruangKelas:id,nama',
                        'tahunAjaran:id,nama_tahun,semester,status',
                    ]);
                },
                'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'guru:id,nama,nip,nuptk',
            ])
            ->find($id);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $jadwal->id,
                'rombel_id' => $jadwal->rombel_id,
                'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                'guru_id' => $jadwal->guru_id,
                'hari' => $jadwal->hari,
                'slot_kode' => $jadwal->slot_kode,
                'durasi_jp' => $jadwal->durasi_jp,
                'jam_mulai' => $jadwal->jam_mulai,
                'jam_selesai' => $jadwal->jam_selesai,
                'rombel' => $jadwal->rombel ? [
                    'id' => $jadwal->rombel->id,
                    'nama_rombel' => $jadwal->rombel->nama_rombel,
                    'tingkat' => $jadwal->rombel->tingkat,
                    'ruang_kelas' => $jadwal->rombel->ruangKelas ? [
                        'id' => $jadwal->rombel->ruangKelas->id,
                        'nama' => $jadwal->rombel->ruangKelas->nama,
                    ] : null,
                    'tahun_ajaran' => $jadwal->rombel->tahunAjaran ? [
                        'id' => $jadwal->rombel->tahunAjaran->id,
                        'nama_tahun' => $jadwal->rombel->tahunAjaran->nama_tahun,
                        'semester' => $jadwal->rombel->tahunAjaran->semester,
                        'status' => $jadwal->rombel->tahunAjaran->status,
                    ] : null,
                ] : null,
                'mapel' => $jadwal->mataPelajaran ? [
                    'id' => $jadwal->mataPelajaran->id,
                    'nama_mapel' => $jadwal->mataPelajaran->nama_mapel,
                    'kelompok' => $jadwal->mataPelajaran->kelompok,
                    'kkm' => $jadwal->mataPelajaran->kkm,
                    'status' => $jadwal->mataPelajaran->status,
                ] : null,
                'guru' => $jadwal->guru ? [
                    'id' => $jadwal->guru->id,
                    'nama' => $jadwal->guru->nama,
                    'nip' => $jadwal->guru->nip,
                    'nuptk' => $jadwal->guru->nuptk,
                ] : null,
            ],
        ]);
    }
}