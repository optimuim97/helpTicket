<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        // Only supervisors can manage users
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Accès refusé. Seuls les superviseurs peuvent gérer les utilisateurs.');
        }

        $users = User::with(['roles', 'service', 'permissions'])->paginate(15);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request): Response
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403);
        }

        return Inertia::render('Users/Create', [
            'roles' => Role::all(),
            'services' => Service::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|exists:roles,name',
            'service_id' => 'nullable|exists:services,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'service_id' => $validated['service_id'] ?? null,
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(Request $request, User $user): Response
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403);
        }

        // Get all permissions grouped by category (first part before underscore)
        $permissions = Permission::all()->groupBy(function ($permission) {
            return explode('_', $permission->name)[0];
        });
        
        $permissionsGrouped = $permissions->map(function ($group, $key) {
            return [
                'permissions' => $group->values(),
                'count' => $group->count(),
            ];
        });

        return Inertia::render('Users/Edit', [
            'user' => $user->load(['roles.permissions', 'service', 'permissions']),
            'roles' => Role::all(),
            'services' => Service::active()->orderBy('name')->get(),
            'allPermissions' => $permissionsGrouped,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'role' => 'sometimes|exists:roles,name',
            'service_id' => 'nullable|exists:services,id',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
            'service_id' => $validated['service_id'] ?? $user->service_id,
        ]);

        if (isset($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        if (isset($validated['role'])) {
            $user->syncRoles([$validated['role']]);
        }

        // Sync direct user permissions (separate from role permissions)
        if (isset($validated['permissions'])) {
            $user->syncPermissions($validated['permissions']);
        }

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user)
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
    }
}
