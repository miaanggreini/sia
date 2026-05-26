<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PengumumanController extends Controller
{
    private const KATEGORI_OPTIONS = [
        'akademik'        => 'Akademik',
        'kesiswaan'       => 'Kesiswaan',
        'ekstrakurikuler' => 'Ekstrakurikuler',
        'kegiatan'        => 'Kegiatan',
    ];

    public function index(Request $r)
    {
        $q        = trim((string) $r->q);
        $kategori = $r->kategori;

        $items = Pengumuman::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('judul', 'like', "%{$q}%")
                        ->orWhere('isi', 'like', "%{$q}%");
                });
            })
            ->when($kategori, function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $kategoriOptions = self::KATEGORI_OPTIONS;

        $perluDiajukanCount    = Pengumuman::whereIn('status', ['draft', 'rejected'])->count();
        $menungguApprovalCount = Pengumuman::where('status', 'pending')->count();
        $terpublikasiCount     = Pengumuman::where('status', 'publik')->count();

        return view('admin.pengumuman.index', compact(
            'items',
            'q',
            'kategori',
            'kategoriOptions',
            'perluDiajukanCount',
            'menungguApprovalCount',
            'terpublikasiCount'
        ));
    }

    public function create()
    {
        $kategoriOptions = self::KATEGORI_OPTIONS;

        return view('admin.pengumuman.create', [
            'item'            => new Pengumuman(),
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'judul'           => ['required', 'string', 'max:180'],
            'isi'             => ['required', 'string'],
            'kategori'        => ['nullable', Rule::in(array_keys(self::KATEGORI_OPTIONS))],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'judul.required'          => 'Judul pengumuman wajib diisi.',
            'judul.max'               => 'Judul pengumuman maksimal 180 karakter.',
            'isi.required'            => 'Isi pengumuman wajib diisi.',
            'kategori.in'             => 'Kategori pengumuman tidak valid.',
            'tanggal_mulai.required'  => 'Tanggal mulai tayang wajib diisi.',
            'tanggal_mulai.date'      => 'Tanggal mulai tayang tidak valid.',
            'tanggal_selesai.date'    => 'Tanggal selesai tayang tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $data['status'] = 'draft';

        Pengumuman::create($data);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat sebagai draft.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'rejected'], true)) {
            return redirect()
                ->route('admin.pengumuman.show', $pengumuman)
                ->with('error', 'Pengumuman dengan status ini tidak dapat diedit.');
        }

        $kategoriOptions = self::KATEGORI_OPTIONS;

        return view('admin.pengumuman.edit', [
            'item'            => $pengumuman,
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    public function update(Request $r, Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'rejected'], true)) {
            return redirect()
                ->route('admin.pengumuman.show', $pengumuman)
                ->with('error', 'Pengumuman dengan status ini tidak dapat diedit.');
        }

        $data = $r->validate([
            'judul'           => ['required', 'string', 'max:180'],
            'isi'             => ['required', 'string'],
            'kategori'        => ['nullable', Rule::in(array_keys(self::KATEGORI_OPTIONS))],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'judul.required'          => 'Judul pengumuman wajib diisi.',
            'judul.max'               => 'Judul pengumuman maksimal 180 karakter.',
            'isi.required'            => 'Isi pengumuman wajib diisi.',
            'kategori.in'             => 'Kategori pengumuman tidak valid.',
            'tanggal_mulai.required'  => 'Tanggal mulai tayang wajib diisi.',
            'tanggal_mulai.date'      => 'Tanggal mulai tayang tidak valid.',
            'tanggal_selesai.date'    => 'Tanggal selesai tayang tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        // Setelah direvisi dari rejected, kembalikan ke draft agar bisa diajukan ulang
        if ($pengumuman->status === 'rejected') {
            $data['status']       = 'draft';
            $data['alasan_tolak'] = null;
            $data['submitted_at'] = null;
        }

        $pengumuman->update($data);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function show(Pengumuman $pengumuman)
    {
        return view('admin.pengumuman.show', [
            'item' => $pengumuman,
        ]);
    }

    public function destroy(Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Hanya pengumuman Draft atau Ditolak yang dapat dihapus.');
        }

        $pengumuman->delete();

        return back()->with('success', 'Pengumuman berhasil dihapus.');
    }

    public function submit(Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Hanya pengumuman Draft atau Ditolak yang bisa diajukan.');
        }

        $pengumuman->update([
            'status'       => 'pending',
            'submitted_at' => now(),
            'alasan_tolak' => null,
        ]);

        return back()->with('success', 'Pengumuman berhasil diajukan ke Kepala Sekolah.');
    }
}