<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Support\Facades\Auth;

class ProfilSayaController extends Controller
{
    public function show()
    {
        $user  = Auth::user();
        $siswa = Siswa::where('user_id', $user->id)->firstOrFail();

        return view('siswa.profil_saya', compact('siswa'));
    }
}
