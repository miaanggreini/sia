<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\TahunAjaran;
use App\Models\Guru;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $guruId = optional($user->guru)->id
            ?? Guru::where('user_id', $user->id)->value('id');

        abort_unless($guruId, 403, 'Akun ini belum terhubung dengan data guru.');

        $ta = TahunAjaran::where('status', 'aktif')->first();

        abort_unless($ta, 404, 'Tahun ajaran aktif belum diatur.');

        /*
        |--------------------------------------------------------------------------
        | Jadwal guru hanya dari rombel tahun ajaran aktif
        |--------------------------------------------------------------------------
        | Jangan hanya filter guru_id, karena guru bisa punya jadwal lama.
        | Jadwal harus dikunci lewat rombel.tahun_ajaran_id.
        */
        $items = Jadwal::query()
            ->where('guru_id', $guruId)
            ->whereHas('rombel', function ($q) use ($ta) {
                $q->where('tahun_ajaran_id', $ta->id)
                  ->where(function ($w) {
                      $w->where('aktif', 1)
                        ->orWhereNull('aktif');
                  });
            })
            ->with(['rombel', 'mapel', 'mataPelajaran'])
            ->orderByRaw("FIELD(hari,'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")
            ->orderBy('jam_mulai')
            ->get();

        return view('guru.jadwal.index', compact('items', 'ta'));
    }

    public function siswa(Request $request, Jadwal $jadwal)
    {
        $user = $request->user();

        $guruId = optional($user->guru)->id
            ?? Guru::where('user_id', $user->id)->value('id');

        abort_unless($guruId, 403, 'Akun ini belum terhubung dengan data guru.');

        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        abort_unless($tahunAktif, 404, 'Tahun ajaran aktif belum diatur.');

        /*
        |--------------------------------------------------------------------------
        | Keamanan akses jadwal
        |--------------------------------------------------------------------------
        | Pastikan jadwal:
        | 1. milik guru login
        | 2. berasal dari rombel tahun ajaran aktif
        */
        $jadwal = Jadwal::with(['rombel', 'mapel', 'mataPelajaran'])
            ->where('id', $jadwal->id)
            ->where('guru_id', $guruId)
            ->whereHas('rombel', function ($q) use ($tahunAktif) {
                $q->where('tahun_ajaran_id', $tahunAktif->id)
                  ->where(function ($w) {
                      $w->where('aktif', 1)
                        ->orWhereNull('aktif');
                  });
            })
            ->firstOrFail();

        $q = trim((string) $request->get('q', ''));

        /*
        |--------------------------------------------------------------------------
        | Ambil siswa aktif di rombel jadwal pada tahun ajaran aktif
        |--------------------------------------------------------------------------
        | Jangan hanya pakai siswaAktif() kalau relasi itu belum filter tahun_ajaran_id.
        */
        $siswa = DB::table('siswa as s')
            ->join('siswa_rombel as sr', 'sr.siswa_id', '=', 's.id')
            ->where('sr.rombel_id', $jadwal->rombel_id)
            ->where('sr.tahun_ajaran_id', $tahunAktif->id)
            ->where('sr.aktif', 1)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('s.nama', 'like', "%{$q}%")
                        ->orWhere('s.nis', 'like', "%{$q}%")
                        ->orWhere('s.nisn', 'like', "%{$q}%");
                });
            })
            ->select([
                's.id',
                's.nis',
                's.nisn',
                's.nama',
            ])
            ->orderBy('s.nama')
            ->paginate(20)
            ->withQueryString();

        return view('guru.jadwal.siswa', compact(
            'jadwal',
            'tahunAktif',
            'siswa',
            'q'
        ));
    }
}