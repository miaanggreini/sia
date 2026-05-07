<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $guru = optional($user->guru) ?: Guru::where('user_id', $user->id)->first();

        abort_unless($guru, 403, 'Akun ini belum terhubung dengan data guru.');

        $taAktif = TahunAjaran::where('status', 'aktif')->first();

        abort_unless($taAktif, 404, 'Tahun ajaran aktif belum diatur.');

        $taLabel = $taAktif->nama_tahun
            ?? $taAktif->label
            ?? '-';

        $semester = $taAktif->semester ?? null;

        $hariIni = Carbon::now('Asia/Jakarta')
            ->locale('id')
            ->isoFormat('dddd');

        $hariIni = ucfirst(strtolower($hariIni));

        /*
        |--------------------------------------------------------------------------
        | Jadwal hari ini hanya dari tahun ajaran aktif
        |--------------------------------------------------------------------------
        | Jadwal dikunci lewat rombel.tahun_ajaran_id agar jadwal tahun lama
        | tidak ikut tampil di dashboard guru.
        */
        $jadwalHariIni = Jadwal::with(['rombel', 'mataPelajaran', 'mapel'])
            ->where('guru_id', $guru->id)
            ->where('hari', $hariIni)
            ->whereHas('rombel', function ($q) use ($taAktif) {
                $q->where('tahun_ajaran_id', $taAktif->id)
                    ->where(function ($w) {
                        $w->where('aktif', 1)
                            ->orWhereNull('aktif');
                    });
            })
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.index', [
            'guru'          => $guru,
            'hariIni'       => $hariIni,
            'jadwalHariIni' => $jadwalHariIni,
            'taAktif'       => $taAktif,
            'taLabel'       => $taLabel,
            'semester'      => $semester,
        ]);
    }
}