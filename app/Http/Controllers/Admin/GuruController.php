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
                $like = '%' . $q . '%';

                $query->where(function ($sub) use ($like) {
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

        /*
         * Kolom nip pada database kamu bertipe NOT NULL.
         * Jadi kalau admin mengosongkan NIP untuk Non-PNS, sistem simpan string kosong,
         * bukan NULL, supaya tidak error di database.
         */
        $data['nip'] = $data['nip'] ?? '';

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('foto_guru', 'public');
        }

        if ($request->hasFile('ttd_file')) {
            $data['ttd_path'] = $request->file('ttd_file')->store('ttd-guru', 'public');
        }

        unset($data['ttd_file']);

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

        $data['nip'] = $data['nip'] ?? '';

        if ($request->hasFile('foto')) {
            if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
                Storage::disk('public')->delete($guru->foto);
            }

            $data['foto'] = $request->file('foto')->store('foto_guru', 'public');
        } else {
            unset($data['foto']);
        }

        if ($request->hasFile('ttd_file')) {
            if ($guru->ttd_path && Storage::disk('public')->exists($guru->ttd_path)) {
                Storage::disk('public')->delete($guru->ttd_path);
            }

            $data['ttd_path'] = $request->file('ttd_file')->store('ttd-guru', 'public');
        }

        unset($data['ttd_file']);

        $guru->update($data);

        return redirect()
            ->route('admin.guru.index')
            ->with('ok', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        if ($guru->foto && Storage::disk('public')->exists($guru->foto)) {
            Storage::disk('public')->delete($guru->foto);
        }

        if ($guru->ttd_path && Storage::disk('public')->exists($guru->ttd_path)) {
            Storage::disk('public')->delete($guru->ttd_path);
        }

        $guru->delete();

        return redirect()
            ->route('admin.guru.index')
            ->with('success', 'Data guru berhasil dihapus.');
    }

    public function show(Guru $guru)
    {
        return view('admin.guru.show', compact('guru'));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $regexNama = "/^[A-Za-zÀ-ÿ\s\.\'\-,]+$/u";

        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                "regex:$regexNama",
            ],

            'jk' => ['required', Rule::in(['L', 'P'])],

            'nip' => ['nullable', 'digits_between:8,25'],

            'nuptk' => ['required', 'digits_between:8,25'],

            'tempat_lahir' => [
                'required',
                'string',
                'max:100',
                "regex:$regexNama",
            ],

            'tanggal_lahir' => ['required', 'date'],

            'status_kepegawaian' => ['required', Rule::in(['PNS', 'PPPK', 'Non-PNS'])],

            'no_hp' => ['required', 'digits_between:10,15'],

            'email' => [
                'required',
                'email:rfc,dns',
                'max:150',
                Rule::unique('guru', 'email')->ignore($ignoreId),
            ],

            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],

            'alamat' => ['required', 'string', 'min:5'],

            'foto' => array_filter([
                $ignoreId ? 'nullable' : 'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ]),

            'ttd_file' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],
        ], [
            'ttd_file.image' => 'Tanda tangan digital harus berupa file gambar.',
            'ttd_file.mimes' => 'Tanda tangan digital harus berformat JPG, JPEG, atau PNG.',
            'ttd_file.max' => 'Ukuran tanda tangan digital maksimal 2 MB.',
        ], [
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
            'ttd_file' => 'tanda tangan digital',
        ]);
    }
}