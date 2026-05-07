<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\Rombel;
use App\Models\Guru;
use Illuminate\Http\Request;

class ScheduleMonitorController extends Controller
{
    public function index(Request $request)
    {
        $rombelId = $request->query('rombel_id');
        $guruId   = $request->query('guru_id');
        $hari     = $request->query('hari');
        $q        = $request->query('q');

        // Urutan hari (senin–jumat)
        $dayOrder = ['Senin','Selasa','Rabu','Kamis','Jumat'];

        $items = Jadwal::with(['rombel','mataPelajaran','guru'])
            ->when($rombelId, fn($qr) => $qr->where('rombel_id', $rombelId))
            ->when($guruId,   fn($qr) => $qr->where('guru_id', $guruId))
            ->when($hari,     fn($qr) => $qr->where('hari', $hari))
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->whereHas('rombel', fn($r)=>$r->where('nama_rombel','like',"%{$q}%"))
                      ->orWhereHas('mataPelajaran', fn($m)=>$m->where('nama_mapel','like',"%{$q}%"))
                      ->orWhereHas('guru', fn($g)=>$g->where('nama','like',"%{$q}%"))
                      ->orWhere('hari','like',"%{$q}%");
                });
            })
            ->orderByRaw("FIELD(hari, '".implode("','",$dayOrder)."')")
            ->orderBy('jam_mulai')
            ->paginate(12)
            ->withQueryString();

        $daftarRombel = Rombel::orderBy('nama_rombel')->get(['id','nama_rombel']);
        $daftarGuru   = Guru::orderBy('nama')->get(['id','nama']);
        $hariOptions  = $dayOrder;

        return view('kepsek.jadwal.index', compact(
            'items','daftarRombel','daftarGuru','hariOptions','rombelId','guruId','hari','q'
        ));
    }
}
