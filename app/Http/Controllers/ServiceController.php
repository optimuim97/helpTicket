<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    /**
     * Display a listing of services.
     */
    public function index(): Response
    {
        abort_unless(auth()->user()->can('view_services'), 403);

        $services = Service::withCount('users')
            ->orderBy('name')
            ->get();

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(): Response
    {
        abort_unless(auth()->user()->can('create_services'), 403);

        return Inertia::render('Services/Create');
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        abort_unless(auth()->user()->can('create_services'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Service::create($validated);

        return redirect()->route('services.index')->with('success', 'Service créé avec succès.');
    }

    /**
     * Show the form for editing the specified service.
     */
    public function edit(Service $service): Response
    {
        abort_unless(auth()->user()->can('update_services'), 403);

        $service->load('users');

        return Inertia::render('Services/Edit', [
            'service' => $service,
        ]);
    }

    /**
     * Update the specified service.
     */
    public function update(Request $request, Service $service)
    {
        abort_unless(auth()->user()->can('update_services'), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:services,name,' . $service->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $service->update($validated);

        return redirect()->route('services.index')->with('success', 'Service mis à jour avec succès.');
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Service $service)
    {
        abort_unless(auth()->user()->can('delete_services'), 403);

        // Users will have service_id set to null automatically (onDelete('set null'))
        $service->delete();

        return redirect()->route('services.index')->with('success', 'Service supprimé avec succès.');
    }
}
