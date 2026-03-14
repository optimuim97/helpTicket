<?php

namespace App\DTOs;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseDTO
{
    /**
     * Convert DTO to array
     *
     * @return array
     */
    public function toArray(): array
    {
        $result = [];
        
        foreach (get_object_vars($this) as $property => $value) {
            $result[$property] = $this->convertValue($value);
        }
        
        return $result;
    }

    /**
     * Convert complex values to arrays
     *
     * @param mixed $value
     * @return mixed
     */
    protected function convertValue($value)
    {
        if ($value instanceof BaseDTO) {
            return $value->toArray();
        }
        
        if ($value instanceof Collection) {
            return $value->map(fn($item) => $this->convertValue($item))->toArray();
        }
        
        if (is_array($value)) {
            return array_map(fn($item) => $this->convertValue($item), $value);
        }
        
        return $value;
    }

    /**
     * Create DTO from array
     *
     * @param array $data
     * @return static
     */
    public static function fromArray(array $data): static
    {
        $dto = new static();
        
        foreach ($data as $key => $value) {
            if (property_exists($dto, $key)) {
                $dto->{$key} = $value;
            }
        }
        
        return $dto;
    }

    /**
     * Create DTO from Eloquent model
     *
     * @param Model $model
     * @return static
     */
    public static function fromModel(Model $model): static
    {
        return static::fromArray($model->toArray());
    }

    /**
     * Create collection of DTOs from array of models
     *
     * @param iterable $models
     * @return Collection
     */
    public static function collection(iterable $models): Collection
    {
        return collect($models)->map(fn($model) => static::fromModel($model));
    }

    /**
     * Fill a model with DTO data
     *
     * @param Model $model
     * @return Model
     */
    public function fillModel(Model $model): Model
    {
        $model->fill($this->toArray());
        return $model;
    }

    /**
     * Get only the fillable attributes for a model
     *
     * @param array $fillable
     * @return array
     */
    public function only(array $fillable): array
    {
        return array_intersect_key($this->toArray(), array_flip($fillable));
    }

    /**
     * Exclude specific attributes
     *
     * @param array $exclude
     * @return array
     */
    public function except(array $exclude): array
    {
        return array_diff_key($this->toArray(), array_flip($exclude));
    }

    /**
     * Convert DTO to JSON
     *
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Create DTO from JSON string
     *
     * @param string $json
     * @return static
     */
    public static function fromJson(string $json): static
    {
        return static::fromArray(json_decode($json, true));
    }

    /**
     * Check if DTO has a property with a non-null value
     *
     * @param string $property
     * @return bool
     */
    public function has(string $property): bool
    {
        return property_exists($this, $property) && $this->{$property} !== null;
    }

    /**
     * Get a property value or default
     *
     * @param string $property
     * @param mixed $default
     * @return mixed
     */
    public function get(string $property, $default = null)
    {
        return $this->has($property) ? $this->{$property} : $default;
    }
}
