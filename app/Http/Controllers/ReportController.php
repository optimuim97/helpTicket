<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Display agent performance statistics.
     */
    public function agentPerformance(Request $request): Response
    {
        // Only supervisors can view reports
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Accès refusé. Seuls les superviseurs peuvent consulter les rapports.');
        }

        $agents = User::whereHas('roles', function ($query) {
            $query->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur']);
        })->get();

        $statistics = $agents->map(function ($agent) {
            $assignedTickets = Ticket::where('assigned_to', $agent->id)->count();
            $resolvedTickets = Ticket::where('assigned_to', $agent->id)
                ->whereNotNull('resolved_at')
                ->count();
            $pendingTickets = Ticket::where('assigned_to', $agent->id)
                ->open()
                ->count();

            // Calculate average resolution time in hours
            $avgResolutionTime = Ticket::where('assigned_to', $agent->id)
                ->whereNotNull('resolved_at')
                ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) as avg_hours'))
                ->value('avg_hours');

            return [
                'agent' => $agent,
                'assigned' => $assignedTickets,
                'resolved' => $resolvedTickets,
                'pending' => $pendingTickets,
                'avg_resolution_hours' => $avgResolutionTime ? round($avgResolutionTime, 2) : 0,
                'resolution_rate' => $assignedTickets > 0 ? round(($resolvedTickets / $assignedTickets) * 100, 2) : 0,
            ];
        });

        return Inertia::render('Reports/AgentPerformance', [
            'statistics' => $statistics,
        ]);
    }

    /**
     * Display global system statistics.
     */
    public function globalStatistics(Request $request): Response
    {
        // Only supervisors can view reports
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Accès refusé. Seuls les superviseurs peuvent consulter les rapports.');
        }

        $dateRange = $request->input('range', 'month'); // month, week, year

        $startDate = match($dateRange) {
            'week' => now()->subWeek(),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };

        // Total tickets
        $totalTickets = Ticket::where('created_at', '>=', $startDate)->count();

        // Tickets by status
        $ticketsByStatus = Ticket::where('created_at', '>=', $startDate)
            ->select('status_id', DB::raw('count(*) as count'))
            ->with('status')
            ->groupBy('status_id')
            ->get();

        // Tickets by priority
        $ticketsByPriority = Ticket::where('created_at', '>=', $startDate)
            ->select('priority_id', DB::raw('count(*) as count'))
            ->with('priority')
            ->groupBy('priority_id')
            ->get();

        // Tickets by type
        $ticketsByType = Ticket::where('created_at', '>=', $startDate)
            ->select('type_id', DB::raw('count(*) as count'))
            ->with('type')
            ->groupBy('type_id')
            ->get();

        // Tickets by channel
        $ticketsByChannel = Ticket::where('created_at', '>=', $startDate)
            ->select('channel_id', DB::raw('count(*) as count'))
            ->with('channel')
            ->groupBy('channel_id')
            ->get();

        // Average handling time
        $avgHandlingTime = Ticket::where('created_at', '>=', $startDate)
            ->whereNotNull('closed_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_hours'))
            ->value('avg_hours');

        return Inertia::render('Reports/GlobalStatistics', [
            'statistics' => [
                'total_tickets' => $totalTickets,
                'by_status' => $ticketsByStatus,
                'by_priority' => $ticketsByPriority,
                'by_type' => $ticketsByType,
                'by_channel' => $ticketsByChannel,
                'avg_handling_hours' => $avgHandlingTime ? round($avgHandlingTime, 2) : 0,
            ],
            'dateRange' => $dateRange,
        ]);
    }
}
