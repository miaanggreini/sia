<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pilihan_rombel', function (Blueprint $t) {
            $t->id();

            // Kunci periode & siswa
            $t->unsignedBigInteger('periode_id');         // FK → periode_pemilihan.id
            $t->unsignedBigInteger('siswa_id');           // FK → siswa.id

            // Preferensi siswa (boleh isi 1–3)
            $t->unsignedBigInteger('pilihan_1_menu_id')->nullable();
            $t->unsignedBigInteger('pilihan_2_menu_id')->nullable();
            $t->unsignedBigInteger('pilihan_3_menu_id')->nullable();

            // Hasil penempatan
            $t->unsignedBigInteger('menu_diterima_id')->nullable();
            $t->enum('status', ['diterima','cadangan','belum_tertempatkan'])
              ->default('belum_tertempatkan');

            // Tie-break default
            $t->timestamp('waktu_pengajuan')->useCurrent();
            $t->timestamps();

            // Keunikan 1 siswa per periode
            $t->unique(['periode_id','siswa_id']);
            $t->index(['pilihan_1_menu_id','pilihan_2_menu_id','pilihan_3_menu_id']);
            $t->index('menu_diterima_id');

            // FK
            $t->foreign('periode_id')->references('id')->on('periode_pemilihan')->cascadeOnDelete();
            $t->foreign('siswa_id')->references('id')->on('siswa')->cascadeOnDelete();

            $t->foreign('pilihan_1_menu_id')->references('id')->on('menu_rombel')->nullOnDelete();
            $t->foreign('pilihan_2_menu_id')->references('id')->on('menu_rombel')->nullOnDelete();
            $t->foreign('pilihan_3_menu_id')->references('id')->on('menu_rombel')->nullOnDelete();

            $t->foreign('menu_diterima_id')->references('id')->on('menu_rombel')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilihan_rombel');
    }
};
