<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruang_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();     // contoh: "R. XI-1" / "Lab Fisika"
            $table->unsignedSmallInteger('kapasitas')  // contoh: 25
                  ->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_kelas');
    }
};