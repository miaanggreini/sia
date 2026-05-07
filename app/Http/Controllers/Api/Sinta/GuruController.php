<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    /**
     * LIST GURU
     * GET /api/sinta/guru
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        $data = Guru::query()
            ->select([
                'id',
                'user_id',
                'nip',
                'nuptk',
                'nama',
                'jk',
                'tempat_lahir',
                'tanggal_lahir',
                'status_kepegawaian',
                'no_hp',
                'email',
                'foto',
                'status',
                'alamat',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhere('nip', 'like', "%{$q}%")
                        ->orWhere('nuptk', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama')
            ->get()
            ->map(fn (Guru $g) => $this->transformGuruList($g))
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    /**
     * DETAIL GURU
     * GET /api/sinta/guru/{id|nuptk|nip}
     */
    public function show($id)
    {
        $guru = Guru::query()
            ->where('id', $id)
            ->orWhere('nuptk', $id)
            ->orWhere('nip', $id)
            ->first();

        if (!$guru) {
            return response()->json([
                'status' => false,
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $this->transformGuruDetail($guru),
        ]);
    }

    private function transformGuruList(Guru $g): array
    {
        return [
            'id' => $g->id,
            'nama' => $g->nama,
            'nip' => $g->nip,
            'nuptk' => $g->nuptk,
            'jk' => $g->jk,
            'status_kepegawaian' => $g->status_kepegawaian,
            'status' => $g->status,
            'email' => $g->email,
            'no_hp' => $g->no_hp,
            'alamat' => $g->alamat,
            'foto' => $g->foto,
            'foto_url' => $this->resolveFotoUrl($g->foto),
        ];
    }

    private function transformGuruDetail(Guru $g): array
    {
        return [
            'id' => $g->id,
            'user_id' => $g->user_id,
            'nama' => $g->nama,
            'nip' => $g->nip,
            'nuptk' => $g->nuptk,
            'jk' => $g->jk,
            'tempat_lahir' => $g->tempat_lahir,
            'tanggal_lahir' => optional($g->tanggal_lahir)->format('Y-m-d'),
            'status_kepegawaian' => $g->status_kepegawaian,
            'status' => $g->status,
            'email' => $g->email,
            'no_hp' => $g->no_hp,
            'alamat' => $g->alamat,
            'foto' => $g->foto,
            'foto_url' => $this->resolveFotoUrl($g->foto),
        ];
    }

    private function resolveFotoUrl(?string $foto): ?string
    {
        if (empty($foto)) {
            return null;
        }

        $rawFoto = trim((string) $foto);

        if (preg_match('/^https?:\/\//i', $rawFoto)) {
            return $rawFoto;
        }

        $rawFoto = str_replace('\\', '/', $rawFoto);
        $rawFoto = preg_replace('#/+#', '/', $rawFoto);
        $rawFoto = ltrim($rawFoto, '/');

        $basename = basename($rawFoto);

        $candidates = [
            $rawFoto,
            'foto_guru/' . $basename,
            'sia/' . $rawFoto,
            'sia/foto_guru/' . $basename,
            'storage/foto_guru/' . $basename,
            'storage/sia/foto_guru/' . $basename,
        ];

        foreach (array_unique(array_filter($candidates)) as $relativePath) {
            if (is_file(public_path($relativePath))) {
                return asset($relativePath);
            }
        }

        return null;
    }
}