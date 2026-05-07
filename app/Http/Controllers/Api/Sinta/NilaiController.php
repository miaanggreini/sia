<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Siswa;

class NilaiController extends Controller
{
    /**
     * NILAI BY SISWA (NIS)
     * GET /api/sinta/siswa/{nis}/nilai
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

        $nilaiList = Nilai::query()
            ->with([
                'jadwal.rombel:id,nama_rombel,tingkat',
                'jadwal.mataPelajaran:id,nama_mapel,kelompok,kkm,status',
                'jadwal.guru:id,nama,nip,nuptk',
            ])
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('jadwal_id')
            ->orderByDesc('id')
            ->get();

        $rows = $nilaiList->map(function (Nilai $n) {
            $jadwal = $n->jadwal;

            return [
                'id' => $n->id,
                'jadwal_id' => $n->jadwal_id,
                'tahun_ajaran_id' => $n->tahun_ajaran_id,
                'semester' => $n->semester,

                'rombel' => $jadwal?->rombel ? [
                    'id' => $jadwal->rombel->id,
                    'nama_rombel' => $jadwal->rombel->nama_rombel,
                    'tingkat' => $jadwal->rombel->tingkat,
                ] : null,

                'mapel' => $jadwal?->mataPelajaran ? [
                    'id' => $jadwal->mataPelajaran->id,
                    'nama_mapel' => $jadwal->mataPelajaran->nama_mapel,
                    'kelompok' => $jadwal->mataPelajaran->kelompok,
                    'kkm' => $jadwal->mataPelajaran->kkm,
                    'status' => $jadwal->mataPelajaran->status,
                ] : null,

                'guru' => $jadwal?->guru ? [
                    'id' => $jadwal->guru->id,
                    'nama' => $jadwal->guru->nama,
                    'nip' => $jadwal->guru->nip,
                    'nuptk' => $jadwal->guru->nuptk,
                ] : null,

                'lm1_tp1' => $n->lm1_tp1,
                'lm1_tp2' => $n->lm1_tp2,
                'lm1_tp3' => $n->lm1_tp3,
                'lm1_tp4' => $n->lm1_tp4,
                'lm1_nilai' => $n->lm1_nilai,

                'lm2_tp1' => $n->lm2_tp1,
                'lm2_tp2' => $n->lm2_tp2,
                'lm2_tp3' => $n->lm2_tp3,
                'lm2_tp4' => $n->lm2_tp4,
                'lm2_nilai' => $n->lm2_nilai,

                'lm3_tp1' => $n->lm3_tp1,
                'lm3_tp2' => $n->lm3_tp2,
                'lm3_tp3' => $n->lm3_tp3,
                'lm3_tp4' => $n->lm3_tp4,
                'lm3_nilai' => $n->lm3_nilai,

                'lm4_tp1' => $n->lm4_tp1,
                'lm4_tp2' => $n->lm4_tp2,
                'lm4_tp3' => $n->lm4_tp3,
                'lm4_tp4' => $n->lm4_tp4,
                'lm4_nilai' => $n->lm4_nilai,

                'nilai_akhir' => $n->nilai_akhir,
                'status' => $n->status,
                'status_penilaian' => $n->status_penilaian,
                'finalized_at' => optional($n->finalized_at)->toDateTimeString(),
                'created_at' => optional($n->created_at)->toDateTimeString(),
                'updated_at' => optional($n->updated_at)->toDateTimeString(),
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
                    'rombel_nama' => $rombelAktif?->nama_rombel,
                ],
                'nilai' => $rows,
            ],
        ]);
    }
}