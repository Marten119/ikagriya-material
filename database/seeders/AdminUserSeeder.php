<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Isi SEED_ADMIN_PASSWORD di .env sebelum seeding. Ganti password setelah login pertama.
        $password = env('SEED_ADMIN_PASSWORD');
        if (! $password) {
            throw new \RuntimeException('Set SEED_ADMIN_PASSWORD di file .env sebelum menjalankan seeder.');
        }

        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@example.test',
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'aktif',
            ]
        );
    }
}
