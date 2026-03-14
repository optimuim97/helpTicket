<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get statistics based on user role
        if ($user->hasRole('Technicien')) {
            // Technicians see only their assigned tickets
            $totalTickets = Ticket::where('assigned_to', $user->id)->count();
            $openTickets = Ticket::where('assigned_to', $user->id)->open()->count();
            $myTickets = Ticket::where('assigned_to', $user->id)
                ->with(['type', 'priority', 'status', 'createdBy'])
                ->latest()
                ->take(10)
                ->get();
        } else {
            // Agents and Supervisors see all tickets
            $totalTickets = Ticket::count();
            $openTickets = Ticket::open()->count();
            $myTickets = Ticket::with(['type', 'priority', 'status', 'createdBy', 'assignedTo'])
                ->latest()
                ->take(10)
                ->get();
        }

        $resolvedToday = Ticket::whereDate('resolved_at', today())->count();
        
        return Inertia::render('Dashboard', [
            'stats' => [
                'total' => $totalTickets,
                'open' => $openTickets,
                'resolved_today' => $resolvedToday,
            ],
            'recentTickets' => $myTickets,
        ]);
    }
}
