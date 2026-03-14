<?php

namespace App\DTOs\Roles;

use App\DTOs\BaseDTO;

class RoleUpdateDTO extends BaseDTO
{
    public ?string $name = null;
    public ?string $guard_name = null;
    public ?array $permissions = null;

    /**
     * Create DTO from validated request data
     *
     * @param array $data
     * @return static
     */
    public static function fromRequest(array $data): static
    {
        $dto = new static();
        
        if (isset($data['name'])) {
            $dto->name = $data['name'];
        }
        
        if (isset($data['guard_name'])) {
            $dto->guard_name = $data['guard_name'];
        }
        
        if (isset($data['permissions'])) {
            $dto->permissions = $data['permissions'];
        }
        
        return $dto;
    }

    /**
     * Get data for role update
     *
     * @return array
     */
    public function toRoleData(): array
    {
        $data = [];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        
        if ($this->guard_name !== null) {
            $data['guard_name'] = $this->guard_name;
        }
        
        return $data;
    }

    /**
     * Check if permissions should be updated
     *
     * @return bool
     */
    public function shouldUpdatePermissions(): bool
    {
        return $this->permissions !== null;
    }

    /**
     * Get permission IDs (support both id and name formats)
     *
     * @return array
     */
    public function getPermissionIds(): array
    {
        if ($this->permissions === null) {
            return [];
        }
        
        return array_map(function ($permission) {
            return is_array($permission) ? ($permission['id'] ?? $permission) : $permission;
        }, $this->permissions);
    }
}
