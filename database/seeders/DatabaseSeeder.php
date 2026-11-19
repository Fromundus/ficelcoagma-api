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
            'password' => Hash::make("1234"),
            'role' => 'admin',
        ]);

        User::factory()->create([
            'name' => 'superadmin',
            'fullname' => 'Superadmin',
            'password' => Hash::make("1234"),
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'user',
            'fullname' => 'User',
            'password' => Hash::make("1234"),
            'role' => 'user',
        ]);

        Setting::factory(1)->create();

        $this->call([
            MemberSeeder::class,
        ]);
    }
}
