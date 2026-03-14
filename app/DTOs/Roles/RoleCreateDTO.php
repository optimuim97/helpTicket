<?php

namespace App\DTOs\Roles;

use App\DTOs\BaseDTO;

class RoleCreateDTO extends BaseDTO
{
    public string $name;
    public string $guard_name = 'web';
    public array $permissions = [];

    /**
     * Create DTO from validated request data
     *
     * @param array $data
     * @return static
     */
    public static function fromRequest(array $data): static
    {
        $dto = new static();
        
        $dto->name = $data['name'];
        $dto->guard_name = $data['guard_name'] ?? 'web';
        $dto->permissions = $data['permissions'] ?? [];
        
        return $dto;
    }

    /**
     * Get data for role creation
     *
     * @return array
     */
    public function toRoleData(): array
    {
        return [
            'name' => $this->name,
            'guard_name' => $this->guard_name,
        ];
    }

    /**
     * Check if permissions should be assigned
     *
     * @return bool
     */
    public function hasPermissions(): bool
    {
        return !empty($this->permissions);
    }

    /**
     * Get permission IDs (support both id and name formats)
     *
     * @return array
     */
    public function getPermissionIds(): array
    {
        return array_map(function ($permission) {
            return is_array($permission) ? ($permission['id'] ?? $permission) : $permission;
        }, $this->permissions);
    }
}
