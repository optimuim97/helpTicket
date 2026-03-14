<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DIAGNOSTIC DES PERMISSIONS ===\n\n";

// Get all roles
echo "Rôles disponibles:\n";
$roles = \Spatie\Permission\Models\Role::all();
foreach ($roles as $role) {
    echo "- {$role->name} (ID: {$role->id})\n";
}

echo "\n--- Permissions du Superviseur ---\n";
$superviseur = \Spatie\Permission\Models\Role::where('name', 'Superviseur')->first();
if ($superviseur) {
    $permissions = $superviseur->permissions->pluck('name')->toArray();
    echo "Total: " . count($permissions) . " permissions\n";
    echo implode(", ", $permissions) . "\n";
} else {
    echo "❌ Rôle Superviseur non trouvé!\n";
}

echo "\n--- Permissions Admin Users ---\n";
$adminPermissions = ['view_users', 'create_users', 'edit_users', 'update_users', 'delete_users'];
foreach ($adminPermissions as $perm) {
    $exists = \Spatie\Permission\Models\Permission::where('name', $perm)->exists();
    echo ($exists ? "✅" : "❌") . " $perm\n";
}

echo "\n--- Premier utilisateur Superviseur ---\n";
$adminUser = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Superviseur');
})->first();

if ($adminUser) {
    echo "User: {$adminUser->name} ({$adminUser->email})\n";
    echo "Peut voir users: " . ($adminUser->can('view_users') ? "✅ OUI" : "❌ NON") . "\n";
    echo "Peut créer users: " . ($adminUser->can('create_users') ? "✅ OUI" : "❌ NON") . "\n";
    echo "Peut éditer users: " . ($adminUser->can('edit_users') ? "✅ OUI" : "❌ NON") . "\n";
} else {
    echo "❌ Aucun utilisateur Superviseur trouvé!\n";
}
