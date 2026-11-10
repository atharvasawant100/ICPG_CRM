<?php

namespace App\Policies;

use App\Models\InternalTicket;
use App\Models\User;
use App\Policies\Concerns\BasePolicy;

class InternalTicketPolicy extends BasePolicy
{
    /**
     * Anyone authenticated can view the tickets index (scoped to their own tickets unless Admin).
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Strict confidentiality rule:
     * Only the ticket creator (employee) and Administrators can view the ticket.
     * The employee's manager or colleagues can NEVER view this ticket.
     */
    public function view(User $user, InternalTicket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $ticket->user_id === (int) $user->id;
    }

    /**
     * Any authenticated employee can submit a confidential ticket.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Admin can update status, internal notes and resolution.
     */
    public function update(User $user, InternalTicket $ticket): bool
    {
        return $user->isAdmin();
    }

    /**
     * Both the ticket creator and Administrators can post messages in the discussion thread.
     */
    public function reply(User $user, InternalTicket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $ticket->user_id === (int) $user->id;
    }

    /**
     * Only Admin can delete tickets.
     */
    public function delete(User $user, InternalTicket $ticket): bool
    {
        return $user->isAdmin();
    }
}

