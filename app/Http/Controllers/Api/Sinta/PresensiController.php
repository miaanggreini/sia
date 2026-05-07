<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\PresensiSiswa;
use App\Models\SesiPresensi;

class PresensiController extends Controller
{
    /**
     * PRESENSI BY SISWA (NIS)
     * GET /api/sinta/siswa/{nis}/presensi
     */
    public function bySiswa($nis)
    {
        $siswa = Siswa::query()
            ->with([
                'rombelAktif:id,nama_rombel,tingkat',
            ])
            ->where('nis', $nis)
            ->first();

        if (!$siswa) {
            return response()->json([
                'status' => false,
                'message' => 'Siswa tidak ditemukan',
                'data' => [],
            ], 404);
        }

        $rombelAktif = $siswa->rombelAktif->first();

        $presensi = PresensiSiswa::query()
            ->with([
                'sesi.rombel:id,nama_rombel,tingkat',
                'sesi.mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'sesi.guru:id,nama,nip,nuptk',
            ])
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('dipindai_pada')
            ->orderByDesc('id')
            ->get();

        $rows = $presensi->map(function (PresensiSiswa $p) {
            $sesi = $p->sesi;

            return [
                'id' => $p->id,
                'sesi_id' => $p->sesi_presensi_id,
                'tanggal' => optional($sesi?->mulai_pada)->format('Y-m-d'),
                'waktu_mulai' => optional($sesi?->mulai_pada)->format('H:i'),
                'waktu_tutup' => optional($sesi?->ditutup_pada)->format('H:i'),
                'rombel' => $sesi?->rombel ? [
                    'id' => $sesi->rombel->id,
                    'nama_rombel' => $sesi->rombel->nama_rombel,
                    'tingkat' => $sesi->rombel->tingkat,
                ] : null,
                'mapel' => $sesi?->mataPelajaran ? [
                    'id' => $sesi->mataPelajaran->id,
                    'nama_mapel' => $sesi->mataPelajaran->nama_mapel,
                    'kelompok' => $sesi->mataPelajaran->kelompok,
                    'kkm' => $sesi->mataPelajaran->kkm,
                    'status' => $sesi->mataPelajaran->status,
                ] : null,
                'guru' => $sesi?->guru ? [
                    'id' => $sesi->guru->id,
                    'nama' => $sesi->guru->nama,
                    'nip' => $sesi->guru->nip,
                    'nuptk' => $sesi->guru->nuptk,
                ] : null,
                'status' => $p->status,
                'dipindai_pada' => optional($p->dipindai_pada)->format('Y-m-d H:i:s'),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'message' => 'OK',
            'data' => [
                'siswa' => [
                    'id' => $siswa->id,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'nama' => $siswa->nama,
                    'rombel' => $rombelAktif ? [
                        'id' => $rombelAktif->id,
                        'nama_rombel' => $rombelAktif->nama_rombel,
                        'tingkat' => $rombelAktif->tingkat,
                    ] : null,
                ],
                'presensi' => $rows,
            ],
        ]);
    }

    /**
     * LIST SESI PRESENSI PER ROMBEL
     * GET /api/sinta/master/rombel/{id}/sesi-presensi
     */
    public function rombelSessions($rombelId)
    {
        $sessions = SesiPresensi::query()
            ->with([
                'rombel:id,nama_rombel,tingkat',
                'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'guru:id,nama,nip,nuptk',
                'presensi:id,sesi_presensi_id,status',
            ])
            ->where('rombel_id', $rombelId)
            ->orderByDesc('mulai_pada')
            ->get();

        $data = $sessions->map(function (SesiPresensi $s) {
            return [
                'id' => $s->id,
                'tanggal' => optional($s->mulai_pada)->format('Y-m-d'),
                'mulai_pada' => optional($s->mulai_pada)->toDateTimeString(),
                'ditutup_pada' => optional($s->ditutup_pada)->toDateTimeString(),
                'status' => $s->status,
                'rombel' => $s->rombel ? [
                    'id' => $s->rombel->id,
                    'nama_rombel' => $s->rombel->nama_rombel,
                    'tingkat' => $s->rombel->tingkat,
                ] : null,
                'mapel' => $s->mataPelajaran ? [
                    'id' => $s->mataPelajaran->id,
                    'nama_mapel' => $s->mataPelajaran->nama_mapel,
                ] : null,
                'guru' => $s->guru ? [
                    'id' => $s->guru->id,
                    'nama' => $s->guru->nama,
                ] : null,
                'rekap' => $s->rekap,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL PRESENSI PER SESI
     * GET /api/sinta/presensi/sesi/{id}
     */
    public function sesiDetail($id)
    {
        $sesi = SesiPresensi::query()
            ->with([
                'rombel:id,nama_rombel,tingkat',
                'mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'guru:id,nama,nip,nuptk',
                'presensi.siswa:id,nis,nama',
            ])
            ->find($id);

        if (!$sesi) {
            return response()->json([
                'status' => false,
                'message' => 'Sesi presensi tidak ditemukan',
                'data' => null,
            ], 404);
        }

        $presensi = $sesi->presensi
            ->sortBy('siswa.nama')
            ->values()
            ->map(function (PresensiSiswa $p) {
                return [
                    'id' => $p->id,
                    'siswa' => $p->siswa ? [
                        'id' => $p->siswa->id,
                        'nis' => $p->siswa->nis,
                        'nama' => $p->siswa->nama,
                    ] : null,
                    'status' => $p->status,
                    'dipindai_pada' => optional($p->dipindai_pada)->format('Y-m-d H:i:s'),
                ];
            });

        $data = [
            'id' => $sesi->id,
            'mulai_pada' => optional($sesi->mulai_pada)->toDateTimeString(),
            'ditutup_pada' => optional($sesi->ditutup_pada)->toDateTimeString(),
            'status' => $sesi->status,
            'rombel' => $sesi->rombel ? [
                'id' => $sesi->rombel->id,
                'nama_rombel' => $sesi->rombel->nama_rombel,
                'tingkat' => $sesi->rombel->tingkat,
            ] : null,
            'mapel' => $sesi->mataPelajaran ? [
                'id' => $sesi->mataPelajaran->id,
                'nama_mapel' => $sesi->mataPelajaran->nama_mapel,
                'kelompok' => $sesi->mataPelajaran->kelompok,
                'kkm' => $sesi->mataPelajaran->kkm,
                'status' => $sesi->mataPelajaran->status,
            ] : null,
            'guru' => $sesi->guru ? [
                'id' => $sesi->guru->id,
                'nama' => $sesi->guru->nama,
                'nip' => $sesi->guru->nip,
                'nuptk' => $sesi->guru->nuptk,
            ] : null,
            'rekap' => $sesi->rekap,
            'presensi' => $presensi,
        ];

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * REKAP PRESENSI PER ROMBEL
     * GET /api/sinta/presensi/rombel/{id}
     */
    public function byRombel($rombelId)
    {
        $sessions = SesiPresensi::query()
            ->with([
                'presensi:id,sesi_presensi_id,status',
            ])
            ->where('rombel_id', $rombelId)
            ->orderByDesc('mulai_pada')
            ->get();

        $data = $sessions->map(function (SesiPresensi $s) {
            return [
                'id' => $s->id,
                'tanggal' => optional($s->mulai_pada)->format('Y-m-d'),
                'mulai_pada' => optional($s->mulai_pada)->toDateTimeString(),
                'status' => $s->status,
                'rekap' => $s->rekap,
            ];
        })->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}