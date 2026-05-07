<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('periode_pemilihan', function (Blueprint $t) {
            $t->id();
            $t->string('nama_periode');                    // "Pemilihan Rombel XI 2025/2026"
            $t->unsignedBigInteger('tahun_ajaran_id');     // FK → tahun_ajaran.id
            $t->string('tingkat')->default('XI');          // target jenjang
            $t->dateTime('tanggal_mulai');
            $t->dateTime('tanggal_selesai');
            $t->enum('status', ['draft','dibuka','ditutup','ditempatkan','terkunci'])->default('draft');
            $t->timestamps();

            $t->index('tahun_ajaran_id');
            $t->foreign('tahun_ajaran_id')
              ->references('id')->on('tahun_ajaran')
              ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periode_pemilihan');
    }
};
