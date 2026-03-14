<?php

namespace App\Services;

use App\Models\Ticket;

class TicketNumberGenerator
{
    /**
     * Generate a unique ticket number in the format TCK-YYYY-NNNNN
     *
     * @return string
     */
    public function generate(): string
    {
        $year = date('Y');
        $prefix = "TCK-{$year}-";

        // Get the last ticket number for the current year
        $lastTicket = Ticket::where('ticket_number', 'LIKE', "{$prefix}%")
            ->orderBy('ticket_number', 'desc')
            ->first();

        if ($lastTicket) {
            // Extract the number part and increment
            $lastNumber = (int) substr($lastTicket->ticket_number, -5);
            $nextNumber = $lastNumber + 1;
        } else {
            // First ticket of the year
            $nextNumber = 1;
        }

        // Format with leading zeros
        $ticketNumber = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        // Verify uniqueness (in case of race conditions)
        if (Ticket::where('ticket_number', $ticketNumber)->exists()) {
            // If it exists, recursively try again
            return $this->generate();
        }

        return $ticketNumber;
    }
}
