<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('presensi', function (Blueprint $table) {
        $table->id();
        $table->foreignId('jadwal_id')->constrained('jadwal')->onDelete('cascade');
        $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
        $table->date('tanggal');
        $table->enum('status', ['Hadir', 'Izin', 'Sakit', 'Alpa'])->default('Hadir');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
