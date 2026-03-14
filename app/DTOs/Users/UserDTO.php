<?php

namespace App\DTOs\Users;

use App\DTOs\BaseDTO;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?int $service_id = null;
    public ?string $service_name = null;
    public ?array $roles = [];
    public ?array $permissions = [];
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Create DTO from User model with relationships
     *
     * @param Model $model
     * @return static
     */
    public static function fromModel(Model $model): static
    {
        /** @var User $model */
        $dto = new static();
        
        $dto->id = $model->id;
        $dto->name = $model->name;
        $dto->email = $model->email;
        $dto->service_id = $model->service_id;
        $dto->service_name = $model->service?->name;
        $dto->created_at = $model->created_at?->toISOString();
        $dto->updated_at = $model->updated_at?->toISOString();
        
        // Load roles
        if ($model->relationLoaded('roles')) {
            $dto->roles = $model->roles->map(fn($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])->toArray();
        }
        
        // Load permissions
        if ($model->relationLoaded('permissions')) {
            $dto->permissions = $model->permissions->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->toArray();
        } elseif ($model->relationLoaded('roles')) {
            // Get permissions from roles
            $dto->permissions = $model->getAllPermissions()->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->toArray();
        }
        
        return $dto;
    }

    /**
     * Get display name for the user
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->name ?? $this->email ?? 'Unknown';
    }

    /**
     * Check if user has a specific role
     *
     * @param string $roleName
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return collect($this->roles)->contains('name', $roleName);
    }

    /**
     * Check if user has a specific permission
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return collect($this->permissions)->contains('name', $permissionName);
    }

    /**
     * Get role names as array
     *
     * @return array
     */
    public function getRoleNames(): array
    {
        return collect($this->roles)->pluck('name')->toArray();
    }

    /**
     * Get permission names as array
     *
     * @return array
     */
    public function getPermissionNames(): array
    {
        return collect($this->permissions)->pluck('name')->toArray();
    }
}
