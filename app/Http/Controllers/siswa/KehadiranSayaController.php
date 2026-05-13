<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KehadiranSayaController extends Controller
{
public function index(Request $request)
{
    $user = auth()->user();

    $siswa = DB::table('siswa')
        ->where('user_id', $user->id)
        ->first();

    abort_unless($siswa, 403, 'Akun ini bukan siswa.');

    $agama = trim((string) ($siswa->agama ?? ''));

    /*
     * Ambil tahun ajaran aktif.
     * Ini dipakai supaya TA baru tetap muncul di dropdown,
     * walaupun belum ada presensi.
     */
    $tahunAjaranAktif = DB::table('tahun_ajaran')
        ->where('status', 'aktif')
        ->first();

    /*
     * Ambil semua riwayat rombel siswa.
     * Jangan pakai aktif = 1, supaya histori presensi kelas lama tetap muncul.
     */
    $riwayatRombel = DB::table('siswa_rombel as sr')
        ->where('sr.siswa_id', $siswa->id)
        ->pluck('sr.rombel_id');

    /*
     * Dropdown tahun ajaran:
     * - dari riwayat rombel siswa
     * - ditambah tahun ajaran aktif
     *
     * Jadi 2026/2027 tetap muncul meskipun belum ada presensi.
     */
    $taIds = DB::table('siswa_rombel as sr')
        ->where('sr.siswa_id', $siswa->id)
        ->pluck('sr.tahun_ajaran_id')
        ->filter()
        ->unique()
        ->values();

    if ($tahunAjaranAktif && !$taIds->contains($tahunAjaranAktif->id)) {
        $taIds->push($tahunAjaranAktif->id);
    }

    $taOptions = DB::table('tahun_ajaran')
        ->whereIn('id', $taIds)
        ->orderByDesc('nama_tahun')
        ->pluck('nama_tahun', 'id');

    $taId = $request->get('tahun_ajaran_id');
    $semester = $request->get('semester');

    if ($riwayatRombel->isEmpty()) {
        return view('siswa.kehadiran.index', [
            'items'     => collect(),
            'groups'    => collect(),
            'taOptions' => $taOptions,
            'taId'      => $taId,
            'semester'  => $semester,
        ]);
    }

    /*
     * Semester dihitung dari tanggal sesi presensi:
     * - Ganjil: Juli - Desember
     * - Genap : Januari - Juni
     */
    $semesterCase = "
        CASE
            WHEN MONTH(sp.mulai_pada) BETWEEN 7 AND 12 THEN 'Ganjil'
            WHEN MONTH(sp.mulai_pada) BETWEEN 1 AND 6 THEN 'Genap'
            ELSE '-'
        END
    ";

    $query = DB::table('sesi_presensi as sp')
        ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
        ->leftJoin('tahun_ajaran as ta', 'ta.id', '=', 'r.tahun_ajaran_id')
        ->join('mata_pelajaran as m', 'm.id', '=', 'sp.mata_pelajaran_id')
        ->leftJoin('presensi as p', function ($join) use ($siswa) {
            $join->on('p.sesi_presensi_id', '=', 'sp.id')
                ->where('p.siswa_id', '=', $siswa->id);
        })
        ->whereIn('sp.rombel_id', $riwayatRombel)
        ->where(function ($q) use ($agama) {
            $q->where('m.nama_mapel', 'not like', 'Pendidikan Agama%');

            if ($agama !== '') {
                $q->orWhere('m.nama_mapel', 'like', '%' . $agama . '%');
            }
        });

    if (!empty($taId)) {
        $query->where('r.tahun_ajaran_id', $taId);
    }

    if (!empty($semester)) {
        if ($semester === 'Ganjil') {
            $query->whereRaw('MONTH(sp.mulai_pada) BETWEEN 7 AND 12');
        } elseif ($semester === 'Genap') {
            $query->whereRaw('MONTH(sp.mulai_pada) BETWEEN 1 AND 6');
        }
    }

    $items = $query
        ->groupBy(
            'sp.rombel_id',
            'r.nama_rombel',
            'r.tingkat',
            'r.tahun_ajaran_id',
            'ta.nama_tahun',
            DB::raw($semesterCase),
            'sp.mata_pelajaran_id',
            'm.nama_mapel'
        )
        ->selectRaw("
            sp.rombel_id,
            r.nama_rombel as rombel,
            r.tingkat,
            r.tahun_ajaran_id,
            COALESCE(ta.nama_tahun, '-') as tahun_ajaran,
            {$semesterCase} as semester,
            sp.mata_pelajaran_id as mapel_id,
            m.nama_mapel as mapel,
            COUNT(DISTINCT sp.id) as total,
            SUM(CASE WHEN p.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN p.status = 'izin' THEN 1 ELSE 0 END) as izin,
            SUM(CASE WHEN p.status = 'sakit' THEN 1 ELSE 0 END) as sakit,
            SUM(CASE WHEN p.status = 'alfa' THEN 1 ELSE 0 END) as alfa
        ")
        ->orderByDesc('ta.nama_tahun')
        ->orderByRaw("
            CASE r.tingkat
                WHEN 'X' THEN 1
                WHEN 'XI' THEN 2
                WHEN 'XII' THEN 3
                ELSE 4
            END
        ")
        ->orderBy('r.nama_rombel')
        ->orderBy('m.nama_mapel')
        ->get()
        ->sortBy(function ($item) {
            $semesterOrder = match ($item->semester) {
                'Ganjil' => 1,
                'Genap' => 2,
                default => 3,
            };

            return sprintf(
                '%s-%s-%s-%s',
                $item->tahun_ajaran,
                $item->rombel,
                $semesterOrder,
                $item->mapel
            );
        })
        ->values()
        ->map(function ($item) {
            $item->total = (int) ($item->total ?? 0);
            $item->hadir = (int) ($item->hadir ?? 0);
            $item->izin  = (int) ($item->izin ?? 0);
            $item->sakit = (int) ($item->sakit ?? 0);
            $item->alfa  = (int) ($item->alfa ?? 0);

            $item->belum = max(
                $item->total - ($item->hadir + $item->izin + $item->sakit + $item->alfa),
                0
            );

            $item->persen = $item->total > 0
                ? round(($item->hadir / $item->total) * 100, 1)
                : 0;

            return $item;
        });

    $groups = $items->groupBy(function ($item) {
        return implode('|', [
            $item->tahun_ajaran ?? '-',
            $item->semester ?? '-',
            $item->rombel ?? '-',
        ]);
    });

    return view('siswa.kehadiran.index', compact(
        'items',
        'groups',
        'taOptions',
        'taId',
        'semester'
    ));
}

public function show(Request $request, $rombelId, $mapelId)
{
    $user = auth()->user();

    $siswa = DB::table('siswa')
        ->where('user_id', $user->id)
        ->first();

    abort_unless($siswa, 403, 'Akun ini bukan siswa.');

    $agama = trim((string) ($siswa->agama ?? ''));

    $rombel = DB::table('rombel')
        ->where('id', $rombelId)
        ->first();

    $mapel = DB::table('mata_pelajaran')
        ->where('id', $mapelId)
        ->first();

    abort_unless($rombel && $mapel, 404);

    if (str_starts_with($mapel->nama_mapel, 'Pendidikan Agama')) {
        abort_if(
            $agama === '' || !str_contains($mapel->nama_mapel, $agama),
            403,
            'Data kehadiran ini tidak sesuai dengan agama Anda.'
        );
    }

    // Cek siswa pernah menjadi anggota rombel ini, tidak harus aktif
    $anggota = DB::table('siswa_rombel')
        ->where('siswa_id', $siswa->id)
        ->where('rombel_id', $rombelId)
        ->exists();

    abort_unless($anggota, 403, 'Anda bukan anggota rombel ini.');

    $rows = DB::table('sesi_presensi as sp')
    ->leftJoin('presensi as ps', function ($join) use ($siswa) {
        $join->on('ps.sesi_presensi_id', '=', 'sp.id')
            ->where('ps.siswa_id', '=', $siswa->id);
    })
    ->where('sp.rombel_id', $rombelId)
    ->where('sp.mata_pelajaran_id', $mapelId)
    ->orderBy('sp.mulai_pada')
    ->get([
        'sp.id as sesi_id',
        'sp.mulai_pada',
        'ps.status',
        'ps.dipindai_pada',
    ])
    ->map(function ($row) {
        $row->status = $row->status ?: 'alfa';

        $row->tanggal = $row->mulai_pada
            ? Carbon::parse($row->mulai_pada)->timezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') . ' WIB'
            : '-';

        $row->waktu_scan = $row->dipindai_pada
            ? Carbon::parse($row->dipindai_pada)->timezone('Asia/Jakarta')->translatedFormat('d M Y • H:i') . ' WIB'
            : '-';

        return $row;
    });

    $total = $rows->count();

    $count = [
        'hadir' => $rows->where('status', 'hadir')->count(),
        'izin'  => $rows->where('status', 'izin')->count(),
        'sakit' => $rows->where('status', 'sakit')->count(),
        'alfa'  => $rows->where('status', 'alfa')->count(),
    ];

    $persen = $total > 0
        ? round(($count['hadir'] / $total) * 100, 1)
        : 0;

    return view('siswa.kehadiran.show', compact(
        'rombel',
        'mapel',
        'rows',
        'total',
        'count',
        'persen'
    ));
}
}