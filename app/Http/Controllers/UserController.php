<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
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
        $filters = [
            'role' => $request->get('role'),
            'service_id' => $request->get('service_id'),
            'search' => $request->get('search'),
        ];

        $query = User::query()
            ->with(['roles:id,name', 'service:id,name', 'position:id,fonction,metier'])
            ->select(['id', 'matricule', 'name', 'email', 'numero_fixe', 'numero_flotte', 'service_id', 'position_id', 'created_at']);

        if (! empty($filters['role'])) {
            $query->role($filters['role']);
        }
        if (! empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }
        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('matricule', 'like', "%{$term}%");
            });
        }

        $users = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => Role::all(['id', 'name']),
            'filters' => array_filter($filters, fn ($v) => $v !== null && $v !== ''),
        ]);
    }

    /**
     * Recherche d'utilisateurs pour les autocomplétions (annuaire, affectations…).
     */
    public function search(Request $request): JsonResponse
    {
        $q = trim((string) $request->get('q', ''));

        $query = User::with(['service:id,name', 'position:id,fonction,metier'])
            ->select(['id', 'matricule', 'name', 'email', 'numero_fixe', 'numero_flotte', 'service_id', 'position_id']);

        if ($q !== '') {
            $like = '%'.$q.'%';
            $query->where(function ($w) use ($like) {
                $w->where('name', 'like', $like)
                    ->orWhere('matricule', 'like', $like)
                    ->orWhere('email', 'like', $like);
            });
        }

        $users = $query->orderBy('name')->limit(20)->get();

        return response()->json($users);
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
