<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\Rombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $tahunAktif = TahunAjaran::where('status', 'aktif')
            ->orderByDesc('id')
            ->first();

        $tahunAjaranId = $request->integer('tahun_ajaran_id');

        if (!$tahunAjaranId) {
            $tahunAjaranId = $tahunAktif?->id
                ?? TahunAjaran::orderByDesc('id')->value('id');
        }

        $tahunDipilih = TahunAjaran::find($tahunAjaranId);

        if (!$tahunDipilih) {
            $tahunDipilih = $tahunAktif ?? TahunAjaran::orderByDesc('id')->first();
            $tahunAjaranId = $tahunDipilih?->id;
        }

        $rombelId = $request->query('rombel_id');
        $guruId   = $request->query('guru_id');
        $hari     = $request->query('hari');
        $q        = trim((string) $request->query('q'));

        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $items = Jadwal::with(['rombel.tahunAjaran', 'mataPelajaran', 'guru'])
            ->when($tahunAjaranId, function ($qr) use ($tahunAjaranId) {
                $qr->whereHas('rombel', function ($r) use ($tahunAjaranId) {
                    $r->where('tahun_ajaran_id', $tahunAjaranId);
                });
            })
            ->when($rombelId, fn ($qr) => $qr->where('rombel_id', $rombelId))
            ->when($guruId, fn ($qr) => $qr->where('guru_id', $guruId))
            ->when($hari, fn ($qr) => $qr->where('hari', $hari))
            ->when($q !== '', function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->whereHas('rombel', fn ($r) => $r->where('nama_rombel', 'like', "%{$q}%"))
                        ->orWhereHas('mataPelajaran', fn ($m) => $m->where('nama_mapel', 'like', "%{$q}%"))
                        ->orWhereHas('guru', fn ($g) => $g->where('nama', 'like', "%{$q}%"))
                        ->orWhere('hari', 'like', "%{$q}%")
                        ->orWhere('jam_mulai', 'like', "%{$q}%")
                        ->orWhere('jam_selesai', 'like', "%{$q}%");
                });
            })
            ->orderByRaw("FIELD(hari, '" . implode("','", $dayOrder) . "')")
            ->orderBy('jam_mulai')
            ->paginate(12)
            ->withQueryString();

        $daftarTahunAjaran = TahunAjaran::orderByDesc('id')->get();

        $daftarRombel = Rombel::query()
            ->when($tahunAjaranId, fn ($qr) => $qr->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tahun_ajaran_id']);

        $kelasTabs = $daftarRombel;

        $daftarGuru = Guru::whereHas('jadwal', function ($jadwal) use ($tahunAjaranId) {
                $jadwal->whereHas('rombel', function ($rombel) use ($tahunAjaranId) {
                    $rombel->where('tahun_ajaran_id', $tahunAjaranId);
                });
            })
            ->orderBy('nama')
            ->get(['id', 'nama']);

        $hariOptions = $dayOrder;

        return view('kepsek.jadwal.index', compact(
            'items',
            'daftarTahunAjaran',
            'tahunAktif',
            'tahunDipilih',
            'tahunAjaranId',
            'daftarRombel',
            'kelasTabs',
            'daftarGuru',
            'hariOptions',
            'rombelId',
            'guruId',
            'hari',
            'q'
        ));
    }

    public function show(Jadwal $jadwal)
    {
        $jadwal->load(['rombel.tahunAjaran', 'mataPelajaran', 'guru']);

        return view('kepsek.jadwal.show', [
            'item' => $jadwal,
        ]);
    }

    public function edit(Jadwal $jadwal)
    {
        $jadwal->load(['rombel']);

        $tahunAjaranId = $jadwal->rombel->tahun_ajaran_id ?? null;

        $daftarRombel = Rombel::query()
            ->when($tahunAjaranId, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->orderBy('nama_rombel')
            ->get(['id', 'nama_rombel', 'tingkat', 'tahun_ajaran_id']);

        $daftarGuru = Guru::orderBy('nama')->get(['id', 'nama']);

        $slotOptions = [
            1  => ['JP1', '07:00', '07:45'],
            2  => ['JP2', '07:45', '08:30'],
            3  => ['JP3', '08:30', '09:15'],
            4  => ['JP4', '09:30', '10:15'],
            5  => ['JP5', '10:15', '11:00'],
            6  => ['JP6', '11:00', '11:45'],
            7  => ['JP7', '12:30', '13:15'],
            8  => ['JP8', '13:15', '14:00'],
            9  => ['JP9', '14:00', '14:45'],
            10 => ['JP10', '14:45', '15:30'],
            11 => ['JP11', '15:30', '16:15'],
        ];

        $hariOptions = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $mapelRombel = $jadwal->rombel
            ? $jadwal->rombel->mataPelajaran()
                ->select('mata_pelajaran.id as id', 'mata_pelajaran.nama_mapel')
                ->orderBy('mata_pelajaran.nama_mapel', 'asc')
                ->get()
            : collect();

        return view('kepsek.jadwal.edit', compact(
            'jadwal',
            'daftarRombel',
            'daftarGuru',
            'slotOptions',
            'hariOptions',
            'mapelRombel'
        ));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $v = $request->validate([
            'rombel_id'         => 'required|exists:rombel,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'guru_id'           => 'required|exists:guru,id',
            'hari'              => 'required|string',
            'slot_kode'         => 'required',
            'durasi_jp'         => 'required|integer|min:1|max:12',
        ]);

        $slotMap = [
            1  => ['JP1', '07:00', '07:45'],
            2  => ['JP2', '07:45', '08:30'],
            3  => ['JP3', '08:30', '09:15'],
            4  => ['JP4', '09:30', '10:15'],
            5  => ['JP5', '10:15', '11:00'],
            6  => ['JP6', '11:00', '11:45'],
            7  => ['JP7', '12:30', '13:15'],
            8  => ['JP8', '13:15', '14:00'],
            9  => ['JP9', '14:00', '14:45'],
            10 => ['JP10', '14:45', '15:30'],
            11 => ['JP11', '15:30', '16:15'],
        ];

        $keys = array_keys($slotMap);
        $slotKode = (int) $v['slot_kode'];
        $durasi   = (int) $v['durasi_jp'];

        $startIdx = array_search($slotKode, $keys, true);
        abort_if($startIdx === false, 422, 'Slot mulai tidak valid.');

        $picked = [];

        for ($k = $startIdx; $k < count($keys) && count($picked) < $durasi; $k++) {
            $picked[] = $slotMap[$keys[$k]];
        }

        abort_if(empty($picked), 422, 'Durasi melebihi slot yang tersedia.');

        $jamMulai   = $picked[0][1];
        $jamSelesai = $picked[count($picked) - 1][2];

        $duplikat = Jadwal::where('id', '<>', $jadwal->id)
            ->where('rombel_id', $v['rombel_id'])
            ->where('mata_pelajaran_id', $v['mata_pelajaran_id'])
            ->where('guru_id', $v['guru_id'])
            ->where('hari', $v['hari'])
            ->where('jam_mulai', $jamMulai)
            ->where('jam_selesai', $jamSelesai)
            ->exists();

        if ($duplikat) {
            return back()->withInput()->withErrors([
                'msg' => 'Jadwal yang sama sudah ada.'
            ]);
        }

        $jadwal->update([
            'rombel_id'         => $v['rombel_id'],
            'mata_pelajaran_id' => $v['mata_pelajaran_id'],
            'guru_id'           => $v['guru_id'],
            'hari'              => $v['hari'],
            'slot_kode'         => $slotKode,
            'durasi_jp'         => $durasi,
            'jam_mulai'         => $jamMulai,
            'jam_selesai'       => $jamSelesai,
        ]);

        return redirect()
            ->route('kepala_sekolah.data.jadwal')
            ->with('ok', 'Jadwal berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();

        return redirect()
            ->route('kepala_sekolah.data.jadwal')
            ->with('ok', 'Jadwal berhasil dihapus.');
    }
}