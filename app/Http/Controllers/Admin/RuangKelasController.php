<?php

namespace App\Http\Controllers\Admin;

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
                WHEN nama LIKE 'XII%' THEN 3
                WHEN nama LIKE 'XI%'  THEN 2
                WHEN nama LIKE 'X%'   THEN 1
                ELSE 4
            END
        ")
        ->orderByRaw('LENGTH(nama), nama')
        ->paginate(10);

    return view('admin.ruang.index', compact('items', 'q'));
}


    public function create()
    {
        $item = new RuangKelas();
        return view('admin.ruang.create', compact('item'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama'      => 'required|string|max:100|unique:ruang_kelas,nama',
            'kapasitas' => 'nullable|integer|min:1|max:200',
        ]);
        RuangKelas::create($data);
        return redirect()->route('admin.ruang-kelas.index')->with('ok','Ruang berhasil ditambahkan.');
    }

    public function edit(RuangKelas $ruang)
    {
        $item = $ruang;
        return view('admin.ruang.edit', compact('item'));
    }

    public function update(Request $r, RuangKelas $ruang)
    {
        $data = $r->validate([
            'nama'      => 'required|string|max:100|unique:ruang_kelas,nama,'.$ruang->id,
            'kapasitas' => 'nullable|integer|min:1|max:200',
        ]);
        $ruang->update($data);
        return redirect()->route('admin.ruang-kelas.index')->with('ok','Ruang diperbarui.');
    }

    public function destroy(RuangKelas $ruang)
    {
        // Aman, karena di FK rombel kita gunakan ON DELETE SET NULL
        $ruang->delete();
        return redirect()->route('admin.ruang-kelas.index')->with('ok','Ruang dihapus.');
    }
}
