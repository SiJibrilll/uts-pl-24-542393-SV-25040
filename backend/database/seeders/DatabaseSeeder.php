<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User Admin untuk pengujian autentikasi token
        User::firstOrCreate(
            ['email' => 'admin@bookstore.test'],
            [
                'name' => 'Jibrilian Wulsa Ariswanto',
                'password' => Hash::make('password123'),
            ]
        );

        $this->call([
            BookSeeder::class,
        ]);
    }
}
