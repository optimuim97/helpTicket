<?php

namespace App\Services;

use App\DTOs\Roles\RoleCreateDTO;
use App\DTOs\Roles\RoleDTO;
use App\DTOs\Roles\RoleUpdateDTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Get all roles with optional filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllRoles(array $filters = []): Collection
    {
        $query = Role::with(['permissions']);

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%");
        }

        // Include user count
        if (!empty($filters['with_users_count'])) {
            $query->withCount('users');
        }

        // Include permission count
        if (!empty($filters['with_permissions_count'])) {
            $query->withCount('permissions');
        }

        // Order by name
        $query->orderBy('name');

        return RoleDTO::collection($query->get());
    }

    /**
     * Get role by ID
     *
     * @param int $id
     * @return RoleDTO|null
     */
    public function getRoleById(int $id): ?RoleDTO
    {
        $role = Role::with(['permissions', 'users'])->find($id);

        return $role ? RoleDTO::fromModel($role) : null;
    }

    /**
     * Get role by name
     *
     * @param string $name
     * @return RoleDTO|null
     */
    public function getRoleByName(string $name): ?RoleDTO
    {
        $role = Role::with(['permissions'])->where('name', $name)->first();

        return $role ? RoleDTO::fromModel($role) : null;
    }

    /**
     * Create a new role
     *
     * @param RoleCreateDTO $dto
     * @return RoleDTO
     * @throws \Throwable
     */
    public function createRole(RoleCreateDTO $dto): RoleDTO
    {
        return DB::transaction(function () use ($dto) {
            // Create role
            $role = Role::create($dto->toRoleData());

            // Assign permissions
            if ($dto->hasPermissions()) {
                $role->syncPermissions($dto->getPermissionIds());
            }

            // Reload relationships
            $role->load(['permissions']);

            return RoleDTO::fromModel($role);
        });
    }

    /**
     * Update existing role
     *
     * @param int $id
     * @param RoleUpdateDTO $dto
     * @return RoleDTO
     * @throws \Throwable
     */
    public function updateRole(int $id, RoleUpdateDTO $dto): RoleDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $role = Role::findOrFail($id);

            // Update basic info
            $roleData = $dto->toRoleData();
            if (!empty($roleData)) {
                $role->update($roleData);
            }

            // Update permissions if provided
            if ($dto->shouldUpdatePermissions()) {
                $role->syncPermissions($dto->getPermissionIds());
            }

            // Reload relationships
            $role->load(['permissions']);

            return RoleDTO::fromModel($role);
        });
    }

    /**
     * Delete role
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteRole(int $id): bool
    {
        $role = Role::findOrFail($id);

        // Check if role has users
        if ($role->users()->exists()) {
            throw new \Exception('Cannot delete role that has users assigned.');
        }

        return $role->delete();
    }

    /**
     * Assign permissions to role
     *
     * @param int $id
     * @param array $permissionIds
     * @return RoleDTO
     */
    public function assignPermissions(int $id, array $permissionIds): RoleDTO
    {
        $role = Role::findOrFail($id);
        $role->syncPermissions($permissionIds);
        $role->load(['permissions']);

        return RoleDTO::fromModel($role);
    }

    /**
     * Get users count for role
     *
     * @param int $id
     * @return int
     */
    public function getUsersCount(int $id): int
    {
        $role = Role::findOrFail($id);
        return $role->users()->count();
    }

    /**
     * Check if role name is unique (excluding given role ID)
     *
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function isNameUnique(string $name, ?int $excludeId = null): bool
    {
        $query = Role::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }

    /**
     * Get all role names
     *
     * @return array
     */
    public function getAllRoleNames(): array
    {
        return Role::pluck('name')->toArray();
    }
}
