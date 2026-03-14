<?php

namespace App\Http\Controllers;

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
            'statuses' => TicketStatus::all(),
            'priorities' => TicketPriority::all(),
            'types' => TicketType::all(),
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
            'types' => TicketType::all(),
            'channels' => TicketChannel::all(),
            'priorities' => TicketPriority::all(),
        ]);
    }

    /**
     * Store a newly created ticket.
     */
    public function store(Request $request, TicketNumberGenerator $generator)
    {
        $this->authorize('create', Ticket::class);

        $validated = $request->validate([
            'type_id' => 'required|exists:ticket_types,id',
            'channel_id' => 'required|exists:ticket_channels,id',
            'priority_id' => 'required|exists:ticket_priorities,id',
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date|after:now',
        ]);

        // Get default "Nouveau" status
        $defaultStatus = TicketStatus::where('name', 'Nouveau')->first();

        $ticket = Ticket::create([
            'ticket_number' => $generator->generate(),
            'type_id' => $validated['type_id'],
            'channel_id' => $validated['channel_id'],
            'priority_id' => $validated['priority_id'],
            'status_id' => $defaultStatus->id,
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => $request->user()->id,
            'due_date' => $validated['due_date'] ?? null,
        ]);

        // Notify supervisors about new ticket
        $supervisors = User::whereHas('roles', function ($query) {
            $query->where('name', 'Superviseur');
        })->get();
        
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
            'ticket' => $ticket->load(['type', 'channel', 'priority', 'status']),
            'types' => TicketType::all(),
            'channels' => TicketChannel::all(),
            'priorities' => TicketPriority::all(),
            'statuses' => TicketStatus::all(),
        ]);
    }

    /**
     * Update the specified ticket.
     */
    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'type_id' => 'sometimes|exists:ticket_types,id',
            'channel_id' => 'sometimes|exists:ticket_channels,id',
            'priority_id' => 'sometimes|exists:ticket_priorities,id',
            'status_id' => 'sometimes|exists:ticket_statuses,id',
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'notes' => 'nullable|string',
            'due_date' => 'nullable|date',
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

        $closedStatus = TicketStatus::where('name', 'Fermé')->first();
        $resolvedStatus = TicketStatus::where('name', 'Résolu')->first();

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

        $resolvedStatus = TicketStatus::where('name', 'Résolu')->first();

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

        // Notify supervisors
        $supervisors = User::whereHas('roles', function ($query) {
            $query->where('name', 'Superviseur');
        })->get();
        
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
