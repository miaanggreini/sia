<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuRombel;
use App\Models\PeriodePemilihanMenu;
use App\Models\Rombel;
use App\Models\Guru;
use App\Models\RuangKelas;
use App\Models\SiswaMenuPeriode;
use App\Models\SiswaRombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PenempatanRombelController extends Controller
{
    private int $kapasitasPerRombelDefault = 36;

    /**
     * Mapel umum yang otomatis masuk ke rombel final kelas XI.
     *
     * Catatan:
     * - Ini hanya dipakai saat sistem membuat rombel final dari hasil penempatan menu rombel.
     * - Rombel kelas X tetap manual sesuai pengaturan admin.
     * - Pencarian dibuat case-insensitive menggunakan LOWER(nama_mapel).
     */
    private array $mapelUmumRombelFinal = [
        'pendidikan agama islam',
        'pendidikan agama kristen',
        'pendidikan agama katolik',
        'pendidikan agama buddha',
        'pendidikan pancasila',
        'bahasa indonesia',
        'sejarah',
        'bahasa inggris',
        'pjok',
        'seni budaya',
        'prakarya',
    ];

    private function taAktif(): ?TahunAjaran
    {
        if (method_exists(TahunAjaran::class, 'aktif')) {
            $ta = TahunAjaran::aktif();

            if ($ta instanceof \Illuminate\Database\Eloquent\Builder) {
                return $ta->first();
            }

            return $ta;
        }

        return TahunAjaran::where('status', 'aktif')->first()
            ?? TahunAjaran::orderByDesc('id')->first();
    }

private function periodeTerpilih(Request $request): ?PeriodePemilihanMenu
{
    $taAktif = $this->taAktif();

    /*
     * Jika user membuka periode tertentu dari tombol/link,
     * tetap validasi agar periode tersebut berasal dari tahun ajaran aktif.
     */
    if ($request->filled('periode_id')) {
        return PeriodePemilihanMenu::where('tingkat', 'XI')
            ->when($taAktif, fn ($q) => $q->where('tahun_ajaran_id', $taAktif->id))
            ->where('id', $request->periode_id)
            ->first();
    }

    /*
     * Default halaman rekap hanya mengambil periode kelas XI
     * pada tahun ajaran aktif.
     */
    return PeriodePemilihanMenu::where('tingkat', 'XI')
        ->when($taAktif, fn ($q) => $q->where('tahun_ajaran_id', $taAktif->id))
        ->orderByDesc('id')
        ->first();
}

    /**
     * Ambil ID mapel umum dari tabel mata_pelajaran.
     * Dibuat case-insensitive agar tetap aman jika penulisan huruf besar/kecil berbeda.
     */
    private function mapelUmumIds(): array
    {
        return DB::table('mata_pelajaran')
            ->whereIn(DB::raw('LOWER(nama_mapel)'), $this->mapelUmumRombelFinal)
            ->pluck('id')
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Gabungan mapel umum + mapel pilihan dari menu rombel.
     */
    private function mapelUntukRombelFinal(MenuRombel $menu): array
    {
        $mapelUmumIds = $this->mapelUmumIds();

        $mapelPilihanIds = $menu->mapel
            ? $menu->mapel->pluck('id')->all()
            : $menu->mapel()->pluck('mata_pelajaran.id')->all();

        return collect($mapelUmumIds)
            ->merge($mapelPilihanIds)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Sinkronisasi mapel untuk rombel final XI.
     * Tidak menggunakan sync(), supaya mapel yang sudah ada tidak terhapus.
     */
    private function sinkronMapelRombelFinal(MenuRombel $menu, Rombel $rombel): void
    {
        if (!method_exists($rombel, 'mataPelajaran')) {
            return;
        }

        $mapelIds = $this->mapelUntukRombelFinal($menu);

        if (!empty($mapelIds)) {
            $rombel->mataPelajaran()->syncWithoutDetaching($mapelIds);
        }
    }

public function index(Request $request)
{
    $ta = $this->taAktif();

    if (!$ta) {
        return view('admin.pemilihan.rekap', [
            'taLabel' => '—',
            'ta' => null,
            'periode' => null,
            'periodeList' => collect(),
            'menu' => collect(),
            'rekap' => [],
            'belum' => collect(),
            'rombels' => collect(),
            'totalPendaftar' => 0,
        ])->with('err', 'Tahun ajaran aktif belum tersedia.');
    }

    /*
     * Halaman ini dikunci ke tahun ajaran aktif.
     * Jadi periode, menu, dan rombel final yang tampil tidak bercampur dengan tahun ajaran lain.
     */
    $periode = $this->periodeTerpilih($request);

    $taLabel = $ta->label
        ?? $ta->nama_tahun
        ?? $ta->tahun
        ?? '—';

    $periodeList = PeriodePemilihanMenu::with('tahunAjaran')
        ->where('tingkat', 'XI')
        ->where('tahun_ajaran_id', $ta->id)
        ->orderByDesc('id')
        ->get();

    /*
     * Jika belum ada periode pada tahun ajaran aktif,
     * data tetap ditampilkan kosong supaya halaman tidak error.
     */
    $menu = MenuRombel::with('mapel')
        ->where('tahun_ajaran_id', $ta->id)
        ->where('tingkat', 'XI')
        ->where('aktif', 1)
        ->orderBy('nama')
        ->get();

    $totalPendaftar = 0;

    if ($periode) {
        $totalPendaftar = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->distinct('siswa_id')
            ->count('siswa_id');
    }

    $rekap = [];

    foreach ($menu as $m) {
        $base = SiswaMenuPeriode::query()
            ->when($periode, fn ($q) => $q->where('periode_id', $periode->id));

        $p1 = (clone $base)->where('pilihan_1_menu_id', $m->id)->count();
        $p2 = (clone $base)->where('pilihan_2_menu_id', $m->id)->count();
        $diterima = (clone $base)->where('menu_diterima_id', $m->id)->count();

        $rekap[$m->id] = [
            'p1'       => $p1,
            'p2'       => $p2,
            'total'    => $p1 + $p2,
            'diterima' => $diterima,
            'sisa'     => max(0, (int) $m->kapasitas_total - $diterima),
        ];
    }

    $belum = collect();

    if ($periode) {
        $belum = SiswaMenuPeriode::with(['siswa', 'pilihan1', 'pilihan2'])
            ->where('periode_id', $periode->id)
            ->whereNull('menu_diterima_id')
            ->orderBy('waktu_pengajuan')
            ->limit(100)
            ->get();
    }

    $rombels = Rombel::query()
        ->select('id', 'nama_rombel', 'menu_rombel_id', 'tingkat', 'tahun_ajaran_id')
        ->whereNotNull('menu_rombel_id')
        ->where('tingkat', 'XI')
        ->where('tahun_ajaran_id', $ta->id)
        ->orderBy('nama_rombel')
        ->get()
        ->groupBy('menu_rombel_id');

    return view('admin.pemilihan.rekap', compact(
        'taLabel',
        'ta',
        'periode',
        'periodeList',
        'menu',
        'rekap',
        'belum',
        'rombels',
        'totalPendaftar'
    ));
}

    public function jalankan(Request $request)
    {
        $periode = $this->periodeTerpilih($request);

        if (!$periode) {
            return back()->with('err', 'Periode pemilihan kelas XI belum tersedia.');
        }

        if (!in_array($periode->status, ['ditutup', 'ditempatkan', 'terkunci'])) {
            return back()->with('err', 'Periode harus ditutup terlebih dahulu sebelum penempatan dijalankan.');
        }

        $ta = TahunAjaran::find($periode->tahun_ajaran_id);

        if (!$ta) {
            return back()->with('err', 'Tahun ajaran pada periode tidak ditemukan.');
        }

        $menu = MenuRombel::where('tahun_ajaran_id', $ta->id)
            ->where('tingkat', 'XI')
            ->where('aktif', 1)
            ->get()
            ->keyBy('id');

        if ($menu->isEmpty()) {
            return back()->with('err', 'Menu rombel kelas XI belum tersedia untuk tahun ajaran periode ini.');
        }

        $pendaftar = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->orderBy('waktu_pengajuan')
            ->get();

        if ($pendaftar->isEmpty()) {
            return back()->with('err', 'Belum ada siswa yang memilih menu pada periode ini.');
        }

        DB::transaction(function () use ($periode, $menu, $pendaftar) {
            $sisa = [];

            foreach ($menu as $m) {
                $sisa[$m->id] = (int) $m->kapasitas_total;
            }

            SiswaMenuPeriode::where('periode_id', $periode->id)
                ->update([
                    'menu_diterima_id' => null,
                    'status'           => 'belum_tertempatkan',
                    'updated_at'       => now(),
                ]);

            $placeRank = function (int $rank) use (&$pendaftar, &$sisa) {
                foreach ($pendaftar as $row) {
                    if ($row->menu_diterima_id) {
                        continue;
                    }

                    $menuId = $row->{"pilihan_{$rank}_menu_id"} ?? null;

                    if (!$menuId || !isset($sisa[$menuId]) || $sisa[$menuId] <= 0) {
                        continue;
                    }

                    $row->menu_diterima_id = $menuId;
                    $row->status = 'diterima';
                    $row->save();

                    $sisa[$menuId]--;
                }
            };

            $placeRank(1);
            $placeRank(2);

            foreach ($pendaftar as $row) {
                if (!$row->menu_diterima_id) {
                    $row->status = 'cadangan';
                    $row->save();
                }
            }

            $periode->update([
                'status'     => 'ditempatkan',
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pemilihan.rekap', ['periode_id' => $periode->id])
            ->with('ok', 'Penempatan otomatis selesai. Silakan review hasil, lalu Commit ke Rombel.');
    }

    public function commit(Request $request)
    {
        $periode = $this->periodeTerpilih($request);

        if (!$periode) {
            return back()->with('err', 'Periode pemilihan tidak ditemukan.');
        }

        if (!in_array($periode->status, ['ditempatkan', 'terkunci'])) {
            return back()->with('err', 'Jalankan penempatan otomatis terlebih dahulu sebelum commit ke rombel.');
        }

        $ta = TahunAjaran::find($periode->tahun_ajaran_id);

        if (!$ta) {
            return back()->with('err', 'Tahun ajaran pada periode tidak ditemukan.');
        }

        $taLabel = $ta->label ?? $ta->nama_tahun ?? '-';

        $accepted = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->whereNotNull('menu_diterima_id')
            ->orderBy('waktu_pengajuan')
            ->get();

        if ($accepted->isEmpty()) {
            return back()->with('err', 'Belum ada hasil penempatan yang bisa di-commit. Jalankan penempatan otomatis terlebih dahulu.');
        }

        $menuIds = $accepted->pluck('menu_diterima_id')->filter()->unique()->values();

        $menus = MenuRombel::with('mapel')
            ->whereIn('id', $menuIds)
            ->where('tahun_ajaran_id', $ta->id)
            ->where('tingkat', 'XI')
            ->get()
            ->keyBy('id');

        if ($menus->count() !== $menuIds->count()) {
            return back()->with('err', 'Ada menu hasil penempatan yang tidak sesuai dengan tahun ajaran periode.');
        }

        DB::transaction(function () use ($accepted, $menus, $ta, $periode) {
            $this->pastikanRombelUntukMenu($accepted, $menus, $ta);

            $rombelByMenu = Rombel::query()
                ->where('tingkat', 'XI')
                ->where('tahun_ajaran_id', $ta->id)
                ->whereIn('menu_rombel_id', $menus->keys())
                ->orderBy('nama_rombel')
                ->get()
                ->groupBy('menu_rombel_id');

            $cursor = [];

            foreach ($accepted as $acc) {
                $menuId = (int) $acc->menu_diterima_id;
                $rombels = ($rombelByMenu->get($menuId) ?? collect())->values();

                if ($rombels->isEmpty()) {
                    continue;
                }

                $target = $this->pilihRombelTarget($rombels, $cursor, $menuId, $ta->id);

                if (!$target) {
                    continue;
                }

                SiswaRombel::where('siswa_id', $acc->siswa_id)
                    ->where('tahun_ajaran_id', $ta->id)
                    ->where('aktif', 1)
                    ->update([
                        'aktif'      => 0,
                        'updated_at' => now(),
                    ]);

                SiswaRombel::updateOrCreate(
                    [
                        'siswa_id'        => $acc->siswa_id,
                        'rombel_id'       => $target->id,
                        'tahun_ajaran_id' => $ta->id,
                    ],
                    [
                        'aktif'          => 1,
                        'status_pilihan' => 'utama',
                        'updated_at'     => now(),
                    ]
                );
            }

            $periode->update([
                'status'     => 'terkunci',
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pemilihan.rekap', ['periode_id' => $periode->id])
            ->with('commit_success', [
                'message' => 'Commit berhasil. Rombel XI dan anggota siswa telah diperbarui untuk TA ' . $taLabel . '.',
                'next_url' => route('admin.pemilihan.rombel-final', ['periode_id' => $periode->id]),
            ]);
    }

    /**
     * Membuat rombel final XI berdasarkan menu yang diterima siswa.
     * Sekaligus mengisi rombel_mapel dengan:
     * - mapel umum
     * - mapel pilihan dari menu rombel
     */
    private function pastikanRombelUntukMenu($accepted, $menus, TahunAjaran $ta): void
    {
        foreach ($menus as $menu) {
            $jumlahDiterima = $accepted
                ->where('menu_diterima_id', $menu->id)
                ->count();

            $rombelAda = Rombel::where('tahun_ajaran_id', $ta->id)
                ->where('tingkat', 'XI')
                ->where('menu_rombel_id', $menu->id)
                ->orderBy('nama_rombel')
                ->get();

            /*
             * Jika rombel final sudah pernah dibuat,
             * tetap pastikan mapel umum + mapel pilihan sudah tersinkron.
             * Jadi kalau sebelumnya belum ada mapel umum, commit ulang akan melengkapinya.
             */
            if ($rombelAda->isNotEmpty()) {
                foreach ($rombelAda as $rombel) {
                    $this->sinkronMapelRombelFinal($menu, $rombel);
                }

                continue;
            }

            $kapasitasPerRombel = $this->kapasitasPerRombelDefault;
            $kelasDibutuhkan = max(1, (int) ceil($jumlahDiterima / $kapasitasPerRombel));

            for ($i = 0; $i < $kelasDibutuhkan; $i++) {
                $suffix = chr(65 + $i);
                $namaRombel = 'XI-' . $this->slugNamaMenu($menu->nama) . '-' . $suffix;

                $payload = [
                    'nama_rombel'     => $namaRombel,
                    'tingkat'         => 'XI',
                    'tahun_ajaran_id' => $ta->id,
                    'menu_rombel_id'  => $menu->id,
                    'kapasitas'       => $kapasitasPerRombel,
                    'aktif'           => 1,
                    'guru_id'         => null,
                    'ruang_kelas_id'  => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ];

                if (Schema::hasColumn('rombel', 'tahun_ajaran')) {
                    $payload['tahun_ajaran'] = $ta->label ?? $ta->nama_tahun ?? null;
                }

                $rombelId = DB::table('rombel')->insertGetId($payload);
                $rombel = Rombel::find($rombelId);

                if ($rombel) {
                    $this->sinkronMapelRombelFinal($menu, $rombel);
                }
            }
        }
    }

    private function pilihRombelTarget($rombels, array &$cursor, int $menuId, int $tahunAjaranId)
    {
        $rombels = collect($rombels)->values();
        $jumlahRombel = $rombels->count();

        if ($jumlahRombel <= 0) {
            return null;
        }

        $start = $cursor[$menuId] ?? 0;

        for ($attempt = 0; $attempt < $jumlahRombel; $attempt++) {
            $index = ($start + $attempt) % $jumlahRombel;
            $target = $rombels->get($index);

            if (!$target) {
                continue;
            }

            if (empty($target->kapasitas)) {
                $cursor[$menuId] = $index + 1;
                return $target;
            }

            $jumlahAktif = SiswaRombel::where('rombel_id', $target->id)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->where('aktif', 1)
                ->count();

            if ($jumlahAktif < (int) $target->kapasitas) {
                $cursor[$menuId] = $index + 1;
                return $target;
            }
        }

        return null;
    }

public function reset(Request $request)
{
    $request->validate([
        'periode_id' => ['required', 'integer'],
    ]);

    $periode = PeriodePemilihanMenu::findOrFail($request->periode_id);

    try {
        DB::transaction(function () use ($periode) {
            $menuIds = MenuRombel::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                ->where('tingkat', 'XI')
                ->pluck('id');

            $rombelIds = Rombel::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                ->where('tingkat', 'XI')
                ->whereIn('menu_rombel_id', $menuIds)
                ->pluck('id');

            $sudahDipakaiJadwal = DB::table('jadwal')
                ->whereIn('rombel_id', $rombelIds)
                ->exists();

            if ($sudahDipakaiJadwal) {
                throw new \Exception('Reset tidak dapat dilakukan karena rombel final sudah digunakan pada jadwal.');
            }

            /*
             * Kembalikan hasil siswa menjadi belum ditempatkan.
             * Pilihan utama dan cadangan tetap aman.
             */
            SiswaMenuPeriode::where('periode_id', $periode->id)
                ->update([
                    'menu_diterima_id' => null,
                    'status'           => 'belum_tertempatkan',
                    'updated_at'       => now(),
                ]);

            /*
             * Hapus anggota dan rombel final hasil commit.
             */
            if ($rombelIds->isNotEmpty()) {
                DB::table('siswa_rombel')
                    ->whereIn('rombel_id', $rombelIds)
                    ->delete();

                DB::table('rombel_mapel')
                    ->whereIn('rombel_id', $rombelIds)
                    ->delete();

                Rombel::whereIn('id', $rombelIds)->delete();
            }

            /*
             * Setelah reset, periode harus kembali ke DITUTUP,
             * supaya admin bisa menjalankan penempatan otomatis ulang.
             */
            $periode->update([
                'status'     => 'ditutup',
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.pemilihan.rekap', ['periode_id' => $periode->id])
            ->with('ok', 'Reset berhasil. Pilihan siswa tetap tersimpan, hasil penempatan dikosongkan, dan periode siap dijalankan ulang.');

    } catch (\Exception $e) {
        return redirect()
            ->route('admin.pemilihan.rekap', ['periode_id' => $periode->id])
            ->with('err', $e->getMessage());
    }
}
    public function geser(Request $request)
    {
        $data = $request->validate([
            'siswa_menu_id' => ['required', 'exists:pilihan_rombel,id'],
            'menu_id'       => ['required', 'exists:menu_rombel,id'],
        ]);

        $row = SiswaMenuPeriode::with('periode')->findOrFail($data['siswa_menu_id']);
        $menu = MenuRombel::findOrFail($data['menu_id']);

        if ((int) $menu->tahun_ajaran_id !== (int) $row->periode->tahun_ajaran_id) {
            return back()->with('err', 'Menu tujuan tidak sesuai dengan tahun ajaran periode.');
        }

        $row->update([
            'menu_diterima_id' => $menu->id,
            'status'           => 'diterima',
            'updated_at'       => now(),
        ]);

        return redirect()
            ->route('admin.pemilihan.rekap', ['periode_id' => $row->periode_id])
            ->with('ok', 'Siswa berhasil dipindahkan ke menu lain. Commit ulang untuk memperbarui rombel.');
    }

    private function slugNamaMenu(string $nama): string
    {
        $nama = trim($nama);
        $nama = preg_replace('/\s+/', ' ', $nama);

        return str_replace(' ', '', ucwords(strtolower($nama)));
    }

    public function rombelFinal(Request $request)
    {
        $periode = $this->periodeTerpilih($request);

        if (!$periode) {
            return redirect()
                ->route('admin.pemilihan.rekap')
                ->with('err', 'Periode pemilihan tidak ditemukan.');
        }

        $ta = TahunAjaran::find($periode->tahun_ajaran_id);

        if (!$ta) {
            return redirect()
                ->route('admin.pemilihan.rekap', ['periode_id' => $periode->id])
                ->with('err', 'Tahun ajaran pada periode tidak ditemukan.');
        }

        $rombels = Rombel::with(['menuRombel', 'waliKelas', 'ruangKelas'])
            ->where('tahun_ajaran_id', $ta->id)
            ->where('tingkat', 'XI')
            ->whereNotNull('menu_rombel_id')
            ->orderBy('nama_rombel')
            ->get();

        $waliKelas = Guru::orderBy('nama')->get();
        $ruangKelas = RuangKelas::orderBy('nama')->get();

        return view('admin.pemilihan.rombel_final', compact(
            'periode',
            'ta',
            'rombels',
            'waliKelas',
            'ruangKelas'
        ));
    }

    public function simpanRombelFinal(Request $request)
    {
        $data = $request->validate([
            'periode_id' => ['required', 'exists:periode_pemilihan,id'],
            'rombels' => ['required', 'array'],
            'rombels.*.id' => ['required', 'exists:rombel,id'],
            'rombels.*.nama_rombel' => ['required', 'string', 'max:100'],
            'rombels.*.guru_id' => ['nullable', 'exists:guru,id'],
            'rombels.*.ruang_kelas_id' => ['nullable', 'exists:ruang_kelas,id'],
        ]);

        $periode = PeriodePemilihanMenu::findOrFail($data['periode_id']);

        DB::transaction(function () use ($data, $periode) {
            foreach ($data['rombels'] as $row) {
                Rombel::where('id', $row['id'])
                    ->where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                    ->update([
                        'nama_rombel' => $row['nama_rombel'],
                        'guru_id' => $row['guru_id'] ?? null,
                        'ruang_kelas_id' => $row['ruang_kelas_id'] ?? null,
                        'updated_at' => now(),
                    ]);
            }
        });

        return redirect()
            ->route('admin.pemilihan.rombel-final', ['periode_id' => $periode->id])
            ->with('ok', 'Pengaturan rombel final berhasil disimpan.');
    }
}