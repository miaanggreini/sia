<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MapelController extends Controller
{
    public function index(Request $request)
{
    $q = $request->q;

    $items = \App\Models\MataPelajaran::query()
        ->when($q, function ($qr) use ($q) {
            $like = '%'.$q.'%';
            $qr->where(function ($sub) use ($like) {
                $sub->where('nama_mapel', 'like', $like)
                    ->orWhere('kelompok', 'like', $like)
                    ->orWhere('status', 'like', $like);
            });
        })
        ->orderBy('nama_mapel', 'asc')
        ->paginate(10)
        ->withQueryString();

    return view('admin.mapel.index', compact('items', 'q'));
}


    public function create()
    {
        $daftarGuru = \App\Models\Guru::orderBy('nama')->get();
        $mapel = new MataPelajaran();
        return view('admin.mapel.create', compact('daftarGuru', 'mapel'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        MataPelajaran::create($data);

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(MataPelajaran $mapel)
    {
        $daftarGuru = \App\Models\Guru::orderBy('nama')->get();
        return view('admin.mapel.edit', compact('daftarGuru', 'mapel'));
    }

    public function update(Request $request, MataPelajaran $mapel)
    {
        $data = $this->validatedData($request, $mapel->id);
        $mapel->update($data);

        return redirect()->route('admin.mapel.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mapel)
    {
        $mapel->delete();
        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    private function validatedData(Request $request, $ignoreId = null): array
    {
        // Samakan opsi dengan yang ada di form!
        return $request->validate([
        
            'nama_mapel'  => ['required','string','max:120'],
         
            'kelompok'    => ['nullable', Rule::in(['Umum','Pilihan'])], // <-- disamakan
            'kkm'         => ['nullable','numeric','between:0,100'],
            'status'      => ['required', Rule::in(['aktif','nonaktif'])],
        ], [], [
            'nama_mapel'  => 'nama mapel',
        ]);
    }
    public function show(MataPelajaran $mapel)
{
    // eager load guru bila perlu

    return view('admin.mapel.show', compact('mapel'));
}

}
