<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('guru', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->nullable()->unique();
            $table->string('nuptk', 30)->nullable()->unique();
            $table->string('nama', 100);
            $table->enum('jk', ['L','P']);
            $table->string('tempat_lahir', 60)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('status_kepegawaian', ['PNS','PPPK','Non-PNS']);
            $table->string('no_hp', 20)->nullable();
            $table->string('email', 120)->nullable()->unique();
            $table->enum('status', ['aktif','nonaktif'])->default('aktif');
            $table->string('alamat', 100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru');
    }
};