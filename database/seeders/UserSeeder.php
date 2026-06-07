<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin User',
                'email' => 'admin@test.com',
                'password' => Hash::make('123456'),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Reviewer User',
                'email' => 'reviewer@test.com',
                'password' => Hash::make('123456'),
                'role' => 'reviewer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}