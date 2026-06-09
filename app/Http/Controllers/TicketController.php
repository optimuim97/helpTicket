<?php

namespace App\Http\Controllers;

use App\Models\EquipmentAssignment;
use App\Models\InterventionSheet;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\TicketChannel;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\User;
use App\Notifications\TicketCreatedNotification;
use App\Notifications\TicketAssignedNotification;
use App\Notifications\TicketResolvedNotification;
use App\Notifications\TicketClosedNotification;
use App\Notifications\DeadlineExtendedNotification;
use App\Services\TicketNumberGenerator;
use App\Services\DuplicateDetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class TicketController extends Controller
{
    /**
     * Display a listing of tickets.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $query = Ticket::query()->with([
            'type',
            'channel',
            'priority',
            'status',
            'assignedTo',
            'createdBy'
        ]);

        // Role-based filtering
        if ($user->hasRole('Technicien')) {
            $query->where('assigned_to', $user->id);
        }

        // Apply manual search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        // Apply other filters
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->input('status_id'));
        }
        if ($request->filled('priority_id')) {
            $query->where('priority_id', $request->input('priority_id'));
        }
        if ($request->filled('type_id')) {
            $query->where('type_id', $request->input('type_id'));
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->input('assigned_to'));
        }

        $tickets = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        $users = null;
        // Only Agents and Supervisors can see all users for filtering
        if (!$user->hasRole('Technicien')) {
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur']);
            })->get();
        }

        return Inertia::render('Tickets/Index', [
            'tickets' => $tickets,
            'filters' => $request->only(['search', 'status_id', 'priority_id', 'type_id', 'assigned_to']),
            'statuses' => TicketStatus::allCached(),
            'priorities' => TicketPriority::allCached(),
            'types' => TicketType::allCached(),
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new ticket.
     */
    public function create(): Response
    {
        $this->authorize('create', Ticket::class);

        return Inertia::render('Tickets/Create', [
            'types'                => TicketType::allCached(),
            'channels'             => TicketChannel::allCached(),
            'priorities'           => TicketPriority::allCached(),
            'projects'             => Project::orderBy('name')->get(['id', 'name', 'status']),
            'users'                => User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur']))
                ->orderBy('name')->get(['id', 'name']),
            'preselectedProjectId' => request()->query('project_id'),
            'interventionSheets'   => InterventionSheet::select('id', 'reference', 'site', 'agent_name', 'status')
                ->latest()->limit(100)->get(),
            'equipmentAssignments' => EquipmentAssignment::select('id', 'reference', 'agent_name', 'equipment_type', 'status')
                ->latest()->limit(100)->get(),
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request, TicketNumberGenerator $generator)
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'project_id'    => 'nullable|exists:projects,id',
            'type_id'       => 'required|exists:ticket_types,id',
            'channel_id'    => 'required|exists:ticket_channels,id',
            'priority_id'   => 'required|exists:ticket_priorities,id',
            'subject'       => 'required|string|max:255',
            'description'   => 'required|string',
            'notes'         => 'nullable|string',
            'assigned_to'   => 'nullable|exists:users,id',
            'due_date'      => 'nullable|date|after:now',

            // Soit on lie à une fiche existante…
            'linkable_type' => 'nullable|in:intervention_sheet,equipment_assignment',
            'linkable_id'   => 'nullable|integer|required_with:linkable_type',

            // …soit on crée une fiche en parallèle au ticket.
            'create_intervention_sheet'              => 'nullable|boolean',
            'intervention_sheet.site'                => 'required_if:create_intervention_sheet,true|nullable|string|max:255',
            'intervention_sheet.building'            => 'required_if:create_intervention_sheet,true|nullable|string|max:255',
            'intervention_sheet.agent_name'          => 'required_if:create_intervention_sheet,true|nullable|string|max:255',
            'intervention_sheet.incidence'           => 'nullable|in:critique,majeur,mineur',
            'intervention_sheet.reported_fault'      => 'nullable|string',

            'create_equipment_assignment'              => 'nullable|boolean',
            'equipment_assignment.equipment_type'      => 'required_if:create_equipment_assignment,true|nullable|string|max:255',
            'equipment_assignment.equipment_model'     => 'required_if:create_equipment_assignment,true|nullable|string|max:255',
            'equipment_assignment.equipment_serial'    => 'required_if:create_equipment_assignment,true|nullable|string|max:255',
            'equipment_assignment.agent_matricule'     => 'required_if:create_equipment_assignment,true|nullable|string|max:50',
            'equipment_assignment.agent_name'          => 'required_if:create_equipment_assignment,true|nullable|string|max:255',
            'equipment_assignment.agent_direction'     => 'nullable|string|max:255',
            'equipment_assignment.agent_department'    => 'nullable|string|max:255',
            'equipment_assignment.operation_type'      => 'nullable|in:affectation,remplacement',
        ]);

        $defaultStatus = Cache::remember('status_id_nouveau', 3600, fn () =>
            TicketStatus::where('name', 'Nouveau')->first()
        );

        $linkableType = $validated['linkable_type'] ?? null;
        $linkableId   = $validated['linkable_id'] ?? null;

        // Création éventuelle d'une fiche d'intervention liée
        if ($request->boolean('create_intervention_sheet') && empty($linkableType)) {
            $sheet = InterventionSheet::create([
                'site'           => $validated['intervention_sheet']['site'],
                'building'       => $validated['intervention_sheet']['building'],
                'agent_name'     => $validated['intervention_sheet']['agent_name'],
                'incidence'      => $validated['intervention_sheet']['incidence'] ?? 'mineur',
                'reported_fault' => $validated['intervention_sheet']['reported_fault'] ?? null,
                'status'         => 'brouillon',
                'start_date'     => now(),
                'created_by'     => $request->user()->id,
            ]);
            $linkableType = 'intervention_sheet';
            $linkableId   = $sheet->id;
        }

        // Création éventuelle d'une fiche d'affectation liée
        if ($request->boolean('create_equipment_assignment') && empty($linkableType)) {
            $assignment = EquipmentAssignment::create([
                'equipment_type'   => $validated['equipment_assignment']['equipment_type'],
                'equipment_model'  => $validated['equipment_assignment']['equipment_model'],
                'equipment_serial' => $validated['equipment_assignment']['equipment_serial'],
                'agent_matricule'  => $validated['equipment_assignment']['agent_matricule'],
                'agent_name'       => $validated['equipment_assignment']['agent_name'],
                'agent_direction'  => $validated['equipment_assignment']['agent_direction'] ?? '',
                'agent_department' => $validated['equipment_assignment']['agent_department'] ?? '',
                'operation_type'   => $validated['equipment_assignment']['operation_type'] ?? 'affectation',
                'status'           => 'brouillon',
                'created_by'       => $request->user()->id,
            ]);
            // Pré-créer les 3 slots de validation
            foreach (['chef_atelier', 'utilisateur', 'chef_service'] as $role) {
                $assignment->validations()->create(['validator_role' => $role]);
            }
            $linkableType = 'equipment_assignment';
            $linkableId   = $assignment->id;
        }

        $ticket = Ticket::create([
            'ticket_number' => $generator->generate(),
            'project_id'    => $validated['project_id'] ?? null,
            'type_id'       => $validated['type_id'],
            'channel_id'    => $validated['channel_id'],
            'priority_id'   => $validated['priority_id'],
            'status_id'     => $defaultStatus->id,
            'subject'       => $validated['subject'],
            'description'   => $validated['description'],
            'notes'         => $validated['notes'] ?? null,
            'assigned_to'   => $validated['assigned_to'] ?? null,
            'created_by'    => $request->user()->id,
            'due_date'      => $validated['due_date'] ?? null,
            'linkable_type' => $linkableType,
            'linkable_id'   => $linkableId,
        ]);

        $supervisors = Cache::remember('users_role_superviseur', 300, fn () =>
            User::whereHas('roles', fn ($q) => $q->where('name', 'Superviseur'))->get()
        );

        Notification::send($supervisors, new TicketCreatedNotification($ticket->load(['priority', 'type', 'createdBy'])));

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket créé avec succès.');
    }

    /**
     * Check for duplicate tickets before creating.
     */
    public function checkDuplicates(Request $request, DuplicateDetectionService $duplicateService)
    {
        $request->validate([
            'subject' => 'required|string',
            'description' => 'required|string',
            'type_id' => 'required|integer',
            'priority_id' => 'required|integer',
        ]);

        $duplicates = $duplicateService->findDuplicates(
            $request->subject,
            $request->description,
            $request->type_id,
            $request->priority_id
        );

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Request $request, Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'type',
            'channel',
            'priority',
            'status',
            'assignedTo',
            'createdBy',
            'ticketNotes.user',
            'attachments.user',
            'history.user',
            'assignments.assignedFrom',
            'assignments.assignedTo',
            'linkable',
        ]);

        $users = null;
        if ($request->user()->hasRole('Superviseur')) {
            $users = User::whereHas('roles', function ($query) {
                $query->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur']);
            })->get();
        }

        return Inertia::render('Tickets/Show', [
            'ticket' => $ticket,
            'users' => $users,
            'canUpdate' => $request->user()->can('update', $ticket),
            'canAssign' => $request->user()->can('assign', $ticket),
            'canClose' => $request->user()->can('close', $ticket),
            'canDelete' => $request->user()->can('delete', $ticket),
            'canResolve' => $request->user()->can('resolve', $ticket),
            'canExtendDeadline' => $request->user()->can('extendDeadline', $ticket),
        ]);
    }

    /**
     * Show the form for editing the ticket.
     */
    public function edit(Ticket $ticket): Response
    {
        $this->authorize('update', $ticket);

        return Inertia::render('Tickets/Edit', [
            'ticket'   => $ticket->load(['type', 'channel', 'priority', 'status', 'project']),
            'types'    => TicketType::allCached(),
            'channels' => TicketChannel::allCached(),
            'priorities' => TicketPriority::allCached(),
            'statuses' => TicketStatus::allCached(),
            'projects' => Project::orderBy('name')->get(['id', 'name', 'status']),
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'project_id'  => 'nullable|exists:projects,id',
            'type_id'     => 'sometimes|exists:ticket_types,id',
            'channel_id'  => 'sometimes|exists:ticket_channels,id',
            'priority_id' => 'sometimes|exists:ticket_priorities,id',
            'status_id'   => 'sometimes|exists:ticket_statuses,id',
            'subject'     => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'notes'       => 'nullable|string',
            'due_date'    => 'nullable|date',
        ]);

        $ticket->update($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket mis à jour avec succès.');
    }

    /**
     * Assign ticket to a user.
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $this->authorize('assign', $ticket);

        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $previousAssignee = $ticket->assigned_to;

        $ticket->update([
            'assigned_to' => $validated['assigned_to'],
        ]);

        // Create assignment record
        $ticket->assignments()->create([
            'assigned_from' => $previousAssignee,
            'assigned_to' => $validated['assigned_to'],
            'assigned_at' => now(),
        ]);

        // Notify assigned user
        $assignedUser = User::find($validated['assigned_to']);
        if ($assignedUser) {
            $assignedUser->notify(new TicketAssignedNotification($ticket->load(['priority', 'type', 'createdBy'])));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket assigné avec succès.');
    }

    /**
     * Close a resolved ticket.
     */
    public function close(Ticket $ticket)
    {
        $this->authorize('close', $ticket);

        $closedStatus = Cache::remember('status_id_ferme', 3600, fn () =>
            TicketStatus::where('name', 'Fermé')->first()
        );
        $resolvedStatus = Cache::remember('status_id_resolu', 3600, fn () =>
            TicketStatus::where('name', 'Résolu')->first()
        );

        $ticket->update([
            'status_id' => $closedStatus->id,
            'resolved_at' => now(),
            'closed_at' => now(),
        ]);

        // Notify ticket creator
        if ($ticket->createdBy) {
            $ticket->createdBy->notify(new TicketClosedNotification($ticket->load(['priority', 'assignedTo'])));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket fermé avec succès.');
    }

    /**
     * Mark ticket as resolved.
     */
    public function resolve(Ticket $ticket)
    {
        $this->authorize('resolve', $ticket);

        $resolvedStatus = Cache::remember('status_id_resolu', 3600, fn () =>
            TicketStatus::where('name', 'Résolu')->first()
        );

        $ticket->update([
            'status_id' => $resolvedStatus->id,
            'resolved_at' => now(),
        ]);

        // Notify ticket creator
        if ($ticket->createdBy) {
            $ticket->createdBy->notify(new TicketResolvedNotification($ticket->load(['priority', 'assignedTo'])));
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Ticket marqué comme résolu.');
    }

    /**
     * Extend ticket deadline.
     */
    public function extendDeadline(Request $request, Ticket $ticket)
    {
        $this->authorize('extendDeadline', $ticket);

        $validated = $request->validate([
            'due_date' => 'required|date|after:now',
        ]);

        $oldDueDate = $ticket->due_date;

        $ticket->update([
            'due_date' => $validated['due_date'],
        ]);

        // Notify ticket creator
        if ($ticket->createdBy) {
            $ticket->createdBy->notify(new DeadlineExtendedNotification($ticket, $oldDueDate, $validated['due_date']));
        }

        $supervisors = Cache::remember('users_role_superviseur', 300, fn () =>
            User::whereHas('roles', fn ($q) => $q->where('name', 'Superviseur'))->get()
        );

        foreach ($supervisors as $supervisor) {
            if ($supervisor->id !== $ticket->created_by) {
                $supervisor->notify(new DeadlineExtendedNotification($ticket, $oldDueDate, $validated['due_date']));
            }
        }

        return redirect()->route('tickets.show', $ticket)->with('success', 'Délai repoussé avec succès.');
    }

    /**
     * Remove the specified ticket.
     */
    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Ticket supprimé avec succès.');
    }
}
