<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Guru',
            'email' => 'guru@gmail.com',
            'password' => Hash::make('12345'),
            'role' => 'guru',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Siswa',
            'email' => 'siswa@gmail.com',
            'password' => Hash::make('12345'),
            'role' => 'siswa',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Kepala Sekolah',
            'email' => 'kepsek@gmail.com',
            'password' => Hash::make('12345'),
            'role' => 'kepala_sekolah',
            'email_verified_at' => now(),
        ]);
    }
}
