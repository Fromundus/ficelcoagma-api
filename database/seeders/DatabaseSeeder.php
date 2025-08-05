<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'admin',
            'fullname' => 'Admin',
            'email' => '',
            'password' => Hash::make("123456"),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'superadmin',
            'fullname' => 'Superadmin',
            'email' => '',
            'password' => Hash::make("gabriellehope24"),
            'role' => 'superadmin',
        ]);

        Setting::factory(1)->create();
    }
}
