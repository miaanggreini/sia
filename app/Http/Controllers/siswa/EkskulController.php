<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\EkskulPresensi;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EkskulController extends Controller
{
    protected function getSiswaAktif(): Siswa
    {
        $user = auth()->user();

        return Siswa::where('user_id', $user->id)->firstOrFail();
    }

    protected function getTahunAjaranAktif(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')->first();
    }

    protected function getSessionKey(Siswa $siswa, TahunAjaran $tahunAktif): string
    {
        return 'pilihan_ekskul_' . $siswa->id . '_' . $tahunAktif->id;
    }

    protected function isJadwalBentrok(Ekskul $ekskulBaru, $ekskulList): bool
    {
        if (
            !$ekskulBaru->hari ||
            !$ekskulBaru->jam_mulai ||
            !$ekskulBaru->jam_selesai
        ) {
            return false;
        }

        $hariBaru = strtolower(trim($ekskulBaru->hari));
        $mulaiBaru = $ekskulBaru->jam_mulai;
        $selesaiBaru = $ekskulBaru->jam_selesai;

        foreach ($ekskulList as $item) {
            $ekskulLama = $item instanceof Ekskul ? $item : ($item->ekskul ?? null);

            if (
                !$ekskulLama ||
                !$ekskulLama->hari ||
                !$ekskulLama->jam_mulai ||
                !$ekskulLama->jam_selesai
            ) {
                continue;
            }

            if ((int) $ekskulLama->id === (int) $ekskulBaru->id) {
                continue;
            }

            $hariLama = strtolower(trim($ekskulLama->hari));

            if ($hariBaru !== $hariLama) {
                continue;
            }

            $mulaiLama = $ekskulLama->jam_mulai;
            $selesaiLama = $ekskulLama->jam_selesai;

            if ($mulaiBaru < $selesaiLama && $selesaiBaru > $mulaiLama) {
                return true;
            }
        }

        return false;
    }

public function index(Request $request)
{
    $siswa = $this->getSiswaAktif();
    $tahunAktif = $this->getTahunAjaranAktif();

    $daftarEkskul = Ekskul::with('pembina')
        ->orderBy('nama')
        ->get();

    if (!$tahunAktif) {
        return view('siswa.ekskul.index', [
            'daftarEkskul'         => $daftarEkskul,
            'ekskulSaya'           => collect(),
            'ekskulSayaIds'        => [],
            'pilihanSementara'     => collect(),
            'pilihanSementaraIds'  => [],
            'tahunAktif'           => null,
            'tahunSebelumnya'      => null,
            'lanjutEkskul'         => collect(),
            'abaikanLanjutIds'     => [],
        ])->with('error', 'Tahun ajaran aktif belum diatur. Pendaftaran ekskul belum dapat dilakukan.');
    }

    $ekskulSaya = EkskulAnggota::with(['ekskul.pembina'])
        ->where('siswa_id', $siswa->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->where('status', 'aktif')
        ->get();

    $ekskulSayaIds = $ekskulSaya->pluck('ekskul_id')
        ->map(fn ($id) => (int) $id)
        ->all();

    $sessionKey = $this->getSessionKey($siswa, $tahunAktif);

    $pilihanSementaraIds = collect(session($sessionKey, []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->reject(fn ($id) => in_array($id, $ekskulSayaIds))
        ->values()
        ->all();

    session([$sessionKey => $pilihanSementaraIds]);

    $pilihanSementara = Ekskul::with('pembina')
        ->whereIn('id', $pilihanSementaraIds)
        ->orderBy('nama')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Ekskul tahun lalu yang bisa dilanjutkan
    |--------------------------------------------------------------------------
    | Syarat tampil:
    | - siswa pernah ikut ekskul pada tahun ajaran sebelumnya
    | - belum terdaftar pada ekskul itu di tahun ajaran aktif
    | - belum masuk pilihan sementara
    | - belum diabaikan lewat tombol Tidak Lanjut
    */
    $tahunSebelumnya = $this->getTahunAjaranSebelumnya($tahunAktif);

    $abaikanKey = $this->getAbaikanLanjutKey($siswa, $tahunAktif);
    $abaikanLanjutIds = collect(session($abaikanKey, []))
        ->map(fn ($id) => (int) $id)
        ->filter()
        ->unique()
        ->values()
        ->all();

    $lanjutEkskul = collect();

    if ($tahunSebelumnya) {
        $lanjutEkskul = EkskulAnggota::with(['ekskul.pembina', 'tahunAjaran'])
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunSebelumnya->id)
            ->where('status', 'aktif')
            ->whereNotIn('ekskul_id', $ekskulSayaIds)
            ->whereNotIn('ekskul_id', $pilihanSementaraIds)
            ->whereNotIn('ekskul_id', $abaikanLanjutIds)
            ->get()
            ->filter(fn ($anggota) => $anggota->ekskul !== null)
            ->values();
    }

    return view('siswa.ekskul.index', [
        'daftarEkskul'         => $daftarEkskul,
        'ekskulSaya'           => $ekskulSaya,
        'ekskulSayaIds'        => $ekskulSayaIds,
        'pilihanSementara'     => $pilihanSementara,
        'pilihanSementaraIds'  => $pilihanSementaraIds,
        'tahunAktif'           => $tahunAktif,
        'tahunSebelumnya'      => $tahunSebelumnya,
        'lanjutEkskul'         => $lanjutEkskul,
        'abaikanLanjutIds'     => $abaikanLanjutIds,
    ]);
}

    public function daftar(Request $request, Ekskul $ekskul)
    {
        $siswa = $this->getSiswaAktif();
        $tahunAktif = $this->getTahunAjaranAktif();

        if (!$tahunAktif) {
            return back()->with('error', 'Tahun ajaran aktif belum diatur.');
        }

        $sessionKey = $this->getSessionKey($siswa, $tahunAktif);
        $pilihanIds = collect(session($sessionKey, []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $sudahResmi = EkskulAnggota::where('ekskul_id', $ekskul->id)
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAktif->id)
            ->where('status', 'aktif')
            ->exists();

        if ($sudahResmi) {
            return back()->with('info', 'Kamu sudah terdaftar di ekskul ini.');
        }

        if (in_array((int) $ekskul->id, $pilihanIds)) {
            return back()->with('info', 'Ekskul ini sudah ada di pilihan sementara.');
        }

        $ekskulSaya = EkskulAnggota::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAktif->id)
            ->where('status', 'aktif')
            ->get();

        $pilihanSementara = Ekskul::whereIn('id', $pilihanIds)->get();

        if (($ekskulSaya->count() + $pilihanSementara->count()) >= 3) {
            return back()->with('error', 'Maksimal hanya boleh memilih 3 ekskul.');
        }

        $cekBentrok = $ekskulSaya->pluck('ekskul')
            ->filter()
            ->merge($pilihanSementara);

        if ($this->isJadwalBentrok($ekskul, $cekBentrok)) {
            return back()->with('error', 'Jadwal ekskul ini bentrok dengan ekskul yang sudah kamu ikuti atau sudah kamu pilih sementara.');
        }

        $pilihanIds[] = (int) $ekskul->id;
        session([$sessionKey => array_values(array_unique($pilihanIds))]);

        return back()->with('success', 'Ekskul ' . $ekskul->nama . ' masuk ke pilihan sementara.');
    }

    public function hapusPilihan(Ekskul $ekskul)
    {
        $siswa = $this->getSiswaAktif();
        $tahunAktif = $this->getTahunAjaranAktif();

        if (!$tahunAktif) {
            return back()->with('error', 'Tahun ajaran aktif belum diatur.');
        }

        $sessionKey = $this->getSessionKey($siswa, $tahunAktif);

        $pilihanIds = collect(session($sessionKey, []))
            ->map(fn ($id) => (int) $id)
            ->reject(fn ($id) => $id === (int) $ekskul->id)
            ->values()
            ->all();

        session([$sessionKey => $pilihanIds]);

        return back()->with('success', 'Pilihan ekskul berhasil dibatalkan.');
    }

    public function simpanPilihan()
    {
        $siswa = $this->getSiswaAktif();
        $tahunAktif = $this->getTahunAjaranAktif();

        if (!$tahunAktif) {
            return back()->with('error', 'Tahun ajaran aktif belum diatur.');
        }

        $sessionKey = $this->getSessionKey($siswa, $tahunAktif);

        $pilihanIds = collect(session($sessionKey, []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($pilihanIds)) {
            return back()->with('info', 'Belum ada pilihan ekskul yang perlu disimpan.');
        }

        $ekskulSaya = EkskulAnggota::with('ekskul')
            ->where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAktif->id)
            ->where('status', 'aktif')
            ->get();

        $ekskulSayaIds = $ekskulSaya->pluck('ekskul_id')->map(fn ($id) => (int) $id)->all();

        $pilihanIds = collect($pilihanIds)
            ->reject(fn ($id) => in_array($id, $ekskulSayaIds))
            ->values()
            ->all();

        $pilihanSementara = Ekskul::whereIn('id', $pilihanIds)->get();

        if ($pilihanSementara->isEmpty()) {
            session()->forget($sessionKey);
            return back()->with('info', 'Tidak ada pilihan baru yang perlu disimpan.');
        }

        if (($ekskulSaya->count() + $pilihanSementara->count()) > 3) {
            return back()->with('error', 'Jumlah ekskul melebihi batas maksimal 3.');
        }

        $cekBentrok = $ekskulSaya->pluck('ekskul')->filter()->values();

        foreach ($pilihanSementara as $pilihan) {
            if ($this->isJadwalBentrok($pilihan, $cekBentrok)) {
                return back()->with('error', 'Pilihan ' . $pilihan->nama . ' memiliki jadwal bentrok. Batalkan salah satu pilihan terlebih dahulu.');
            }

            $cekBentrok->push($pilihan);
        }

        DB::transaction(function () use ($pilihanSementara, $siswa, $tahunAktif) {
            foreach ($pilihanSementara as $ekskul) {
                EkskulAnggota::updateOrCreate(
                    [
                        'ekskul_id'       => $ekskul->id,
                        'siswa_id'        => $siswa->id,
                        'tahun_ajaran_id' => $tahunAktif->id,
                    ],
                    [
                        'status'          => 'aktif',
                        'tanggal_gabung'  => now()->toDateString(),
                        'tanggal_keluar'  => null,
                    ]
                );
            }
        });

        session()->forget($sessionKey);

        return back()->with('success', 'Pilihan ekskul berhasil disimpan. Kamu resmi terdaftar sebagai anggota ekskul.');
    }

public function lanjutkan(Request $request, Ekskul $ekskul)
{
    $siswa = $this->getSiswaAktif();
    $tahunAktif = $this->getTahunAjaranAktif();

    if (!$tahunAktif) {
        return back()->with('error', 'Tahun ajaran aktif belum diatur.');
    }

    $tahunSebelumnya = $this->getTahunAjaranSebelumnya($tahunAktif);

    if (!$tahunSebelumnya) {
        return back()->with('error', 'Tahun ajaran sebelumnya tidak ditemukan.');
    }

    $pernahIkut = EkskulAnggota::where('ekskul_id', $ekskul->id)
        ->where('siswa_id', $siswa->id)
        ->where('tahun_ajaran_id', $tahunSebelumnya->id)
        ->where('status', 'aktif')
        ->exists();

    if (!$pernahIkut) {
        return back()->with('error', 'Kamu tidak memiliki riwayat keanggotaan pada ekskul ini di tahun ajaran sebelumnya.');
    }

    $sudahAktif = EkskulAnggota::where('ekskul_id', $ekskul->id)
        ->where('siswa_id', $siswa->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->where('status', 'aktif')
        ->exists();

    if ($sudahAktif) {
        return back()->with('info', 'Kamu sudah terdaftar di ekskul ini pada tahun ajaran aktif.');
    }

    $ekskulSaya = EkskulAnggota::with('ekskul')
        ->where('siswa_id', $siswa->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->where('status', 'aktif')
        ->get();

    if ($ekskulSaya->count() >= 3) {
        return back()->with('error', 'Maksimal hanya boleh mengikuti 3 ekskul pada tahun ajaran aktif.');
    }

    $cekBentrok = $ekskulSaya->pluck('ekskul')->filter();

    if ($this->isJadwalBentrok($ekskul, $cekBentrok)) {
        return back()->with('error', 'Jadwal ekskul ini bentrok dengan ekskul yang sudah kamu ikuti pada tahun ajaran aktif.');
    }

    EkskulAnggota::updateOrCreate(
        [
            'ekskul_id'       => $ekskul->id,
            'siswa_id'        => $siswa->id,
            'tahun_ajaran_id' => $tahunAktif->id,
        ],
        [
            'status'          => 'aktif',
            'tanggal_gabung'  => now()->toDateString(),
            'tanggal_keluar'  => null,
        ]
    );

    $abaikanKey = $this->getAbaikanLanjutKey($siswa, $tahunAktif);

    $abaikanIds = collect(session($abaikanKey, []))
        ->map(fn ($id) => (int) $id)
        ->reject(fn ($id) => $id === (int) $ekskul->id)
        ->values()
        ->all();

    session([$abaikanKey => $abaikanIds]);

    return back()->with('success', 'Ekskul ' . $ekskul->nama . ' berhasil dilanjutkan pada tahun ajaran aktif.');
}

 public function show(Request $request, Ekskul $ekskul)
{
    $siswa = $this->getSiswaAktif();
    $tahunAktif = $this->getTahunAjaranAktif();

    if (!$tahunAktif) {
        return redirect()
            ->route('siswa.ekskul.index')
            ->with('error', 'Tahun ajaran aktif belum diatur.');
    }

    /*
    |--------------------------------------------------------------------------
    | Ambil semua riwayat keanggotaan siswa pada ekskul ini
    |--------------------------------------------------------------------------
    */
    $riwayatAnggota = EkskulAnggota::with(['tahunAjaran', 'ekskul.pembina'])
        ->where('ekskul_id', $ekskul->id)
        ->where('siswa_id', $siswa->id)
        ->orderByDesc('tahun_ajaran_id')
        ->get();

    if ($riwayatAnggota->isEmpty()) {
        return redirect()
            ->route('siswa.ekskul.index')
            ->with('error', 'Kamu belum memiliki riwayat keanggotaan pada ekskul tersebut.');
    }

    /*
    |--------------------------------------------------------------------------
    | Tahun ajaran yang sedang dilihat
    |--------------------------------------------------------------------------
    */
    $tahunAjaranId = $request->integer('tahun_ajaran_id');

    if (!$tahunAjaranId) {
        $anggotaTahunAktif = $riwayatAnggota
            ->firstWhere('tahun_ajaran_id', $tahunAktif->id);

        $tahunAjaranId = $anggotaTahunAktif
            ? $tahunAktif->id
            : optional($riwayatAnggota->first())->tahun_ajaran_id;
    }

    $anggota = $riwayatAnggota
        ->firstWhere('tahun_ajaran_id', $tahunAjaranId);

    if (!$anggota) {
        return redirect()
            ->route('siswa.ekskul.show', $ekskul->id)
            ->with('error', 'Riwayat ekskul pada tahun ajaran tersebut tidak ditemukan.');
    }

    $tahunDipilih = $anggota->tahunAjaran;

    $tab = $request->input('tab', 'presensi');

    /*
    |--------------------------------------------------------------------------
    | Presensi berdasarkan tahun ajaran yang dipilih
    |--------------------------------------------------------------------------
    */
    $presensiList = collect();

    $summary = [
        'hadir' => 0,
        'izin'  => 0,
        'sakit' => 0,
        'alfa'  => 0,
        'total' => 0,
    ];

    $rows = EkskulPresensi::where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAjaranId)
        ->where('siswa_id', $siswa->id)
        ->orderBy('tanggal')
        ->get();

    foreach ($rows as $row) {
        $status = $row->status ?? null;

        if (!$status) {
            continue;
        }

        $presensiList->push((object) [
            'tanggal'    => $row->tanggal,
            'status'     => $status,
            'keterangan' => $row->keterangan,
        ]);

        switch (strtoupper((string) $status)) {
            case 'H':
                $summary['hadir']++;
                break;
            case 'I':
                $summary['izin']++;
                break;
            case 'S':
                $summary['sakit']++;
                break;
            case 'A':
                $summary['alfa']++;
                break;
        }

        $summary['total']++;
    }

    /*
    |--------------------------------------------------------------------------
    | Nilai akhir ekskul
    |--------------------------------------------------------------------------
    | Karena dari struktur sebelumnya nilai akhir ekskul kemungkinan tersimpan
    | pada record anggota_ekskul, kita kirim sebagai object agar view bisa
    | membaca nilai_akhir, predikat, dan deskripsi.
    */
    $nilaiAkhirEkskul = null;

    $nilaiAkhir = $anggota->nilai_akhir
        ?? $anggota->nilai
        ?? $anggota->skor
        ?? null;

    $predikat = $anggota->predikat
        ?? null;

    $deskripsi = $anggota->deskripsi
        ?? $anggota->keterangan
        ?? null;

    if ($nilaiAkhir !== null || $predikat !== null || $deskripsi !== null) {
        $nilaiAkhirEkskul = (object) [
            'nilai_akhir' => $nilaiAkhir,
            'predikat'    => $predikat,
            'deskripsi'   => $deskripsi,
        ];
    }

    return view('siswa.ekskul.show', [
        'ekskul'            => $ekskul->load('pembina'),
        'tahunAktif'        => $tahunAktif,
        'tahunDipilih'      => $tahunDipilih,
        'tahunAjaranId'     => $tahunAjaranId,
        'riwayatAnggota'    => $riwayatAnggota,
        'anggota'           => $anggota,
        'presensiList'      => $presensiList,
        'summary'           => $summary,
        'tab'               => $tab,
        'nilaiAkhirEkskul'  => $nilaiAkhirEkskul,
    ]);
}

public function tidakLanjut(Request $request, Ekskul $ekskul)
{
    $siswa = $this->getSiswaAktif();
    $tahunAktif = $this->getTahunAjaranAktif();

    if (!$tahunAktif) {
        return back()->with('error', 'Tahun ajaran aktif belum diatur.');
    }

    $abaikanKey = $this->getAbaikanLanjutKey($siswa, $tahunAktif);

    $abaikanIds = collect(session($abaikanKey, []))
        ->map(fn ($id) => (int) $id)
        ->push((int) $ekskul->id)
        ->unique()
        ->values()
        ->all();

    session([$abaikanKey => $abaikanIds]);

    return back()->with('info', 'Ekskul ' . $ekskul->nama . ' tidak dilanjutkan pada tahun ajaran ini.');
}

    public function presensi(Ekskul $ekskul)
    {
        return redirect()->route('siswa.ekskul.show', [
            'ekskul' => $ekskul->id,
            'tab'    => 'presensi',
        ]);
    }

    protected function getTahunAjaranSebelumnya(TahunAjaran $tahunAktif): ?TahunAjaran
{
    return TahunAjaran::where('id', '<', $tahunAktif->id)
        ->orderByDesc('id')
        ->first();
}

protected function getAbaikanLanjutKey(Siswa $siswa, TahunAjaran $tahunAktif): string
{
    return 'abaikan_lanjut_ekskul_' . $siswa->id . '_' . $tahunAktif->id;
}
}