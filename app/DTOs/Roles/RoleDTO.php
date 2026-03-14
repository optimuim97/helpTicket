<?php

namespace App\DTOs\Roles;

use App\DTOs\BaseDTO;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;

class RoleDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $guard_name = null;
    public ?int $permissions_count = 0;
    public ?int $users_count = 0;
    public ?array $permissions = [];
    public ?array $users = [];
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Create DTO from Role model with relationships
     *
     * @param Model $model
     * @return static
     */
    public static function fromModel(Model $model): static
    {
        /** @var Role $model */
        $dto = new static();
        
        $dto->id = $model->id;
        $dto->name = $model->name;
        $dto->guard_name = $model->guard_name;
        $dto->created_at = $model->created_at?->toISOString();
        $dto->updated_at = $model->updated_at?->toISOString();
        
        // Load permissions
        if ($model->relationLoaded('permissions')) {
            $dto->permissions = $model->permissions->map(fn($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->toArray();
            $dto->permissions_count = $model->permissions->count();
        }
        
        // Load users
        if ($model->relationLoaded('users')) {
            $dto->users = $model->users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ])->toArray();
            $dto->users_count = $model->users->count();
        }
        
        return $dto;
    }

    /**
     * Get display name for the role
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return ucfirst($this->name ?? 'Unknown');
    }

    /**
     * Check if role has a specific permission
     *
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return collect($this->permissions)->contains('name', $permissionName);
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

    /**
     * Get permission IDs as array
     *
     * @return array
     */
    public function getPermissionIds(): array
    {
        return collect($this->permissions)->pluck('id')->toArray();
    }

    /**
     * Check if role has users assigned
     *
     * @return bool
     */
    public function hasUsers(): bool
    {
        return $this->users_count > 0;
    }
}
