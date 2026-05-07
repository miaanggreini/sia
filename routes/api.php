<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Sinta\SiswaController;
use App\Http\Controllers\Api\Sinta\GuruController;
use App\Http\Controllers\Api\Sinta\MapelController;
use App\Http\Controllers\Api\Sinta\RombelController;
use App\Http\Controllers\Api\Sinta\TahunAjaranController;
use App\Http\Controllers\Api\Sinta\JadwalController;
use App\Http\Controllers\Api\Sinta\NilaiController;
use App\Http\Controllers\Api\Sinta\PresensiController;
use App\Http\Controllers\Api\Sinta\EkskulController;
use App\Http\Controllers\Api\Sinta\DashboardStatController;

Route::prefix('sinta')
    ->middleware('sinta_token')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD STATS UNTUK SINTA
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard/summary',                [DashboardStatController::class, 'summary']);
        Route::get('/dashboard/presensi-today',         [DashboardStatController::class, 'presensiToday']);
        Route::get('/dashboard/presensi-trend7',        [DashboardStatController::class, 'presensiTrend7']);
        Route::get('/dashboard/presensi-trend-monthly', [DashboardStatController::class, 'presensiTrendMonthly']);
        Route::get('/dashboard/rekap-nilai-kelas',      [DashboardStatController::class, 'rekapNilaiPerKelas']);
        Route::get('/dashboard/rekap-nilai-tingkat',    [DashboardStatController::class, 'rekapNilaiPerTingkat']);
        Route::get('/dashboard/rata-nilai-global',      [DashboardStatController::class, 'rataRataNilaiGlobal']);
        Route::get('/dashboard/top-siswa',              [DashboardStatController::class, 'top5Siswa']);

        /*
        |--------------------------------------------------------------------------
        | MASTER LIST
        |--------------------------------------------------------------------------
        */
        Route::get('/master/siswa',        [SiswaController::class,       'index']);
        Route::get('/master/guru',         [GuruController::class,        'index']);
        Route::get('/master/mapel',        [MapelController::class,       'index']);
        Route::get('/master/rombel',       [RombelController::class,      'index']);
        Route::get('/master/tahun-ajaran', [TahunAjaranController::class, 'index']);
        Route::get('/master/ekskul',       [EkskulController::class,      'index']);

        /*
        |--------------------------------------------------------------------------
        | TAHUN AJARAN AKTIF
        |--------------------------------------------------------------------------
        | Route ini wajib diletakkan sebelum /master/tahun-ajaran/{id}
        | agar "tahun-ajaran-aktif" tidak terbaca sebagai parameter {id}.
        */
        Route::get('/master/tahun-ajaran-aktif', [TahunAjaranController::class, 'aktif']);

        /*
        |--------------------------------------------------------------------------
        | ALIAS API GURU
        |--------------------------------------------------------------------------
        */
        Route::get('/guru',      [GuruController::class, 'index']);
        Route::get('/guru/{id}', [GuruController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | ROMBEL CHILD
        |--------------------------------------------------------------------------
        | Route child wajib diletakkan sebelum /master/rombel/{id}
        | supaya /anggota, /jadwal, dan /presensi tidak tertangkap sebagai detail.
        */
        Route::get('/master/rombel-by-guru/{guruId}',  [RombelController::class,   'byGuru']);
        Route::get('/master/rombel/{id}/anggota',      [RombelController::class,   'anggota']);
        Route::get('/master/rombel/{id}/jadwal',       [RombelController::class,   'jadwal']);
        Route::get('/master/rombel/{id}/presensi',     [PresensiController::class, 'byRombel']);
        Route::get('/master/rombel/{id}/sesi-presensi',[PresensiController::class, 'rombelSessions']);

        /*
        |--------------------------------------------------------------------------
        | MASTER DETAIL
        |--------------------------------------------------------------------------
        */
        Route::get('/master/siswa/{id}',        [SiswaController::class,       'show']);
        Route::get('/master/guru/{id}',         [GuruController::class,        'show']);
        Route::get('/master/mapel/{id}',        [MapelController::class,       'show']);
        Route::get('/master/rombel/{id}',       [RombelController::class,      'show']);
        Route::get('/master/tahun-ajaran/{id}', [TahunAjaranController::class, 'show']);
        Route::get('/master/ekskul/{id}',       [EkskulController::class,      'show']);

        /*
        |--------------------------------------------------------------------------
        | MASTER JADWAL
        |--------------------------------------------------------------------------
        */
        Route::get('/master/jadwal',      [JadwalController::class, 'index']);
        Route::get('/master/jadwal/{id}', [JadwalController::class, 'show']);

        /*
        |--------------------------------------------------------------------------
        | DETAIL PRESENSI PER SESI
        |--------------------------------------------------------------------------
        */
        Route::get('/presensi/sesi/{id}', [PresensiController::class, 'sesiDetail']);

        /*
        |--------------------------------------------------------------------------
        | API SISWA UNTUK APLIKASI SINTA
        |--------------------------------------------------------------------------
        */
        Route::get('/siswa/{nis}',          [SiswaController::class,    'showByNis']);
        Route::get('/siswa/{nis}/jadwal',   [JadwalController::class,   'bySiswa']);
        Route::get('/siswa/{nis}/nilai',    [NilaiController::class,    'bySiswa']);
        Route::get('/siswa/{nis}/presensi', [PresensiController::class, 'bySiswa']);
    });