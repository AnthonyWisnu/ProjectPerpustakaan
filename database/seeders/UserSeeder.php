
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@library.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
            'phone' => '081234567890',
            'address' => 'Jl. Perpustakaan No. 1',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        // Create 2 Regular Admins
        User::factory()->admin()->count(2)->create();

        // Create 10 Members
        User::factory()->member()->count(10)->create();
    }
}
