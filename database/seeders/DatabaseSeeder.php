<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun ADMIN (IT / Pemeliharaan)
        // Tugas: Menerima laporan kerusakan & Mengerjakan request aplikasi
        User::updateOrCreate([
            'email' => 'admin@rs.com',
        ], [
            'name' => 'Admin IT',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        // 2. Akun MANAGER (Pengaju Request Aplikasi)
        // Tugas: Mengisi form pengajuan aplikasi baru
        User::updateOrCreate([
            'email' => 'manager@rs.com',
        ], [
            'name' => 'Budi Manager',
            'role' => 'manager',
            'password' => Hash::make('password'),
        ]);

        // 3. Akun DIREKTUR (Pemberi ACC)
        // Tugas: Menyetujui atau menolak pengajuan Manager
        User::updateOrCreate([
            'email' => 'direktur@rs.com',
        ], [
            'name' => 'Ibu Direktur',
            'role' => 'direktur',
            'password' => Hash::make('password'),
        ]);

        // 4. Akun BENDAHARA (Pengelola Keuangan)
        // Tugas: Mengonfirmasi pengadaan sebelum diajukan ke Direktur
        User::updateOrCreate([
            'email' => 'bendahara@rs.com',
        ], [
            'name' => 'Pak Bendahara',
            'role' => 'bendahara',
            'password' => Hash::make('password'),
        ]);

        // 4. Akun STAFF (Pelapor Kerusakan Biasa)
        // Tugas: Hanya lapor AC rusak, dll (Fitur lama)
        // User::create([
        //     'name' => 'Perawat Jaga',
        //     'email' => 'staff@rs.com',
        //     'role' => 'staff',
        //     'password' => Hash::make('password'),
        // ]);

        // Seed rooms and manager accounts, and map existing reports
        $this->call(\Database\Seeders\RoomAndManagerSeeder::class);
    }
}