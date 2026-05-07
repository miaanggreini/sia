<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PresensiSiswa;
use App\Models\SesiPresensi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KehadiranController extends Controller
{
    private function tahunAjaranAktif(): TahunAjaran
    {
        $taAktif = TahunAjaran::where('status', 'aktif')->first();

        abort_unless($taAktif, 404, 'Tahun ajaran aktif belum diatur.');

        return $taAktif;
    }

    private function guruLoginId(): int
    {
        $guruId = DB::table('guru')
            ->where('user_id', auth()->id())
            ->value('id');

        abort_unless($guruId, 403, 'Akun ini belum terhubung data guru.');

        return (int) $guruId;
    }

    private function guruMengajarDiTahunAktif(int $guruId, int $rombelId, int $mapelId, int $tahunAjaranId): bool
    {
        return DB::table('jadwal as j')
            ->join('rombel as r', 'r.id', '=', 'j.rombel_id')
            ->where('j.guru_id', $guruId)
            ->where('j.rombel_id', $rombelId)
            ->where('j.mata_pelajaran_id', $mapelId)
            ->where('r.tahun_ajaran_id', $tahunAjaranId)
            ->where(function ($q) {
                $q->where('r.aktif', 1)
                  ->orWhereNull('r.aktif');
            })
            ->exists();
    }

    public function index(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        /*
        |--------------------------------------------------------------------------
        | Rombel hanya dari jadwal guru pada tahun ajaran aktif
        |--------------------------------------------------------------------------
        */
        $rombels = DB::table('jadwal as j')
            ->join('rombel as r', 'r.id', '=', 'j.rombel_id')
            ->where('j.guru_id', $guruId)
            ->where('r.tahun_ajaran_id', $taAktif->id)
            ->where(function ($q) {
                $q->where('r.aktif', 1)
                  ->orWhereNull('r.aktif');
            })
            ->select('r.id', 'r.nama_rombel as nama')
            ->distinct()
            ->orderBy('r.nama_rombel')
            ->get();

        $allowedRombelIds = $rombels->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $rombelId = $request->integer('rombel_id');

        if (!$rombelId || !in_array($rombelId, $allowedRombelIds, true)) {
            $rombelId = $allowedRombelIds[0] ?? null;
        }

        /*
        |--------------------------------------------------------------------------
        | Mapel hanya dari jadwal guru pada rombel tahun ajaran aktif
        |--------------------------------------------------------------------------
        */
        $mapels = collect();

        if ($rombelId) {
            $mapels = DB::table('jadwal as j')
                ->join('rombel as r', 'r.id', '=', 'j.rombel_id')
                ->join('mata_pelajaran as m', 'm.id', '=', 'j.mata_pelajaran_id')
                ->where('j.guru_id', $guruId)
                ->where('j.rombel_id', $rombelId)
                ->where('r.tahun_ajaran_id', $taAktif->id)
                ->where(function ($q) {
                    $q->where('r.aktif', 1)
                      ->orWhereNull('r.aktif');
                })
                ->select('m.id', 'm.nama_mapel as nama')
                ->distinct()
                ->orderBy('m.nama_mapel')
                ->get();
        }

        $allowedMapelIds = $mapels->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $mapelId = $request->integer('mata_pelajaran_id');

        if (!$mapelId || !in_array($mapelId, $allowedMapelIds, true)) {
            $mapelId = $allowedMapelIds[0] ?? null;
        }

        $tanggal = $request->input('tanggal', now($tz)->toDateString());
        $tanggal = Carbon::parse($tanggal, $tz)->toDateString();

        $sesiId = null;

        if ($rombelId && $mapelId) {
            $sesiId = DB::table('sesi_presensi as sp')
                ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
                ->where('sp.guru_id', $guruId)
                ->where('sp.rombel_id', $rombelId)
                ->where('sp.mata_pelajaran_id', $mapelId)
                ->where('r.tahun_ajaran_id', $taAktif->id)
                ->whereDate('sp.mulai_pada', $tanggal)
                ->orderByDesc('sp.mulai_pada')
                ->value('sp.id');
        }

        $siswaList = collect();

        if ($rombelId) {
            $base = DB::table('siswa as s')
                ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
                ->where('sr.rombel_id', $rombelId)
                ->where('sr.tahun_ajaran_id', $taAktif->id)
                ->where('sr.aktif', 1)
                ->where('s.status', 'aktif')
                ->select('s.id', 's.nama', 's.nis')
                ->orderBy('s.nama');

            if ($sesiId) {
                $base->leftJoin('presensi as p', function ($j) use ($sesiId) {
                    $j->on('p.siswa_id', '=', 's.id')
                        ->where('p.sesi_presensi_id', '=', $sesiId);
                })
                ->addSelect(
                    DB::raw("COALESCE(p.status, 'alfa') as status_presensi"),
                    'p.dipindai_pada'
                );
            } else {
                $base->addSelect(
                    DB::raw("'alfa' as status_presensi"),
                    DB::raw('NULL as dipindai_pada')
                );
            }

            $siswaList = $base->get();
        }

        return view('guru.kehadiran.index', compact(
            'rombels',
            'rombelId',
            'mapels',
            'mapelId',
            'tanggal',
            'siswaList',
            'sesiId',
            'taAktif'
        ));
    }

    public function bulkSetStatus(Request $request)
    {
        $tz = 'Asia/Jakarta';
        $userId = auth()->id();
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        $data = $request->validate([
            'rombel_id'         => 'required|exists:rombel,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tanggal'           => 'required|date',
            'statuses'          => 'required|array|min:1',
            'statuses.*'        => 'required|in:hadir,izin,sakit,alfa',
        ]);

        $data['tanggal'] = Carbon::parse($data['tanggal'], $tz)->toDateString();

        $ajar = $this->guruMengajarDiTahunAktif(
            $guruId,
            (int) $data['rombel_id'],
            (int) $data['mata_pelajaran_id'],
            (int) $taAktif->id
        );

        abort_unless($ajar, 403, 'Anda bukan pengajar rombel/mapel ini pada tahun ajaran aktif.');

        $validSiswaIds = DB::table('siswa_rombel as sr')
            ->join('siswa as s', 's.id', '=', 'sr.siswa_id')
            ->where('sr.rombel_id', $data['rombel_id'])
            ->where('sr.tahun_ajaran_id', $taAktif->id)
            ->where('sr.aktif', 1)
            ->where('s.status', 'aktif')
            ->pluck('sr.siswa_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $submittedIds = collect(array_keys($data['statuses']))
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($submittedIds as $sid) {
            abort_unless(
                in_array($sid, $validSiswaIds, true),
                403,
                'Ada siswa yang tidak valid untuk rombel ini.'
            );
        }

        $sesiId = DB::table('sesi_presensi as sp')
            ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
            ->where('sp.guru_id', $guruId)
            ->where('sp.rombel_id', $data['rombel_id'])
            ->where('sp.mata_pelajaran_id', $data['mata_pelajaran_id'])
            ->where('r.tahun_ajaran_id', $taAktif->id)
            ->whereDate('sp.mulai_pada', $data['tanggal'])
            ->orderByDesc('sp.mulai_pada')
            ->value('sp.id');

        if (!$sesiId) {
            DB::beginTransaction();

            try {
                $mulai = Carbon::parse($data['tanggal'] . ' 07:00:00', $tz);

                $sesi = SesiPresensi::create([
                    'guru_id'           => $guruId,
                    'rombel_id'         => $data['rombel_id'],
                    'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                    'mulai_pada'        => $mulai,
                    'status'            => 'tertutup',
                    'masa_aktif_detik'  => 0,
                    'secret_salt'       => Str::random(16),
                ]);

                $sesiId = $sesi->id;

                $this->seedDefaultAlfa($sesiId, $data['rombel_id'], $userId);

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
        }

        DB::beginTransaction();

        try {
            foreach ($data['statuses'] as $siswaId => $status) {
                $presensi = PresensiSiswa::firstOrNew([
                    'sesi_presensi_id' => $sesiId,
                    'siswa_id'         => (int) $siswaId,
                ]);

                if (!$presensi->exists) {
                    $presensi->dibuat_oleh = $userId;
                }

                $presensi->status = $status;
                $presensi->dipindai_pada = now($tz);
                $presensi->ip_address = $request->ip();
                $presensi->device_fingerprint = substr((string) ($request->userAgent() ?? ''), 0, 180);
                $presensi->diperbarui_oleh = $userId;
                $presensi->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return redirect()
            ->route('guru.kehadiran.index', [
                'rombel_id' => $data['rombel_id'],
                'mata_pelajaran_id' => $data['mata_pelajaran_id'],
                'tanggal' => $data['tanggal'],
            ])
            ->with('success', 'Perubahan status presensi berhasil disimpan.');
    }

    public function showSiswa(Request $request, $rombelId, $mapelId, $siswaId)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        $ajar = $this->guruMengajarDiTahunAktif(
            $guruId,
            (int) $rombelId,
            (int) $mapelId,
            (int) $taAktif->id
        );

        abort_unless($ajar, 403, 'Anda bukan pengajar rombel/mapel ini pada tahun ajaran aktif.');

        $siswaValid = DB::table('siswa_rombel as sr')
            ->join('siswa as s', 's.id', '=', 'sr.siswa_id')
            ->where('sr.rombel_id', $rombelId)
            ->where('sr.siswa_id', $siswaId)
            ->where('sr.tahun_ajaran_id', $taAktif->id)
            ->where('sr.aktif', 1)
            ->where('s.status', 'aktif')
            ->exists();

        abort_unless($siswaValid, 403, 'Siswa tidak terdaftar aktif pada rombel tahun ajaran aktif.');

        $rombel = DB::table('rombel')
            ->where('id', $rombelId)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->first();

        $mapel  = DB::table('mata_pelajaran')->where('id', $mapelId)->first();
        $siswa  = DB::table('siswa')->where('id', $siswaId)->first();

        abort_unless($rombel && $mapel && $siswa, 404);

        $rows = DB::table('sesi_presensi as sp')
            ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
            ->where('sp.guru_id', $guruId)
            ->where('sp.rombel_id', $rombelId)
            ->where('sp.mata_pelajaran_id', $mapelId)
            ->where('r.tahun_ajaran_id', $taAktif->id)
            ->leftJoin('presensi as ps', function ($j) use ($siswaId) {
                $j->on('ps.sesi_presensi_id', '=', 'sp.id')
                    ->where('ps.siswa_id', $siswaId);
            })
            ->orderBy('sp.mulai_pada')
            ->get([
                'sp.id as sesi_id',
                'sp.mulai_pada',
                DB::raw("COALESCE(ps.status, 'alfa') as status"),
            ]);

        $total = $rows->count();

        $count = [
            'hadir' => $rows->where('status', 'hadir')->count(),
            'izin'  => $rows->where('status', 'izin')->count(),
            'sakit' => $rows->where('status', 'sakit')->count(),
            'alfa'  => $rows->where('status', 'alfa')->count(),
        ];

        $persenHadir = $total ? round(($count['hadir'] / $total) * 100, 1) : 0;

        return view('guru.kehadiran.show-siswa', compact(
            'rombel',
            'mapel',
            'siswa',
            'rows',
            'total',
            'count',
            'persenHadir'
        ));
    }

    public function exportSiswa($rombelId, $mapelId, $siswaId)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        $ajar = $this->guruMengajarDiTahunAktif(
            $guruId,
            (int) $rombelId,
            (int) $mapelId,
            (int) $taAktif->id
        );

        abort_unless($ajar, 403, 'Anda bukan pengajar rombel/mapel ini pada tahun ajaran aktif.');

        $siswaValid = DB::table('siswa_rombel as sr')
            ->join('siswa as s', 's.id', '=', 'sr.siswa_id')
            ->where('sr.rombel_id', $rombelId)
            ->where('sr.siswa_id', $siswaId)
            ->where('sr.tahun_ajaran_id', $taAktif->id)
            ->where('sr.aktif', 1)
            ->where('s.status', 'aktif')
            ->exists();

        abort_unless($siswaValid, 403, 'Siswa tidak terdaftar aktif pada rombel tahun ajaran aktif.');

        $rombel = DB::table('rombel')
            ->where('id', $rombelId)
            ->where('tahun_ajaran_id', $taAktif->id)
            ->first();

        $mapel  = DB::table('mata_pelajaran')->where('id', $mapelId)->first();
        $siswa  = DB::table('siswa')->where('id', $siswaId)->first();

        abort_unless($rombel && $mapel && $siswa, 404);

        $rows = DB::table('presensi as p')
            ->join('sesi_presensi as s', 's.id', '=', 'p.sesi_presensi_id')
            ->join('rombel as r', 'r.id', '=', 's.rombel_id')
            ->where('s.guru_id', $guruId)
            ->where('s.rombel_id', $rombelId)
            ->where('s.mata_pelajaran_id', $mapelId)
            ->where('r.tahun_ajaran_id', $taAktif->id)
            ->where('p.siswa_id', $siswaId)
            ->select('p.status', 'p.dipindai_pada', 's.mulai_pada')
            ->orderByDesc('s.mulai_pada')
            ->get();

        $filename = sprintf(
            'presensi_%s_%s_%s.csv',
            str($siswa->nama)->slug('_'),
            str($rombel->nama_rombel)->slug('_'),
            str($mapel->nama_mapel)->slug('_')
        );

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Tanggal (WIB)', 'Status', 'Dipindai Pada (WIB)']);

            foreach ($rows as $r) {
                $tgl  = Carbon::parse($r->mulai_pada)->timezone('Asia/Jakarta')->format('Y-m-d');
                $scan = $r->dipindai_pada
                    ? Carbon::parse($r->dipindai_pada)->timezone('Asia/Jakarta')->format('Y-m-d H:i')
                    : '';

                fputcsv($out, [$tgl, strtoupper($r->status ?? ''), $scan]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    protected function seedDefaultAlfa(int $sesiId, int $rombelId, int $userId): void
    {
        $taAktif = $this->tahunAjaranAktif();

        $anggota = DB::table('siswa_rombel as sr')
            ->join('siswa as s', 's.id', '=', 'sr.siswa_id')
            ->where('sr.rombel_id', $rombelId)
            ->where('sr.tahun_ajaran_id', $taAktif->id)
            ->where('sr.aktif', 1)
            ->where('s.status', 'aktif')
            ->pluck('sr.siswa_id');

        if ($anggota->isEmpty()) {
            return;
        }

        $now = now('Asia/Jakarta');
        $rows = [];

        foreach ($anggota as $siswaId) {
            $rows[] = [
                'sesi_presensi_id'   => $sesiId,
                'siswa_id'           => $siswaId,
                'status'             => 'alfa',
                'dipindai_pada'      => null,
                'ip_address'         => null,
                'device_fingerprint' => null,
                'dibuat_oleh'        => $userId,
                'diperbarui_oleh'    => $userId,
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        }

        DB::table('presensi')->insert($rows);
    }
}