<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ekskul', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 120);
            $table->foreignId('pembina_id')->constrained('guru')->cascadeOnUpdate()->restrictOnDelete();
            $table->enum('hari', ['Senin','Selasa','Rabu','Kamis','Jumat'])->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('lokasi', 120)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('ekskul');
    }
};
