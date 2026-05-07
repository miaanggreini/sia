<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa_rombel', function (Blueprint $table) {
            $table->id();

            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombel')->cascadeOnDelete();

            $table->string('tahun_ajaran', 9);         // "2025/2026"
            $table->boolean('aktif')->default(true);
            // opsional: tandai pilihan utama/cadangan, bisa dihapus kalau belum perlu
            $table->enum('status_pilihan', ['utama','cadangan'])->default('utama');

            $table->timestamps();

            // Hindari duplikasi penempatan siswa pada rombel yang sama di TA yang sama
            $table->unique(['siswa_id','rombel_id','tahun_ajaran'], 'uq_sr_siswa_rombel_ta');

            // Index bantu
            $table->index(['rombel_id','aktif'], 'idx_sr_rombel_aktif');
            $table->index(['siswa_id','tahun_ajaran','aktif'], 'idx_sr_siswa_ta_aktif');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_rombel');
    }
};
