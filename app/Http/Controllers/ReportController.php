<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function agentPerformance(Request $request): Response
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Accès refusé. Seuls les superviseurs peuvent consulter les rapports.');
        }

        $statistics = Cache::remember('report_agent_performance', 600, function () {
            $agents = User::whereHas('roles', fn ($q) =>
                $q->whereIn('name', ['Agent Helpdesk', 'Technicien', 'Superviseur'])
            )->get();

            $agentIds = $agents->pluck('id')->toArray();

            // Open status IDs for the pending count (uses the same cached list as scopeOpen)
            $openStatusIds = Cache::remember('ticket_status_open_ids', 3600, fn () =>
                TicketStatus::where('is_closed', false)->pluck('id')
            );

            $openIn = $openStatusIds->isEmpty() ? '0' : $openStatusIds->implode(',');

            // Single aggregated query instead of 4×N queries
            $rawStats = Ticket::selectRaw("
                assigned_to,
                COUNT(*) as assigned,
                SUM(CASE WHEN resolved_at IS NOT NULL THEN 1 ELSE 0 END) as resolved,
                SUM(CASE WHEN status_id IN ({$openIn}) THEN 1 ELSE 0 END) as pending,
                AVG(CASE WHEN resolved_at IS NOT NULL THEN TIMESTAMPDIFF(HOUR, created_at, resolved_at) END) as avg_hours
            ")
            ->whereIn('assigned_to', $agentIds)
            ->groupBy('assigned_to')
            ->get()
            ->keyBy('assigned_to');

            return $agents->map(function ($agent) use ($rawStats) {
                $s = $rawStats->get($agent->id);
                $assigned = (int) ($s->assigned ?? 0);
                $resolved = (int) ($s->resolved ?? 0);

                return [
                    'agent' => $agent,
                    'assigned' => $assigned,
                    'resolved' => $resolved,
                    'pending' => (int) ($s->pending ?? 0),
                    'avg_resolution_hours' => round((float) ($s->avg_hours ?? 0), 2),
                    'resolution_rate' => $assigned > 0 ? round(($resolved / $assigned) * 100, 2) : 0,
                ];
            });
        });

        return Inertia::render('Reports/AgentPerformance', [
            'statistics' => $statistics,
        ]);
    }

    public function globalStatistics(Request $request): Response
    {
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Accès refusé. Seuls les superviseurs peuvent consulter les rapports.');
        }

        $dateRange = $request->input('range', 'month');

        $ttl = match($dateRange) {
            'year' => 1800,  // 30 min — données annuelles changent peu
            'month' => 900,  // 15 min
            default => 300,  // 5 min pour la semaine
        };

        $statistics = Cache::remember("report_global_{$dateRange}", $ttl, function () use ($dateRange) {
            $startDate = match($dateRange) {
                'week' => now()->subWeek(),
                'year' => now()->subYear(),
                default => now()->subMonth(),
            };

            return [
                'total_tickets' => Ticket::where('created_at', '>=', $startDate)->count(),
                'by_status' => Ticket::where('created_at', '>=', $startDate)
                    ->select('status_id', DB::raw('count(*) as count'))
                    ->with('status')
                    ->groupBy('status_id')
                    ->get(),
                'by_priority' => Ticket::where('created_at', '>=', $startDate)
                    ->select('priority_id', DB::raw('count(*) as count'))
                    ->with('priority')
                    ->groupBy('priority_id')
                    ->get(),
                'by_type' => Ticket::where('created_at', '>=', $startDate)
                    ->select('type_id', DB::raw('count(*) as count'))
                    ->with('type')
                    ->groupBy('type_id')
                    ->get(),
                'by_channel' => Ticket::where('created_at', '>=', $startDate)
                    ->select('channel_id', DB::raw('count(*) as count'))
                    ->with('channel')
                    ->groupBy('channel_id')
                    ->get(),
                'avg_handling_hours' => round((float) Ticket::where('created_at', '>=', $startDate)
                    ->whereNotNull('closed_at')
                    ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, closed_at)) as avg_hours'))
                    ->value('avg_hours'), 2),
            ];
        });

        return Inertia::render('Reports/GlobalStatistics', [
            'statistics' => $statistics,
            'dateRange' => $dateRange,
        ]);
    }
}
