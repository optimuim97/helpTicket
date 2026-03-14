<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@tique.helpdesk'],
            [
                'name' => 'Administrateur',
                'password' => Hash::make('password'),
            ]
        );

        // Assign Superviseur role to admin
        if (!$admin->hasRole('Superviseur')) {
            $admin->assignRole('Superviseur');
        }
    }
}
