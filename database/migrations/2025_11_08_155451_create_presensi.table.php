<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presensi', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sesi_presensi_id');
            $table->unsignedBigInteger('siswa_id');

            // status kehadiran
            $table->enum('status', ['hadir','terlambat','izin','sakit','alfa'])->default('hadir');

            // metadata
            $table->timestamp('dipindai_pada')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->string('device_fingerprint', 191)->nullable();

            // siapa yang membuat/perbarui (override manual oleh guru)
            $table->unsignedBigInteger('dibuat_oleh')->nullable(); // id user pembuat/override
            $table->unsignedBigInteger('diperbarui_oleh')->nullable();

            $table->timestamps();

            // 1 siswa hanya 1 presensi per sesi
            $table->unique(['sesi_presensi_id','siswa_id']);

            // foreign keys
            $table->foreign('sesi_presensi_id')->references('id')->on('sesi_presensi')->cascadeOnDelete();
            $table->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
