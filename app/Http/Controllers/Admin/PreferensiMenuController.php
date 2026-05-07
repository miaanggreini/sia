<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\MenuRombel;
use App\Models\PeriodePemilihanMenu;
use App\Models\SiswaMenuPeriode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PreferensiMenuController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $siswa = $user->siswa;

        abort_unless($siswa, 403, 'Akun ini belum terhubung dengan data siswa.');

        // Hanya siswa kelas X aktif yang boleh memilih menu rombel XI
        $rombelAktif = $this->rombelAktifSiswa($siswa->id);

        abort_unless(
            $rombelAktif && strtoupper($rombelAktif->tingkat) === 'X',
            403,
            'Pemilihan menu rombel hanya dapat dilakukan oleh siswa kelas X.'
        );

        $periode = PeriodePemilihanMenu::aktifDibuka()
            ->where('tingkat', 'XI')
            ->orderByDesc('id')
            ->first();

        $daftarMenu = collect();
        $preferensi = null;
        $rekomendasiMenu = [];

        if ($periode) {
            $daftarMenu = MenuRombel::with('mapel')
                ->where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                ->where('tingkat', 'XI')
                ->where('aktif', 1)
                ->orderBy('nama')
                ->get();

            $preferensi = SiswaMenuPeriode::where('periode_id', $periode->id)
                ->where('siswa_id', $siswa->id)
                ->first();

            foreach ($daftarMenu as $menu) {
                $rekomendasiMenu[$menu->id] = $this->rekomendasiProdiUntukMenu($menu);
            }
        }

        return view('siswa.preferensi_menu.index', compact(
            'periode',
            'daftarMenu',
            'preferensi',
            'rekomendasiMenu'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $siswa = $user->siswa;

        abort_unless($siswa, 403, 'Akun ini belum terhubung dengan data siswa.');

        $rombelAktif = $this->rombelAktifSiswa($siswa->id);

        abort_unless(
            $rombelAktif && strtoupper($rombelAktif->tingkat) === 'X',
            403,
            'Pemilihan menu rombel hanya dapat dilakukan oleh siswa kelas X.'
        );

        $periode = PeriodePemilihanMenu::aktifDibuka()
            ->where('tingkat', 'XI')
            ->orderByDesc('id')
            ->firstOrFail();

        $menuIds = MenuRombel::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
            ->where('tingkat', 'XI')
            ->where('aktif', 1)
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'pilihan_1_menu_id' => ['required', Rule::in($menuIds)],
            'pilihan_2_menu_id' => ['required', Rule::in($menuIds), 'different:pilihan_1_menu_id'],
        ], [
            'pilihan_1_menu_id.required' => 'Pilihan utama wajib dipilih.',
            'pilihan_2_menu_id.required' => 'Pilihan cadangan wajib dipilih.',
            'pilihan_1_menu_id.in' => 'Pilihan utama tidak valid untuk periode ini.',
            'pilihan_2_menu_id.in' => 'Pilihan cadangan tidak valid untuk periode ini.',
            'pilihan_2_menu_id.different' => 'Pilihan cadangan tidak boleh sama dengan pilihan utama.',
        ]);

        SiswaMenuPeriode::updateOrCreate(
            [
                'periode_id' => $periode->id,
                'siswa_id'   => $siswa->id,
            ],
            [
                'pilihan_1_menu_id' => $data['pilihan_1_menu_id'],
                'pilihan_2_menu_id' => $data['pilihan_2_menu_id'],
                'pilihan_3_menu_id' => null,
                'menu_diterima_id'  => null,
                'status'            => 'belum_tertempatkan',
                'waktu_pengajuan'   => now(),
            ]
        );

        return back()->with('ok');
    }

    private function rombelAktifSiswa(int $siswaId)
    {
        return DB::table('siswa_rombel as sr')
            ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
            ->where('sr.siswa_id', $siswaId)
            ->where('sr.aktif', 1)
            ->select('r.*', 'sr.tahun_ajaran_id')
            ->first();
    }

    private function rekomendasiProdiUntukMenu(MenuRombel $menu): array
    {
        $aturan = config('prodi_mapel', []);

        $mapelMenu = $menu->mapel
            ->pluck('nama_mapel')
            ->filter()
            ->map(fn ($nama) => $this->normalisasiNama($nama))
            ->values()
            ->all();

        $hasil = [];

        foreach ($aturan as $namaProdi => $mapelPendukung) {
            $daftarMapel = $this->ambilDaftarMapelDariConfig($mapelPendukung);

            if (empty($daftarMapel)) {
                continue;
            }

            $mapelNormal = collect($daftarMapel)
                ->filter()
                ->map(fn ($nama) => $this->normalisasiNama($nama))
                ->values()
                ->all();

            $cocok = collect($mapelNormal)
                ->filter(fn ($mapel) => in_array($mapel, $mapelMenu, true))
                ->values();

            if ($cocok->isEmpty()) {
                continue;
            }

            $hasil[] = [
                'prodi' => $namaProdi,
                'jumlah_cocok' => $cocok->count(),
                'status' => $cocok->count() >= 2 ? 'Cocok' : 'Relevan',
                'mapel_cocok' => $cocok->map(fn ($m) => Str::title($m))->all(),
            ];
        }

        return collect($hasil)
            ->sortByDesc('jumlah_cocok')
            ->values()
            ->all();
    }

    private function ambilDaftarMapelDariConfig($value): array
    {
        if (is_array($value)) {
            if (isset($value['mapel']) && is_array($value['mapel'])) {
                return $value['mapel'];
            }

            if (isset($value['mata_pelajaran']) && is_array($value['mata_pelajaran'])) {
                return $value['mata_pelajaran'];
            }

            return collect($value)
                ->flatten()
                ->filter(fn ($item) => is_string($item))
                ->values()
                ->all();
        }

        return is_string($value) ? [$value] : [];
    }

    private function normalisasiNama(string $nama): string
    {
        return Str::of($nama)
            ->lower()
            ->replace(['pendidikan ', 'tingkat lanjut'], ['', 'tingkat lanjut'])
            ->squish()
            ->toString();
    }
}