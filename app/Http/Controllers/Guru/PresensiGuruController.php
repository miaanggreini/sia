<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\PresensiSiswa;
use App\Models\SesiPresensi;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class PresensiGuruController extends Controller
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

        abort_unless($guruId, 403, 'Akun ini belum terhubung dengan data guru.');

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

    private function sesiMilikGuruTahunAktif(SesiPresensi $sesi, int $guruId, int $tahunAjaranId): bool
    {
        return DB::table('sesi_presensi as sp')
            ->join('rombel as r', 'r.id', '=', 'sp.rombel_id')
            ->where('sp.id', $sesi->id)
            ->where('sp.guru_id', $guruId)
            ->where('r.tahun_ajaran_id', $tahunAjaranId)
            ->where(function ($q) {
                $q->where('r.aktif', 1)
                  ->orWhereNull('r.aktif');
            })
            ->exists();
    }

    public function index(Request $request)
    {
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

        return view('guru.presensi.index', compact(
            'rombels',
            'mapels',
            'rombelId',
            'taAktif'
        ));
    }

    public function mulai(Request $request)
    {
        $request->validate([
            'rombel_id'         => 'required|exists:rombel,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'waktu_mulai'       => 'required|date_format:H:i',
            'waktu_selesai'     => 'required|date_format:H:i',
        ]);

        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        $ajar = $this->guruMengajarDiTahunAktif(
            $guruId,
            (int) $request->rombel_id,
            (int) $request->mata_pelajaran_id,
            (int) $taAktif->id
        );

        abort_unless($ajar, 403, 'Anda tidak mengajar mapel ini di rombel tahun ajaran aktif.');

        $tz      = 'Asia/Jakarta';
        $today   = now($tz)->startOfDay();
        $mulai   = $today->copy()->setTimeFromTimeString($request->waktu_mulai);
        $selesai = $today->copy()->setTimeFromTimeString($request->waktu_selesai);

        if ($selesai->lessThanOrEqualTo($mulai)) {
            return back()
                ->withErrors(['waktu_selesai' => 'Jam selesai harus setelah jam mulai.'])
                ->withInput();
        }

        $ttl = $mulai->diffInSeconds($selesai);

        DB::beginTransaction();

        try {
            $sesi = SesiPresensi::create([
                'guru_id'           => $guruId,
                'rombel_id'         => (int) $request->rombel_id,
                'mata_pelajaran_id' => (int) $request->mata_pelajaran_id,
                'mulai_pada'        => $mulai,
                'status'            => 'terbuka',
                'masa_aktif_detik'  => $ttl,
                'secret_salt'       => Str::random(16),
            ]);

            $this->seedDefaultAlfa(
                $sesi->id,
                (int) $request->rombel_id,
                (int) $request->mata_pelajaran_id,
                auth()->id()
            );

            DB::commit();

            return redirect()->route('guru.presensi.sesi.tampil', $sesi->id);
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function tampil(SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        $siswaList = $this->eligibleSiswaQuery($sesi->rombel_id, $sesi->mata_pelajaran_id)
            ->leftJoin('presensi as p', function ($j) use ($sesi) {
                $j->on('p.siswa_id', '=', 's.id')
                    ->where('p.sesi_presensi_id', '=', $sesi->id);
            })
            ->select(
                's.id',
                's.nama',
                's.nis',
                DB::raw("COALESCE(p.status, 'alfa') as status_presensi"),
                'p.dipindai_pada'
            )
            ->orderBy('s.nama')
            ->get();

        $total = $siswaList->count();
        $hadir = $siswaList->where('status_presensi', 'hadir')->count();
        $izin  = $siswaList->where('status_presensi', 'izin')->count();
        $sakit = $siswaList->where('status_presensi', 'sakit')->count();
        $alfa  = $siswaList->where('status_presensi', 'alfa')->count();

        return view('guru.presensi.sesi-tampil', compact(
            'sesi',
            'siswaList',
            'total',
            'hadir',
            'izin',
            'sakit',
            'alfa'
        ));
    }

    public function payloadQr(SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        abort_unless($sesi->terbuka(), 403);

        $mulai = $sesi->mulai_pada->clone()->setTimezone('Asia/Jakarta');
        $akhir = $mulai->copy()->addSeconds((int) $sesi->masa_aktif_detik);
        $sisa  = now('Asia/Jakarta')->diffInSeconds($akhir, false);

        if ($sisa <= 0) {
            $sesi->update([
                'status'       => 'tertutup',
                'ditutup_pada' => now('Asia/Jakarta'),
            ]);

            return response()->json([
                'url'           => null,
                'short_url'     => null,
                'berlaku_detik' => 0,
                'sisa_detik'    => 0,
            ]);
        }

        $ttl   = min($sisa, 180);
        $nonce = Str::uuid()->toString();

        $signedUrl = URL::temporarySignedRoute(
            'presensi.scan',
            now('Asia/Jakarta')->addSeconds($ttl),
            ['sesi' => $sesi->id, 'n' => $nonce],
            absolute: false
        );

        $code = Str::lower(Str::random(8));
        Cache::put('qr:' . $code, $signedUrl, $ttl);

        return response()->json([
            'url'           => $signedUrl,
            'short_url'     => url('/q/' . $code),
            'berlaku_detik' => (int) $ttl,
            'sisa_detik'    => (int) $sisa,
        ]);
    }

    public function override(Request $request, SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'status'   => 'required|in:hadir,izin,sakit,alfa',
        ]);

        $valid = $this->eligibleSiswaQuery($sesi->rombel_id, $sesi->mata_pelajaran_id)
            ->where('s.id', $request->siswa_id)
            ->exists();

        abort_unless($valid, 403, 'Siswa tidak valid untuk sesi presensi ini.');

        $presensi = PresensiSiswa::firstOrNew([
            'sesi_presensi_id' => $sesi->id,
            'siswa_id'         => $request->siswa_id,
        ]);

        if (!$presensi->exists) {
            $presensi->dibuat_oleh = auth()->id();
        }

        $presensi->status = $request->status;
        $presensi->dipindai_pada = now('Asia/Jakarta');
        $presensi->ip_address = $request->ip();
        $presensi->device_fingerprint = substr((string) ($request->userAgent() ?? ''), 0, 180);
        $presensi->diperbarui_oleh = auth()->id();
        $presensi->save();

        return back()->with('success', 'Status presensi diperbarui.');
    }

    public function statusJson(SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        $siswaList = $this->eligibleSiswaQuery($sesi->rombel_id, $sesi->mata_pelajaran_id)
            ->leftJoin('presensi as p', function ($j) use ($sesi) {
                $j->on('p.siswa_id', '=', 's.id')
                    ->where('p.sesi_presensi_id', '=', $sesi->id);
            })
            ->select(
                's.id',
                's.nama',
                's.nis',
                DB::raw("COALESCE(p.status, 'alfa') as status_presensi"),
                'p.dipindai_pada'
            )
            ->orderBy('s.nama')
            ->get();

        $data = $siswaList->map(function ($s) {
            return [
                'id' => $s->id,
                'nama' => $s->nama,
                'nis' => $s->nis,
                'status_presensi' => $s->status_presensi ?? 'alfa',
                'dipindai_pada' => $s->dipindai_pada
                    ? \Carbon\Carbon::parse($s->dipindai_pada)->timezone('Asia/Jakarta')->format('H:i')
                    : null,
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total' => $siswaList->count(),
                'hadir' => $siswaList->where('status_presensi', 'hadir')->count(),
                'izin'  => $siswaList->where('status_presensi', 'izin')->count(),
                'sakit' => $siswaList->where('status_presensi', 'sakit')->count(),
                'alfa'  => $siswaList->where('status_presensi', 'alfa')->count(),
            ],
            'students' => $data,
        ]);
    }

    public function bulkOverride(Request $request, SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        $data = $request->validate([
            'statuses'   => 'required|array|min:1',
            'statuses.*' => 'required|in:hadir,izin,sakit,alfa',
        ]);

        $validSiswaIds = $this->eligibleSiswaQuery($sesi->rombel_id, $sesi->mata_pelajaran_id)
            ->pluck('s.id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $submittedIds = collect(array_keys($data['statuses']))
            ->map(fn ($v) => (int) $v)
            ->all();

        foreach ($submittedIds as $sid) {
            abort_unless(in_array($sid, $validSiswaIds, true), 403, 'Ada siswa yang tidak valid untuk sesi ini.');
        }

        DB::beginTransaction();

        try {
            foreach ($data['statuses'] as $siswaId => $status) {
                $presensi = PresensiSiswa::firstOrNew([
                    'sesi_presensi_id' => $sesi->id,
                    'siswa_id'         => (int) $siswaId,
                ]);

                if (!$presensi->exists) {
                    $presensi->dibuat_oleh = auth()->id();
                }

                $presensi->status = $status;
                $presensi->dipindai_pada = now('Asia/Jakarta');
                $presensi->ip_address = $request->ip();
                $presensi->device_fingerprint = substr((string) ($request->userAgent() ?? ''), 0, 180);
                $presensi->diperbarui_oleh = auth()->id();
                $presensi->save();
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        return back()->with('success', 'Perubahan status presensi berhasil disimpan.');
    }

    public function tutup(SesiPresensi $sesi)
    {
        $guruId = $this->guruLoginId();
        $taAktif = $this->tahunAjaranAktif();

        abort_unless(
            $this->sesiMilikGuruTahunAktif($sesi, $guruId, (int) $taAktif->id),
            403,
            'Sesi presensi ini bukan milik Anda pada tahun ajaran aktif.'
        );

        $sesi->update([
            'status'       => 'tertutup',
            'ditutup_pada' => now(),
        ]);

        return redirect()
            ->route('guru.presensi.index')
            ->with('success', 'Sesi ditutup.');
    }

    public function redirectShort($code)
    {
        $signed = Cache::get('qr:' . $code);

        if (!$signed) {
            abort(404, 'QR kedaluwarsa');
        }

        return redirect()->to($signed);
    }

    public function rekap(Request $request)
    {
        return view('guru.presensi.rekap');
    }

    public function mapelByRombel(Request $request)
    {
        $request->validate([
            'rombel_id' => 'required|exists:rombel,id',
        ]);

        $guruId = DB::table('guru')->where('user_id', auth()->id())->value('id');

        if (!$guruId) {
            return response()->json([]);
        }

        $taAktif = TahunAjaran::where('status', 'aktif')->first();

        if (!$taAktif) {
            return response()->json([]);
        }

        $mapel = DB::table('jadwal as j')
            ->join('rombel as r', 'r.id', '=', 'j.rombel_id')
            ->join('mata_pelajaran as m', 'm.id', '=', 'j.mata_pelajaran_id')
            ->where('j.rombel_id', $request->rombel_id)
            ->where('j.guru_id', $guruId)
            ->where('r.tahun_ajaran_id', $taAktif->id)
            ->where(function ($q) {
                $q->where('r.aktif', 1)
                  ->orWhereNull('r.aktif');
            })
            ->distinct()
            ->select([
                'm.id',
                'm.nama_mapel as nama',
            ])
            ->orderBy('m.nama_mapel')
            ->get();

        return response()->json($mapel);
    }

    protected function seedDefaultAlfa(int $sesiId, int $rombelId, int $mataPelajaranId, int $userId): void
    {
        $anggota = $this->eligibleSiswaQuery($rombelId, $mataPelajaranId)
            ->pluck('s.id');

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

    private function eligibleSiswaQuery(int $rombelId, int $mataPelajaranId)
    {
        $taAktif = $this->tahunAjaranAktif();

        $mapel = DB::table('mata_pelajaran')
            ->where('id', $mataPelajaranId)
            ->value('nama_mapel');

        $query = DB::table('siswa as s')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
            ->where('sr.rombel_id', $rombelId)
            ->where('sr.tahun_ajaran_id', $taAktif->id)
            ->where('sr.aktif', 1)
            ->where('s.status', 'aktif');

        if ($this->isMapelAgama($mapel)) {
            $agama = $this->agamaDariMapel($mapel);

            $query->whereRaw('LOWER(s.agama) = ?', [strtolower($agama)]);
        }

        return $query;
    }

    private function isMapelAgama(?string $namaMapel): bool
    {
        return $namaMapel && Str::startsWith($namaMapel, 'Pendidikan Agama');
    }

    private function agamaDariMapel(?string $namaMapel): string
    {
        $namaMapel = trim((string) $namaMapel);

        return trim(Str::after($namaMapel, 'Pendidikan Agama'));
    }
}