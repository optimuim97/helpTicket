<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles for the helpdesk system
        $roles = [
            ['name' => 'Agent Helpdesk', 'guard_name' => 'web'],
            ['name' => 'Technicien', 'guard_name' => 'web'],
            ['name' => 'Superviseur', 'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate($role);
        }
    }
}
