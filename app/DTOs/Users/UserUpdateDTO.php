<?php

namespace App\DTOs\Users;

use App\DTOs\BaseDTO;

class UserUpdateDTO extends BaseDTO
{
    public ?string $name = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?int $service_id = null;
    public ?string $role = null;
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
        
        if (isset($data['email'])) {
            $dto->email = $data['email'];
        }
        
        if (isset($data['password']) && !empty($data['password'])) {
            $dto->password = $data['password'];
        }
        
        if (isset($data['service_id'])) {
            $dto->service_id = $data['service_id'];
        }
        
        if (isset($data['role'])) {
            $dto->role = $data['role'];
        }
        
        if (isset($data['permissions'])) {
            $dto->permissions = $data['permissions'];
        }
        
        return $dto;
    }

    /**
     * Get data for user update (exclude empty and password)
     *
     * @return array
     */
    public function toUserData(): array
    {
        $data = [];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        
        if ($this->email !== null) {
            $data['email'] = $this->email;
        }
        
        if ($this->service_id !== null) {
            $data['service_id'] = $this->service_id;
        }
        
        return $data;
    }

    /**
     * Get hashed password if provided
     *
     * @return string|null
     */
    public function getHashedPassword(): ?string
    {
        return $this->password ? bcrypt($this->password) : null;
    }

    /**
     * Check if password should be updated
     *
     * @return bool
     */
    public function shouldUpdatePassword(): bool
    {
        return !empty($this->password);
    }

    /**
     * Check if role should be updated
     *
     * @return bool
     */
    public function shouldUpdateRole(): bool
    {
        return $this->role !== null;
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
}
