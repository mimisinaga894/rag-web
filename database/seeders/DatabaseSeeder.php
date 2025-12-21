<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'username' => 'admin.siassist',
            'name' => 'Admin SIAssist',
            'email' => 'admin@siassist.com',
            'nim_nidn' => '0001',
            'password' => Hash::make('Adm1n@S1Assist2024!'),
            'role' => 'admin',
        ]);

        User::create([
            'username' => '41522010000',
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@siassist.com',
            'nim_nidn' => '2024001001',
            'password' => Hash::make('Mhs@Demo2024!'),
            'role' => 'user',
        ]);
    }
}
