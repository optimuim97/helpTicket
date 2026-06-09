<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isTechnicien = $user->hasRole('Technicien');

        $cacheKey = $isTechnicien
            ? "dashboard_stats_tech_{$user->id}"
            : 'dashboard_stats_global';

        $stats = Cache::remember($cacheKey, 300, function () use ($user, $isTechnicien) {
            if ($isTechnicien) {
                return [
                    'total' => Ticket::where('assigned_to', $user->id)->count(),
                    'open' => Ticket::where('assigned_to', $user->id)->open()->count(),
                    'resolved_today' => Ticket::where('assigned_to', $user->id)
                        ->whereDate('resolved_at', today())->count(),
                ];
            }

            return [
                'total' => Ticket::count(),
                'open' => Ticket::open()->count(),
                'resolved_today' => Ticket::whereDate('resolved_at', today())->count(),
            ];
        });

        // Recent tickets are not cached (must stay fresh)
        $myTickets = $isTechnicien
            ? Ticket::where('assigned_to', $user->id)
                ->with(['type', 'priority', 'status', 'createdBy'])
                ->latest()->take(10)->get()
            : Ticket::with(['type', 'priority', 'status', 'createdBy', 'assignedTo'])
                ->latest()->take(10)->get();

        return Inertia::render('Dashboard', [
            'stats' => $stats,
            'recentTickets' => $myTickets,
        ]);
    }
}
