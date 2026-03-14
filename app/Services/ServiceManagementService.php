<?php

namespace App\Services;

use App\DTOs\Services\ServiceCreateDTO;
use App\DTOs\Services\ServiceDTO;
use App\DTOs\Services\ServiceUpdateDTO;
use App\Models\Service;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ServiceManagementService
{
    /**
     * Get all services with optional filters
     *
     * @param array $filters
     * @return Collection
     */
    public function getAllServices(array $filters = []): Collection
    {
        $query = Service::query();

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        // Filter by search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Include user count
        if (!empty($filters['with_users_count'])) {
            $query->withCount('users');
        }

        // Include users
        if (!empty($filters['with_users'])) {
            $query->with('users');
        }

        // Order by name
        $query->orderBy('name');

        return ServiceDTO::collection($query->get());
    }

    /**
     * Get service by ID
     *
     * @param int $id
     * @return ServiceDTO|null
     */
    public function getServiceById(int $id): ?ServiceDTO
    {
        $service = Service::with('users')->find($id);

        return $service ? ServiceDTO::fromModel($service) : null;
    }

    /**
     * Create a new service
     *
     * @param ServiceCreateDTO $dto
     * @return ServiceDTO
     * @throws \Throwable
     */
    public function createService(ServiceCreateDTO $dto): ServiceDTO
    {
        return DB::transaction(function () use ($dto) {
            $service = Service::create($dto->toServiceData());
            $service->load('users');

            return ServiceDTO::fromModel($service);
        });
    }

    /**
     * Update existing service
     *
     * @param int $id
     * @param ServiceUpdateDTO $dto
     * @return ServiceDTO
     * @throws \Throwable
     */
    public function updateService(int $id, ServiceUpdateDTO $dto): ServiceDTO
    {
        return DB::transaction(function () use ($id, $dto) {
            $service = Service::findOrFail($id);

            $serviceData = $dto->toServiceData();
            if (!empty($serviceData)) {
                $service->update($serviceData);
            }

            $service->load('users');

            return ServiceDTO::fromModel($service);
        });
    }

    /**
     * Delete service
     *
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function deleteService(int $id): bool
    {
        $service = Service::findOrFail($id);

        // Check if service has users
        if ($service->users()->exists()) {
            throw new \Exception('Cannot delete service that has users assigned.');
        }

        return $service->delete();
    }

    /**
     * Toggle service active status
     *
     * @param int $id
     * @return ServiceDTO
     */
    public function toggleActiveStatus(int $id): ServiceDTO
    {
        $service = Service::findOrFail($id);
        $service->update(['is_active' => !$service->is_active]);
        $service->load('users');

        return ServiceDTO::fromModel($service);
    }

    /**
     * Get active services only
     *
     * @return Collection
     */
    public function getActiveServices(): Collection
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return ServiceDTO::collection($services);
    }

    /**
     * Get users count for service
     *
     * @param int $id
     * @return int
     */
    public function getUsersCount(int $id): int
    {
        $service = Service::findOrFail($id);
        return $service->users()->count();
    }

    /**
     * Check if service name is unique (excluding given service ID)
     *
     * @param string $name
     * @param int|null $excludeId
     * @return bool
     */
    public function isNameUnique(string $name, ?int $excludeId = null): bool
    {
        $query = Service::where('name', $name);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }

    /**
     * Get service statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        return [
            'total_services' => Service::count(),
            'active_services' => Service::where('is_active', true)->count(),
            'inactive_services' => Service::where('is_active', false)->count(),
            'services_with_users' => Service::has('users')->count(),
        ];
    }
}
