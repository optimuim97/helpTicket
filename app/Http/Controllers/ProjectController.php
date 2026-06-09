<?php

namespace App\Http\Controllers;

use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\TicketChannel;
use App\Models\TicketPriority;
use App\Models\TicketStatus;
use App\Models\TicketType;
use App\Models\User;
use App\Services\TicketNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->can('view_projects'), 403);

        $query = Project::withCount('tickets')->with('createdBy');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters'  => $request->only(['search', 'status']),
            'statuses' => Project::STATUSES,
        ]);
    }

    public function create(Request $request): Response
    {
        abort_unless($request->user()->can('create_projects'), 403);

        return Inertia::render('Projects/Create', [
            'statuses'   => Project::STATUSES,
            'types'      => TicketType::allCached(),
            'priorities' => TicketPriority::allCached(),
            'channels'   => TicketChannel::allCached(),
            'users'      => User::whereHas('roles', fn ($q) => $q->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur']))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StoreProjectRequest $request, TicketNumberGenerator $generator)
    {
        $data = $request->validated();
        $tasks = $data['tasks'] ?? [];
        unset($data['tasks']);

        $project = DB::transaction(function () use ($data, $tasks, $request, $generator) {
            $project = Project::create([
                ...$data,
                'created_by' => $request->user()->id,
            ]);

            if (! empty($tasks)) {
                $defaultStatus = Cache::remember('status_id_nouveau', 3600, fn () =>
                    TicketStatus::where('name', 'Nouveau')->first()
                );
                $defaultChannel = TicketChannel::first();

                foreach ($tasks as $task) {
                    Ticket::create([
                        'ticket_number' => $generator->generate(),
                        'project_id'    => $project->id,
                        'type_id'       => $task['type_id'],
                        'channel_id'    => $task['channel_id'] ?? $defaultChannel?->id,
                        'priority_id'   => $task['priority_id'],
                        'status_id'     => $defaultStatus->id,
                        'subject'       => $task['subject'],
                        'description'   => $task['description'] ?? $task['subject'],
                        'assigned_to'   => $task['assigned_to'] ?? null,
                        'due_date'      => $task['due_date'] ?? null,
                        'created_by'    => $request->user()->id,
                    ]);
                }
            }

            return $project;
        });

        $count = count($tasks);
        $msg = $count > 0
            ? "Projet créé avec {$count} tâche".($count > 1 ? 's' : '').'.'
            : 'Projet créé avec succès.';

        return redirect()->route('projects.show', $project)->with('success', $msg);
    }

    public function show(Request $request, Project $project): Response
    {
        abort_unless($request->user()->can('view_projects'), 403);

        $project->load([
            'createdBy',
            'tickets.status',
            'tickets.priority',
            'tickets.type',
            'tickets.assignedTo',
            'tickets.createdBy',
        ]);

        $tickets = $project->tickets;

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'statuses' => Project::STATUSES,
            'stats' => [
                'total'    => $tickets->count(),
                'open'     => $tickets->filter(fn ($t) => $t->status && !$t->status->is_closed)->count(),
                'resolved' => $tickets->filter(fn ($t) => $t->resolved_at !== null)->count(),
                'closed'   => $tickets->filter(fn ($t) => $t->status && $t->status->is_closed)->count(),
            ],
            'canEdit'   => $request->user()->can('edit_projects'),
            'canDelete' => $request->user()->can('delete_projects'),
        ]);
    }

    public function edit(Request $request, Project $project): Response
    {
        abort_unless($request->user()->can('edit_projects'), 403);

        return Inertia::render('Projects/Edit', [
            'project'  => $project,
            'statuses' => Project::STATUSES,
        ]);
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $project->update($request->validated());

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy(Request $request, Project $project)
    {
        abort_unless($request->user()->can('delete_projects'), 403);

        if ($project->tickets()->exists()) {
            return back()->withErrors([
                'error' => 'Impossible de supprimer un projet qui contient des tickets. Retirez d\'abord les tickets du projet.',
            ]);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }
}
