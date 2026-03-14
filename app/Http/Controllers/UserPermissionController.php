<?php

namespace App\Http\Controllers;

use App\Models\User;
use Spatie\Permission\Models\Permission;

class UserPermissionController extends Controller
{
    /**
     * Assign a permission to a user.
     */
    public function assign(User $user, Permission $permission)
    {
        abort_unless(auth()->user()->can('manage_user_permissions'), 403);

        $user->givePermissionTo($permission);

        return redirect()->back()->with('success', 'Permission assignée à l\'utilisateur avec succès.');
    }

    /**
     * Revoke a permission from a user.
     */
    public function revoke(User $user, Permission $permission)
    {
        abort_unless(auth()->user()->can('manage_user_permissions'), 403);

        $user->revokePermissionTo($permission);

        return redirect()->back()->with('success', 'Permission révoquée de l\'utilisateur avec succès.');
    }
}
