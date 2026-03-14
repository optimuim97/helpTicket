<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    /**
     * Sync permissions for a role.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        abort_unless(auth()->user()->can('manage_role_permissions'), 403);

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->syncPermissions($validated['permissions']);

        return redirect()->back()->with('success', 'Permissions du rôle mises à jour avec succès.');
    }
}
