<?php

namespace App\Http\Controllers\Kepsek;

use App\Http\Controllers\Controller;
use App\Models\Rombel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelKelulusanController extends Controller
{
    public function form(Rombel $rombel)
    {
        $siswa = $rombel->siswa()
            ->wherePivot('aktif',1)
            ->get();

        return view('kepsek.rombel.kelulusan', compact('rombel','siswa'));
    }

    public function proses(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'siswa_ids' => ['required','array']
        ]);

        DB::transaction(function () use ($data, $rombel) {

            foreach ($data['siswa_ids'] as $sid) {

                DB::table('siswa')
                    ->where('id',$sid)
                    ->update(['status'=>'lulus']);

                DB::table('siswa_rombel')
                    ->where('rombel_id',$rombel->id)
                    ->where('siswa_id',$sid)
                    ->update(['aktif'=>0]);
            }
        });

        return back()->with('ok','Kelulusan berhasil.');
    }
}