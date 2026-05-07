<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sesi_presensi', function (Blueprint $table) {
            $table->id();

            // relasi utama
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('rombel_id');
            $table->unsignedBigInteger('mata_pelajaran_id')->nullable(); // opsional
            $table->unsignedBigInteger('jadwal_id')->nullable();         // opsional (link ke jadwal tertentu)

            // informasi sesi
            $table->timestamp('mulai_pada');
            $table->timestamp('ditutup_pada')->nullable();
            $table->enum('status', ['terbuka','tertutup'])->default('terbuka');

            // pengaturan keamanan & logika QR
            $table->unsignedInteger('masa_aktif_detik')->default(1200);     // default 20 menit
            $table->unsignedInteger('terlambat_setelah_menit')->nullable(); // ambang keterlambatan
            $table->string('secret_salt')->nullable();

            $table->timestamps();

            // index yang sering dipakai untuk filter
            $table->index(['rombel_id','mata_pelajaran_id','status']);

            // foreign keys — sesuaikan nama tabel di proyekmu (berikut pakai nama Indonesia)
            $table->foreign('guru_id')->references('id')->on('guru')->cascadeOnDelete();
            $table->foreign('rombel_id')->references('id')->on('rombel')->cascadeOnDelete();
            $table->foreign('mata_pelajaran_id')->references('id')->on('mata_pelajaran')->nullOnDelete();
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesi_presensi');
    }
};
