<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\RuangKelas;
use Illuminate\Http\Request;

class RuangKelasController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $items = RuangKelas::query()
            ->when($q, function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%");
            })
            ->orderByRaw("
                CASE
                    WHEN nama LIKE 'X%'   THEN 1
                    WHEN nama LIKE 'XI%'  THEN 2
                    WHEN nama LIKE 'XII%' THEN 3
                    ELSE 4
                END
            ")
            ->orderByRaw('LENGTH(nama), nama')
            ->paginate(50);

        return view('kepsek.ruang.index', compact('items', 'q'));
    }

    public function edit(RuangKelas $ruang)
    {
        $item = $ruang;
        return view('kepsek.ruang.edit', compact('item'));
    }

    public function update(Request $request, RuangKelas $ruang)
    {
        $data = $request->validate([
            'nama'      => 'required|string|max:100|unique:ruang_kelas,nama,' . $ruang->id,
            'kapasitas' => 'nullable|integer|min:1|max:200',
        ]);

        $ruang->update($data);

        return redirect()
            ->route('kepala_sekolah.data.ruang-kelas')
            ->with('ok', 'Ruang kelas berhasil diperbarui.');
    }

    public function destroy(RuangKelas $ruang)
    {
        $ruang->delete();

        return redirect()
            ->route('kepala_sekolah.data.ruang-kelas')
            ->with('ok', 'Ruang kelas berhasil dihapus.');
    }
}