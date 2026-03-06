<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Menonaktifkan foreign key check agar truncate tidak error jika ada relasi
        Schema::disableForeignKeyConstraints();
        
        // Menghapus data user lama agar tidak duplikat saat dijalankan ulang
        User::truncate();

        // 1. Akun Administrator
        User::create([
            'nip' => '123',
            'name' => 'Administrator',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Akun User Achmad Syaifullah
        User::create([
            'nip' => '19950424',
            'name' => 'Achmad Syaifullah',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // 3. Akun User David Wijaya Mahendra
        User::create([
            'nip' => '20002005',
            'name' => 'David Wijaya Mahendra',
            'password' => Hash::make('password'),
            'role' => 'user',
        ]);

        // Mengaktifkan kembali foreign key check
        Schema::enableForeignKeyConstraints();
    }
}