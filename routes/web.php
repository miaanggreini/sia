<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminController as AdminDashboardController;

// Admin
use App\Http\Controllers\Admin\GuruController              as AdminGuruController;
use App\Http\Controllers\Admin\SiswaController             as AdminSiswaController;
use App\Http\Controllers\Admin\MapelController             as AdminMapelController;
use App\Http\Controllers\Admin\JadwalController            as AdminJadwalController;
use App\Http\Controllers\Admin\PresensiController          as AdminPresensiController;
use App\Http\Controllers\Admin\PenilaianController         as AdminPenilaianController;
use App\Http\Controllers\Admin\PengumumanController        as AdminPengumumanController;
use App\Http\Controllers\Admin\MenuRombelController;
use App\Http\Controllers\Admin\PeriodePemilihanMenuController;
use App\Http\Controllers\Admin\PenempatanRombelController;
use App\Http\Controllers\Admin\RombelController            as AdminRombelController;
use App\Http\Controllers\Admin\EkskulController            as AdminEkskulController;
use App\Http\Controllers\Admin\TahunAjaranController       as AdminTahunAjaranController;
use App\Http\Controllers\Admin\RuangKelasController        as AdminRuangKelasController;
use App\Http\Controllers\Admin\RombelKenaikanController;
use App\Http\Controllers\Admin\RombelKelulusanController;
use App\Http\Controllers\Admin\EkskulPresensiController as AdminEkskulPresensiController;

// Guru
use App\Http\Controllers\Guru\DashboardController          as GuruDashboardController;
use App\Http\Controllers\Guru\JadwalController             as GuruJadwalController;
use App\Http\Controllers\Guru\PresensiGuruController;
use App\Http\Controllers\Guru\PenilaianController          as GuruPenilaianController;
use App\Http\Controllers\Guru\WaliKelasController;
use App\Http\Controllers\Guru\KehadiranController          as GuruKehadiranController;
use App\Http\Controllers\Guru\WaliMonitoringController;
use App\Http\Controllers\Guru\ProfilSayaController;
use App\Http\Controllers\Guru\EkskulPembinaController;

// Siswa
use App\Http\Controllers\Siswa\DashboardController         as SiswaDashboardController;
use App\Http\Controllers\Siswa\JadwalController            as SiswaJadwalController;
use App\Http\Controllers\Siswa\NilaiController             as SiswaNilaiController;
use App\Http\Controllers\Siswa\PengumumanController        as SiswaPengumumanController;
use App\Http\Controllers\Siswa\KehadiranSayaController;
use App\Http\Controllers\Siswa\PreferensiMenuController;
use App\Http\Controllers\Siswa\PresensiSiswaController;
use App\Http\Controllers\Siswa\ProfilSayaController        as SiswaProfilSayaController;
use App\Http\Controllers\Siswa\EkskulController         as EkskulSiswaController;

// Kepsek
use App\Http\Controllers\Kepsek\DashboardController        as KepsekDashboardController;
use App\Http\Controllers\Kepsek\AnnouncementApprovalController;
use App\Http\Controllers\Kepsek\GradeMonitorController;
use App\Http\Controllers\Kepsek\PresensiMonitoringController;
use App\Http\Controllers\Kepsek\RekapRombelController;
use App\Http\Controllers\Kepsek\GuruController as KepsekGuruController;
use App\Http\Controllers\Kepsek\SiswaController as KepsekSiswaController;
use App\Http\Controllers\Kepsek\MapelController as KepsekMapelController;
use App\Http\Controllers\Kepsek\RuangKelasController as KepsekRuangKelasController;
use App\Http\Controllers\Kepsek\TahunAjaranController as KepsekTahunAjaranController;
use App\Http\Controllers\Kepsek\EkskulController as KepsekEkskulController;
use App\Http\Controllers\Kepsek\JadwalController as KepsekJadwalController;

// Profile (Breeze)
use App\Http\Controllers\ProfileController;

// =====================
// ROOT & DASHBOARD
// =====================

// Root -> login
Route::redirect('/', '/login');

// Dashboard universal -> redirect sesuai role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if (!$user) return redirect('/');

    return match ($user->role) {
        'admin'          => redirect()->route('admin.dashboard'),
        'guru'           => redirect()->route('guru.dashboard'),
        'siswa'          => redirect()->route('siswa.dashboard'),
        'kepala_sekolah' => redirect()->route('kepala_sekolah.dashboard'),
        default          => redirect('/'),
    };
})->middleware(['auth'])->name('dashboard');


// =====================
// ADMIN AREA
// =====================
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        // =======================
        // MASTER DATA
        // =======================
        Route::resource('guru', AdminGuruController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'show']);

        Route::resource('siswa', AdminSiswaController::class);
        Route::resource('mapel', AdminMapelController::class);

        // Mapel -> Kelompok (static blade)
        Route::get('mapel/kelompok', function () {
            return view('admin.mapel.kelompok_index');
        })->name('mapel.kelompok');

        // Ruang Kelas
        Route::resource('ruang-kelas', AdminRuangKelasController::class)
            ->parameters(['ruang-kelas' => 'ruang']);

        // =======================
        // JADWAL
        // =======================
        Route::resource('jadwal', AdminJadwalController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy', 'show']);

        Route::get('jadwal/export/pdf', [AdminJadwalController::class, 'exportPdf'])
            ->name('jadwal.export.pdf');
        
        Route::post('jadwal/cek-bentrok', [AdminJadwalController::class, 'cekBentrok'])
            ->name('jadwal.cek-bentrok');

         // ===== PRESENSI (ADMIN) =====
        Route::prefix('presensi')->name('presensi.')->group(function () {

            // Buku absen per rombel (tampilan accordion rombel)
            Route::get('/', [AdminPresensiController::class, 'index'])
                ->name('index');

            // Detail harian (opsional, dipakai kalau masih ada link lama)
            Route::get('/harian', [AdminPresensiController::class, 'showHarian'])
                ->name('harian');

            // Buku absen per mapel di dalam 1 rombel
            Route::get('/rombel/{rombel}/mapel/{mapel}', [AdminPresensiController::class, 'rombelMapel'])
                ->name('rombel-mapel');

            // Route "show" lama (kalau masih ada yang pakai admin.presensi.show)
            Route::get('/{presensi}', [AdminPresensiController::class, 'show'])
                ->name('show');
                 });

        // =======================
        // PENILAIAN (Monitoring)
        // =======================
        Route::get('penilaian', [AdminPenilaianController::class, 'index'])
            ->name('penilaian.index');

        Route::get('penilaian/{jadwal}', [AdminPenilaianController::class, 'show'])
            ->name('penilaian.show');
        Route::get('/penilaian/{jadwal}/pdf', [App\Http\Controllers\Admin\PenilaianController::class, 'pdf'])
        ->name('penilaian.pdf');
        // =======================
        // PENGUMUMAN
        // =======================
        Route::resource('pengumuman', AdminPengumumanController::class);

        Route::post('pengumuman/{pengumuman}/submit',
            [AdminPengumumanController::class, 'submit'])
            ->name('pengumuman.submit');

        Route::get('pengumuman/{pengumuman}/publish',
            [AdminPengumumanController::class, 'publishForm'])
            ->name('pengumuman.publish.form');

        Route::post('pengumuman/{pengumuman}/publish',
            [AdminPengumumanController::class, 'publishStore'])
            ->name('pengumuman.publish.store');

        // =======================
        // PEMILIHAN ROMBEL
        // =======================

        // Menu Rombel
        Route::resource('menu-rombel', MenuRombelController::class)
            ->parameters(['menu-rombel' => 'menuRombel']);

Route::get('/pemilihan/rombel-final', [PenempatanRombelController::class, 'rombelFinal'])
    ->name('pemilihan.rombel-final');

Route::post('/pemilihan/rombel-final', [PenempatanRombelController::class, 'simpanRombelFinal'])
    ->name('pemilihan.rombel-final.simpan');
        // Menu Rombel -> Bentuk Rombel langsung dari menu
        Route::get('menu-rombel/{menu}/bentuk',
            [MenuRombelController::class, 'bentukForm'])
            ->name('menu-rombel.bentuk.form');

        Route::post('menu-rombel/{menu}/bentuk',
            [MenuRombelController::class, 'bentukStore'])
            ->name('menu-rombel.bentuk.store');

        // Periode Pemilihan Menu
        Route::get('periode-pemilihan-menu',
            [PeriodePemilihanMenuController::class, 'index'])
            ->name('periode.index');

        Route::get('periode-pemilihan-menu/buat',
            [PeriodePemilihanMenuController::class, 'create'])
            ->name('periode.create');

        Route::post('periode-pemilihan-menu',
            [PeriodePemilihanMenuController::class, 'store'])
            ->name('periode.store');

        Route::post('periode-pemilihan-menu/{periode}/buka',
            [PeriodePemilihanMenuController::class, 'buka'])
            ->name('periode.buka');

        Route::post('periode-pemilihan-menu/{periode}/tutup',
            [PeriodePemilihanMenuController::class, 'tutup'])
            ->name('periode.tutup');

        Route::post('periode-pemilihan-menu/{periode}/tempatkan-otomatis',
            [PeriodePemilihanMenuController::class, 'tempatkanOtomatis'])
            ->name('periode.tempatkan');

        Route::get('periode-pemilihan-menu/{periode}/hasil',
            [PeriodePemilihanMenuController::class, 'hasil'])
            ->name('periode.hasil');

        Route::post('periode-pemilihan-menu/{periode}/kunci',
            [PeriodePemilihanMenuController::class, 'kunci'])
            ->name('periode.kunci');

        Route::post('periode-pemilihan-menu/{periode}/bentuk-rombel',
            [PeriodePemilihanMenuController::class, 'bentukRombel'])
            ->name('periode.bentukRombel');

        // Rekap & Penempatan lintas menu
        Route::prefix('pemilihan')->name('pemilihan.')->group(function () {
            Route::get('rekap', [PenempatanRombelController::class, 'index'])
                ->name('rekap');

            Route::post('jalankan', [PenempatanRombelController::class, 'jalankan'])
                ->name('jalankan');

            Route::post('commit', [PenempatanRombelController::class, 'commit'])
                ->name('commit');

            Route::post('reset', [PenempatanRombelController::class, 'reset'])
                ->name('reset');

            Route::post('geser', [PenempatanRombelController::class, 'geser'])
                ->name('geser');

            
            
        });

        // =======================
        // EKSKUL & TAHUN AJARAN
        // =======================
        Route::resource('ekskul', AdminEkskulController::class);
        Route::get('ekskul/{ekskul}/anggota', [AdminEkskulController::class, 'anggota'])
    ->name('ekskul.anggota');

// Keluarkan anggota dari ekskul
Route::post('ekskul/{ekskul}/anggota/{anggota}/keluarkan', [AdminEkskulController::class, 'keluarkanAnggota'])
    ->name('ekskul.anggota.keluarkan');

    Route::get('ekskul/{ekskul}/presensi', [AdminEkskulPresensiController::class, 'index'])
    ->name('ekskul.presensi');

Route::get('ekskul/{ekskul}/presensi/{tanggal}', [AdminEkskulPresensiController::class, 'detail'])
    ->name('ekskul.presensi.detail');

Route::resource('tahun_ajaran', AdminTahunAjaranController::class);

Route::patch('tahun_ajaran/{tahun_ajaran}/aktifkan', [AdminTahunAjaranController::class, 'setAktif'])
    ->name('tahun_ajaran.setAktif');

        // =======================
        // ROMBEL
        // =======================
        Route::resource('rombel', AdminRombelController::class);

        Route::get('rombel/{rombel}/anggota',
            [AdminRombelController::class, 'anggota'])
            ->name('rombel.anggota');

        Route::post('rombel/{rombel}/anggota',
            [AdminRombelController::class, 'storeAnggota'])
            ->name('rombel.anggota.store');

        Route::delete('rombel/{rombel}/anggota/{siswa}',
            [AdminRombelController::class, 'destroyAnggota'])
            ->name('rombel.anggota.destroy');

        // API dropdown mapel untuk form jadwal
        Route::get('rombel/{rombel}/mapel',
            [AdminRombelController::class, 'mapelJson'])
            ->name('rombel.mapel');

        // KENAIKAN ROMBEL (ADMIN)
        Route::get('rombel/{rombel}/kenaikan', [RombelKenaikanController::class, 'form'])
            ->name('rombel.kenaikan.form');

        Route::post('rombel/{rombel}/kenaikan', [RombelKenaikanController::class, 'proses'])
            ->name('rombel.kenaikan.proses');

            Route::get('rombel/{rombel}/kelulusan', [RombelKelulusanController::class, 'form'])
            ->name('rombel.kelulusan.form');

        Route::post('rombel/{rombel}/kelulusan', [RombelKelulusanController::class, 'proses'])
            ->name('rombel.kelulusan.proses');

        Route::get('ekskul/{ekskul}/anggota', [AdminEkskulController::class, 'anggota'])->name('ekskul.anggota');
        Route::post('ekskul/{ekskul}/anggota/{anggota}/keluarkan', [AdminEkskulController::class, 'keluarkanAnggota'])->name('ekskul.anggota.keluarkan');


    });
// =====================
// QR & PRESENSI SCAN (umum)
// =====================
Route::middleware(['auth', 'signed:relative'])
    ->get('/presensi/scan', [PresensiSiswaController::class, 'scan'])
    ->name('presensi.scan');

Route::get('/q/{code}', [PresensiGuruController::class, 'redirectShort'])
    ->name('qr.short');


// =====================
// GURU AREA
// =====================
Route::middleware(['auth', 'role:guru'])
    ->prefix('guru')
    ->name('guru.')
    ->group(function () {

        // DASHBOARD
        Route::get('/dashboard', [GuruDashboardController::class, 'index'])
            ->name('dashboard');

        // JADWAL GURU
        Route::get('/jadwal', [GuruJadwalController::class, 'index'])
            ->name('jadwal.index');

Route::get('/presensi', [PresensiGuruController::class, 'index'])->name('presensi.index');

Route::post('/presensi/sesi', [PresensiGuruController::class, 'mulai'])
    ->name('presensi.sesi.mulai');

Route::get('/presensi/sesi/{sesi}', [PresensiGuruController::class, 'tampil'])
    ->name('presensi.sesi.tampil');

Route::get('/presensi/sesi/{sesi}/qr', [PresensiGuruController::class, 'payloadQr'])
    ->name('presensi.sesi.qr');

Route::post('/presensi/sesi/{sesi}/override', [PresensiGuruController::class, 'override'])
    ->name('presensi.sesi.override');

Route::post('/presensi/sesi/{sesi}/tutup', [PresensiGuruController::class, 'tutup'])
    ->name('presensi.sesi.tutup');

Route::get('/presensi/mapel-by-rombel', [PresensiGuruController::class, 'mapelByRombel'])
    ->name('presensi.mapel.by-rombel');

    Route::get('/presensi/sesi/{sesi}/status-json', [PresensiGuruController::class, 'statusJson'])
    ->name('presensi.sesi.status-json');
        // PENILAIAN (GURU MAPEL)
        Route::get('/penilaian', [GuruPenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/penilaian/input', [GuruPenilaianController::class, 'create'])->name('penilaian.create');
        Route::post('/penilaian/simpan', [GuruPenilaianController::class, 'store'])->name('penilaian.store');
        Route::post('/penilaian/finalize', [GuruPenilaianController::class, 'finalize'])->name('penilaian.finalize');

        // WALI KELAS: DATA KELAS & SISWA
        Route::get('kelas-wali', [WaliKelasController::class, 'kelasSaya'])->name('wali.kelas-saya');
        Route::get('kelas-wali/{rombel}', [WaliKelasController::class, 'daftarSiswa'])->name('wali.daftar-siswa');
        Route::get('wali/siswa/{rombel}/{siswa}', [WaliKelasController::class, 'siswaShow'])->name('wali.siswa.show');
        Route::get('wali/siswa/{rombel}/{siswa}/nilai', [WaliKelasController::class, 'siswaNilai'])->name('wali.siswa.nilai');

        // Profil guru
        Route::get('/profil-saya', [ProfilSayaController::class, 'index'])
            ->name('profil');

        // WALI KELAS: MONITORING PRESENSI & PENILAIAN
        Route::prefix('wali')->name('wali.')->group(function () {

            // Monitoring Presensi (wali kelas)
            Route::get('monitoring-presensi', [WaliMonitoringController::class, 'presensi'])
                ->name('monitoring-presensi');

            // DETAIL: pakai ID sesi presensi
            Route::get('monitoring-presensi/{sesi}', [WaliMonitoringController::class, 'presensiDetail'])
                ->name('monitoring-presensi.detail');

            // Rekap kehadiran per siswa
            Route::get('monitoring-presensi/{rombel}/{mapel}/siswa/{siswa}', [WaliMonitoringController::class, 'presensiSiswa'])
                ->name('monitoring-presensi.siswa');

            // Monitoring Penilaian (wali kelas)
            Route::get('monitoring-penilaian', [WaliMonitoringController::class, 'penilaian'])
                ->name('monitoring-penilaian');

            Route::get('monitoring-penilaian/{siswa}', [WaliMonitoringController::class, 'penilaianDetail'])
                ->name('monitoring-penilaian.detail');



            // Alias lama
            Route::get('monitoring', [WaliMonitoringController::class, 'penilaian'])
                ->name('monitoring');

        
        });

        // MONITORING KEHADIRAN (FITUR KHUSUS GURU)
        Route::get('/kehadiran', [GuruKehadiranController::class, 'index'])
            ->name('kehadiran.index');

Route::post('/kehadiran/bulk-set', [GuruKehadiranController::class, 'bulkSetStatus'])
    ->name('kehadiran.bulk-set');
    Route::post('/presensi/sesi/{sesi}/bulk-override', [PresensiGuruController::class, 'bulkOverride'])
    ->name('presensi.sesi.bulk-override');

        Route::get('/kehadiran/{rombel}/{mapel}/siswa/{siswa}', [GuruKehadiranController::class, 'showSiswa'])
            ->name('kehadiran.siswa');

        Route::get('/kehadiran/{rombel}/{mapel}/{siswa}/export', [GuruKehadiranController::class, 'exportSiswa'])
            ->name('kehadiran.siswa.export');

                    // EKSKUL (PEMBINA)
        Route::prefix('ekskul')->name('ekskul.')->group(function () {
            Route::get('/', [EkskulPembinaController::class, 'index'])
                ->name('index');

            Route::get('/{ekskul}/anggota', [EkskulPembinaController::class, 'anggota'])
                ->name('anggota');

            Route::post('/{ekskul}/anggota/{anggota}/keluarkan',
                [EkskulPembinaController::class, 'keluarkanAnggota'])
                ->name('anggota.keluarkan');

                 Route::get('/{ekskul}/penilaian', [EkskulPembinaController::class, 'penilaianForm'])
            ->name('penilaian');

        Route::post('/{ekskul}/penilaian', [EkskulPembinaController::class, 'penilaianStore'])
            ->name('penilaian.store');

                Route::get('/{ekskul}/presensi', [EkskulPembinaController::class, 'presensiForm'])
        ->name('presensi');

    Route::post('/{ekskul}/presensi', [EkskulPembinaController::class, 'presensiStore'])
        ->name('presensi.store');
    Route::get('/{ekskul}/presensi/{tanggal}', [EkskulPembinaController::class, 'presensiDetail'])
        ->name('presensi.detail');

        });

        Route::get('/jadwal/{jadwal}/siswa', [\App\Http\Controllers\Guru\JadwalController::class, 'siswa'])
            ->name('jadwal.siswa');



            
    });


// =====================
// SISWA AREA
// =====================
Route::middleware(['auth', 'role:siswa'])
    ->prefix('siswa')
    ->name('siswa.')
    ->group(function () {
        Route::get('/dashboard', [SiswaDashboardController::class, 'index'])->name('dashboard');

        Route::get('/jadwal', [SiswaJadwalController::class, 'index'])->name('jadwal.index');
        Route::get('/presensi', [PresensiSiswaController::class, 'index'])->name('presensi.index');
        Route::get('/nilai', [SiswaNilaiController::class, 'index'])->name('nilai.index');
        Route::get('/nilai/detail', [SiswaNilaiController::class, 'detail'])->name('nilai.detail');
        Route::get('/nilai/download-pdf', [SiswaNilaiController::class, 'downloadPdf'])->name('nilai.download-pdf');
        Route::get('/pengumuman', [SiswaPengumumanController::class, 'index'])->name('pengumuman.index');
        Route::get('/pengumuman/{pengumuman}', [SiswaPengumumanController::class, 'show'])->name('pengumuman.show');

        Route::get('/ekskul', [EkskulSiswaController::class, 'index'])->name('ekskul.index');

        Route::post('/ekskul/simpan-pilihan', [EkskulSiswaController::class, 'simpanPilihan'])->name('ekskul.simpan-pilihan');

        Route::post('/ekskul/{ekskul}/daftar', [EkskulSiswaController::class, 'daftar'])->name('ekskul.daftar');
        Route::delete('/ekskul/{ekskul}/pilihan', [EkskulSiswaController::class, 'hapusPilihan'])->name('ekskul.hapus-pilihan');

        Route::get('/ekskul/{ekskul}', [EkskulSiswaController::class, 'show'])->name('ekskul.show');
        Route::get('/ekskul/{ekskul}/presensi', [EkskulSiswaController::class, 'presensi'])->name('ekskul.presensi');

Route::post('/ekskul/{ekskul}/lanjutkan', [EkskulSiswaController::class, 'lanjutkan'])
    ->name('ekskul.lanjutkan');

Route::post('/ekskul/{ekskul}/tidak-lanjut', [EkskulSiswaController::class, 'tidakLanjut'])
    ->name('ekskul.tidak-lanjut');
            // Scan presensi via URL/kode
        Route::get('/presensi/kode', [PresensiSiswaController::class, 'formKode'])->name('presensi.kode');
        Route::post('/presensi/url', [PresensiSiswaController::class, 'pakaiUrl'])->name('presensi.url.pakai');

        // Kehadiran Saya
        Route::get('/kehadiran', [KehadiranSayaController::class, 'index'])->name('kehadiran.index');
        Route::get('/kehadiran/export', [KehadiranSayaController::class, 'exportCsv'])->name('kehadiran.export');
        Route::get('/kehadiran/{rombel}/{mapel}', [KehadiranSayaController::class, 'show'])->name('kehadiran.show');

        // Pemilihan Menu Rombel (XI)
        Route::get('pilih-menu', [PreferensiMenuController::class, 'index'])->name('menu.index');
        Route::post('pilih-menu', [PreferensiMenuController::class, 'store'])->name('menu.store');

        // Profil siswa
        Route::get('/profil-saya', [SiswaProfilSayaController::class, 'show'])
            ->name('profil-saya');
    });


// =====================
// KEPALA SEKOLAH AREA
// =====================
Route::middleware(['auth','role:kepala_sekolah'])
    ->prefix('kepala')->as('kepala_sekolah.')
    ->group(function () {

        // DASHBOARD
        Route::get('/dashboard', [KepsekDashboardController::class,'index'])
            ->name('dashboard');

        // =======================
        // PERSETUJUAN PENGUMUMAN
        // =======================
        Route::get('/approvals/pengumuman', [AnnouncementApprovalController::class,'index'])
            ->name('approvals.pengumuman.index');

        Route::post('/approvals/pengumuman/{announcement}/approve',
            [AnnouncementApprovalController::class,'approve'])
            ->name('approvals.pengumuman.approve');

        Route::post('/approvals/pengumuman/{announcement}/reject',
            [AnnouncementApprovalController::class,'reject'])
            ->name('approvals.pengumuman.reject');
            Route::get('/persetujuan-pengumuman/{pengumuman}', [\App\Http\Controllers\Kepsek\AnnouncementApprovalController::class, 'show'])
    ->name('persetujuan.show');  

        // =======================
        // MONITORING KHUSUS KEPSEK
        // =======================
        Route::get('/monitor/presensi', [PresensiMonitoringController::class, 'index'])
            ->name('monitor.presensi.index');

        Route::get('/monitor/presensi/detail', [PresensiMonitoringController::class, 'detail'])
            ->name('monitor.presensi.detail');

        Route::get('/monitor/presensi', [PresensiMonitoringController::class, 'index'])
            ->name('monitor.presensi.index');

        Route::get('/monitor/presensi/rombel/{rombel}/mapel/{mapel}', [PresensiMonitoringController::class, 'rombelMapel'])
            ->name('monitor.presensi.rombel-mapel');

        Route::get('/rekap-rombel', [RekapRombelController::class, 'index'])
            ->name('rekap_rombel.index');

        Route::get('/monitor/nilai', [GradeMonitorController::class, 'index'])
            ->name('monitor.nilai.index');

        Route::get('/monitor/nilai/{jadwal}', [GradeMonitorController::class, 'show'])
            ->name('monitor.nilai.show');

        Route::get('/rekap-rombel', [RekapRombelController::class, 'index'])
            ->name('rekap_rombel.index');

        Route::get('/rekap-rombel/{periode}', [RekapRombelController::class, 'show'])
            ->name('rekap_rombel.show');

        Route::get('/rekap-rombel/{periode}/menu/{menu}', [RekapRombelController::class, 'detailMenu'])
            ->name('rekap_rombel.detail_menu');
        // =======================
        // DATA MASTER & KELOLA DATA
        // =======================
        Route::prefix('data')->name('data.')->group(function () {

            // GURU
            Route::get('/guru', [KepsekGuruController::class, 'index'])
                ->name('guru');
            Route::get('/guru/{guru}', [KepsekGuruController::class, 'show'])
                ->name('guru.show');
            Route::get('/guru/{guru}/edit', [KepsekGuruController::class, 'edit'])
                ->name('guru.edit');
            Route::put('/guru/{guru}', [KepsekGuruController::class, 'update'])
                ->name('guru.update');
            Route::delete('/guru/{guru}', [KepsekGuruController::class, 'destroy'])
                ->name('guru.destroy');

            // SISWA
            Route::get('/siswa', [KepsekSiswaController::class, 'index'])
                ->name('siswa');
            Route::get('/siswa/{siswa}', [KepsekSiswaController::class, 'show'])
                ->name('siswa.show');
            Route::get('/siswa/{siswa}/edit', [KepsekSiswaController::class, 'edit'])
                ->name('siswa.edit');
            Route::put('/siswa/{siswa}', [KepsekSiswaController::class, 'update'])
                ->name('siswa.update');
            Route::delete('/siswa/{siswa}', [KepsekSiswaController::class, 'destroy'])
                ->name('siswa.destroy');

            // ROMBEL
            // ROMBEL (KEPSEK)
            Route::resource('rombel', \App\Http\Controllers\Kepsek\RombelController::class);

            Route::get('rombel/{rombel}/anggota',
                [\App\Http\Controllers\Kepsek\RombelController::class, 'anggota'])
                ->name('rombel.anggota');

            Route::post('rombel/{rombel}/anggota',
                [\App\Http\Controllers\Kepsek\RombelController::class, 'storeAnggota'])
                ->name('rombel.anggota.store');

            Route::delete('rombel/{rombel}/anggota/{siswa}',
                [\App\Http\Controllers\Kepsek\RombelController::class, 'destroyAnggota'])
                ->name('rombel.anggota.destroy');

            Route::get('rombel/{rombel}/mapel',
                [\App\Http\Controllers\Kepsek\RombelController::class, 'mapelJson'])
                ->name('rombel.mapel');


            // KENAIKAN
            Route::get('rombel/{rombel}/kenaikan',
                [\App\Http\Controllers\Kepsek\RombelKenaikanController::class, 'form'])
                ->name('rombel.kenaikan.form');

            Route::post('rombel/{rombel}/kenaikan',
                [\App\Http\Controllers\Kepsek\RombelKenaikanController::class, 'proses'])
                ->name('rombel.kenaikan.proses');

            // KELULUSAN
            Route::get('rombel/{rombel}/kelulusan',
                [\App\Http\Controllers\Kepsek\RombelKelulusanController::class, 'form'])
                ->name('rombel.kelulusan.form');

            Route::post('rombel/{rombel}/kelulusan',
                [\App\Http\Controllers\Kepsek\RombelKelulusanController::class, 'proses'])
                ->name('rombel.kelulusan.proses');

            // MAPEL
            Route::get('/mapel', [KepsekMapelController::class,'index'])
                ->name('mapel');
            Route::get('/mapel/{mapel}/edit', [KepsekMapelController::class,'edit'])
                ->name('mapel.edit');
            Route::put('/mapel/{mapel}', [KepsekMapelController::class,'update'])
                ->name('mapel.update');
            Route::delete('/mapel/{mapel}', [KepsekMapelController::class,'destroy'])
                ->name('mapel.destroy');

            // RUANG KELAS
            Route::get('/ruang-kelas', [KepsekRuangKelasController::class, 'index'])
                ->name('ruang-kelas');
            Route::get('/ruang-kelas/{ruang}/edit', [KepsekRuangKelasController::class, 'edit'])
                ->name('ruang-kelas.edit');
            Route::put('/ruang-kelas/{ruang}', [KepsekRuangKelasController::class, 'update'])
                ->name('ruang-kelas.update');
            Route::delete('/ruang-kelas/{ruang}', [KepsekRuangKelasController::class, 'destroy'])
                ->name('ruang-kelas.destroy');

            // TAHUN AJARAN
            Route::get('/tahun-ajaran', [KepsekTahunAjaranController::class, 'index'])
                ->name('tahun-ajaran');
            Route::get('/tahun-ajaran/{tahun_ajaran}/edit', [KepsekTahunAjaranController::class, 'edit'])
                ->name('tahun-ajaran.edit');
            Route::put('/tahun-ajaran/{tahun_ajaran}', [KepsekTahunAjaranController::class, 'update'])
                ->name('tahun-ajaran.update');
            Route::delete('/tahun-ajaran/{tahun_ajaran}', [KepsekTahunAjaranController::class, 'destroy'])
                ->name('tahun-ajaran.destroy');
            Route::patch('/tahun-ajaran/{tahun_ajaran}/aktifkan', [KepsekTahunAjaranController::class, 'setAktif'])
                ->name('tahun-ajaran.setAktif');

            // EKSKUL
            Route::get('/ekskul', [KepsekEkskulController::class, 'index'])
                ->name('ekskul');
            Route::get('/ekskul/{ekskul}/edit', [KepsekEkskulController::class, 'edit'])
                ->name('ekskul.edit');
            Route::put('/ekskul/{ekskul}', [KepsekEkskulController::class, 'update'])
                ->name('ekskul.update');
            Route::delete('/ekskul/{ekskul}', [KepsekEkskulController::class, 'destroy'])
                ->name('ekskul.destroy');
            Route::get('/ekskul/{ekskul}/anggota', [KepsekEkskulController::class, 'anggota'])
                ->name('ekskul.anggota');

            // JADWAL
            Route::get('/jadwal', [KepsekJadwalController::class, 'index'])
                ->name('jadwal');
            Route::get('/jadwal/{jadwal}', [KepsekJadwalController::class, 'show'])
                ->name('jadwal.show');
            Route::get('/jadwal/{jadwal}/edit', [KepsekJadwalController::class, 'edit'])
                ->name('jadwal.edit');
            Route::put('/jadwal/{jadwal}', [KepsekJadwalController::class, 'update'])
                ->name('jadwal.update');
            Route::delete('/jadwal/{jadwal}', [KepsekJadwalController::class, 'destroy'])
                ->name('jadwal.destroy');

            // PENGUMUMAN
          Route::get('/pengumuman', [AdminAnnouncementController::class, 'index'])
                ->name('pengumuman');
            Route::get('/pengumuman/{announcement}', [AdminAnnouncementController::class, 'show'])
                ->name('pengumuman.show');
        });
    });


// =====================
// Profile (Breeze)
// =====================
Route::middleware('auth')->group(function () {
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Auth scaffolding
require __DIR__ . '/auth.php';
