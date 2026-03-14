<?php

namespace App\Observers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\TicketStatus;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Support\Facades\Auth;

class TicketObserver
{
    /**
     * Handle the Ticket "created" event.
     */
    public function created(Ticket $ticket): void
    {
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id() ?? $ticket->created_by,
            'action' => 'created',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => "Ticket créé: {$ticket->subject}",
        ]);
    }

    /**
     * Handle the Ticket "updating" event.
     */
    public function updating(Ticket $ticket): void
    {
        // Track which fields changed
        $changes = $ticket->getDirty();
        $original = $ticket->getOriginal();

        foreach ($changes as $field => $newValue) {
            // Skip tracking for some fields
            if (in_array($field, ['updated_at'])) {
                continue;
            }

            $oldValue = $original[$field] ?? null;

            // Format values for better readability
            $oldValueFormatted = $this->formatValue($field, $oldValue, $ticket);
            $newValueFormatted = $this->formatValue($field, $newValue, $ticket);

            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'field_changed' => $field,
                'old_value' => $oldValueFormatted,
                'new_value' => $newValueFormatted,
            ]);

            // Send notification when status changes
            if ($field === 'status_id' && $oldValue !== $newValue) {
                $oldStatus = TicketStatus::find($oldValue)?->name ?? 'N/A';
                $newStatus = TicketStatus::find($newValue)?->name ?? 'N/A';
                
                // Notify ticket creator and assigned user
                $notifiables = collect([$ticket->createdBy, $ticket->assignedTo])
                    ->filter()
                    ->unique('id');
                
                foreach ($notifiables as $user) {
                    $user->notify(new TicketStatusChangedNotification($ticket, $oldStatus, $newStatus));
                }
            }
        }
    }

    /**
     * Handle the Ticket "deleted" event.
     */
    public function deleted(Ticket $ticket): void
    {
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => 'Ticket supprimé',
        ]);
    }

    /**
     * Handle the Ticket "restored" event.
     */
    public function restored(Ticket $ticket): void
    {
        TicketHistory::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'action' => 'restored',
            'field_changed' => null,
            'old_value' => null,
            'new_value' => 'Ticket restauré',
        ]);
    }

    /**
     * Format value for history display
     */
    private function formatValue(string $field, $value, Ticket $ticket): ?string
    {
        if ($value === null) {
            return null;
        }

        // Format specific fields
        switch ($field) {
            case 'status_id':
                return \App\Models\TicketStatus::find($value)?->name ?? $value;
            case 'priority_id':
                return \App\Models\TicketPriority::find($value)?->name ?? $value;
            case 'type_id':
                return \App\Models\TicketType::find($value)?->name ?? $value;
            case 'channel_id':
                return \App\Models\TicketChannel::find($value)?->name ?? $value;
            case 'assigned_to':
                return \App\Models\User::find($value)?->name ?? $value;
            case 'created_by':
                return \App\Models\User::find($value)?->name ?? $value;
            case 'resolved_at':
            case 'closed_at':
            case 'due_date':
                return $value ? date('Y-m-d H:i:s', strtotime($value)) : null;
            default:
                return (string) $value;
        }
    }
}
