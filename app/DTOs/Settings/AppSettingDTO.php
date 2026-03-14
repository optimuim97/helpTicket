<?php

namespace App\DTOs\Settings;

use App\DTOs\BaseDTO;

class AppSettingDTO extends BaseDTO
{
    public ?string $key = null;
    public ?string $value = null;
    public ?string $type = 'string'; // string, boolean, integer, json
    public ?string $description = null;
    public ?string $group = 'general'; // general, email, system, security

    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        $dto->key = $data['key'] ?? null;
        $dto->value = $data['value'] ?? null;
        $dto->type = $data['type'] ?? 'string';
        $dto->description = $data['description'] ?? null;
        $dto->group = $data['group'] ?? 'general';
        
        return $dto;
    }

    /**
     * Get typed value based on type
     *
     * @return mixed
     */
    public function getTypedValue()
    {
        return match($this->type) {
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    /**
     * Set value with type conversion
     *
     * @param mixed $value
     * @return void
     */
    public function setTypedValue($value): void
    {
        $this->value = match($this->type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };
    }

    /**
     * Check if setting is boolean type
     *
     * @return bool
     */
    public function isBoolean(): bool
    {
        return $this->type === 'boolean';
    }

    /**
     * Check if setting is json type
     *
     * @return bool
     */
    public function isJson(): bool
    {
        return $this->type === 'json';
    }

    /**
     * Get display label for the setting
     *
     * @return string
     */
    public function getDisplayLabel(): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $this->key ?? 'Unknown'));
    }

    /**
     * Get formatted value for display
     *
     * @return string
     */
    public function getFormattedValue(): string
    {
        $value = $this->getTypedValue();
        
        if (is_bool($value)) {
            return $value ? 'Oui' : 'Non';
        }
        
        if (is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT);
        }
        
        return (string) $value;
    }
}
