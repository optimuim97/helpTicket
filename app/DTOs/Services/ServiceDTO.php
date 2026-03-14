<?php

namespace App\DTOs\Services;

use App\DTOs\BaseDTO;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;

class ServiceDTO extends BaseDTO
{
    public ?int $id = null;
    public ?string $name = null;
    public ?string $description = null;
    public ?bool $is_active = true;
    public ?int $users_count = 0;
    public ?array $users = [];
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Create DTO from Service model with relationships
     *
     * @param Model $model
     * @return static
     */
    public static function fromModel(Model $model): static
    {
        /** @var Service $model */
        $dto = new static();
        
        $dto->id = $model->id;
        $dto->name = $model->name;
        $dto->description = $model->description;
        $dto->is_active = $model->is_active;
        $dto->created_at = $model->created_at?->toISOString();
        $dto->updated_at = $model->updated_at?->toISOString();
        
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
     * Get display name for the service
     *
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->name ?? 'Unknown Service';
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel(): string
    {
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    /**
     * Check if service is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Check if service has users assigned
     *
     * @return bool
     */
    public function hasUsers(): bool
    {
        return $this->users_count > 0;
    }

    /**
     * Get user IDs as array
     *
     * @return array
     */
    public function getUserIds(): array
    {
        return collect($this->users)->pluck('id')->toArray();
    }
}
