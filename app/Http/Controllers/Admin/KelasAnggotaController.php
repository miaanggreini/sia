<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\SiswaKelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasAnggotaController extends Controller
{
    public function index(Request $request, Kelas $kelas)
    {
        // Anggota aktif di kelas ini
        $anggota = $kelas->anggotaAktif()
            ->orderBy('nama')
            ->get(['siswa.id','siswa.nis','siswa.nama']);

        // Kandidat siswa yang BELUM punya keanggotaan aktif di kelas manapun
        // (bisa difilter nama/nis via ?q=)
        $q = trim((string) $request->get('q'));

        $kandidat = Siswa::query()
            ->where('status', 'aktif')
            ->whereDoesntHave('keanggotaanKelas', fn($sub) => $sub->where('aktif', 1))
            ->when($q, fn($w) => $w->where(function($x) use ($q) {
                $x->where('nis', 'like', "%{$q}%")
                  ->orWhere('nama', 'like', "%{$q}%");
            }))
            ->orderBy('nama')
            ->limit(200) // batasi agar dropdown ringan
            ->get(['id','nis','nama']);

        return view('admin.kelas.anggota', compact('kelas','anggota','kandidat','q'));
    }

public function store(Request $r, Kelas $kelas)
{
    $ta = \App\Models\TahunAjaran::aktifKode();

    $nis = $r->input('nis');
    $ids = $r->input('siswa_ids', []);
    if ($nis) {
        $siswa = \App\Models\Siswa::where('nis', $nis)->firstOrFail();
        $ids = [$siswa->id];
    }
    if (!is_array($ids) || count($ids) === 0) {
        return back()->with('error', 'Pilih minimal satu siswa.')->withInput();
    }

    \DB::transaction(function () use ($ids, $kelas, $ta) {
        foreach ($ids as $sid) {
            // 1) Nonaktifkan keanggotaan aktif sebelumnya → 'pindah kelas'
            \App\Models\SiswaKelas::where('siswa_id', $sid)
                ->where('aktif', 1)
                ->update([
                    'aktif' => 0,
                    'tanggal_selesai' => now(),
                    'keterangan' => 'pindah kelas',
                ]);

            // 1b) (opsional) Jika sebelumnya admin sempat "Keluarkan",
            //     timpa baris non-aktif paling baru di hari yang sama menjadi 'pindah kelas'
            $last = \App\Models\SiswaKelas::where('siswa_id', $sid)
                ->where('aktif', 0)
                ->orderByDesc('updated_at')
                ->first();

            if ($last && $last->tanggal_selesai && $last->tanggal_selesai->isToday()) {
                $last->update(['keterangan' => 'pindah kelas']);
            }

            // 2) Buat baris aktif baru di kelas tujuan
            \App\Models\SiswaKelas::create([
                'siswa_id'      => $sid,
                'kelas_id'      => $kelas->id,
                'tahun_ajaran'  => $ta,
                'aktif'         => 1,
                'tanggal_mulai' => now(),
            ]);
        }
    });

    return back()->with('success', 'Anggota kelas berhasil ditambahkan.');
}

public function destroy(Kelas $kelas, string $siswaNis)
{
    $siswa = \App\Models\Siswa::where('nis',$siswaNis)->firstOrFail();

    \App\Models\SiswaKelas::where('siswa_id', $siswa->id)
        ->where('kelas_id', $kelas->id)
        ->where('aktif', 1)
        ->update([
            'aktif' => 0,
            'tanggal_selesai' => now(),
            'keterangan' => 'dikeluarkan', // standar, bukan "keluar dari kelas"
        ]);

    return back()->with('success','Siswa dikeluarkan dari kelas.');
}

}
