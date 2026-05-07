<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('menu_rombel_mapel', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('menu_rombel_id');    // FK → menu_rombel.id
            $t->unsignedBigInteger('mata_pelajaran_id'); // FK → mata_pelajaran.id
            $t->timestamps();

            $t->unique(['menu_rombel_id','mata_pelajaran_id']);
            $t->index(['menu_rombel_id','mata_pelajaran_id']);

            $t->foreign('menu_rombel_id')
              ->references('id')->on('menu_rombel')
              ->cascadeOnDelete();

            $t->foreign('mata_pelajaran_id')
              ->references('id')->on('mata_pelajaran')
              ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_rombel_mapel');
    }
};
