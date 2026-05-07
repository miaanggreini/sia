<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::create('siswa_kelas', function (Blueprint $table) {
    $table->id();
    $table->string('siswa_nis', 20);                 // FK ke siswa.nis
    $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
    $table->string('tahun_ajaran', 9);               // contoh "2024/2025"
    $table->boolean('aktif')->default(true);
    $table->date('tanggal_mulai')->nullable();
    $table->date('tanggal_selesai')->nullable();
    $table->string('keterangan')->nullable();
    $table->timestamps();

    $table->foreign('siswa_nis')->references('nis')->on('siswa')->cascadeOnDelete();
    $table->index(['kelas_id','aktif']);
    });
    
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_kelas');
    }
};
