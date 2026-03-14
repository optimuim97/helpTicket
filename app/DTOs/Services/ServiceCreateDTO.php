<?php

namespace App\DTOs\Services;

use App\DTOs\BaseDTO;

class ServiceCreateDTO extends BaseDTO
{
    public string $name;
    public ?string $description = null;
    public bool $is_active = true;

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
        $dto->description = $data['description'] ?? null;
        $dto->is_active = $data['is_active'] ?? true;
        
        return $dto;
    }

    /**
     * Get data for service creation
     *
     * @return array
     */
    public function toServiceData(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }

    /**
     * Check if service has description
     *
     * @return bool
     */
    public function hasDescription(): bool
    {
        return !empty($this->description);
    }
}
