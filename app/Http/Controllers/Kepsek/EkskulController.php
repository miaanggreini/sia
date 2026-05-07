<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Ekskul;
use App\Models\Guru;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

class EkskulController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->q;

        $items = Ekskul::query()
            ->with('pembina')
            ->when($q, function ($qr) use ($q) {
                $like = '%' . $q . '%';
                $qr->where(function ($sub) use ($like) {
                    $sub->where('nama', 'like', $like)
                        ->orWhere('hari', 'like', $like)
                        ->orWhere('lokasi', 'like', $like)
                        ->orWhereHas('pembina', function ($g) use ($like) {
                            $g->where('nama', 'like', $like);
                        });
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('kepsek.ekskul.index', compact('items', 'q'));
    }

    public function edit(Ekskul $ekskul)
    {
        $daftarGuru = Guru::orderBy('nama')->get();

        return view('kepsek.ekskul.edit', compact('ekskul', 'daftarGuru'));
    }

    public function update(Request $request, Ekskul $ekskul)
    {
        $data = $this->validated($request, $ekskul->id);

        $ekskul->update($data);

        return redirect()
            ->route('kepala_sekolah.data.ekskul')
            ->with('ok', 'Data ekskul berhasil diperbarui.');
    }

    public function destroy(Ekskul $ekskul)
    {
        try {
            $ekskul->delete();

            return redirect()
                ->route('kepala_sekolah.data.ekskul')
                ->with('ok', 'Data ekskul berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()
                ->route('kepala_sekolah.data.ekskul')
                ->with('err', 'Ekskul tidak dapat dihapus karena masih digunakan pada data anggota, presensi, atau penilaian.');
        }
    }

    public function anggota(Request $request, Ekskul $ekskul)
    {
        $tab = $request->get('tab', 'anggota');

        $tahunList = TahunAjaran::orderByRaw("status = 'aktif' DESC")
            ->orderByDesc('tanggal_mulai')
            ->get();

        $tahunDipilih = null;
        if ($request->filled('tahun_ajaran_id')) {
            $tahunDipilih = TahunAjaran::find($request->tahun_ajaran_id);
        }
        if (!$tahunDipilih) {
            $tahunDipilih = TahunAjaran::where('status', 'aktif')->first() ?? $tahunList->first();
        }

        $anggota = $ekskul->anggota()
            ->with(['siswa'])
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->where(function ($q) {
                $q->whereNull('status')
                    ->orWhere('status', 'aktif');
            })
            ->orderBy('tanggal_gabung')
            ->orderBy('id')
            ->get();

        $riwayatAnggota = $ekskul->anggota()
            ->with(['siswa'])
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->where('status', 'nonaktif')
            ->orderByDesc('tanggal_keluar')
            ->orderByDesc('id')
            ->get();

        $presensiSummary = $ekskul->presensi()
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->selectRaw("
                tanggal,
                SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as hadir,
                SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as izin,
                SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as sakit,
                SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as alfa,
                COUNT(*) as total
            ")
            ->groupBy('tanggal')
            ->orderByDesc('tanggal')
            ->get();

        $bulanOptions = $ekskul->presensi()
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as value, DATE_FORMAT(tanggal, '%m') as bulan, DATE_FORMAT(tanggal, '%Y') as tahun")
            ->distinct()
            ->orderByDesc('value')
            ->get()
            ->map(function ($row) {
                $namaBulan = [
                    '01' => 'Januari',
                    '02' => 'Februari',
                    '03' => 'Maret',
                    '04' => 'April',
                    '05' => 'Mei',
                    '06' => 'Juni',
                    '07' => 'Juli',
                    '08' => 'Agustus',
                    '09' => 'September',
                    '10' => 'Oktober',
                    '11' => 'November',
                    '12' => 'Desember',
                ];

                return [
                    'value' => $row->value,
                    'label' => ($namaBulan[$row->bulan] ?? $row->bulan) . ' ' . $row->tahun,
                ];
            })
            ->values();

        $bulanDipilih = $request->get('bulan');
        if (!$bulanDipilih) {
            $bulanDipilih = $bulanOptions->first()['value'] ?? now()->format('Y-m');
        }

        $presensiTanggalList = $ekskul->presensi()
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanDipilih])
            ->select('tanggal')
            ->distinct()
            ->orderBy('tanggal')
            ->pluck('tanggal')
            ->map(function ($tgl) {
                return \Carbon\Carbon::parse($tgl)->format('Y-m-d');
            })
            ->values();

        $presensiRaw = $ekskul->presensi()
            ->with('siswa')
            ->when($tahunDipilih, function ($q) use ($tahunDipilih) {
                $q->where('tahun_ajaran_id', $tahunDipilih->id);
            })
            ->whereRaw("DATE_FORMAT(tanggal, '%Y-%m') = ?", [$bulanDipilih])
            ->get();

        $presensiGrouped = $presensiRaw->groupBy('siswa_id');

        $presensiMatrixRows = $anggota->map(function ($anggotaRow) use ($presensiGrouped, $presensiTanggalList) {
            $rows = $presensiGrouped->get($anggotaRow->siswa_id, collect());

            $statuses = [];
            foreach ($presensiTanggalList as $tgl) {
                $tglKey = \Carbon\Carbon::parse($tgl)->format('Y-m-d');

                $found = $rows->first(function ($item) use ($tglKey) {
                    return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') === $tglKey;
                });

                $statuses[$tglKey] = $found->status ?? null;
            }

            return (object) [
                'nama_siswa' => $anggotaRow->siswa->nama ?? '-',
                'nis'        => $anggotaRow->siswa->nis ?? '-',
                'nisn'       => $anggotaRow->siswa->nisn ?? '-',
                'statuses'   => $statuses,
                'hadir'      => $rows->where('status', 'H')->count(),
                'izin'       => $rows->where('status', 'I')->count(),
                'sakit'      => $rows->where('status', 'S')->count(),
                'alfa'       => $rows->where('status', 'A')->count(),
            ];
        });

        return view('kepsek.ekskul.anggota', compact(
            'ekskul',
            'tab',
            'anggota',
            'riwayatAnggota',
            'tahunList',
            'tahunDipilih',
            'presensiSummary',
            'presensiTanggalList',
            'presensiMatrixRows',
            'bulanOptions',
            'bulanDipilih'
        ));
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'nama' => [
                'required',
                'string',
                'max:100',
                Rule::unique('ekskul', 'nama')->ignore($ignoreId),
            ],
            'pembina_id' => ['required', 'exists:guru,id'],
            'hari' => ['nullable', Rule::in(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'])],
            'jam_mulai' => ['nullable', 'date_format:H:i'],
            'jam_selesai' => ['nullable', 'date_format:H:i', 'after:jam_mulai'],
            'lokasi' => ['nullable', 'string', 'max:150'],
        ], [], [
            'nama' => 'nama ekskul',
            'pembina_id' => 'pembina',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
        ]);
    }
}