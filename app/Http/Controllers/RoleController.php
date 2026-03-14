<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Services\RoleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    protected RoleService $roleService;

    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    /**
     * Display a listing of roles.
     */
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('view_roles'), 403);

        // Get filters from request
        $filters = [
            'search' => $request->get('search'),
            'with_users_count' => true,
            'with_permissions_count' => true,
        ];

        $roles = $this->roleService->getAllRoles(array_filter($filters));

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('create_roles'), 403);

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
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        try {
            $this->roleService->createRole($request->toDTO());

            return redirect()->route('roles.index')->with('success', 'Rôle créé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Request $request, Role $role): Response
    {
        abort_unless($request->user()->can('edit_roles'), 403);

        $roleDTO = $this->roleService->getRoleById($role->id);

        $allPermissions = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });

        return Inertia::render('Roles/Edit', [
            'role' => $roleDTO,
            'allPermissions' => $allPermissions,
            'rolePermissions' => $roleDTO->getPermissionNames(),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        try {
            $this->roleService->updateRole($role->id, $request->toDTO());

            return redirect()->route('roles.index')->with('success', 'Rôle mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        abort_unless($request->user()->can('delete_roles'), 403);

        try {
            $this->roleService->deleteRole($role->id);

            return redirect()->route('roles.index')->with('success', 'Rôle supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
