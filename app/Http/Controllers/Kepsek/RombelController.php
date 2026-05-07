<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Rombel;
use App\Models\Siswa;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RombelController extends Controller
{
public function index(Request $request)
{
    $tahunAjaranList = TahunAjaran::orderByDesc('id')->get();
    $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

    $selectedTahun = $request->filled('tahun_ajaran_id')
        ? (int) $request->tahun_ajaran_id
        : optional($tahunAjaranAktif)->id;

    $selectedTingkat = $request->tingkat;
    $q = trim((string) $request->q);

    $items = Rombel::query()
        ->with([
            'waliKelas',
            'tahunAjaran',
            'mataPelajaran',
        ])
        ->when($selectedTahun, function ($query) use ($selectedTahun) {
            $query->where('tahun_ajaran_id', $selectedTahun);
        })
        ->when($selectedTingkat, function ($query) use ($selectedTingkat) {
            $query->where('tingkat', $selectedTingkat);
        })
        ->when($q !== '', function ($query) use ($q) {
            $query->where(function ($sub) use ($q) {
                $sub->where('nama_rombel', 'like', "%{$q}%")
                    ->orWhereHas('waliKelas', function ($wali) use ($q) {
                        $wali->where('nama', 'like', "%{$q}%");
                    });
            });
        })
        ->orderByRaw("FIELD(tingkat, 'X', 'XI', 'XII')")
        ->orderBy('nama_rombel')
        ->paginate(10)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | Hitung jumlah siswa sesuai konteks tahun ajaran
    |--------------------------------------------------------------------------
    | - Rombel tahun ajaran aktif:
    |   hitung hanya siswa yang masih aktif pada rombel tersebut.
    |
    | - Rombel tahun ajaran lama:
    |   hitung semua siswa yang pernah tercatat pada rombel tersebut
    |   di tahun ajaran itu, meskipun aktif = 0.
    */
    $items->getCollection()->transform(function ($rombel) use ($tahunAjaranAktif) {
        $isRombelTahunAktif = $tahunAjaranAktif
            && (int) $rombel->tahun_ajaran_id === (int) $tahunAjaranAktif->id;

        $jumlahSiswaQuery = DB::table('siswa_rombel')
            ->where('rombel_id', $rombel->id)
            ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id);

        if ($isRombelTahunAktif) {
            $jumlahSiswaQuery->where('aktif', 1);
        }

        $rombel->jumlah_siswa = $jumlahSiswaQuery->count();
        $rombel->is_tahun_aktif = $isRombelTahunAktif;

        return $rombel;
    });

    return view('kepsek.rombel.index', [
        'items' => $items,
        'rombels' => $items,
        'tahunAjaranList' => $tahunAjaranList,
        'tahunAjaranAktif' => $tahunAjaranAktif,
        'selectedTahun' => $selectedTahun,
        'selectedTingkat' => $selectedTingkat,
        'q' => $q,
    ]);
}

public function anggota(Request $request, Rombel $rombel)
{
    $q = trim((string) $request->q);

    $rombel->loadMissing([
        'waliKelas',
        'guru',
        'mataPelajaran',
        'tahunAjaran',
    ]);

    $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

    $isRombelTahunAktif = $tahunAktif
        && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id;

    /*
    |--------------------------------------------------------------------------
    | Anggota rombel
    |--------------------------------------------------------------------------
    | Tahun aktif  : hanya anggota aktif.
    | Tahun histori: semua siswa yang pernah tercatat di rombel itu pada TA tsb.
    */
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

    /*
    |--------------------------------------------------------------------------
    | Kandidat anggota baru
    |--------------------------------------------------------------------------
    | Hanya disediakan untuk rombel tahun ajaran aktif.
    | Rombel histori tidak boleh tambah/keluarkan anggota.
    */
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

            ->where(function ($query) use ($rombel) {
                $query
                    // siswa baru/pindahan: belum pernah punya rombel sama sekali
                    ->whereNotExists(function ($sub) {
                        $sub->selectRaw(1)
                            ->from('siswa_rombel as sr_all')
                            ->whereColumn('sr_all.siswa_id', 'siswa.id');
                    })

                    // siswa pindah kelas/koreksi: punya record di TA ini,
                    // tapi sudah tidak aktif di rombel mana pun pada TA tersebut
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
        ->orderByDesc('id')
        ->get();

    return view('kepsek.rombel.anggota', compact(
        'rombel',
        'anggota',
        'kandidat',
        'daftarTahunAjaran',
        'tahunAktif',
        'isRombelTahunAktif'
    ));
}
    public function edit(Rombel $rombel)
    {
        $rombel->load('mataPelajaran');

        $waliKelas = Guru::orderBy('nama')->get();
        $mapel = MataPelajaran::orderBy('nama_mapel')->get();
        $tingkatOptions = ['X', 'XI', 'XII'];

        return view('kepsek.rombel.edit', compact(
            'rombel',
            'waliKelas',
            'mapel',
            'tingkatOptions'
        ));
    }

    public function update(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'tingkat' => ['required', Rule::in(['X', 'XI', 'XII'])],
            'guru_id' => ['nullable', 'exists:guru,id'],
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama_rombel' => [
                'required',
                'string',
                'max:100',
                Rule::unique('rombel', 'nama_rombel')->ignore($rombel->id),
            ],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
            'mapel_ids' => ['nullable', 'array'],
            'mapel_ids.*' => ['integer', 'exists:mata_pelajaran,id'],
        ]);

        $rombel->update([
            'tingkat' => $data['tingkat'],
            'guru_id' => $data['guru_id'] ?? null,
            'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            'nama_rombel' => $data['nama_rombel'],
            'kapasitas' => $data['kapasitas'] ?? null,
            'aktif' => $request->boolean('aktif'),
        ]);

        $rombel->mataPelajaran()->sync($data['mapel_ids'] ?? []);

        return redirect()
            ->route('kepala_sekolah.data.rombel.index', [
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                'tingkat' => $data['tingkat'],
            ])
            ->with('ok', 'Rombel berhasil diperbarui.');
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

public function destroyAnggota(Rombel $rombel, $siswaId)
{
    $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

    $isRombelTahunAktif = $tahunAktif
        && (int) $rombel->tahun_ajaran_id === (int) $tahunAktif->id;

    if (!$isRombelTahunAktif) {
        return back()->with('err', 'Rombel tahun ajaran lama hanya ditampilkan sebagai riwayat. Anggota tidak dapat dikeluarkan.');
    }

    SiswaRombel::where('rombel_id', $rombel->id)
        ->where('siswa_id', $siswaId)
        ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
        ->where('aktif', 1)
        ->update([
            'aktif'      => 0,
            'updated_at' => now(),
        ]);

    return back()->with('ok', 'Anggota berhasil dikeluarkan dari rombel.');
}
}