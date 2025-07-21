<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $userRole = Role::where('name', 'superadmin')->first();
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('@dmin//2#25'), // or bcrypt('password')
        ]);

        $user = User::create([
            'name' => 'superadmin User',
            'email' => 'superadmin@example.com',
            'password' => bcrypt('@dmin//2#25'),
        ]);

        // Attach roles
        $admin->roles()->attach($adminRole->id);
        $user->roles()->attach($userRole->id);
    }
}
