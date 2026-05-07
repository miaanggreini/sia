<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\MenuRombel;
use App\Models\PeriodePemilihanMenu;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuRombelController extends Controller
{
    /**
     * Daftar mapel pilihan yang boleh digunakan pada menu rombel.
     */
    private array $mapelPilihanMenuRombel = [
        'Matematika Tingkat Lanjut',
        'Fisika',
        'Kimia',
        'Sosiologi',
        'Biologi',
        'Geografi',
        'Ekonomi',
        'Informatika dan Koding',
    ];

    private function daftarMapelPilihan()
    {
        return MataPelajaran::query()
            ->whereIn('nama_mapel', $this->mapelPilihanMenuRombel)
            ->orderByRaw("
                FIELD(
                    nama_mapel,
                    'Matematika Tingkat Lanjut',
                    'Fisika',
                    'Kimia',
                    'Biologi',
                    'Sosiologi',
                    'Geografi',
                    'Ekonomi',
                    'Informatika dan Koding'
                )
            ")
            ->orderBy('nama_mapel')
            ->get();
    }

    public function index(Request $request)
    {
        $q = trim((string) $request->q);

        $tahunAjaranList = TahunAjaran::orderByDesc('id')->get();

        $periodeList = PeriodePemilihanMenu::with('tahunAjaran')
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $periodeId = $request->periode_id;
        $periodeAktif = $periodeId
            ? PeriodePemilihanMenu::with('tahunAjaran')->find($periodeId)
            : null;

        /*
         * Jika filter periode dipilih, tahun ajaran dan tingkat menu
         * mengikuti periode tersebut.
         *
         * Jika periode tidak dipilih, gunakan filter tahun_ajaran_id.
         * Jika tahun_ajaran_id kosong, default ke tahun ajaran terbaru.
         */
        if ($periodeAktif) {
            $ta = $periodeAktif->tahunAjaran;
            $tahunAjaranId = $periodeAktif->tahun_ajaran_id;
            $tingkat = $periodeAktif->tingkat ?? 'XI';
        } else {
            $tahunAjaranId = $request->tahun_ajaran_id;

            $ta = $tahunAjaranId
                ? TahunAjaran::find($tahunAjaranId)
                : TahunAjaran::orderByDesc('id')->first();

            $tahunAjaranId = $ta?->id;
            $tingkat = 'XI';
        }

        $taLabel = $ta?->nama_tahun ?? '—';

        $items = MenuRombel::with(['mapel', 'tahunAjaran'])
            ->when($tahunAjaranId, function ($query) use ($tahunAjaranId) {
                $query->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->where('tingkat', $tingkat)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('nama', 'like', "%{$q}%")
                        ->orWhereHas('mapel', function ($mapel) use ($q) {
                            $mapel->where('nama_mapel', 'like', "%{$q}%");
                        });
                });
            })
            ->orderBy('nama')
            ->get();

        return view('admin.menu_rombel.index', compact(
            'items',
            'taLabel',
            'q',
            'periodeList',
            'periodeId',
            'periodeAktif',
            'tahunAjaranList',
            'tahunAjaranId',
            'tingkat'
        ));
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('id')->get();
        $daftarMapel = $this->daftarMapelPilihan();

        return view('admin.menu_rombel.create', compact('tahunAjaran', 'daftarMapel'));
    }

    public function store(Request $request)
    {
        $mapelPilihanIds = $this->daftarMapelPilihan()
            ->pluck('id')
            ->toArray();

        $data = $request->validate([
            'tahun_ajaran_id' => ['required', 'exists:tahun_ajaran,id'],
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('menu_rombel', 'nama')
                    ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
                    ->where('tingkat', 'XI'),
            ],
            'kapasitas_total' => ['required', 'integer', 'min:1'],
            'daftar_mapel' => ['required', 'array', 'min:1'],
            'daftar_mapel.*' => [
                'integer',
                Rule::in($mapelPilihanIds),
            ],
        ], [
            'nama.unique' => 'Nama menu rombel sudah digunakan pada tahun ajaran ini.',
            'daftar_mapel.required' => 'Pilih minimal satu mata pelajaran untuk menu rombel.',
            'daftar_mapel.min' => 'Pilih minimal satu mata pelajaran untuk menu rombel.',
            'daftar_mapel.*.in' => 'Mata pelajaran yang dipilih tidak termasuk mapel pilihan menu rombel.',
        ]);

        DB::transaction(function () use ($data) {
            $menu = MenuRombel::create([
                'nama' => $data['nama'],
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                'tingkat' => 'XI',
                'kapasitas_total' => $data['kapasitas_total'],
                'aktif' => true,
            ]);

            $menu->mapel()->sync($data['daftar_mapel']);
        });

        return redirect()
            ->route('admin.menu-rombel.index', [
                'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            ])
            ->with('ok', 'Menu rombel berhasil dibuat.');
    }

    public function edit(MenuRombel $menuRombel)
    {
        $menuRombel->load('mapel');

        $daftarMapel = $this->daftarMapelPilihan();

        return view('admin.menu_rombel.edit', [
            'item' => $menuRombel,
            'daftarMapel' => $daftarMapel,
        ]);
    }

    public function update(Request $request, MenuRombel $menuRombel)
    {
        $mapelPilihanIds = $this->daftarMapelPilihan()
            ->pluck('id')
            ->toArray();

        $data = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('menu_rombel', 'nama')
                    ->ignore($menuRombel->id)
                    ->where('tahun_ajaran_id', $menuRombel->tahun_ajaran_id)
                    ->where('tingkat', $menuRombel->tingkat),
            ],
            'kapasitas_total' => ['required', 'integer', 'min:1'],
            'daftar_mapel' => ['required', 'array', 'min:1'],
            'daftar_mapel.*' => [
                'integer',
                Rule::in($mapelPilihanIds),
            ],
        ], [
            'nama.unique' => 'Nama menu rombel sudah digunakan pada tahun ajaran ini.',
            'daftar_mapel.required' => 'Pilih minimal satu mata pelajaran untuk menu rombel.',
            'daftar_mapel.min' => 'Pilih minimal satu mata pelajaran untuk menu rombel.',
            'daftar_mapel.*.in' => 'Mata pelajaran yang dipilih tidak termasuk mapel pilihan menu rombel.',
        ]);

        DB::transaction(function () use ($menuRombel, $data) {
            $menuRombel->update([
                'nama' => $data['nama'],
                'kapasitas_total' => $data['kapasitas_total'],
            ]);

            $menuRombel->mapel()->sync($data['daftar_mapel']);
        });

        return redirect()
            ->route('admin.menu-rombel.index', [
                'tahun_ajaran_id' => $menuRombel->tahun_ajaran_id,
            ])
            ->with('ok', 'Menu rombel berhasil diperbarui.');
    }

    public function destroy(MenuRombel $menuRombel)
    {
        $tahunAjaranId = $menuRombel->tahun_ajaran_id;

        $menuRombel->delete();

        return redirect()
            ->route('admin.menu-rombel.index', [
                'tahun_ajaran_id' => $tahunAjaranId,
            ])
            ->with('ok', 'Menu rombel berhasil dihapus.');
    }
}