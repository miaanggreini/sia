<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();

            // relasi opsional ke users
            $table->foreignId('user_id')->nullable()
                ->constrained('users')->nullOnDelete();

            $table->string('nisn', 20)->unique();
            $table->string('nis', 20)->unique();
            $table->string('nama', 100);

            $table->enum('jk', ['L','P']);

            $table->string('tempat_lahir', 60)->nullable();
            $table->date('tanggal_lahir')->nullable();

            $table->enum('agama', ['Islam','Kristen','Katolik','Hindu','Buddha','Konghucu'])
                  ->nullable();

            $table->text('alamat')->nullable();

            $table->string('no_hp', 20)->nullable();
            $table->string('email', 120)->nullable();

            $table->enum('jalur_penerimaan', ['Zonasi','Prestasi','Afirmasi','Pindahan','Lainnya'])
                  ->nullable();

            $table->enum('kebutuhan_khusus', ['Ya','Tidak'])->default('Tidak');

            // YEAR: schema builder tidak punya tipe YEAR; gunakan smallInteger(4) / string(4).
            $table->unsignedSmallInteger('tahun_masuk')->nullable(); // isi 4 digit (mis: 2024)

            $table->enum('status', ['aktif','lulus','pindah','keluar'])->default('aktif');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa');
    }
};
