<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rombel_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rombel_id')->constrained('rombel')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();

            // kombinasi unik agar 1 mapel tidak dobel di rombel yang sama
            $table->unique(['rombel_id', 'mata_pelajaran_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rombel_mapel');
    }
};
