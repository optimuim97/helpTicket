<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $assignedTo = $this->ticket->assignedTo?->name ?? 'N/A';
        
        return (new MailMessage)
            ->subject("Ticket fermé: {$this->ticket->ticket_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre ticket a été fermé.")
            ->line("**Numéro:** {$this->ticket->ticket_number}")
            ->line("**Sujet:** {$this->ticket->subject}")
            ->line("**Fermé par:** {$assignedTo}")
            ->action('Voir le ticket', route('tickets.show', $this->ticket->id))
            ->line('Si vous avez besoin d\'une assistance supplémentaire, n\'hésitez pas à créer un nouveau ticket.')
            ->line('Merci d\'utiliser notre système de helpdesk!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_number' => $this->ticket->ticket_number,
            'subject' => $this->ticket->subject,
            'closed_by' => $this->ticket->assignedTo?->name ?? 'N/A',
            'message' => "Ticket {$this->ticket->ticket_number} a été fermé",
        ];
    }
}
