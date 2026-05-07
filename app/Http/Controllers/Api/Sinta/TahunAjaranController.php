<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;

class TahunAjaranController extends Controller
{
    /**
     * LIST TAHUN AJARAN
     * GET /api/sinta/master/tahun-ajaran
     */
    public function index()
    {
        $data = TahunAjaran::query()
            ->select([
                'id',
                'nama_tahun',
                'semester',
                'status',
                'tanggal_mulai',
                'tanggal_selesai',
            ])
            ->orderByDesc('id')
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'nama_tahun' => $r->nama_tahun,
                    'semester' => $r->semester,
                    'status' => $r->status,
                    'tanggal_mulai' => optional($r->tanggal_mulai)->format('Y-m-d'),
                    'tanggal_selesai' => optional($r->tanggal_selesai)->format('Y-m-d'),
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL TAHUN AJARAN
     * GET /api/sinta/master/tahun-ajaran/{id}
     */
    public function show($id)
    {
        $tahunAjaran = TahunAjaran::query()->find($id);

        if (!$tahunAjaran) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $tahunAjaran->id,
                'nama_tahun' => $tahunAjaran->nama_tahun,
                'semester' => $tahunAjaran->semester,
                'status' => $tahunAjaran->status,
                'tanggal_mulai' => optional($tahunAjaran->tanggal_mulai)->format('Y-m-d'),
                'tanggal_selesai' => optional($tahunAjaran->tanggal_selesai)->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * TAHUN AJARAN AKTIF
     * GET /api/sinta/master/tahun-ajaran-aktif
     */
    public function aktif()
    {
        $tahunAjaran = TahunAjaran::aktif();

        if (!$tahunAjaran) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $tahunAjaran->id,
                'nama_tahun' => $tahunAjaran->nama_tahun,
                'semester' => $tahunAjaran->semester,
                'status' => $tahunAjaran->status,
                'tanggal_mulai' => optional($tahunAjaran->tanggal_mulai)->format('Y-m-d'),
                'tanggal_selesai' => optional($tahunAjaran->tanggal_selesai)->format('Y-m-d'),
            ],
        ]);
    }
}