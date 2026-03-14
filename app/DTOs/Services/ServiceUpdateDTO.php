<?php

namespace App\DTOs\Services;

use App\DTOs\BaseDTO;

class ServiceUpdateDTO extends BaseDTO
{
    public ?string $name = null;
    public ?string $description = null;
    public ?bool $is_active = null;

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
        
        if (isset($data['description'])) {
            $dto->description = $data['description'];
        }
        
        if (isset($data['is_active'])) {
            $dto->is_active = $data['is_active'];
        }
        
        return $dto;
    }

    /**
     * Get data for service update
     *
     * @return array
     */
    public function toServiceData(): array
    {
        $data = [];
        
        if ($this->name !== null) {
            $data['name'] = $this->name;
        }
        
        if ($this->description !== null) {
            $data['description'] = $this->description;
        }
        
        if ($this->is_active !== null) {
            $data['is_active'] = $this->is_active;
        }
        
        return $data;
    }

    /**
     * Check if any data should be updated
     *
     * @return bool
     */
    public function hasChanges(): bool
    {
        return $this->name !== null || 
               $this->description !== null || 
               $this->is_active !== null;
    }
}
