<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentAssignments\StoreEquipmentAssignmentRequest;
use App\Http\Requests\EquipmentAssignments\UpdateEquipmentAssignmentRequest;
use App\Models\EquipmentAssignment;
use App\Models\EquipmentAssignmentValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EquipmentAssignmentController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', EquipmentAssignment::class);

        $query = EquipmentAssignment::with('createdBy', 'validations')
            ->search($request->search)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->operation_type, fn ($q) => $q->where('operation_type', $request->operation_type))
            ->latest();

        return Inertia::render('EquipmentAssignments/Index', [
            'assignments'    => $query->paginate(15)->withQueryString(),
            'filters'        => $request->only(['search', 'status', 'operation_type']),
            'statuses'       => EquipmentAssignment::STATUS_LABELS,
            'operationTypes' => EquipmentAssignment::OPERATION_LABELS,
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', EquipmentAssignment::class);

        return Inertia::render('EquipmentAssignments/Create');
    }

    public function store(StoreEquipmentAssignmentRequest $request): RedirectResponse
    {
        $assignment = EquipmentAssignment::create([
            ...$request->validated(),
            'created_by' => auth()->id(),
        ]);

        // Pre-create the 3 validation slots
        foreach (array_keys(EquipmentAssignmentValidation::ROLES) as $role) {
            $assignment->validations()->create(['validator_role' => $role]);
        }

        return redirect()
            ->route('equipment-assignments.show', $assignment)
            ->with('success', "Fiche d'affectation {$assignment->reference} créée avec succès.");
    }

    public function show(EquipmentAssignment $equipmentAssignment): Response
    {
        $this->authorize('view', $equipmentAssignment);

        $equipmentAssignment->load('createdBy', 'validations');

        return Inertia::render('EquipmentAssignments/Show', [
            'assignment'   => $equipmentAssignment,
            'validations'  => $equipmentAssignment->validations->keyBy('validator_role'),
            'roleLabels'   => EquipmentAssignmentValidation::ROLES,
            'statusLabels' => EquipmentAssignment::STATUS_LABELS,
        ]);
    }

    public function edit(EquipmentAssignment $equipmentAssignment): Response
    {
        $this->authorize('update', $equipmentAssignment);

        return Inertia::render('EquipmentAssignments/Edit', [
            'assignment' => $equipmentAssignment,
        ]);
    }

    public function update(UpdateEquipmentAssignmentRequest $request, EquipmentAssignment $equipmentAssignment): RedirectResponse
    {
        $equipmentAssignment->update($request->validated());

        return redirect()
            ->route('equipment-assignments.show', $equipmentAssignment)
            ->with('success', 'Fiche d\'affectation mise à jour avec succès.');
    }

    public function destroy(EquipmentAssignment $equipmentAssignment): RedirectResponse
    {
        $this->authorize('delete', $equipmentAssignment);

        $equipmentAssignment->delete();

        return redirect()
            ->route('equipment-assignments.index')
            ->with('success', 'Fiche d\'affectation supprimée.');
    }

    public function sign(Request $request, EquipmentAssignment $equipmentAssignment, string $role): RedirectResponse
    {
        $this->authorize('validate', $equipmentAssignment);

        $data = $request->validate([
            'validator_name' => ['required', 'string', 'max:255'],
            'comment'        => ['nullable', 'string', 'max:1000'],
            'signature'      => ['nullable', 'string'],
        ], [
            'validator_name.required' => 'Le nom du validateur est obligatoire.',
        ]);

        abort_unless(array_key_exists($role, EquipmentAssignmentValidation::ROLES), 404);

        $equipmentAssignment->validations()
            ->where('validator_role', $role)
            ->update([
                'validator_name' => $data['validator_name'],
                'comment'        => $data['comment'] ?? null,
                'signature'      => $data['signature'] ?? null,
                'validated_at'   => now(),
            ]);

        $count = $equipmentAssignment->validations()->whereNotNull('validated_at')->count();
        if ($count >= 3) {
            $equipmentAssignment->update(['status' => 'signe']);
        } elseif ($count >= 1) {
            $equipmentAssignment->update(['status' => 'valide']);
        }

        return back()->with('success', 'Validation enregistrée.');
    }
}
