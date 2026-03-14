<?php

namespace App\Notifications;

use App\Models\Ticket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeadlineExtendedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Ticket $ticket,
        public $oldDueDate,
        public $newDueDate
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
            ->subject("Délai repoussé: {$this->ticket->ticket_number}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Le délai du ticket a été repoussé.")
            ->line("**Numéro:** {$this->ticket->ticket_number}")
            ->line("**Sujet:** {$this->ticket->subject}")
            ->line("**Ancien délai:** " . ($this->oldDueDate ? \Carbon\Carbon::parse($this->oldDueDate)->format('d/m/Y H:i') : 'Non défini'))
            ->line("**Nouveau délai:** " . \Carbon\Carbon::parse($this->newDueDate)->format('d/m/Y H:i'))
            ->action('Voir le ticket', route('tickets.show', $this->ticket->id))
            ->line('Merci de votre compréhension.');
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
            'old_due_date' => $this->oldDueDate,
            'new_due_date' => $this->newDueDate,
            'message' => "Le délai du ticket {$this->ticket->ticket_number} a été repoussé.",
        ];
    }
}
