<?php

namespace App\Http\Controllers;

use App\Http\Requests\Services\StoreServiceRequest;
use App\Http\Requests\Services\UpdateServiceRequest;
use App\Models\Service;
use App\Services\ServiceManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    protected ServiceManagementService $serviceManagementService;

    public function __construct(ServiceManagementService $serviceManagementService)
    {
        $this->serviceManagementService = $serviceManagementService;
    }

    /**
     * Display a listing of services.
     */
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('view_services'), 403);

        // Get filters from request
        $filters = [
            'search' => $request->get('search'),
            'is_active' => $request->get('is_active'),
            'with_users_count' => true,
        ];

        $services = $this->serviceManagementService->getAllServices(array_filter($filters, function ($value) {
            return $value !== null;
        }));

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('create_services'), 403);

        return Inertia::render('Services/Create');
    }

    /**
     * Store a newly created service.
     */
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        try {
            $this->serviceManagementService->createService($request->toDTO());

            return redirect()->route('services.index')->with('success', 'Service créé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Request $request, Service $service): Response
    {
        abort_unless($request->user()->can('edit_services'), 403);

        $serviceDTO = $this->serviceManagementService->getServiceById($service->id);

        return Inertia::render('Services/Edit', [
            'service' => $serviceDTO,
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(UpdateServiceRequest $request, Service $service): RedirectResponse
    {
        try {
            $this->serviceManagementService->updateService($service->id, $request->toDTO());

            return redirect()->route('services.index')->with('success', 'Service mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Request $request, Service $service): RedirectResponse
    {
        abort_unless($request->user()->can('delete_services'), 403);

        try {
            $this->serviceManagementService->deleteService($service->id);

            return redirect()->route('services.index')->with('success', 'Service supprimé avec succès.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
