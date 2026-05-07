<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;

class ProfilSayaController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Ambil data guru yang terhubung dengan user login
        $guru = $user->guru ?? Guru::where('user_id', $user->id)->first();

        if (!$guru) {
            abort(403, 'Data guru untuk akun ini tidak ditemukan.');
        }

        return view('guru.profil_saya', compact('guru'));
    }
}
