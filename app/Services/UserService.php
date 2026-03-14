<?php

namespace App\Services;

use App\DTOs\Users\UserCreateDTO;
use App\DTOs\Users\UserDTO;
use App\DTOs\Users\UserUpdateDTO;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    /**
     * Get all users with optional filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllUsers(array $filters = []): Collection
    {
        $query = User::with(['roles', 'permissions', 'service']);

        // Filter by role
        if (!empty($filters['role'])) {
            $query->role($filters['role']);
        }

        // Filter by service
        if (!empty($filters['service_id'])) {
            $query->where('service_id', $filters['service_id']);
        }

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Order by name
        $query->orderBy('name');

        return UserDTO::collection($query->get());
    }

    /**
     * Get user by ID
     *
     * @param int $id
     * @return UserDTO|null
     */
    public function getUserById(int $id): ?UserDTO
    {
        $user = User::with(['roles', 'permissions', 'service'])->find($id);

        return $user ? UserDTO::fromModel($user) : null;
    }

    /**
     * Create a new user
     *
     * @param UserCreateDTO $dto
     * @return UserDTO
     * @throws \Throwable
     */
    public function createUser(UserCreateDTO $dto): UserDTO
    {
        return DB::transaction(function () use ($dto) {
            // Create user
            $user = User::create([
                'name' => $dto->name,
                'email' => $dto->email,
                'password' => $dto->getHashedPassword(),
                'service_id' => $dto->service_id,
            ]);

            // Assign role
            if ($dto->hasRole()) {
                $user->assignRole($dto->role);
            }

            // Assign permissions
            if ($dto->hasPermissions()) {
                $user->syncPermissions($dto->permissions);
            }

            // Reload relationships
            $user->load(['roles', 'permissions', 'service']);

            return UserDTO::fromModel($user);
        });
    }

    /**
     * Update existing user
     *
     * @param int $id
     * @param UserUpdateDTO $dto
     * @return UserDTO
     * @throws \Throwable
     */
    public function updateUser(int $id, UserUpdateDTO $dto): UserDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $user = User::findOrFail($id);

            // Update basic info
            $user->update($dto->toUserData());

            // Update password if provided
            if ($dto->shouldUpdatePassword()) {
                $user->update(['password' => $dto->getHashedPassword()]);
            }

            // Update role if provided
            if ($dto->shouldUpdateRole()) {
                $user->syncRoles([$dto->role]);
            }

            // Update permissions if provided
            if ($dto->shouldUpdatePermissions()) {
                $user->syncPermissions($dto->permissions);
            }

            // Reload relationships
            $user->load(['roles', 'permissions', 'service']);

            return UserDTO::fromModel($user);
        });
    }

    /**
     * Delete user
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = User::findOrFail($id);
        
        return $user->delete();
    }

    /**
     * Assign permissions to user
     *
     * @param int $id
     * @param array $permissionIds
     * @return UserDTO
     */
    public function assignPermissions(int $id, array $permissionIds): UserDTO
    {
        $user = User::findOrFail($id);
        $user->syncPermissions($permissionIds);
        $user->load(['roles', 'permissions', 'service']);

        return UserDTO::fromModel($user);
    }

    /**
     * Remove permission from user
     *
     * @param int $id
     * @param int $permissionId
     * @return UserDTO
     */
    public function removePermission(int $id, int $permissionId): UserDTO
    {
        $user = User::findOrFail($id);
        $user->revokePermissionTo($permissionId);
        $user->load(['roles', 'permissions', 'service']);

        return UserDTO::fromModel($user);
    }

    /**
     * Get users by role
     *
     * @param string $roleName
     * @return Collection
     */
    public function getUsersByRole(string $roleName): Collection
    {
        $users = User::with(['roles', 'permissions', 'service'])
            ->role($roleName)
            ->orderBy('name')
            ->get();

        return UserDTO::collection($users);
    }

    /**
     * Get users by service
     *
     * @param int $serviceId
     * @return Collection
     */
    public function getUsersByService(int $serviceId): Collection
    {
        $users = User::with(['roles', 'permissions', 'service'])
            ->where('service_id', $serviceId)
            ->orderBy('name')
            ->get();

        return UserDTO::collection($users);
    }

    /**
     * Check if email is unique (excluding given user ID)
     *
     * @param string $email
     * @param int|null $excludeId
     * @return bool
     */
    public function isEmailUnique(string $email, ?int $excludeId = null): bool
    {
        $query = User::where('email', $email);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }
}
