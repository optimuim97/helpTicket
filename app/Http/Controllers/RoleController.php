<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): Response
    {
        abort_unless(auth()->user()->can('view_roles'), 403);

        $roles = Role::withCount(['users', 'permissions'])
            ->orderBy('name')
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(): Response
    {
        abort_unless(auth()->user()->can('create_roles'), 403);

        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('create_roles'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role = Role::create(['name' => $validated['name']]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): Response
    {
        abort_unless(auth()->user()->can('update_roles'), 403);

        $role->load(['permissions', 'users']);

        $allPermissions = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'allPermissions' => $allPermissions,
            'rolePermissions' => $role->permissions->pluck('name'),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        abort_unless(auth()->user()->can('update_roles'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        return redirect()->route('roles.index')->with('success', 'Rôle mis à jour avec succès.');
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        abort_unless(auth()->user()->can('delete_roles'), 403);

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', 'Impossible de supprimer ce rôle car des utilisateurs y sont assignés.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rôle supprimé avec succès.');
    }
}
