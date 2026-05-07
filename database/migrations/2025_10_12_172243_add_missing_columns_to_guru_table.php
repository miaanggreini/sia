<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (!Schema::hasColumn('guru', 'jk')) {
                $table->enum('jk', ['L','P'])->after('nama')->nullable();
            }
            if (!Schema::hasColumn('guru', 'tempat_lahir')) {
                $table->string('tempat_lahir', 60)->nullable()->after('jk');
            }
            if (!Schema::hasColumn('guru', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            }
            if (!Schema::hasColumn('guru', 'status_kepegawaian')) {
                $table->enum('status_kepegawaian', ['PNS','PPPK','Non-PNS'])->after('tanggal_lahir');
            }
            if (!Schema::hasColumn('guru', 'email')) {
                $table->string('email', 120)->nullable()->unique()->after('no_hp');
            }
            if (!Schema::hasColumn('guru', 'status')) {
                $table->enum('status', ['aktif','nonaktif'])->default('aktif')->after('email');
            }
            if (!Schema::hasColumn('guru', 'alamat')) {
                $table->string('alamat', 255)->nullable()->after('no_hp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['jk','tempat_lahir','tanggal_lahir','status_kepegawaian','email','status','alamat']);
        });
    }
};
