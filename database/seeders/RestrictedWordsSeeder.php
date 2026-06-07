<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestrictedWordsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('restricted_words')->insert([
            ['word' => 'damn',      'type' => 'profanity',  'created_at' => now(), 'updated_at' => now()],
            ['word' => 'hell',      'type' => 'profanity',  'created_at' => now(), 'updated_at' => now()],
            ['word' => 'bomb',      'type' => 'restricted', 'created_at' => now(), 'updated_at' => now()],
            ['word' => 'terrorist', 'type' => 'restricted', 'created_at' => now(), 'updated_at' => now()],
            ['word' => 'drugs',     'type' => 'restricted', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}