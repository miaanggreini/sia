<?php

namespace App\Http\Controllers\Api\Sinta;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    /**
     * LIST MASTER SISWA
     * GET /api/sinta/master/siswa
     */
    public function index(Request $request)
    {
        $q = $request->query('q');
        $status = $request->query('status');
        $jk = $request->query('jk');
        $tahunMasuk = $request->query('tahun_masuk');

        $orderBy = $request->query('order_by', 'nama');
        $orderDir = $request->query('order_dir', 'asc');
        $perPage = (int) $request->query('per_page', 20);

        $allowedSort = ['id', 'nama', 'nis', 'nisn', 'jenis_kelamin', 'status', 'tahun_masuk', 'tanggal_lahir'];
        if (!in_array($orderBy, $allowedSort, true)) {
            $orderBy = 'nama';
        }

        $allowedDir = ['asc', 'desc'];
        if (!in_array($orderDir, $allowedDir, true)) {
            $orderDir = 'asc';
        }

        $query = Siswa::query()
            ->select([
                'id',
                'nama',
                'nis',
                'nisn',
                'jenis_kelamin',
                'status',
                'tanggal_lahir',
                'tahun_masuk',
                'foto',
            ])
            ->when($q, function ($s) use ($q) {
                $s->where(function ($x) use ($q) {
                    $x->where('nama', 'like', "%{$q}%")
                        ->orWhere('nis', 'like', "%{$q}%")
                        ->orWhere('nisn', 'like', "%{$q}%");
                });
            })
            ->when($status, fn ($s) => $s->where('status', $status))
            ->when($jk, fn ($s) => $s->where('jenis_kelamin', $jk))
            ->when($tahunMasuk, fn ($s) => $s->where('tahun_masuk', $tahunMasuk))
            ->orderBy($orderBy, $orderDir);

        $data = $query->paginate($perPage)->appends($request->query());

        return response()->json([
            'status' => true,
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
            'data' => collect($data->items())->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nama' => $s->nama,
                    'nis' => $s->nis,
                    'nisn' => $s->nisn,
                    'jenis_kelamin' => $s->jenis_kelamin,
                    'status' => $s->status,
                    'tanggal_lahir' => optional($s->tanggal_lahir)->format('Y-m-d'),
                    'tahun_masuk' => $s->tahun_masuk,
                    'foto' => $s->foto,
                ];
            })->values(),
        ]);
    }

    /**
     * DETAIL SISWA LENGKAP
     * GET /api/sinta/master/siswa/{id}
     */
    public function show($id)
    {
        $siswa = Siswa::query()->find($id);

        if (!$siswa) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        $rombelAktif = $this->resolveRombelAktifSiswa($siswa);

        return response()->json([
            'status' => true,
            'data' => $this->formatSiswaDetail($siswa, $rombelAktif),
        ]);
    }

    /**
     * DETAIL SISWA BERDASARKAN NIS / NISN
     * GET /api/sinta/siswa/{nis}
     */
    public function showByNis($nis)
    {
        $siswa = Siswa::query()
            ->where('nis', $nis)
            ->orWhere('nisn', $nis)
            ->first();

        if (!$siswa) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        $rombelAktif = $this->resolveRombelAktifSiswa($siswa);

        return response()->json([
            'status' => true,
            'data' => $this->formatSiswaDetail($siswa, $rombelAktif),
        ]);
    }

    /**
     * ROMBEL AKTIF SISWA
     * GET /api/sinta/master/siswa/{id}/rombel-aktif
     */
    public function rombelAktif($id)
    {
        $siswa = Siswa::query()->find($id);

        if (!$siswa) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Siswa tidak ditemukan.',
            ], 404);
        }

        $tahunAjaranAktif = $this->resolveTahunAjaranAktif();
        $tahunAjaranAktifId = $tahunAjaranAktif?->id;

        $query = $siswa->rombelAktif()
            ->with([
                'tahunAjaran:id,nama_tahun,semester,status',
                'waliKelas:id,nama,nip,nuptk,email',
                'ruangKelas:id,nama',
            ]);

        if ($tahunAjaranAktifId) {
            $query->wherePivot('tahun_ajaran_id', $tahunAjaranAktifId);
        }

        $data = $query
            ->wherePivot('aktif', 1)
            ->orderByDesc('siswa_rombel.tahun_ajaran_id')
            ->orderByDesc('siswa_rombel.id')
            ->get()
            ->map(fn ($rombel) => $this->formatRombelAktif($rombel))
            ->values();

        return response()->json([
            'status' => true,
            'data' => $data,
            'tahun_ajaran_aktif' => $tahunAjaranAktif ? [
                'id' => $tahunAjaranAktif->id,
                'nama_tahun' => $tahunAjaranAktif->nama_tahun,
                'semester' => $tahunAjaranAktif->semester,
                'status' => $tahunAjaranAktif->status,
                'tanggal_mulai' => optional($tahunAjaranAktif->tanggal_mulai)->format('Y-m-d'),
                'tanggal_selesai' => optional($tahunAjaranAktif->tanggal_selesai)->format('Y-m-d'),
            ] : null,
        ]);
    }

    /**
     * Menentukan rombel aktif siswa berdasarkan tahun ajaran aktif.
     *
     * Catatan penting:
     * Tidak menggunakan $siswa->rombel(), karena relasi tersebut tidak ada.
     * Relasi yang dipakai adalah $siswa->rombelAktif().
     */
    private function resolveRombelAktifSiswa(Siswa $siswa)
    {
        if (!method_exists($siswa, 'rombelAktif')) {
            return null;
        }

        $tahunAjaranAktif = $this->resolveTahunAjaranAktif();
        $tahunAjaranAktifId = $tahunAjaranAktif?->id;

        $baseQuery = $siswa->rombelAktif()
            ->with([
                'tahunAjaran:id,nama_tahun,semester,status',
                'waliKelas:id,nama,nip,nuptk,email',
                'ruangKelas:id,nama',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Prioritas 1: rombel aktif pada tahun ajaran aktif
        |--------------------------------------------------------------------------
        */
        if ($tahunAjaranAktifId) {
            $rombel = (clone $baseQuery)
                ->wherePivot('tahun_ajaran_id', $tahunAjaranAktifId)
                ->wherePivot('aktif', 1)
                ->orderByDesc('siswa_rombel.id')
                ->first();

            if ($rombel) {
                return $rombel;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Prioritas 2: fallback rombel pivot aktif terbaru
        |--------------------------------------------------------------------------
        | Dipakai agar API tetap mengembalikan data jika data tahun ajaran aktif
        | belum lengkap, tetapi tetap tidak memanggil relasi rombel() yang tidak ada.
        */
        return (clone $baseQuery)
            ->wherePivot('aktif', 1)
            ->orderByDesc('siswa_rombel.tahun_ajaran_id')
            ->orderByDesc('siswa_rombel.id')
            ->first();
    }

    private function resolveTahunAjaranAktif(): ?TahunAjaran
    {
        return TahunAjaran::query()
            ->where('status', 'aktif')
            ->orderByDesc('id')
            ->first();
    }

    private function formatSiswaDetail(Siswa $siswa, $rombelAktif = null): array
    {
        return [
            'id' => $siswa->id,
            'user_id' => $siswa->user_id,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'nama' => $siswa->nama,
            'tempat_lahir' => $siswa->tempat_lahir,
            'tanggal_lahir' => optional($siswa->tanggal_lahir)->format('Y-m-d'),
            'jenis_kelamin' => $siswa->jenis_kelamin,
            'agama' => $siswa->agama,
            'foto' => $siswa->foto,
            'alamat' => $siswa->alamat,
            'email' => $siswa->email,
            'no_hp' => $siswa->no_hp,
            'status' => $siswa->status,
            'tahun_masuk' => $siswa->tahun_masuk,
            'jalur_penerimaan' => $siswa->jalur_penerimaan,
            'kebutuhan_khusus' => $siswa->kebutuhan_khusus,

            'nama_ayah' => $siswa->nama_ayah,
            'nik_ayah' => $siswa->nik_ayah,
            'status_ayah' => $siswa->status_ayah,
            'pekerjaan_ayah' => $siswa->pekerjaan_ayah,
            'pendidikan_ayah' => $siswa->pendidikan_ayah,
            'no_hp_ayah' => $siswa->no_hp_ayah,
            'alamat_ayah' => $siswa->alamat_ayah,

            'nama_ibu' => $siswa->nama_ibu,
            'nik_ibu' => $siswa->nik_ibu,
            'status_ibu' => $siswa->status_ibu,
            'pekerjaan_ibu' => $siswa->pekerjaan_ibu,
            'pendidikan_ibu' => $siswa->pendidikan_ibu,
            'no_hp_ibu' => $siswa->no_hp_ibu,
            'alamat_ibu' => $siswa->alamat_ibu,

            'rombel_aktif' => $rombelAktif ? $this->formatRombelAktif($rombelAktif) : null,

            'created_at' => optional($siswa->created_at)->toDateTimeString(),
            'updated_at' => optional($siswa->updated_at)->toDateTimeString(),
        ];
    }

    private function formatRombelAktif($rombel): array
    {
        return [
            'id' => $rombel->id,
            'nama_rombel' => $rombel->nama_rombel,
            'tingkat' => $rombel->tingkat,
            'guru_id' => $rombel->guru_id,
            'menu_rombel_id' => $rombel->menu_rombel_id ?? null,
            'ruang_kelas_id' => $rombel->ruang_kelas_id ?? null,
            'tahun_ajaran_id' => $rombel->tahun_ajaran_id,

            'pivot_tahun_ajaran_id' => $rombel->pivot->tahun_ajaran_id ?? null,
            'aktif' => isset($rombel->pivot->aktif)
                ? (int) $rombel->pivot->aktif
                : (isset($rombel->aktif) ? (int) $rombel->aktif : null),
            'status_pilihan' => $rombel->pivot->status_pilihan ?? null,

            'wali_kelas' => $rombel->waliKelas ? [
                'id' => $rombel->waliKelas->id,
                'nama' => $rombel->waliKelas->nama,
                'nip' => $rombel->waliKelas->nip,
                'nuptk' => $rombel->waliKelas->nuptk,
                'email' => $rombel->waliKelas->email,
            ] : null,

            'ruang_kelas' => $rombel->ruangKelas ? [
                'id' => $rombel->ruangKelas->id,
                'nama' => $rombel->ruangKelas->nama,
            ] : null,

            'tahun_ajaran' => $rombel->tahunAjaran ? [
                'id' => $rombel->tahunAjaran->id,
                'nama_tahun' => $rombel->tahunAjaran->nama_tahun,
                'semester' => $rombel->tahunAjaran->semester,
                'status' => $rombel->tahunAjaran->status,
            ] : null,
        ];
    }
}