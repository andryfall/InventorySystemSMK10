<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'username' => 'admin', 
            'name' => 'Joko', 
            'password' => \Illuminate\Support\Facades\Hash::make('pass123'),
        ]);
    }
}
