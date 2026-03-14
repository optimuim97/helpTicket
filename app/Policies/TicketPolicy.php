<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view tickets
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Ticket $ticket): bool
    {
        // Superviseurs can view all tickets
        if ($user->hasRole('Superviseur')) {
            return true;
        }

        // Agents can view all tickets
        if ($user->hasRole('Agent Helpdesk')) {
            return true;
        }

        // Techniciens can only view tickets assigned to them
        if ($user->hasRole('Technicien')) {
            return $ticket->assigned_to === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Agents and Superviseurs can create tickets
        return $user->hasAnyRole(['Agent Helpdesk', 'Superviseur']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Ticket $ticket): bool
    {
        // Superviseurs can update any ticket
        if ($user->hasRole('Superviseur')) {
            return true;
        }

        // Agents can update tickets they created
        if ($user->hasRole('Agent Helpdesk') && $ticket->created_by === $user->id) {
            return true;
        }

        // Techniciens can update tickets assigned to them  
        if ($user->hasRole('Technicien') && $ticket->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can assign the ticket.
     */
    public function assign(User $user, Ticket $ticket): bool
    {
        // Only Superviseurs can assign tickets
        return $user->hasRole('Superviseur');
    }

    /**
     * Determine whether the user can close the ticket.
     */
    public function close(User $user, Ticket $ticket): bool
    {
        // Superviseurs can close any ticket
        if ($user->hasRole('Superviseur')) {
            return true;
        }

        // Techniciens can close tickets assigned to them
        if ($user->hasRole('Technicien') && $ticket->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can resolve the ticket.
     */
    public function resolve(User $user, Ticket $ticket): bool
    {
        // Superviseurs can resolve any ticket
        if ($user->hasRole('Superviseur')) {
            return true;
        }

        // Techniciens can resolve tickets assigned to them
        if ($user->hasRole('Technicien') && $ticket->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can extend the ticket deadline.
     */
    public function extendDeadline(User $user, Ticket $ticket): bool
    {
        // Superviseurs can extend any ticket deadline
        if ($user->hasRole('Superviseur')) {
            return true;
        }

        // Techniciens can extend deadline for tickets assigned to them
        if ($user->hasRole('Technicien') && $ticket->assigned_to === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Ticket $ticket): bool
    {
        // Only Superviseurs can delete tickets
        return $user->hasRole('Superviseur');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Ticket $ticket): bool
    {
        // Only Superviseurs can restore tickets
        return $user->hasRole('Superviseur');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Ticket $ticket): bool
    {
        // Only Superviseurs can permanently delete tickets
        return $user->hasRole('Superviseur');
    }
}
