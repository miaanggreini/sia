<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuRombel;
use App\Models\PeriodePemilihanMenu;
use App\Models\SiswaMenuPeriode;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PeriodePemilihanMenuController extends Controller
{
    public function index()
    {
        $items = PeriodePemilihanMenu::with('tahunAjaran')
            ->orderByDesc('id')
            ->get();

        return view('admin.periode_menu.index', compact('items'));
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('id')->get();

        return view('admin.periode_menu.create', compact('tahunAjaran'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => [
                'required',
                'exists:tahun_ajaran,id',
                Rule::unique('periode_pemilihan', 'tahun_ajaran_id')
                    ->where('tingkat', 'XI'),
            ],
            'nama_periode' => ['required', 'string', 'max:30'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
        ], [
            'tahun_ajaran_id.unique' => 'Periode pemilihan rombel untuk tahun ajaran ini sudah ada.',
            'nama_periode.max' => 'Nama periode maksimal 30 karakter sesuai struktur database.',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai.',
        ]);

        PeriodePemilihanMenu::create([
            'nama_periode' => $data['nama_periode'],
            'tahun_ajaran_id' => $data['tahun_ajaran_id'],
            'tingkat' => 'XI',
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status' => 'draft',
        ]);

        return redirect()
            ->route('admin.periode.index')
            ->with('ok', 'Periode pemilihan berhasil dibuat.');
    }

    public function buka(PeriodePemilihanMenu $periode)
    {
        if (!in_array($periode->status, ['draft', 'ditutup'], true)) {
            return back()->with('err', 'Periode hanya dapat diaktifkan dari status Draft atau Ditutup.');
        }

        $menuCount = MenuRombel::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
            ->where('tingkat', $periode->tingkat ?? 'XI')
            ->where('aktif', 1)
            ->count();

        if ($menuCount < 1) {
            return back()->with('err', 'Menu rombel kelas XI belum tersedia untuk tahun ajaran periode ini.');
        }

        DB::transaction(function () use ($periode) {
            /*
             * Hanya boleh ada satu periode dibuka untuk tahun ajaran dan tingkat yang sama.
             */
            PeriodePemilihanMenu::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
                ->where('tingkat', $periode->tingkat ?? 'XI')
                ->where('id', '!=', $periode->id)
                ->where('status', 'dibuka')
                ->update(['status' => 'ditutup']);

            /*
             * Status dibuka berarti periode sudah disiapkan.
             * Akses siswa tetap harus mengecek tanggal_mulai dan tanggal_selesai.
             */
            $periode->update([
                'status' => 'dibuka',
            ]);
        });

        return back()->with('ok', 'Periode pemilihan berhasil diaktifkan. Siswa dapat memilih sesuai rentang tanggal yang ditentukan.');
    }

    public function tutup(PeriodePemilihanMenu $periode)
    {
        if ($periode->status !== 'dibuka') {
            return back()->with('err', 'Periode hanya dapat ditutup jika statusnya Dibuka.');
        }

        $periode->update([
            'status' => 'ditutup',
        ]);

        return back()->with('ok', 'Periode pemilihan berhasil ditutup.');
    }

    public function tempatkanOtomatis(PeriodePemilihanMenu $periode)
    {
        if (!in_array($periode->status, ['ditutup', 'ditempatkan'], true)) {
            return back()->with('err', 'Periode harus ditutup terlebih dahulu sebelum penempatan dijalankan.');
        }

        $menu = MenuRombel::where('tahun_ajaran_id', $periode->tahun_ajaran_id)
            ->where('tingkat', $periode->tingkat ?? 'XI')
            ->where('aktif', 1)
            ->get()
            ->keyBy('id');

        if ($menu->isEmpty()) {
            return back()->with('err', 'Menu rombel kelas XI belum tersedia.');
        }

        $pendaftar = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->orderBy('waktu_pengajuan')
            ->orderBy('id')
            ->get();

        if ($pendaftar->isEmpty()) {
            return back()->with('err', 'Belum ada siswa yang memilih menu pada periode ini.');
        }

        DB::transaction(function () use ($periode, $menu, $pendaftar) {
            $sisa = [];

            foreach ($menu as $m) {
                $sisa[$m->id] = (int) $m->kapasitas_total;
            }

            /*
             * Reset hasil lama agar penempatan dapat dihitung ulang.
             */
            SiswaMenuPeriode::where('periode_id', $periode->id)
                ->update([
                    'menu_diterima_id' => null,
                    'status' => 'belum_tertempatkan',
                    'updated_at' => now(),
                ]);

            $pendaftar = SiswaMenuPeriode::where('periode_id', $periode->id)
                ->orderBy('waktu_pengajuan')
                ->orderBy('id')
                ->get();

            /*
             * Proses pilihan 1, 2, dan 3.
             * Jika pilihan_3 kosong, otomatis dilewati.
             */
            for ($rank = 1; $rank <= 3; $rank++) {
                foreach ($pendaftar as $row) {
                    $freshRow = $row->fresh();

                    if ($freshRow->menu_diterima_id) {
                        continue;
                    }

                    $menuId = $freshRow->{"pilihan_{$rank}_menu_id"} ?? null;

                    if (!$menuId || !isset($sisa[$menuId]) || $sisa[$menuId] <= 0) {
                        continue;
                    }

                    $freshRow->update([
                        'menu_diterima_id' => $menuId,
                        'status' => 'diterima',
                    ]);

                    $sisa[$menuId]--;
                }
            }

            /*
             * Siswa yang tidak mendapatkan menu menjadi cadangan.
             */
            foreach ($pendaftar as $row) {
                $freshRow = $row->fresh();

                if (!$freshRow->menu_diterima_id) {
                    $freshRow->update([
                        'menu_diterima_id' => null,
                        'status' => 'cadangan',
                    ]);
                }
            }

            /*
             * Status final penempatan.
             * Tidak perlu diubah menjadi terkunci.
             */
            $periode->update([
                'status' => 'ditempatkan',
            ]);
        });

        return redirect()
            ->route('admin.periode.hasil', $periode)
            ->with('ok', 'Penempatan otomatis selesai. Silakan cek hasil sebelum commit ke rombel.');
    }

    public function hasil(PeriodePemilihanMenu $periode)
    {
        $periode->load('tahunAjaran');

        $ringkasMenu = SiswaMenuPeriode::select('menu_diterima_id', DB::raw('COUNT(*) as jumlah'))
            ->where('periode_id', $periode->id)
            ->where('status', 'diterima')
            ->whereNotNull('menu_diterima_id')
            ->groupBy('menu_diterima_id')
            ->with('menuDiterima')
            ->get();

        $belum = SiswaMenuPeriode::where('periode_id', $periode->id)
            ->where(function ($query) {
                $query->whereNull('menu_diterima_id')
                    ->orWhereIn('status', ['belum_tertempatkan', 'cadangan']);
            })
            ->count();

        return view('admin.periode_menu.hasil', compact('periode', 'ringkasMenu', 'belum'));
    }

    public function kunci(PeriodePemilihanMenu $periode)
    {
        /*
         * Alur baru tidak perlu status terkunci.
         * Periode yang sudah ditempatkan sudah dianggap final/terkunci secara proses.
         */
        if ($periode->status !== 'ditempatkan') {
            return back()->with('err', 'Periode hanya dapat difinalkan setelah hasil penempatan tersedia.');
        }

        return back()->with('ok', 'Periode sudah ditempatkan dan hasilnya sudah final.');
    }

    public function bentukRombel(PeriodePemilihanMenu $periode)
    {
        return redirect()
            ->route('admin.pemilihan.index')
            ->with('ok', 'Pembentukan rombel dilakukan melalui menu Rekap & Penempatan dengan tombol Commit ke Rombel.');
    }
}