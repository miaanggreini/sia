<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $items = MataPelajaran::query()
            ->when($q, function ($qr) use ($q) {
                $like = '%' . $q . '%';
                $qr->where(function ($sub) use ($like) {
                    $sub->where('nama_mapel', 'like', $like)
                        ->orWhere('kelompok', 'like', $like)
                        ->orWhere('status', 'like', $like);
                });
            })
            ->orderBy('nama_mapel', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('kepsek.mapel.index', compact('items', 'q'));
    }

    public function edit(MataPelajaran $mapel)
    {
        return view('kepsek.mapel.edit', compact('mapel'));
    }

    public function update(Request $request, MataPelajaran $mapel)
    {
        $data = $this->validatedData($request, $mapel->id);
        $mapel->update($data);

        return redirect()
            ->route('kepala_sekolah.data.mapel')
            ->with('ok', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mapel)
    {
        // cek apakah dipakai
        if ($mapel->rombel()->exists() || $mapel->guru()->exists()) {
            return redirect()
                ->route('kepala_sekolah.data.mapel')
                ->with('err', 'Mapel tidak bisa dihapus karena sudah digunakan. Ubah status menjadi nonaktif.');
        }

        $mapel->delete();

        return redirect()
            ->route('kepala_sekolah.data.mapel')
            ->with('ok', 'Mata pelajaran berhasil dihapus.');
    }

    private function validatedData(Request $request, $ignoreId = null)
    {
        return $request->validate([
            'nama_mapel' => ['required','string','max:120'],
            'kelompok'   => ['nullable', Rule::in(['Umum','Pilihan'])],
            'kkm'        => ['nullable','numeric','between:0,100'],
            'status'     => ['required', Rule::in(['aktif','nonaktif'])],
        ]);
    }
}