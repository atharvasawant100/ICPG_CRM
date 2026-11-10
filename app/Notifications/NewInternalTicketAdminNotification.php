<?php

namespace App\Notifications;

use App\Models\InternalTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dispatched to Company Administrators when an employee raises a confidential ticket.
 * Never sent to the employee's manager.
 */
class NewInternalTicketAdminNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InternalTicket $ticket
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ticket = $this->ticket;
        $userName = $ticket->user?->name ?? 'An employee';
        $category = $ticket->categoryLabel();

        return [
            'type' => 'internal_ticket_created',
            'ticket_id' => $ticket->id,
            'title' => '🛡️ Confidential Ticket: ' . $ticket->ticket_number,
            'message' => "Confidential Ticket {$ticket->ticket_number} raised by {$userName} ({$category}): \"{$ticket->subject}\"",
            'priority' => $ticket->priority,
            'url' => route('tickets.show', $ticket),
        ];
    }
}

