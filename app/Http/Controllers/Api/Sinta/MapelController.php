<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    /**
     * LIST MAPEL
     * GET /api/sinta/master/mapel
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $query = MataPelajaran::query()
            ->select([
                'id',
                'nama_mapel',
                'kelompok',
                'kkm',
                'status',
            ]);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_mapel', 'like', "%{$q}%")
                    ->orWhere('kelompok', 'like', "%{$q}%");
            });
        }

        $data = $query
            ->orderBy('nama_mapel')
            ->get()
            ->map(function (MataPelajaran $m) {
                return [
                    'id' => $m->id,
                    'nama_mapel' => $m->nama_mapel,
                    'kelompok' => $m->kelompok,
                    'kkm' => $m->kkm,
                    'status' => $m->status,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL MAPEL
     * GET /api/sinta/master/mapel/{id}
     */
    public function show($id)
    {
        $mapel = MataPelajaran::query()
            ->with([
                'guru:id,nama,nip,nuptk',
            ])
            ->find($id);

        if (!$mapel) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel,
                'kelompok' => $mapel->kelompok,
                'kkm' => $mapel->kkm,
                'status' => $mapel->status,
                'guru_pengajar' => $mapel->guru
                    ->sortBy('nama')
                    ->values()
                    ->map(function ($g) {
                        return [
                            'id' => $g->id,
                            'nama' => $g->nama,
                            'nip' => $g->nip,
                            'nuptk' => $g->nuptk,
                        ];
                    }),
            ],
        ]);
    }
}