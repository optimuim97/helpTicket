<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): Response
    {
        // Check permission
        if (!$request->user()->can('view_users')) {
            abort(403, 'Accès refusé. Vous n\'avez pas la permission de voir les utilisateurs.');
        }

        // Get filters from request
        $filters = [
            'role' => $request->get('role'),
            'service_id' => $request->get('service_id'),
            'search' => $request->get('search'),
        ];

        $usersCollection = $this->userService->getAllUsers(array_filter($filters));
        
        // Paginate manually
        $perPage = 15;
        $page = $request->get('page', 1);
        $total = $usersCollection->count();
        $users = $usersCollection->slice(($page - 1) * $perPage, $perPage)->values();

        return Inertia::render('Users/Index', [
            'users' => [
                'data' => $users,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage),
            ],
            'roles' => Role::all(),
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(Request $request): Response
    {
        if (!$request->user()->can('create_users')) {
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
    public function store(StoreUserRequest $request): RedirectResponse
    {
        try {
            $this->userService->createUser($request->toDTO());

            return redirect()->route('users.index')->with('success', 'Utilisateur créé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(Request $request, User $user): Response
    {
        if (!$request->user()->can('edit_users')) {
            abort(403);
        }

        // Get user as DTO
        $userDTO = $this->userService->getUserById($user->id);

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
            'user' => $userDTO,
            'roles' => Role::all(),
            'services' => Service::active()->orderBy('name')->get(),
            'allPermissions' => $permissionsGrouped,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        try {
            $this->userService->updateUser($user->id, $request->toDTO());

            return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if (!$request->user()->can('delete_users')) {
            abort(403);
        }

        // Prevent self-deletion
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        try {
            $this->userService->deleteUser($user->id);

            return redirect()->route('users.index')->with('success', 'Utilisateur supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }
}
