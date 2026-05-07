<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        $this->generateCaptcha();

        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha_answer' => ['required', 'numeric'],
        ], [
            'captcha_answer.required' => 'Captcha wajib diisi.',
            'captcha_answer.numeric' => 'Captcha harus berupa angka.',
        ], [
            'login' => 'Email / NUPTK / NIP / NIS',
            'captcha_answer' => 'Captcha',
        ]);

        // Validasi captcha dulu
        if ((int) $data['captcha_answer'] !== (int) session('captcha_result')) {
            $this->generateCaptcha();

            return back()
                ->withErrors([
                    'captcha_answer' => 'Captcha salah. Silakan coba lagi.',
                ])
                ->withInput($request->only('login'));
        }

        $login = trim($data['login']);
        $password = $data['password'];

        $user = null;

        // 1) Login via email
        if (str_contains($login, '@')) {
            $user = User::where('email', $login)->first();
        }

        // 2) Login guru via NUPTK / NIP
        if (!$user) {
            $guru = Guru::with('user')
                ->where('nuptk', $login)
                ->orWhere('nip', $login)
                ->first();

            if ($guru) {
                if ($guru->user) {
                    $user = $guru->user;
                } else {
                    // Auto-provision akun user dari data guru pada login pertama
                    $dob = $guru->tanggal_lahir
                        ? Carbon::parse($guru->tanggal_lahir)->format('dmY')
                        : null;

                    if ($dob && $password === $dob) {
                        $user = User::create([
                            'name' => $guru->nama,
                            'email' => null,
                            'role' => 'guru',
                            'password' => Hash::make($password),
                        ]);

                        $guru->user_id = $user->id;
                        $guru->save();
                    } else {
                        $this->generateCaptcha();

                        return back()
                            ->withErrors([
                                'login' => 'Kredensial tidak cocok.',
                            ])
                            ->withInput($request->only('login'));
                    }
                }
            }
        }

        // 3) Login siswa via NIS
        if (!$user) {
            $siswa = Siswa::with('user')
                ->where('nis', $login)
                ->first();

            if ($siswa) {
                if ($siswa->user) {
                    $user = $siswa->user;
                } else {
                    // Auto-provision akun user dari data siswa pada login pertama
                    $dob = $siswa->tanggal_lahir
                        ? Carbon::parse($siswa->tanggal_lahir)->format('dmY')
                        : null;

                    if ($dob && $password === $dob) {
                        $user = User::create([
                            'name' => $siswa->nama,
                            'email' => null,
                            'role' => 'siswa',
                            'password' => Hash::make($password),
                        ]);

                        $siswa->user_id = $user->id;
                        $siswa->save();
                    } else {
                        $this->generateCaptcha();

                        return back()
                            ->withErrors([
                                'login' => 'Kredensial tidak cocok.',
                            ])
                            ->withInput($request->only('login'));
                    }
                }
            }
        }

        // 4) Jika user ditemukan, cek password
        if ($user && Hash::check($password, $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            // Hapus captcha setelah login sukses
            session()->forget([
                'captcha_num1',
                'captcha_num2',
                'captcha_result',
            ]);

            return match ($user->role) {
                'admin' => redirect()->intended('/admin/dashboard'),
                'guru' => redirect()->intended('/guru/dashboard'),
                'siswa' => redirect()->intended('/siswa/dashboard'),
                'kepala_sekolah' => redirect()->intended('/kepala/dashboard'),
                default => redirect()->intended('/'),
            };
        }

        // Kalau login gagal, captcha generate ulang
        $this->generateCaptcha();

        return back()
            ->withErrors([
                'login' => 'Kredensial tidak cocok.',
            ])
            ->withInput($request->only('login'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function generateCaptcha(): void
    {
        $num1 = rand(1, 9);
        $num2 = rand(1, 9);

        session([
            'captcha_num1' => $num1,
            'captcha_num2' => $num2,
            'captcha_result' => $num1 + $num2,
        ]);
    }
}