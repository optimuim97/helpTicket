<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index(): Response
    {
        abort_unless(auth()->user()->can('view_roles'), 403);

        $permissions = Permission::all()->groupBy(function ($permission) {
            // Group by prefix (e.g., "view_tickets" -> "view")
            $parts = explode('_', $permission->name);
            return $parts[0];
        })->map(function ($group) {
            return [
                'permissions' => $group,
                'count' => $group->count(),
            ];
        });

        return Inertia::render('Permissions/Index', [
            'permissionsGrouped' => $permissions,
        ]);
    }
}
