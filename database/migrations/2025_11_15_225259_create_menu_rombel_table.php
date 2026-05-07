<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_rombel', function (Blueprint $t) {
            $t->id();
            $t->string('nama_menu');                  // contoh: "Menu I – Sains"
            $t->unsignedBigInteger('tahun_ajaran_id')->index();
            $t->string('tingkat')->default('XI');     // X / XI / XII
            $t->unsignedInteger('kapasitas_total');   // kursi total (mis. 50)
            $t->boolean('aktif')->default(true);
            $t->timestamps();

            $t->foreign('tahun_ajaran_id')
              ->references('id')->on('tahun_ajaran')
              ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_rombel');
    }
};
