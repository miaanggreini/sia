<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelKenaikanController extends Controller
{
    public function form(Rombel $rombel)
    {
        $anggota = $rombel->siswa()
            ->wherePivot('aktif',1)
            ->orderBy('nama')
            ->get();

        $rombelsTujuan = Rombel::where('id','!=',$rombel->id)->get();

        return view('kepsek.rombel.kenaikan', compact('rombel','anggota','rombelsTujuan'));
    }

    public function proses(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'rombel_tujuan_id' => ['required'],
            'siswa_ids' => ['required','array']
        ]);

        DB::transaction(function () use ($data, $rombel) {

            foreach ($data['siswa_ids'] as $sid) {

                DB::table('siswa_rombel')
                    ->where('rombel_id',$rombel->id)
                    ->where('siswa_id',$sid)
                    ->update(['aktif'=>0]);

                DB::table('siswa_rombel')->insert([
                    'rombel_id' => $data['rombel_tujuan_id'],
                    'siswa_id' => $sid,
                    'aktif' => 1,
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
            }
        });

        return redirect()->route('kepala_sekolah.data.rombel.anggota',$rombel)
            ->with('ok','Kenaikan berhasil.');
    }
}