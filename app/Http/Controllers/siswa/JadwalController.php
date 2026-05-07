<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Models\Rombel;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $siswa = $request->user()->siswa;

        abort_unless($siswa, 403, 'Akun ini belum terhubung dengan data siswa.');

        $agama = trim((string) ($siswa->agama ?? ''));

        /*
        |--------------------------------------------------------------------------
        | Tahun ajaran aktif
        |--------------------------------------------------------------------------
        */
        $tahunAjaranAktif = TahunAjaran::where('status', 'aktif')->first();

        abort_unless($tahunAjaranAktif, 404, 'Tahun ajaran aktif belum diatur.');

        /*
        |--------------------------------------------------------------------------
        | Ambil rombel aktif siswa untuk tahun ajaran aktif
        |--------------------------------------------------------------------------
        | Karena jadwal terikat ke rombel_id, maka jadwal hanya boleh diambil
        | dari rombel yang tahun_ajaran_id-nya sama dengan tahun ajaran aktif.
        */
        $rombelAktifId = DB::table('siswa_rombel as sr')
            ->join('rombel as r', 'r.id', '=', 'sr.rombel_id')
            ->where('sr.siswa_id', $siswa->id)
            ->where('sr.aktif', 1)
            ->where('r.tahun_ajaran_id', $tahunAjaranAktif->id)
            ->orderByDesc('sr.id')
            ->value('sr.rombel_id');

        /*
        |--------------------------------------------------------------------------
        | Fallback hanya untuk badge rombel, bukan untuk ambil jadwal
        |--------------------------------------------------------------------------
        | Ini supaya kalau data siswa_rombel masih aktif tapi rombelnya belum cocok
        | dengan TA aktif, nama rombel tetap bisa tampil, namun jadwal lama tidak
        | ikut terbaca.
        */
        $rombelBadgeId = $rombelAktifId;

        if (!$rombelBadgeId) {
            $rombelBadgeId = DB::table('siswa_rombel')
                ->where('siswa_id', $siswa->id)
                ->where('aktif', 1)
                ->orderByDesc('id')
                ->value('rombel_id');
        }

        $rombelAktif = null;

        if ($rombelBadgeId) {
            $rombelAktif = Rombel::with(['guru', 'tahunAjaran'])
                ->find($rombelBadgeId);
        }

        /*
        |--------------------------------------------------------------------------
        | Urutan hari
        |--------------------------------------------------------------------------
        */
        $hariOrder = [
            'Senin'  => 1,
            'Selasa' => 2,
            'Rabu'   => 3,
            'Kamis'  => 4,
            'Jumat'  => 5,
            'Sabtu'  => 6,
        ];

        $hariList = array_keys($hariOrder);

        $jadwalKosong = collect($hariList)->mapWithKeys(function ($hari) {
            return [$hari => collect()];
        });

        /*
        |--------------------------------------------------------------------------
        | Kalau tidak ada rombel sama sekali
        |--------------------------------------------------------------------------
        */
        if (!$rombelAktif) {
            return view('siswa.jadwal.index', [
                'siswa'            => $siswa,
                'rombelAktif'      => null,
                'tahunAjaranAktif' => $tahunAjaranAktif,
                'jadwal'           => $jadwalKosong,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Kalau rombel yang tampil bukan rombel tahun ajaran aktif,
        | jangan ambil jadwal.
        |--------------------------------------------------------------------------
        | Ini mencegah jadwal tahun ajaran lama ikut tampil.
        */
        if (!$rombelAktifId) {
            return view('siswa.jadwal.index', [
                'siswa'            => $siswa,
                'rombelAktif'      => $rombelAktif,
                'tahunAjaranAktif' => $tahunAjaranAktif,
                'jadwal'           => $jadwalKosong,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil jadwal hanya dari rombel tahun ajaran aktif
        |--------------------------------------------------------------------------
        */
        $items = Jadwal::with(['mataPelajaran', 'guru', 'rombel'])
            ->where('rombel_id', $rombelAktifId)
            ->whereIn('hari', $hariList)
            ->whereHas('rombel', function ($q) use ($tahunAjaranAktif) {
                $q->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->whereHas('mataPelajaran', function ($q) use ($agama) {
                $q->where(function ($qq) use ($agama) {
                    $qq->where('nama_mapel', 'not like', 'Pendidikan Agama%');

                    if ($agama !== '') {
                        $qq->orWhere('nama_mapel', 'like', '%' . $agama . '%');
                    }
                });
            })
            ->orderByRaw("
                CASE hari
                    WHEN 'Senin' THEN 1
                    WHEN 'Selasa' THEN 2
                    WHEN 'Rabu' THEN 3
                    WHEN 'Kamis' THEN 4
                    WHEN 'Jumat' THEN 5
                    WHEN 'Sabtu' THEN 6
                    ELSE 99
                END
            ")
            ->orderBy('jam_mulai')
            ->get();

        $grouped = $items->groupBy('hari');

        $jadwal = collect($hariList)->mapWithKeys(function ($hari) use ($grouped) {
            return [$hari => $grouped->get($hari, collect())];
        })->sortBy(fn ($_, $hari) => $hariOrder[$hari]);

        return view('siswa.jadwal.index', [
            'siswa'            => $siswa,
            'rombelAktif'      => $rombelAktif,
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'jadwal'           => $jadwal,
        ]);
    }
}