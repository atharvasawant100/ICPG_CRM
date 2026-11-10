<?php

namespace App\Notifications;

use App\Models\InternalTicket;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Dispatched to the employee when Administrator responds to or resolves their confidential ticket.
 */
class InternalTicketResolvedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected InternalTicket $ticket,
        protected User $admin
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $ticket = $this->ticket;
        $statusLabel = $ticket->statusLabel();

        return [
            'type' => 'internal_ticket_updated',
            'ticket_id' => $ticket->id,
            'title' => '🛡️ Admin Update on Ticket: ' . $ticket->ticket_number,
            'message' => "Administrator {$this->admin->name} updated your ticket {$ticket->ticket_number} to [{$statusLabel}].",
            'status' => $ticket->status,
            'url' => route('tickets.show', $ticket),
        ];
    }
}

