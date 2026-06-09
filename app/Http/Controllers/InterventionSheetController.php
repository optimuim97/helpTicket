<?php

namespace App\Http\Controllers;

use App\Http\Requests\InterventionSheets\StoreInterventionSheetRequest;
use App\Http\Requests\InterventionSheets\UpdateInterventionSheetRequest;
use App\Models\InterventionSheet;
use App\Models\InterventionSheetValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InterventionSheetController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', InterventionSheet::class);

        $query = InterventionSheet::with('createdBy', 'validations')
            ->search($request->search)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->incidence, fn ($q) => $q->where('incidence', $request->incidence))
            ->latest();

        return Inertia::render('InterventionSheets/Index', [
            'sheets'     => $query->paginate(15)->withQueryString(),
            'filters'    => $request->only(['search', 'status', 'incidence']),
            'statuses'   => InterventionSheet::STATUS_LABELS,
            'incidences' => InterventionSheet::INCIDENCE_LABELS,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', InterventionSheet::class);

        return Inertia::render('InterventionSheets/Create', [
            'availableServices' => InterventionSheet::AVAILABLE_SERVICES,
            'availableEpi'      => InterventionSheet::AVAILABLE_EPI,
        ]);
    }

    public function store(StoreInterventionSheetRequest $request): RedirectResponse
    {
        $sheet = InterventionSheet::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        // Pre-create the 3 validation slots
        foreach (array_keys(InterventionSheetValidation::ROLES) as $role) {
            $sheet->validations()->create(['validator_role' => $role]);
        }

        return redirect()
            ->route('intervention-sheets.show', $sheet)
            ->with('success', "Fiche d'intervention {$sheet->reference} créée avec succès.");
    }

    public function show(InterventionSheet $interventionSheet): Response
    {
        $this->authorize('view', $interventionSheet);

        $interventionSheet->load('createdBy', 'validations');

        return Inertia::render('InterventionSheets/Show', [
            'sheet'        => $interventionSheet,
            'validations'  => $interventionSheet->validations->keyBy('validator_role'),
            'roleLabels'   => InterventionSheetValidation::ROLES,
            'statusLabels' => InterventionSheet::STATUS_LABELS,
        ]);
    }

    public function edit(InterventionSheet $interventionSheet): Response
    {
        $this->authorize('update', $interventionSheet);

        return Inertia::render('InterventionSheets/Edit', [
            'sheet'             => $interventionSheet,
            'availableServices' => InterventionSheet::AVAILABLE_SERVICES,
            'availableEpi'      => InterventionSheet::AVAILABLE_EPI,
        ]);
    }

    public function update(UpdateInterventionSheetRequest $request, InterventionSheet $interventionSheet): RedirectResponse
    {
        $interventionSheet->update($request->validated());

        return redirect()
            ->route('intervention-sheets.show', $interventionSheet)
            ->with('success', 'Fiche d\'intervention mise à jour avec succès.');
    }

    public function destroy(InterventionSheet $interventionSheet): RedirectResponse
    {
        $this->authorize('delete', $interventionSheet);

        $interventionSheet->delete();

        return redirect()
            ->route('intervention-sheets.index')
            ->with('success', 'Fiche d\'intervention supprimée.');
    }

    public function sign(Request $request, InterventionSheet $interventionSheet, string $role): RedirectResponse
    {
        $this->authorize('validate', $interventionSheet);

        $data = $request->validate([
            'validator_name' => ['required', 'string', 'max:255'],
            'comment'        => ['nullable', 'string', 'max:1000'],
            'signature'      => ['nullable', 'string'],
        ], [
            'validator_name.required' => 'Le nom du validateur est obligatoire.',
        ]);

        abort_unless(array_key_exists($role, InterventionSheetValidation::ROLES), 404);

        $interventionSheet->validations()
            ->where('validator_role', $role)
            ->update([
                'validator_name' => $data['validator_name'],
                'comment'        => $data['comment'] ?? null,
                'signature'      => $data['signature'] ?? null,
                'validated_at'   => now(),
            ]);

        $count = $interventionSheet->validations()->whereNotNull('validated_at')->count();
        if ($count >= 3) {
            $interventionSheet->update(['status' => 'signe']);
        } elseif ($count >= 1) {
            $interventionSheet->update(['status' => 'valide']);
        }

        return back()->with('success', 'Validation enregistrée.');
    }
}
