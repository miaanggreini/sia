<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\EkskulAnggota;
use App\Models\EkskulPresensi;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Siswa;
use Illuminate\Support\Carbon;

class EkskulPembinaController extends Controller
{
    protected function getGuruAktif(): Guru
    {
        $user = Auth::user();

        return Guru::where('user_id', $user->id)->firstOrFail();
    }

    protected function getTahunAjaranAktif(): ?TahunAjaran
    {
        return TahunAjaran::where('status', 'aktif')->first();
    }

    // =========================
    // INDEX EKSKUL SAYA
    // =========================
    public function index()
    {
        $user = Auth::user();

        // cari data guru dari relasi user->guru atau fallback by user_id
        $guru = $user?->guru ?? Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            abort(403, 'Data guru tidak ditemukan.');
        }

        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();
        $taId = $tahunAktif?->id;

        // Ambil semua ekskul yang dibina guru ini
        // + hitung jumlah anggota aktif di TA aktif
        $items = Ekskul::query()
            ->where('pembina_id', $guru->id)
            ->withCount([
                'anggota as anggota_aktif_count' => function ($q) use ($taId) {
                    if ($taId) {
                        $q->where('tahun_ajaran_id', $taId);
                    }
                    $q->where('status', 'aktif');
                }
            ])
            ->orderBy('nama')
            ->get();

        return view('guru.ekskul.index', compact('items', 'tahunAktif'));
    }

    // =========================
    // ANGGOTA EKSKUL
    // =========================
    public function anggota(Request $request, Ekskul $ekskul)
    {
        $guru = $this->getGuruAktif();

        if ($ekskul->pembina_id !== $guru->id) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }

        $tahunList = TahunAjaran::orderByDesc('id')->get();

        $tahunDipilih = null;
        if ($request->filled('tahun_ajaran_id')) {
            $tahunDipilih = $tahunList->firstWhere('id', (int) $request->input('tahun_ajaran_id'));
        }

        if (!$tahunDipilih) {
            $tahunDipilih = $tahunList->firstWhere('status', 'aktif') ?? $tahunList->first();
        }

        $anggotaQuery = EkskulAnggota::with('siswa')
            ->where('ekskul_id', $ekskul->id)
            ->where('status', 'aktif');

        if ($tahunDipilih) {
            $anggotaQuery->where('tahun_ajaran_id', $tahunDipilih->id);
        }

        $anggota = $anggotaQuery->orderBy('created_at')->get();

        return view('guru.ekskul.anggota', [
            'guru'         => $guru,
            'ekskul'       => $ekskul,
            'anggota'      => $anggota,
            'tahunList'    => $tahunList,
            'tahunDipilih' => $tahunDipilih,
        ]);
    }

    public function keluarkanAnggota(Request $request, Ekskul $ekskul, EkskulAnggota $anggota)
    {
        $guru = $this->getGuruAktif();

        if ($ekskul->pembina_id !== $guru->id) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }

        if ($anggota->ekskul_id !== $ekskul->id) {
            return back()->with('error', 'Data anggota tidak sesuai dengan ekskul.');
        }

        if ($anggota->status !== 'aktif') {
            return back()->with('error', 'Anggota ini sudah tidak berstatus aktif.');
        }

        $anggota->status         = 'nonaktif'; // enum('aktif','nonaktif')
        $anggota->tanggal_keluar = now()->toDateString();
        $anggota->save();

        return back()->with('success', 'Siswa berhasil dikeluarkan dari ekskul.');
    }

    // =========================
    // PRESENSI EKSKUL - FORM
    // =========================
    public function presensiForm(Request $request, Ekskul $ekskul)
{
    $guru = $this->getGuruAktif();

    if ($ekskul->pembina_id !== $guru->id) {
        abort(403, 'Anda bukan pembina ekskul ini.');
    }

    $tahunAktif = $this->getTahunAjaranAktif();
    if (!$tahunAktif) {
        return back()->with('error', 'Tahun ajaran aktif belum diatur.');
    }

    // tanggal yang sedang dipilih (default: hari ini)
    $tanggal = $request->input('tanggal') ?: now()->toDateString();

    // Anggota aktif di tahun ajaran aktif
    $anggota = EkskulAnggota::with('siswa')
        ->where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->where('status', 'aktif')
        ->orderBy('created_at')
        ->get();

    // Data presensi yang sudah ada di tanggal tsb (untuk form)
    $presensiAda = EkskulPresensi::where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->whereDate('tanggal', $tanggal)
        ->get()
        ->keyBy('siswa_id');

    // REKAP RIWAYAT PER TANGGAL
    $rekapTanggal = EkskulPresensi::selectRaw('tanggal, status, COUNT(*) as jumlah')
        ->where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->groupBy('tanggal', 'status')
        ->orderByDesc('tanggal')
        ->get()
        ->groupBy('tanggal'); // hasil: [ '2025-11-25' => koleksi status ]

    return view('guru.ekskul.presensi', [
        'guru'         => $guru,
        'ekskul'       => $ekskul,
        'tahunAktif'   => $tahunAktif,
        'tanggal'      => $tanggal,
        'anggota'      => $anggota,
        'presensiAda'  => $presensiAda,
        'rekapTanggal' => $rekapTanggal,
    ]);
}




    // =========================
    // PRESENSI EKSKUL - SIMPAN
    // =========================
    public function presensiStore(Request $request, Ekskul $ekskul)
    {
        $guru = $this->getGuruAktif();

        if ($ekskul->pembina_id !== $guru->id) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }

        $tahunAktif = $this->getTahunAjaranAktif();
        if (!$tahunAktif) {
            return back()->with('error', 'Tahun ajaran aktif belum diatur.');
        }

        $data = $request->validate([
            'tanggal'           => ['required', 'date'],
            'presensi'          => ['array'],
            'presensi.*'        => ['in:H,I,S,A'],
        ], [], [
            'tanggal' => 'tanggal presensi',
        ]);

        $tanggal       = $data['tanggal'];
        $presensiInput = $data['presensi'] ?? [];

        // Pastikan hanya anggota aktif yang diolah
        $anggotaIds = EkskulAnggota::where('ekskul_id', $ekskul->id)
            ->where('tahun_ajaran_id', $tahunAktif->id)
            ->where('status', 'aktif')
            ->pluck('siswa_id')
            ->all();

        foreach ($anggotaIds as $siswaId) {
            $status = $presensiInput[$siswaId] ?? 'H'; // default Hadir

            EkskulPresensi::updateOrCreate(
                [
                    'ekskul_id'       => $ekskul->id,
                    'siswa_id'        => $siswaId,
                    'tahun_ajaran_id' => $tahunAktif->id,
                    'tanggal'         => $tanggal,
                ],
                [
                    'pembina_id' => $guru->id,
                    'status'     => $status,
                ]
            );
        }

        return redirect()
            ->route('guru.ekskul.presensi', [$ekskul, 'tanggal' => $tanggal])
            ->with('success', 'Presensi ekskul berhasil disimpan.');
    }
    public function presensiDetail(Ekskul $ekskul, string $tanggal)
{
    $guru = $this->getGuruAktif();

    if ($ekskul->pembina_id !== $guru->id) {
        abort(403, 'Anda bukan pembina ekskul ini.');
    }

    $tahunAktif = $this->getTahunAjaranAktif();
    if (!$tahunAktif) {
        return back()->with('error', 'Tahun ajaran aktif belum diatur.');
    }

    // Normalisasi tanggal (pastikan format Y-m-d)
    try {
        $tanggalObj   = Carbon::parse($tanggal);
        $tanggalQuery = $tanggalObj->toDateString();
    } catch (\Exception $e) {
        abort(404);
    }

    // Anggota aktif tahun ajaran ini
    // Anggota aktif tahun ajaran ini
$anggota = EkskulAnggota::with('siswa')
    ->where('ekskul_id', $ekskul->id)
    ->where('tahun_ajaran_id', $tahunAktif->id)
    ->where('status', 'aktif')
    ->get()
    ->sortBy(function ($row) {
        return $row->siswa->nama ?? '';
    })
    ->values(); // reset key index 0,1,2,...


    // Daftar presensi pada tanggal tersebut
    $presensiHariIni = EkskulPresensi::with('siswa')
        ->where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->whereDate('tanggal', $tanggalQuery)
        ->orderBy('siswa_id')
        ->get()
        ->keyBy('siswa_id');

    // Ringkasan tanggal tersebut
    $ringkasan = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
    foreach ($presensiHariIni as $row) {
        if (isset($ringkasan[$row->status])) {
            $ringkasan[$row->status]++;
        }
    }
    $ringkasan['total'] = array_sum($ringkasan);

    // REKAP KEHADIRAN PER SISWA (SELURUH PERTEMUAN)
    $rekapSiswaRaw = EkskulPresensi::selectRaw('siswa_id, status, COUNT(*) as jumlah')
        ->where('ekskul_id', $ekskul->id)
        ->where('tahun_ajaran_id', $tahunAktif->id)
        ->groupBy('siswa_id', 'status')
        ->get()
        ->groupBy('siswa_id');

    $rekapSiswa = [];
    foreach ($rekapSiswaRaw as $siswaId => $rows) {
        $rekapSiswa[$siswaId] = ['H' => 0, 'I' => 0, 'S' => 0, 'A' => 0];
        foreach ($rows as $row) {
            if (isset($rekapSiswa[$siswaId][$row->status])) {
                $rekapSiswa[$siswaId][$row->status] = $row->jumlah;
            }
        }
    }

    return view('guru.ekskul.presensi_detail', [
        'guru'           => $guru,
        'ekskul'         => $ekskul,
        'tahunAktif'     => $tahunAktif,
        'tanggalObj'     => $tanggalObj,
        'anggota'        => $anggota,
        'presensiHariIni'=> $presensiHariIni,
        'ringkasan'      => $ringkasan,
        'rekapSiswa'     => $rekapSiswa,
    ]);
}
        protected function hitungPredikatDariNilai(int $nilai): string
    {
        if ($nilai >= 90) return 'A';
        if ($nilai >= 80) return 'B';
        if ($nilai >= 70) return 'C';
        return 'D';
    }

     public function penilaianForm(Ekskul $ekskul)
    {
        $user = Auth::user();
        $guru = $user?->guru;

        // pastikan ini benar-benar pembina ekskul ini
        if (!$guru || $ekskul->pembina_id !== $guru->id) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }

        $tahunAktif = TahunAjaran::where('status', 'aktif')->first();

        if (!$tahunAktif) {
            return redirect()
                ->route('guru.ekskul.index')
                ->with('err', 'Tahun ajaran aktif belum diatur.');
        }

        // Anggota aktif di tahun ajaran aktif
        $anggota = EkskulAnggota::query()
            ->join('siswa', 'siswa.id', '=', 'anggota_ekskul.siswa_id')
            ->where('anggota_ekskul.ekskul_id', $ekskul->id)
            ->where('anggota_ekskul.tahun_ajaran_id', $tahunAktif->id)
            ->where('anggota_ekskul.status', 'aktif')
            ->orderBy('siswa.nama')
            ->select('anggota_ekskul.*') // supaya model tetap EkskulAnggota
            ->with('siswa')
            ->get();

        // Rekap presensi per siswa (total pertemuan, hadir, izin, sakit, alfa)
        $rekapPresensi = EkskulPresensi::selectRaw("
        siswa_id,
        COUNT(*) as total,
        SUM(status = 'H') as hadir,
        SUM(status = 'I') as izin,
        SUM(status = 'S') as sakit,
        SUM(status = 'A') as alfa
    ")
    ->where('ekskul_id', $ekskul->id)
    ->where('tahun_ajaran_id', $tahunAktif->id)
    ->groupBy('siswa_id')
    ->get()
    ->keyBy('siswa_id');

        return view('guru.ekskul.penilaian', [
            'ekskul'        => $ekskul,
            'tahunAktif'    => $tahunAktif,
            'anggota'       => $anggota,
            'rekapPresensi' => $rekapPresensi,
        ]);
    }

    /**
     * Simpan penilaian akhir ekskul
     */
    public function penilaianStore(Request $request, Ekskul $ekskul)
    {
        $user = Auth::user();
        $guru = $user?->guru;

        if (!$guru || $ekskul->pembina_id !== $guru->id) {
            abort(403, 'Anda bukan pembina ekskul ini.');
        }

        $tahunAktif = TahunAjaran::where('status', 'aktif')->firstOrFail();

        $nilaiInput     = $request->input('nilai', []);      // [anggota_id => nilai]
        $deskripsiInput = $request->input('deskripsi', []);  // [anggota_id => text]

        foreach ($nilaiInput as $anggotaId => $nilai) {
            /** @var \App\Models\EkskulAnggota|null $anggota */
            $anggota = EkskulAnggota::where('id', $anggotaId)
                ->where('ekskul_id', $ekskul->id)
                ->where('tahun_ajaran_id', $tahunAktif->id)
                ->first();

            if (!$anggota) {
                continue;
            }

            $deskripsi = $deskripsiInput[$anggotaId] ?? null;

            if ($nilai === null || $nilai === '') {
                // kosongkan penilaian
                $anggota->update([
                    'nilai_akhir' => null,
                    'predikat'    => null,
                    'deskripsi'   => $deskripsi,
                ]);
                continue;
            }

            // pastikan integer 0–100
            $nilaiAngka = (int) $nilai;
            if ($nilaiAngka < 0)   $nilaiAngka = 0;
            if ($nilaiAngka > 100) $nilaiAngka = 100;

            $predikat = $this->hitungPredikatDariNilai($nilaiAngka);

            $anggota->update([
                'nilai_akhir' => $nilaiAngka,
                'predikat'    => $predikat,
                'deskripsi'   => $deskripsi,
            ]);
        }

        return redirect()
            ->route('guru.ekskul.penilaian', $ekskul)
            ->with('ok', 'Penilaian ekskul berhasil disimpan.');
    }

}
