<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketNote;
use Illuminate\Http\Request;

class TicketNoteController extends Controller
{
    /**
     * Store a new note for a ticket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'note' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $ticket->ticketNotes()->create([
            'user_id' => $request->user()->id,
            'note' => $validated['note'],
            'is_internal' => $validated['is_internal'] ?? false,
        ]);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Note ajoutée avec succès.');
    }

    /**
     * Update an existing note.
     */
    public function update(Request $request, Ticket $ticket, TicketNote $note)
    {
        $this->authorize('update', $ticket);

        // Only the note creator can edit it
        if ($note->user_id !== $request->user()->id && !$request->user()->hasRole('Superviseur')) {
            abort(403, 'Vous ne pouvez modifier que vos propres notes.');
        }

        $validated = $request->validate([
            'note' => 'required|string',
            'is_internal' => 'boolean',
        ]);

        $note->update($validated);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Note mise à jour avec succès.');
    }

    /**
     * Remove a note.
     */
    public function destroy(Request $request, Ticket $ticket, TicketNote $note)
    {
        // Only supervisors can delete notes
        if (!$request->user()->hasRole('Superviseur')) {
            abort(403, 'Seuls les superviseurs peuvent supprimer des notes.');
        }

        $note->delete();

        return redirect()->route('tickets.show', $ticket)->with('success', 'Note supprimée avec succès.');
    }
}
