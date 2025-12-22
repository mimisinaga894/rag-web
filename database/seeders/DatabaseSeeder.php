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
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'username' => '41501',
            'name' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@siassist.com',
            'nim_nidn' => '41501',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);
    }
}
