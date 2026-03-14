<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Tickets permissions
            'view_any_tickets',
            'view_own_tickets',
            'create_tickets',
            'update_tickets',
            'delete_tickets',
            'assign_tickets',
            'close_tickets',
            'resolve_tickets',
            
            // Users permissions
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'manage_user_permissions',
            
            // Roles permissions
            'view_roles',
            'create_roles',
            'update_roles',
            'delete_roles',
            'manage_role_permissions',
            
            // Services permissions
            'view_services',
            'create_services',
            'update_services',
            'delete_services',
            
            // Reports permissions
            'view_reports',
            'export_reports',
            
            // Settings permissions
            'manage_settings',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // Assign permissions to existing roles
        $superviseurRole = Role::where('name', 'Superviseur')->first();
        if ($superviseurRole) {
            $superviseurRole->syncPermissions($permissions); // All permissions
        }

        $agentRole = Role::where('name', 'Agent Helpdesk')->first();
        if ($agentRole) {
            $agentRole->syncPermissions([
                'view_any_tickets',
                'create_tickets',
                'update_tickets',
                'view_users',
            ]);
        }

        $technicienRole = Role::where('name', 'Technicien')->first();
        if ($technicienRole) {
            $technicienRole->syncPermissions([
                'view_own_tickets',
                'update_tickets',
                'resolve_tickets',
            ]);
        }
    }
}
