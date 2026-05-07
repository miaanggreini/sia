<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\PeriodePemilihanMenu;
use App\Models\MenuRombel;
use App\Models\SiswaMenuPeriode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RekapRombelController extends Controller
{
    // =======================
    // DAFTAR PERIODE
    // =======================
    public function index()
    {
        $periodeList = PeriodePemilihanMenu::with(['tahunAjaran'])
            ->orderByDesc('tanggal_mulai')
            ->get();

        return view('kepsek.rekap_rombel.index', compact('periodeList'));
    }

    // =======================
    // DETAIL 1 PERIODE
    // =======================
    public function show($periodeId)
    {
        $periode = PeriodePemilihanMenu::with(['tahunAjaran'])->findOrFail($periodeId);

        /*
         * Ambil semua menu yang terlibat pada periode ini:
         * - pilihan utama/cadangan siswa
         * - menu yang diterima
         */
        $menuIdsPeriode = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->get([
                'pilihan_1_menu_id',
                'pilihan_2_menu_id',
                'pilihan_3_menu_id',
                'menu_diterima_id',
            ])
            ->flatMap(function ($row) {
                return [
                    $row->pilihan_1_menu_id,
                    $row->pilihan_2_menu_id,
                    $row->pilihan_3_menu_id,
                    $row->menu_diterima_id,
                ];
            })
            ->filter()
            ->unique()
            ->values();

        /*
         * Total diterima dihitung dari menu_diterima_id.
         * Jangan memakai status = diterima, karena pada beberapa data
         * status bisa masih belum_tertempatkan walaupun menu_diterima_id sudah terisi.
         */
        $menus = MenuRombel::with(['mapel'])
            ->withCount([
                'siswaMenuPeriode as total_diterima' => function ($q) use ($periode) {
                    $q->where('periode_id', $periode->id)
                      ->whereColumn('menu_diterima_id', 'menu_rombel.id');
                },
            ])
            ->whereIn('id', $menuIdsPeriode)
            ->orderBy('nama')
            ->get();

        foreach ($menus as $menu) {
            $kapasitas = (int) ($menu->kapasitas_total ?? 0);
            $diterima = (int) ($menu->total_diterima ?? 0);

            $menu->persentase = $kapasitas > 0
                ? round(($diterima / $kapasitas) * 100)
                : 0;
        }

        return view('kepsek.rekap_rombel.show', compact('periode', 'menus'));
    }

    // =======================
    // DETAIL 1 MENU DALAM 1 PERIODE
    // =======================
    public function detailMenu(PeriodePemilihanMenu $periode, MenuRombel $menu)
    {
        /*
         * Siswa yang diterima di menu ini pada periode tersebut.
         * Patokannya menu_diterima_id, bukan status.
         */
        $pendaftar = SiswaMenuPeriode::with(['siswa'])
            ->where('periode_id', $periode->id)
            ->where('menu_diterima_id', $menu->id)
            ->orderBy('waktu_pengajuan')
            ->get();

        /*
         * Ambil rombel siswa pada tahun ajaran periode tersebut.
         * Kalau tidak ada, fallback ke rombel aktif.
         */
        $siswaIds = $pendaftar->pluck('siswa_id')->unique()->values()->all();

        $rombels = collect();

        if (!empty($siswaIds)) {
            $rombelsQuery = DB::table('siswa_rombel as sr')
                ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
                ->whereIn('sr.siswa_id', $siswaIds)
                ->select(
                    'sr.siswa_id',
                    'sr.aktif',
                    'sr.tahun_ajaran_id',
                    'r.nama_rombel'
                );

            if (!empty($periode->tahun_ajaran_id)) {
                $rombelsQuery->where('sr.tahun_ajaran_id', $periode->tahun_ajaran_id);
            }

            $rombels = $rombelsQuery
                ->orderByDesc('sr.aktif')
                ->get()
                ->groupBy('siswa_id');

            /*
             * Jika pada tahun ajaran periode tidak ditemukan,
             * ambil rombel aktif sebagai fallback.
             */
            $siswaTanpaRombel = collect($siswaIds)
                ->filter(function ($id) use ($rombels) {
                    return !$rombels->has($id) || $rombels->get($id)->isEmpty();
                })
                ->values()
                ->all();

            if (!empty($siswaTanpaRombel)) {
                $fallbackRombels = DB::table('siswa_rombel as sr')
                    ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
                    ->whereIn('sr.siswa_id', $siswaTanpaRombel)
                    ->where('sr.aktif', 1)
                    ->select(
                        'sr.siswa_id',
                        'sr.aktif',
                        'sr.tahun_ajaran_id',
                        'r.nama_rombel'
                    )
                    ->get()
                    ->groupBy('siswa_id');

                $rombels = $rombels->merge($fallbackRombels);
            }
        }

        /*
         * Sisipkan nama rombel dan status tampilan.
         * Status tampilan dibuat "Diterima" karena data sudah difilter
         * berdasarkan menu_diterima_id.
         */
        $pendaftar->transform(function ($row) use ($rombels) {
            $rombelSiswa = $rombels->get($row->siswa_id);

            $row->nama_rombel = $rombelSiswa && $rombelSiswa->count()
                ? ($rombelSiswa->first()->nama_rombel ?? '-')
                : '-';

            $row->status_tampilan = 'Diterima';

            return $row;
        });

        $tanggal_mulai = $this->formatTanggal($periode->tanggal_mulai ?? null);
        $tanggal_selesai = $this->formatTanggal($periode->tanggal_selesai ?? null);

        return view('kepsek.rekap_rombel.detail_menu', [
            'periode'         => $periode,
            'menu'            => $menu,
            'pendaftar'       => $pendaftar,
            'tanggal_mulai'   => $tanggal_mulai,
            'tanggal_selesai' => $tanggal_selesai,
        ]);
    }

    private function formatTanggal($value): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            return Carbon::parse($value)
                ->locale('id')
                ->translatedFormat('d M Y');
        } catch (\Exception $e) {
            return '-';
        }
    }
}