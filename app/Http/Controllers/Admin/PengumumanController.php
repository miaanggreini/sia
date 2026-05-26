<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
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
        $q = trim((string) $r->q);
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

        $perluDiajukanCount = Pengumuman::whereIn('status', ['draft', 'rejected'])->count();
        $menungguApprovalCount = Pengumuman::where('status', 'pending')->count();

        /*
         * Pada alur baru, status approved berarti sudah disetujui kepala sekolah.
         * Pengumuman akan tampil otomatis ke siswa sesuai tanggal_mulai dan tanggal_selesai.
         */
        $siapPublikasiCount = Pengumuman::where('status', 'approved')->count();

        return view('admin.pengumuman.index', compact(
            'items',
            'q',
            'kategori',
            'kategoriOptions',
            'perluDiajukanCount',
            'menungguApprovalCount',
            'siapPublikasiCount'
        ));
    }

    public function create()
    {
        $kategoriOptions = self::KATEGORI_OPTIONS;

        return view('admin.pengumuman.create', [
            'item' => new Pengumuman(),
            'kategoriOptions' => $kategoriOptions,
        ]);
    }

    public function store(Request $r)
    {
        $data = $this->validatedData($r);

        $data['status'] = 'draft';

        $this->setIfColumnExists($data, 'created_by', auth()->id());
        $this->setIfColumnExists($data, 'approved_by', null);
        $this->setIfColumnExists($data, 'approved_at', null);
        $this->setIfColumnExists($data, 'submitted_at', null);
        $this->setIfColumnExists($data, 'alasan_tolak', null);
        $this->setIfColumnExists($data, 'published_at', null);

        Pengumuman::create($data);

        return redirect()
            ->route('admin.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dibuat sebagai draft. Silakan ajukan ke Kepala Sekolah.');
    }

    public function submit(Pengumuman $pengumuman)
    {
        if (!in_array($pengumuman->status, ['draft', 'rejected'], true)) {
            return back()->with('error', 'Hanya pengumuman Draft atau Ditolak yang bisa diajukan.');
        }

        if (empty($pengumuman->tanggal_mulai)) {
            return back()->with('error', 'Tanggal mulai publikasi harus diisi sebelum pengumuman diajukan.');
        }

        $data = [
            'status'       => 'pending',
            'alasan_tolak' => null,
        ];

        $this->setIfColumnExists($data, 'submitted_at', now());
        $this->setIfColumnExists($data, 'approved_by', null);
        $this->setIfColumnExists($data, 'approved_at', null);
        $this->setIfColumnExists($data, 'published_at', null);

        $pengumuman->update($data);

        return back()->with('success', 'Pengumuman berhasil diajukan ke Kepala Sekolah.');
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
            'item' => $pengumuman,
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

        $data = $this->validatedData($r);

        /*
         * Kalau pengumuman sebelumnya ditolak, setelah admin memperbaiki
         * isi atau tanggal publikasi, status dikembalikan menjadi draft.
         * Setelah itu admin bisa mengajukan ulang ke Kepala Sekolah.
         */
        if ($pengumuman->status === 'rejected') {
            $data['status'] = 'draft';
            $data['alasan_tolak'] = null;

            $this->setIfColumnExists($data, 'submitted_at', null);
            $this->setIfColumnExists($data, 'approved_by', null);
            $this->setIfColumnExists($data, 'approved_at', null);
            $this->setIfColumnExists($data, 'published_at', null);
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

    /*
     * Pada alur baru, approval dilakukan oleh Kepala Sekolah,
     * bukan oleh Admin. Method ini dibiarkan agar route lama tidak error.
     */
    public function approve(Pengumuman $pengumuman)
    {
        return back()->with('error', 'Approval pengumuman dilakukan oleh Kepala Sekolah.');
    }

    /*
     * Pada alur baru, admin tidak perlu lagi mengatur publikasi setelah approval.
     * Tanggal publikasi sudah diisi saat membuat atau mengedit pengumuman.
     */
    public function publishForm(Pengumuman $pengumuman)
    {
        return redirect()
            ->route('admin.pengumuman.show', $pengumuman)
            ->with('error', 'Publikasi tidak perlu diatur manual. Pengumuman yang disetujui akan tampil otomatis sesuai tanggal publikasi.');
    }

    public function publishStore(Request $r, Pengumuman $pengumuman)
    {
        return redirect()
            ->route('admin.pengumuman.show', $pengumuman)
            ->with('error', 'Publikasi tidak perlu diatur manual. Pengumuman yang disetujui akan tampil otomatis sesuai tanggal publikasi.');
    }

    private function validatedData(Request $r): array
    {
        return $r->validate([
            'judul'           => ['required', 'string', 'max:180'],
            'isi'             => ['required', 'string'],
            'kategori'        => ['nullable', Rule::in(array_keys(self::KATEGORI_OPTIONS))],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after_or_equal:tanggal_mulai'],
        ], [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.max' => 'Judul pengumuman maksimal 180 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'kategori.in' => 'Kategori pengumuman tidak valid.',
            'tanggal_mulai.required' => 'Tanggal mulai publikasi wajib diisi.',
            'tanggal_mulai.date' => 'Tanggal mulai publikasi tidak valid.',
            'tanggal_selesai.date' => 'Tanggal selesai publikasi tidak valid.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai publikasi tidak boleh lebih awal dari tanggal mulai.',
        ]);
    }

    private function setIfColumnExists(array &$data, string $column, mixed $value): void
    {
        if (Schema::hasColumn('pengumuman', $column)) {
            $data[$column] = $value;
        }
    }
}