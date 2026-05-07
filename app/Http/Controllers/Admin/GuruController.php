<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');

        $items = Guru::query()
            ->when($q, function ($query) use ($q) {
                $like = '%'.$q.'%';
                $query->where(function($sub) use ($like) {
                    $sub->where('nama', 'like', $like)
                        ->orWhere('nip', 'like', $like)
                        ->orWhere('nuptk', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('no_hp', 'like', $like);
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('admin.guru.index', compact('items', 'q'));
    }

    public function create()
    {
        $guru = new Guru();
        return view('admin.guru.create', compact('guru'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request, null);

        // simpan file foto (wajib di create)
        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto_guru', 'public');
        }

        Guru::create($data);

        return redirect()
            ->route('admin.guru.index')
            ->with('ok', 'Data guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('admin.guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $data = $this->validated($request, $guru->id);

        // jika upload foto baru
        if ($request->hasFile('foto')) {
            if ($guru->foto) {
                Storage::disk('public')->delete($guru->foto);
            }
            $data['foto'] = $request->file('foto')->store('foto_guru', 'public');
        } else {
            // jangan menimpa kolom foto jika user tidak mengganti
            unset($data['foto']);
        }

        $guru->update($data);

        return redirect()
            ->route('admin.guru.index')
            ->with('ok', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        // hapus foto kalau ada
        if ($guru->foto) {
            Storage::disk('public')->delete($guru->foto);
        }

        $guru->delete();
        return redirect()->route('admin.guru.index')->with('success','Data guru berhasil dihapus.');
    }

    public function show(Guru $guru)
    {
        return view('admin.guru.show', compact('guru'));
    }

    /**
     * Validasi terpusat untuk store/update.
     * - Semua wajib diisi
     * - Nama & tempat lahir: huruf
     * - NIP/NUPTK/No HP: angka
     * - Foto: wajib saat create, optional saat update
     */
    private function validated(Request $request, ?int $ignoreId = null): array
{
    // regex huruf + spasi + beberapa tanda yang wajar
    $regexNama = "/^[A-Za-zÀ-ÿ\s\.\'\-,]+$/u";

    return $request->validate([
        'nama' => [
            'required',
            'string',
            'max:255',
            "regex:$regexNama",
        ],
        'jk' => ['required', Rule::in(['L','P'])],

        // ✅ NIP boleh kosong (untuk Non-PNS), tapi kalau diisi harus angka
        'nip' => ['nullable', 'digits_between:8,25'],

        // ✅ NUPTK wajib & angka
        'nuptk' => ['required', 'digits_between:8,25'],

        'tempat_lahir' => [
            'required',
            'string',
            'max:100',
            "regex:$regexNama",
        ],
        'tanggal_lahir' => ['required', 'date'],

        'status_kepegawaian' => ['required', Rule::in(['PNS','PPPK','Non-PNS'])],

        'no_hp' => ['required', 'digits_between:10,15'],

        'email' => [
            'required',
            'email:rfc,dns',
            'max:150',
            Rule::unique('guru','email')->ignore($ignoreId),
        ],

        'status' => ['required', Rule::in(['aktif','nonaktif'])],

        'alamat' => ['required', 'string', 'min:5'],

        'foto' => array_filter([
            $ignoreId ? 'nullable' : 'required',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ]),
    ], [], [
        'nama' => 'nama lengkap',
        'jk' => 'jenis kelamin',
        'nip' => 'NIP',
        'nuptk' => 'NUPTK',
        'tempat_lahir' => 'tempat lahir',
        'tanggal_lahir' => 'tanggal lahir',
        'status_kepegawaian' => 'status kepegawaian',
        'no_hp' => 'no. HP',
        'email' => 'email',
        'status' => 'status',
        'alamat' => 'alamat',
        'foto' => 'foto',
    ]);
}

}
