<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketAssignedNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject("Ticket assigné: {$this->ticket->ticket_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un ticket vous a été assigné.")
            ->line("**Numéro:** {$this->ticket->ticket_number}")
            ->line("**Sujet:** {$this->ticket->subject}")
            ->line("**Priorité:** {$this->ticket->priority->name}")
            ->line("**Créé par:** {$this->ticket->createdBy->name}")
            ->action('Voir le ticket', route('tickets.show', $this->ticket->id))
            ->line('Merci de traiter ce ticket dans les meilleurs délais.');
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
            'priority' => $this->ticket->priority->name,
            'message' => "Ticket {$this->ticket->ticket_number} vous a été assigné",
        ];
    }
}
