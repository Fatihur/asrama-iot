<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Kontak;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@asrama.local',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Petugas',
            'email' => 'petugas@asrama.local',
            'password' => Hash::make('password'),
        ]);

        // Create sample contacts
        Kontak::create([
            'nama' => 'Kepala Asrama',
            'jabatan' => 'Kepala Asrama',
            'nomor' => '081234567890',
            'whatsapp' => '081234567890',
            'pesan_wa' => 'DARURAT! Terjadi kejadian di Asrama. Mohon segera ditindaklanjuti.',
            'ikon' => 'user-tie',
            'status' => true,
            'notify_smoke' => true,
            'notify_sos' => true,
            'urutan' => 1,
        ]);

        Kontak::create([
            'nama' => 'Security',
            'jabatan' => 'Kepala Keamanan',
            'nomor' => '081234567891',
            'whatsapp' => '081234567891',
            'pesan_wa' => 'ALERT KEAMANAN! Segera cek kondisi asrama.',
            'ikon' => 'user-shield',
            'status' => true,
            'notify_smoke' => true,
            'notify_sos' => true,
            'urutan' => 2,
        ]);

        Kontak::create([
            'nama' => 'Pemadam Kebakaran',
            'jabatan' => 'Damkar',
            'nomor' => '113',
            'whatsapp' => null,
            'ikon' => 'fire-extinguisher',
            'status' => true,
            'notify_smoke' => true,
            'notify_sos' => false,
            'urutan' => 3,
        ]);
    }
}
