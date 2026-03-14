<?php

namespace App\DTOs\Users;

use App\DTOs\BaseDTO;

class UserCreateDTO extends BaseDTO
{
    public string $name;
    public string $email;
    public string $password;
    public ?int $service_id = null;
    public ?string $role = null;
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
        $dto->email = $data['email'];
        $dto->password = $data['password'];
        $dto->service_id = $data['service_id'] ?? null;
        $dto->role = $data['role'] ?? null;
        $dto->permissions = $data['permissions'] ?? [];
        
        return $dto;
    }

    /**
     * Get data for user creation (without password)
     *
     * @return array
     */
    public function toUserData(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'service_id' => $this->service_id,
        ];
    }

    /**
     * Get hashed password
     *
     * @return string
     */
    public function getHashedPassword(): string
    {
        return bcrypt($this->password);
    }

    /**
     * Check if role is assigned
     *
     * @return bool
     */
    public function hasRole(): bool
    {
        return !empty($this->role);
    }

    /**
     * Check if permissions are assigned
     *
     * @return bool
     */
    public function hasPermissions(): bool
    {
        return !empty($this->permissions);
    }
}
