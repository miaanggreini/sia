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

        /*
         * Periode aktif untuk pemilihan menuju kelas XI.
         * Tahun ajaran pada periode ini adalah tahun ajaran tujuan.
         */
        $periode = PeriodePemilihanMenu::aktifDibuka()
            ->where('tingkat', 'XI')
            ->orderByDesc('id')
            ->first();

        /*
         * Riwayat semua pilihan siswa.
         * Ini tetap ditampilkan walaupun siswa belum boleh memilih,
         * periode sudah tutup, atau siswa sudah ditempatkan.
         */
        $riwayatPilihan = SiswaMenuPeriode::with([
                'pilihan1.mapel',
                'pilihan2.mapel',
                'menuDiterima.mapel',
                'periode.tahunAjaran',
            ])
            ->where('siswa_id', $siswa->id)
            ->orderByDesc('waktu_pengajuan')
            ->orderByDesc('id')
            ->get();

        /*
         * Preferensi terakhir untuk ringkasan hasil.
         */
        $preferensiTerakhir = $riwayatPilihan->first();

        /*
         * Siswa kelas X yang sudah diproses kenaikan akan punya record:
         * siswa_rombel kelas X lama -> aktif = 0.
         * Ini menjadi tanda siswa layak mengikuti pemilihan rombel XI.
         */
        $rombelLayakPilih = $this->rombelKelasXSudahDiprosesNaik(
            $siswa->id,
            $periode?->tahun_ajaran_id
        );

        /*
         * Jika siswa sudah punya rombel aktif pada tahun ajaran tujuan,
         * berarti dia sudah ditempatkan ke rombel final.
         */
        $sudahPunyaRombelTujuan = $periode
            ? $this->punyaRombelAktifPadaTahunAjaran($siswa->id, (int) $periode->tahun_ajaran_id)
            : false;

        $calonPemilih = (bool) $rombelLayakPilih && !$sudahPunyaRombelTujuan;
        $bolehMemilih = (bool) $periode && $calonPemilih;

        /*
         * Kompatibilitas dengan view lama.
         * Sekarang maknanya: siswa sedang boleh memilih.
         */
        $isKelasX = $bolehMemilih;

        $alasanTidakBolehMemilih = null;

        if (!$periode) {
            $alasanTidakBolehMemilih = 'Periode pemilihan rombel belum dibuka.';
        } elseif (!$rombelLayakPilih) {
            $alasanTidakBolehMemilih = 'Kamu belum dapat mengikuti pemilihan rombel karena belum diproses layak naik dari kelas X.';
        } elseif ($sudahPunyaRombelTujuan) {
            $alasanTidakBolehMemilih = 'Kamu sudah memiliki rombel pada tahun ajaran tujuan, sehingga tidak perlu memilih menu rombel lagi.';
        }

        $daftarMenu = collect();
        $rekomendasiMenu = [];

        if ($bolehMemilih) {
            $daftarMenu = MenuRombel::with('mapel')
                ->where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                ->where('tingkat', 'XI')
                ->where('aktif', 1)
                ->orderBy('nama')
                ->get();

            foreach ($daftarMenu as $menu) {
                $rekomendasiMenu[$menu->id] = $this->rekomendasiProdiUntukMenu($menu);
            }
        }

        /*
         * Preferensi pada periode aktif.
         * Kalau tidak ada, fallback ke preferensi terakhir untuk ringkasan riwayat.
         */
        $preferensiAktif = null;

        if ($periode) {
            $preferensiAktif = SiswaMenuPeriode::with([
                    'pilihan1.mapel',
                    'pilihan2.mapel',
                    'menuDiterima.mapel',
                    'periode.tahunAjaran',
                ])
                ->where('siswa_id', $siswa->id)
                ->where('periode_id', $periode->id)
                ->first();
        }

        $preferensi = $preferensiAktif ?? $preferensiTerakhir;

        $rekomendasiHasil = [
            'utama' => [],
            'cadangan' => [],
            'diterima' => [],
        ];

        if ($preferensi?->pilihan1) {
            $rekomendasiHasil['utama'] = $this->rekomendasiProdiUntukMenu($preferensi->pilihan1);
        }

        if ($preferensi?->pilihan2) {
            $rekomendasiHasil['cadangan'] = $this->rekomendasiProdiUntukMenu($preferensi->pilihan2);
        }

        if ($preferensi?->menuDiterima) {
            $rekomendasiHasil['diterima'] = $this->rekomendasiProdiUntukMenu($preferensi->menuDiterima);
        }

        return view('siswa.preferensi_menu.index', [
            'siswa' => $siswa,
            'periode' => $periode,
            'preferensi' => $preferensi,
            'preferensiAktif' => $preferensiAktif,
            'preferensiTerakhir' => $preferensiTerakhir,
            'riwayatPilihan' => $riwayatPilihan,
            'daftarMenu' => $daftarMenu,
            'rekomendasiMenu' => $rekomendasiMenu,
            'rekomendasiHasil' => $rekomendasiHasil,
            'rombelLayakPilih' => $rombelLayakPilih,
            'sudahPunyaRombelTujuan' => $sudahPunyaRombelTujuan,
            'calonPemilih' => $calonPemilih,
            'bolehMemilih' => $bolehMemilih,
            'isKelasX' => $isKelasX,
            'alasanTidakBolehMemilih' => $alasanTidakBolehMemilih,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $siswa = $user->siswa;

        abort_unless($siswa, 403, 'Akun ini belum terhubung dengan data siswa.');

        $periode = PeriodePemilihanMenu::aktifDibuka()
            ->where('tingkat', 'XI')
            ->orderByDesc('id')
            ->first();

        if (!$periode) {
            return back()->with('err', 'Periode pemilihan rombel belum dibuka.');
        }

        $rombelLayakPilih = $this->rombelKelasXSudahDiprosesNaik(
            $siswa->id,
            $periode->tahun_ajaran_id
        );

        if (!$rombelLayakPilih) {
            return back()->with('err', 'Kamu belum dapat mengikuti pemilihan rombel karena belum diproses layak naik dari kelas X.');
        }

        $sudahPunyaRombelTujuan = $this->punyaRombelAktifPadaTahunAjaran(
            $siswa->id,
            (int) $periode->tahun_ajaran_id
        );

        if ($sudahPunyaRombelTujuan) {
            return back()->with('err', 'Kamu sudah memiliki rombel pada tahun ajaran tujuan.');
        }

        $menuIdsValid = MenuRombel::query()
            ->where('tahun_ajaran_id', $periode->tahun_ajaran_id)
            ->where('tingkat', 'XI')
            ->where('aktif', 1)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $data = $request->validate([
            'pilihan_1_menu_id' => [
                'required',
                'integer',
                Rule::in($menuIdsValid),
            ],
            'pilihan_2_menu_id' => [
                'required',
                'integer',
                Rule::in($menuIdsValid),
                'different:pilihan_1_menu_id',
            ],
        ], [
            'pilihan_1_menu_id.required' => 'Pilihan utama wajib dipilih.',
            'pilihan_2_menu_id.required' => 'Pilihan cadangan wajib dipilih.',
            'pilihan_2_menu_id.different' => 'Pilihan cadangan tidak boleh sama dengan pilihan utama.',
        ]);

        SiswaMenuPeriode::updateOrCreate(
            [
                'periode_id' => $periode->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'pilihan_1_menu_id' => $data['pilihan_1_menu_id'],
                'pilihan_2_menu_id' => $data['pilihan_2_menu_id'],
                'pilihan_3_menu_id' => null,
                'menu_diterima_id' => null,
                'status' => 'belum_tertempatkan',
                'waktu_pengajuan' => now(),
            ]
        );

        return redirect()
->route('siswa.menu.index')
    ->with('ok', 'Pilihan menu rombel berhasil disimpan.');
    }

    private function rombelKelasXSudahDiprosesNaik(int $siswaId, ?int $tahunAjaranTujuanId = null)
    {
        return DB::table('siswa_rombel as sr')
            ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
            ->leftJoin('tahun_ajaran as ta', 'ta.id', '=', 'sr.tahun_ajaran_id')
            ->where('sr.siswa_id', $siswaId)
            ->where('sr.aktif', 0)
            ->whereRaw('UPPER(r.tingkat) = ?', ['X'])
            ->when($tahunAjaranTujuanId, function ($query) use ($tahunAjaranTujuanId) {
                $query->where('sr.tahun_ajaran_id', '<>', $tahunAjaranTujuanId);
            })
            ->select([
                'r.id as rombel_id',
                'r.nama_rombel',
                'r.tingkat',
                'sr.tahun_ajaran_id',
                'ta.nama_tahun as nama_tahun_asal',
                'ta.semester as semester_asal',
                'sr.updated_at as diproses_pada',
            ])
            ->orderByDesc('sr.updated_at')
            ->orderByDesc('sr.id')
            ->first();
    }

    private function punyaRombelAktifPadaTahunAjaran(int $siswaId, int $tahunAjaranId): bool
    {
        return DB::table('siswa_rombel')
            ->where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('aktif', 1)
            ->exists();
    }

    private function rekomendasiProdiUntukMenu(MenuRombel $menu): array
    {
        $mapelNames = $menu->relationLoaded('mapel')
            ? $menu->mapel->pluck('nama_mapel')->filter()->values()
            : collect();

        $text = Str::lower($mapelNames->implode(' '));

        $result = [];

        $add = function (string $prodi, string $alasan) use (&$result) {
            $result[] = [
                'prodi' => $prodi,
                'alasan' => $alasan,
            ];
        };

        if (Str::contains($text, ['matematika', 'fisika', 'kimia'])) {
            $add('Teknik', 'Cocok untuk pilihan yang memuat Matematika, Fisika, atau Kimia.');
            $add('Informatika / Sistem Informasi', 'Mendukung bidang komputasi dan analisis logis.');
        }

        if (Str::contains($text, ['biologi', 'kimia'])) {
            $add('Kedokteran / Kesehatan', 'Cocok untuk peminatan yang memuat Biologi atau Kimia.');
            $add('Farmasi', 'Relevan dengan Biologi dan Kimia.');
        }

        if (Str::contains($text, ['ekonomi', 'matematika'])) {
            $add('Ekonomi / Manajemen / Akuntansi', 'Relevan dengan Ekonomi dan kemampuan numerik.');
        }

        if (Str::contains($text, ['geografi', 'sosiologi', 'sejarah'])) {
            $add('Ilmu Sosial / Administrasi / Hukum', 'Relevan dengan rumpun sosial dan humaniora.');
        }

        if (Str::contains($text, ['bahasa indonesia', 'bahasa inggris', 'bahasa jawa'])) {
            $add('Ilmu Komunikasi / Sastra / Pendidikan Bahasa', 'Relevan dengan bidang bahasa dan komunikasi.');
        }

        if (Str::contains($text, ['seni', 'prakarya'])) {
            $add('Desain / Seni / Industri Kreatif', 'Relevan dengan bidang kreatif.');
        }

        if (empty($result)) {
            $add('Pendidikan / Bidang Umum', 'Rekomendasi umum berdasarkan kombinasi mata pelajaran.');
        }

        return collect($result)
            ->unique('prodi')
            ->values()
            ->all();
    }
}