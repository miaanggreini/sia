<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rombel', function (Blueprint $table) {
            $table->id();
            // kelas induk XI/XII
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            // mapel pilihan yang diampu di rombel ini
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->restrictOnDelete();

            $table->string('nama_rombel', 100);        // contoh: "XI IPA - Fisika A"
            $table->string('tahun_ajaran', 9);         // contoh: "2025/2026"
            $table->boolean('aktif')->default(true);

            // opsional kapasitas (boleh kosong)
            $table->smallInteger('kapasitas')->nullable();

            $table->timestamps();

            // index bantu
            $table->index(['kelas_id', 'tahun_ajaran', 'aktif'], 'idx_rombel_kls_ta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rombel');
    }
};
