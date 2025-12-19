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
            'name' => 'Admin SIAssist',
            'email' => 'admin@siassist.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'User SIAssist',
            'email' => 'user@siassist.com',
            'password' => Hash::make('user123'),
            'role' => 'user',
        ]);
    }
}
