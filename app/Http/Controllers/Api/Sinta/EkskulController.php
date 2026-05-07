<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ekskul;

class EkskulController extends Controller
{
    /**
     * MASTER EKSKUL (LIST)
     * GET /api/sinta/master/ekskul
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $pembina = trim((string) $request->query('pembina', ''));
        $hari = trim((string) $request->query('hari', ''));
        $nis = trim((string) $request->query('nis', ''));

        $query = Ekskul::query()
            ->with([
                'pembina:id,nama,nip,nuptk',
            ])
            ->withCount([
                'anggota as jumlah_anggota' => function ($sub) {
                    $sub->where('status', 'aktif');
                },
            ]);

        if ($q !== '') {
            $query->where('nama', 'like', "%{$q}%");
        }

        if ($pembina !== '') {
            if (is_numeric($pembina)) {
                $query->where('pembina_id', (int) $pembina);
            } else {
                $query->whereHas('pembina', function ($g) use ($pembina) {
                    $g->where('nama', 'like', "%{$pembina}%");
                });
            }
        }

        if ($hari !== '') {
            $query->where('hari', $hari);
        }

        // khusus ortu: filter ekskul berdasarkan NIS siswa
        if ($nis !== '') {
            $query->whereHas('anggota', function ($ang) use ($nis) {
                $ang->where('status', 'aktif')
                    ->whereHas('siswa', function ($s) use ($nis) {
                        $s->where('nis', $nis);
                    });
            });
        }

        $data = $query
            ->orderBy('nama')
            ->get()
            ->map(function (Ekskul $e) {
                return [
                    'id' => $e->id,
                    'nama' => $e->nama,
                    'pembina' => [
                        'id' => $e->pembina?->id,
                        'nama' => $e->pembina?->nama,
                        'nip' => $e->pembina?->nip,
                        'nuptk' => $e->pembina?->nuptk,
                    ],
                    'hari' => $e->hari,
                    'jam_mulai' => $e->jam_mulai,
                    'jam_selesai' => $e->jam_selesai,
                    'lokasi' => $e->lokasi,
                    'jumlah_anggota' => (int) ($e->jumlah_anggota ?? 0),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL EKSKUL + ANGGOTA + PRESENSI
     * GET /api/sinta/master/ekskul/{id}
     */
    public function show($id)
    {
        $ekskul = Ekskul::query()
            ->with([
                'pembina:id,nama,nip,nuptk',
                'anggota.siswa:id,nis,nama',
                'anggota.tahunAjaran:id,nama_tahun',
                'presensi.siswa:id,nis,nama',
                'presensi.tahunAjaran:id,nama_tahun',
                'presensi.pembina:id,nama,nip,nuptk',
            ])
            ->find($id);

        if (!$ekskul) {
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Ekskul tidak ditemukan',
            ], 404);
        }

        $anggota = $ekskul->anggota
            ->sortByDesc('tanggal_gabung')
            ->values()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'siswa' => $a->siswa ? [
                        'id' => $a->siswa->id,
                        'nis' => $a->siswa->nis,
                        'nama' => $a->siswa->nama,
                    ] : null,
                    'tahun_ajaran' => $a->tahunAjaran ? [
                        'id' => $a->tahunAjaran->id,
                        'nama_tahun' => $a->tahunAjaran->nama_tahun,
                    ] : null,
                    'status' => $a->status,
                    'tanggal_gabung' => optional($a->tanggal_gabung)->format('Y-m-d'),
                    'tanggal_keluar' => optional($a->tanggal_keluar)->format('Y-m-d'),
                    'nilai_akhir' => $a->nilai_akhir,
                    'predikat' => $a->predikat,
                    'deskripsi' => $a->deskripsi,
                ];
            });

        $presensi = $ekskul->presensi
            ->sortByDesc('tanggal')
            ->values()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'tanggal' => optional($p->tanggal)->format('Y-m-d'),
                    'siswa' => $p->siswa ? [
                        'id' => $p->siswa->id,
                        'nis' => $p->siswa->nis,
                        'nama' => $p->siswa->nama,
                    ] : null,
                    'tahun_ajaran' => $p->tahunAjaran ? [
                        'id' => $p->tahunAjaran->id,
                        'nama_tahun' => $p->tahunAjaran->nama_tahun,
                    ] : null,
                    'pembina' => $p->pembina ? [
                        'id' => $p->pembina->id,
                        'nama' => $p->pembina->nama,
                    ] : null,
                    'status' => $p->status,
                    'keterangan' => $p->keterangan,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $ekskul->id,
                'nama' => $ekskul->nama,
                'hari' => $ekskul->hari,
                'jam_mulai' => $ekskul->jam_mulai,
                'jam_selesai' => $ekskul->jam_selesai,
                'lokasi' => $ekskul->lokasi,
                'pembina' => $ekskul->pembina ? [
                    'id' => $ekskul->pembina->id,
                    'nama' => $ekskul->pembina->nama,
                    'nip' => $ekskul->pembina->nip,
                    'nuptk' => $ekskul->pembina->nuptk,
                ] : null,
                'anggota' => $anggota,
                'presensi' => $presensi,
            ],
        ]);
    }
}