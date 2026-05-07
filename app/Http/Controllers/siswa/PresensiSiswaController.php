<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\SesiPresensi;
use App\Models\PresensiSiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PresensiSiswaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $siswa = DB::table('siswa')
            ->where('user_id', $user->id)
            ->first();

        abort_unless($siswa, 403, 'Akun ini bukan siswa.');

        return view('siswa.presensi.index');
    }

    public function scan(Request $request)
    {
        $user = auth()->user();

        $siswa = DB::table('siswa')
            ->where('user_id', $user->id)
            ->first();

        abort_unless($siswa, 403, 'Hanya siswa yang dapat melakukan presensi.');

        $sesi = SesiPresensi::with('mataPelajaran')->findOrFail($request->sesi);

        abort_unless($sesi->terbuka(), 403, 'Sesi sudah ditutup.');

        // Validasi rentang waktu WIB
        $tz    = 'Asia/Jakarta';
        $mulai = $sesi->mulai_pada->clone()->setTimezone($tz);
        $akhir = $mulai->copy()->addSeconds($sesi->masa_aktif_detik);

        abort_unless(now($tz)->between($mulai, $akhir), 403, 'Di luar rentang waktu presensi.');

        // Pastikan siswa anggota rombel sesi yang aktif
        $anggota = DB::table('siswa_rombel')
            ->where('rombel_id', $sesi->rombel_id)
            ->where('siswa_id', $siswa->id)
            ->where('aktif', 1)
            ->exists();

        abort_unless($anggota, 403, 'Anda bukan anggota rombel sesi ini.');

        // Validasi mapel agama sesuai agama siswa
        $namaMapel = $sesi->mataPelajaran->nama_mapel ?? '';
        $agamaSiswa = trim((string) $siswa->agama);

        if (Str::startsWith($namaMapel, 'Pendidikan Agama')) {
            abort_if(
                $agamaSiswa === '' || !Str::contains($namaMapel, $agamaSiswa),
                403,
                'Presensi ini tidak sesuai dengan agama Anda.'
            );
        }

        $status = 'hadir';

        PresensiSiswa::updateOrCreate(
            [
                'sesi_presensi_id' => $sesi->id,
                'siswa_id' => $siswa->id,
            ],
            [
                'status' => $status,
                'dipindai_pada' => now($tz),
                'ip_address' => $request->ip(),
                'device_fingerprint' => substr(($request->userAgent() ?? ''), 0, 180),
                'diperbarui_oleh' => $user->id,
            ]
        );

        return redirect()
            ->route('siswa.presensi.index')
            ->with('presensi_ok', [
                'status' => $status,
                'waktu'  => now($tz)->translatedFormat('l, d M Y • H:i'),
            ]);
    }

    public function pakaiUrl(Request $request)
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $input = trim($request->input('url'));

        if (str_starts_with($input, '/')) {
            $input = rtrim(config('app.url'), '/') . $input;
        }

        if (!preg_match('~^https?://~i', $input) && str_starts_with($input, 'q/')) {
            $input = rtrim(config('app.url'), '/') . '/' . $input;
        }

        $parts = parse_url($input);
        $path  = $parts['path'] ?? '';

        if (str_starts_with($path, '/q/')) {
            $code = trim(substr($path, 3), '/');
            $signed = Cache::get('qr:' . $code);

            if (!$signed) {
                return back()->with('error', 'QR sudah kedaluwarsa. Minta guru refresh.');
            }

            return redirect()->to($signed);
        }

        if (str_starts_with($path, '/presensi/scan')) {
            return redirect()->to($input);
        }

        return back()->with('error', 'URL tidak valid. Tempel URL presensi dari guru.');
    }
}