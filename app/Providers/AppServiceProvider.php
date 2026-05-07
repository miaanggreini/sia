<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}
    public function boot(): void
    {
        // Force root URL & scheme sesuai host yang sedang mengakses (termasuk ngrok)
        $root = request()->getSchemeAndHttpHost(); // contoh: https://abc123.ngrok-free.dev
        URL::forceRootUrl($root);

        if (request()->isSecure() || Str::contains($root, 'ngrok-free.dev')) {
            URL::forceScheme('https');
        } else {
            URL::forceScheme('http');
        }

        // ❗ PENTING: set domain cookie session ke host saat ini (dinamis)
        // Ini menghindari masalah subdomain/PSL di device tertentu.
        config(['session.domain' => request()->getHost()]);
    }
}