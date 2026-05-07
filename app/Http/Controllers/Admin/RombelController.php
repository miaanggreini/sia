<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{
    Rombel,
    Guru,
    MataPelajaran,
    Siswa,
    SiswaRombel,
    TahunAjaran,
    MenuRombel,
    RuangKelas
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RombelController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $tahunAjaranList = TahunAjaran::orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? $request->tahun_ajaran_id
            : optional($tahunAjaranAktif)->id;

        $tingkat = $request->tingkat;

        $items = Rombel::query()
            ->with([
                'waliKelas',
                'mapel',
                'tahunAjaran',
                'menuRombel',
            ])
            ->select('rombel.*')
->selectSub(function ($query) use ($tahunAjaranAktif, $tahunAjaranId) {
    $query->from('siswa_rombel as sr')
        ->selectRaw('COUNT(*)')
        ->whereColumn('sr.rombel_id', 'rombel.id')
        ->whereColumn('sr.tahun_ajaran_id', 'rombel.tahun_ajaran_id');

    /*
     * Kalau yang sedang dilihat adalah tahun ajaran aktif,
     * jumlah siswa harus hanya anggota yang aktif.
     *
     * Kalau yang dilihat tahun ajaran lama,
     * jumlah siswa boleh menghitung riwayat semua anggota.
     */
    if ($tahunAjaranAktif && (int) $tahunAjaranId === (int) $tahunAjaranAktif->id) {
        $query->where('sr.aktif', 1);
    }
}, 'jumlah_siswa_histori')
            ->when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->when($tingkat, function ($query) use ($tingkat) {
                $query->where('tingkat', $tingkat);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama_rombel', 'like', "%{$q}%")
                        ->orWhereHas('waliKelas', function ($g) use ($q) {
                            $g->where('nama', 'like', "%{$q}%");
                        })
                        ->orWhereHas('tahunAjaran', function ($ta) use ($q) {
                            $ta->where('nama_tahun', 'like', "%{$q}%");
                        });
                });
            })
            ->orderByRaw("
                CASE tingkat
                    WHEN 'X' THEN 1
                    WHEN 'XI' THEN 2
                    WHEN 'XII' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('nama_rombel', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.rombel.index', compact(
            'items',
            'q',
            'tahunAjaranList',
            'tahunAjaranAktif',
            'tahunAjaranId',
            'tingkat'
        ));
    }

    public function create()
    {
        $mapel = MataPelajaran::query()
            ->when(schema_has_column('mata_pelajaran', 'aktif'), fn ($q) => $q->where('aktif', 1))
            ->orderBy('nama_mapel')
            ->get();

        $waliKelas = Guru::orderBy('nama')->get();
        $ruangKelas = RuangKelas::orderBy('nama')->get();
        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        $daftarTahunAjaran = TahunAjaran::orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        $tingkatOptions = ['X', 'XI', 'XII'];

        return view('admin.rombel.create', compact(
            'mapel',
            'waliKelas',
            'ruangKelas',
            'tahunAktif',
            'daftarTahunAjaran',
            'tingkatOptions'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama_rombel'      => ['required', 'string', 'max:150'],
            'tingkat'          => ['required', 'in:X,XI,XII'],
            'guru_id'          => ['nullable', 'exists:guru,id'],
            'ruang_kelas_id'   => ['nullable', 'exists:ruang_kelas,id'],
            'tahun_ajaran_id'  => ['required', 'exists:tahun_ajaran,id'],
            'kapasitas'        => ['nullable', 'integer', 'min:1'],
            'aktif'            => ['nullable', 'boolean'],
            'menu_rombel_id'   => ['nullable', 'exists:menu_rombel,id'],
            'mapel_ids'        => ['nullable', 'array'],
            'mapel_ids.*'      => ['exists:mata_pelajaran,id'],
        ]);

        $menu = null;

        if (!empty($data['menu_rombel_id'])) {
            $menu = MenuRombel::with('mapel')->find($data['menu_rombel_id']);
        }

        $rombel = Rombel::create([
            'nama_rombel'     => $data['nama_rombel'],
            'tingkat'         => $data['tingkat'],
            'guru_id'         => $data['guru_id'] ?? null,
            'ruang_kelas_id'  => $data['ruang_kelas_id'] ?? null,
            'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            'menu_rombel_id'  => $data['menu_rombel_id'] ?? null,
            'kapasitas'       => $data['kapasitas'] ?? ($menu->kapasitas_total ?? null),
            'aktif'           => !empty($data['aktif']) ? 1 : 0,
        ]);

        if (!empty($data['mapel_ids'])) {
            $rombel->mataPelajaran()->sync($data['mapel_ids']);
        } elseif ($menu) {
            $rombel->mataPelajaran()->sync($menu->mapel->pluck('id')->all());
        } else {
            $rombel->mataPelajaran()->sync([]);
        }

        return redirect()
            ->route('admin.rombel.index')
            ->with('ok', 'Rombel berhasil dibuat.');
    }

    public function edit(Rombel $rombel)
    {
        $rombel->load(['waliKelas', 'mapel', 'tahunAjaran']);

        $mapel = MataPelajaran::query()
            ->when(schema_has_column('mata_pelajaran', 'aktif'), fn ($q) => $q->where('aktif', 1))
            ->orderBy('nama_mapel')
            ->get();

        $waliKelas = Guru::orderBy('nama')->get();
        $ruangKelas = RuangKelas::orderBy('nama')->get();
        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        $daftarTahunAjaran = TahunAjaran::orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        $tingkatOptions = ['X', 'XI', 'XII'];

        return view('admin.rombel.edit', compact(
            'rombel',
            'mapel',
            'waliKelas',
            'ruangKelas',
            'tahunAktif',
            'daftarTahunAjaran',
            'tingkatOptions'
        ));
    }

    public function update(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'nama_rombel'      => ['required', 'string', 'max:150'],
            'tingkat'          => ['required', 'in:X,XI,XII'],
            'guru_id'          => ['nullable', 'exists:guru,id'],
            'ruang_kelas_id'   => ['nullable', 'exists:ruang_kelas,id'],
            'tahun_ajaran_id'  => ['required', 'exists:tahun_ajaran,id'],
            'kapasitas'        => ['nullable', 'integer', 'min:1'],
            'aktif'            => ['nullable', 'boolean'],
            'menu_rombel_id'   => ['nullable', 'exists:menu_rombel,id'],
            'mapel_ids'        => ['nullable', 'array'],
            'mapel_ids.*'      => ['exists:mata_pelajaran,id'],
        ]);

        $menu = null;

        if (!empty($data['menu_rombel_id'])) {
            $menu = MenuRombel::with('mapel')->find($data['menu_rombel_id']);
        } elseif ($rombel->menu_rombel_id) {
            $menu = MenuRombel::with('mapel')->find($rombel->menu_rombel_id);
        }

        $rombel->update([
            'nama_rombel'     => $data['nama_rombel'],
            'tingkat'         => $data['tingkat'],
            'guru_id'         => $data['guru_id'] ?? null,
            'ruang_kelas_id'  => $data['ruang_kelas_id'] ?? null,
            'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            'kapasitas'       => $data['kapasitas'] ?? null,
            'aktif'           => !empty($data['aktif']) ? 1 : 0,
            'menu_rombel_id'  => $data['menu_rombel_id'] ?? $rombel->menu_rombel_id,
        ]);

        if (!empty($data['mapel_ids'])) {
            $rombel->mataPelajaran()->sync($data['mapel_ids']);
        } elseif ($menu) {
            $rombel->mataPelajaran()->sync($menu->mapel->pluck('id')->all());
        } else {
            $rombel->mataPelajaran()->sync([]);
        }

        return redirect()
            ->route('admin.rombel.index')
            ->with('ok', 'Rombel berhasil diperbarui.');
    }

    public function destroy(Rombel $rombel)
    {
        $rombel->delete();

        return back()->with('ok', 'Rombel dihapus.');
    }

    public function anggota(Request $request, Rombel $rombel)
    {
        $q = trim((string) $request->q);

        $rombel->loadMissing([
            'waliKelas',
            'guru',
            'mataPelajaran',
            'mapel',
            'tahunAjaran',
        ]);

        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        $isRombelTahunAktif = $tahunAktif
            && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id;

        $anggota = Siswa::select('siswa.*', 'sr.aktif as status_keanggotaan')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 'siswa.id')
            ->where('sr.rombel_id', $rombel->id)
            ->where('sr.tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->when($isRombelTahunAktif, function ($query) {
                $query->where('sr.aktif', 1);
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('siswa.nama', 'like', "%{$q}%")
                        ->orWhere('siswa.nis', 'like', "%{$q}%")
                        ->orWhere('siswa.nisn', 'like', "%{$q}%");
                });
            })
            ->orderBy('siswa.nama')
            ->get();

        $kandidat = collect();

        if ($isRombelTahunAktif) {
$kandidat = Siswa::query()
    ->where('status', 'aktif')

    // belum punya rombel aktif pada tahun ajaran rombel ini
    ->whereNotExists(function ($sub) use ($rombel) {
        $sub->selectRaw(1)
            ->from('siswa_rombel as sr')
            ->whereColumn('sr.siswa_id', 'siswa.id')
            ->where('sr.tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->where('sr.aktif', 1);
    })

    // jangan tampilkan siswa yang masih punya histori di tahun ajaran sebelumnya
    // tetapi belum punya record sama sekali di tahun ajaran rombel ini
    ->where(function ($query) use ($rombel) {
        $query
            // boleh tampil kalau benar-benar belum pernah punya rombel sama sekali
            ->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('siswa_rombel as sr_all')
                    ->whereColumn('sr_all.siswa_id', 'siswa.id');
            })

            // atau boleh tampil kalau sudah punya record di tahun ajaran rombel ini,
            // tapi sedang tidak aktif di rombel mana pun pada tahun itu
            // ini untuk kasus siswa dipindah/koreksi rombel
            ->orWhereExists(function ($sub) use ($rombel) {
                $sub->selectRaw(1)
                    ->from('siswa_rombel as sr_current')
                    ->whereColumn('sr_current.siswa_id', 'siswa.id')
                    ->where('sr_current.tahun_ajaran_id', $rombel->tahun_ajaran_id);
            });
    })

    ->when($q !== '', function ($query) use ($q) {
        $query->where(function ($sub) use ($q) {
            $sub->where('nama', 'like', "%{$q}%")
                ->orWhere('nis', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%");
        });
    })
    ->orderBy('nama')
    ->limit(100)
    ->get(); 
            }

        $daftarTahunAjaran = TahunAjaran::orderByDesc('status')
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('admin.rombel.anggota', compact(
            'rombel',
            'anggota',
            'kandidat',
            'daftarTahunAjaran',
            'tahunAktif',
            'isRombelTahunAktif'
        ));
    }

    public function storeAnggota(Rombel $rombel, Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        $isRombelTahunAktif = $tahunAktif
            && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id;

        if (!$isRombelTahunAktif) {
            return back()->with('err', 'Rombel tahun ajaran lama hanya dapat dilihat sebagai riwayat. Anggota tidak dapat ditambahkan.');
        }

        $data = $request->validate([
            'siswa_ids'   => ['required', 'array', 'min:1'],
            'siswa_ids.*' => ['exists:siswa,id'],
        ], [
            'siswa_ids.required' => 'Pilih minimal satu siswa.',
            'siswa_ids.min'      => 'Pilih minimal satu siswa.',
        ]);

        $rombel->loadMissing('tahunAjaran');

        if (empty($rombel->tahun_ajaran_id)) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Rombel ini belum memiliki tahun ajaran.',
            ]);
        }

        $siswaIds = collect($data['siswa_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $siswaAktifValid = Siswa::whereIn('id', $siswaIds)
            ->where('status', 'aktif')
            ->pluck('id');

        if ($siswaAktifValid->count() !== $siswaIds->count()) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Beberapa siswa tidak valid atau statusnya bukan aktif.',
            ]);
        }

        $jumlahAktifSaatIni = SiswaRombel::where('rombel_id', $rombel->id)
            ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->where('aktif', 1)
            ->count();

        if (!empty($rombel->kapasitas) && ($jumlahAktifSaatIni + $siswaIds->count()) > (int) $rombel->kapasitas) {
            throw ValidationException::withMessages([
                'siswa_ids' => 'Kapasitas rombel tidak mencukupi untuk menambahkan siswa yang dipilih.',
            ]);
        }

        $bentrok = SiswaRombel::with(['siswa', 'rombel'])
            ->whereIn('siswa_id', $siswaIds)
            ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->where('aktif', 1)
            ->get();

        if ($bentrok->isNotEmpty()) {
            $daftar = $bentrok->map(function ($item) {
                $namaSiswa = $item->siswa->nama ?? "ID {$item->siswa_id}";
                $namaRombel = $item->rombel->nama_rombel ?? "ID {$item->rombel_id}";
                return "{$namaSiswa} ({$namaRombel})";
            })->implode(', ');

            throw ValidationException::withMessages([
                'siswa_ids' => 'Beberapa siswa sudah memiliki rombel aktif pada tahun ajaran ini: ' . $daftar,
            ]);
        }

        DB::transaction(function () use ($rombel, $siswaIds) {
            foreach ($siswaIds as $sid) {
                SiswaRombel::create([
                    'siswa_id'        => $sid,
                    'rombel_id'       => $rombel->id,
                    'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                    'aktif'           => 1,
                    'status_pilihan'  => 'utama',
                ]);
            }
        });

        return back()->with('ok', 'Anggota berhasil ditambahkan ke rombel.');
    }

    public function destroyAnggota(Rombel $rombel, Siswa $siswa)
    {
        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        $isRombelTahunAktif = $tahunAktif
            && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id;

        if (!$isRombelTahunAktif) {
            return back()->with('err', 'Rombel tahun ajaran lama hanya ditampilkan sebagai riwayat. Anggota tidak dapat dikeluarkan.');
        }

        SiswaRombel::where('rombel_id', $rombel->id)
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->where('aktif', 1)
            ->update([
                'aktif'      => 0,
                'updated_at' => now(),
            ]);

        return back()->with('ok', 'Anggota berhasil dikeluarkan dari rombel.');
    }

    public function mapelJson($rombelId)
    {
        $rows = DB::table('rombel_mapel as rm')
            ->join('mata_pelajaran as mp', 'mp.id', '=', 'rm.mata_pelajaran_id')
            ->where('rm.rombel_id', $rombelId)
            ->orderBy('mp.nama_mapel', 'asc')
            ->get([
                'mp.id as id',
                'mp.nama_mapel as nama_mapel',
            ]);

        return response()->json($rows);
    }
}

if (!function_exists('schema_has_column')) {
    function schema_has_column(string $table, string $column): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
        } catch (\Throwable $e) {
            return false;
        }
    }
}