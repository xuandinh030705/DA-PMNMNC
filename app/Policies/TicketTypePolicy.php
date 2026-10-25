<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\TicketType;
use App\Models\User;

class TicketTypePolicy
{
    /**
     * Determine whether the user can create ticket types for the event.
     */
    public function create(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer()
            && $user->id === $event->organizer_id
            && $event->isDraft();
    }

    /**
     * Determine whether the user can update the ticket type.
     */
    public function update(User $user, TicketType $ticketType): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer()
            && $user->id === $ticketType->event->organizer_id
            && $ticketType->event->isDraft();
    }

    /**
     * Determine whether the user can delete the ticket type.
     */
    public function delete(User $user, TicketType $ticketType): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer()
            && $user->id === $ticketType->event->organizer_id
            && $ticketType->event->isDraft()
            && $ticketType->sold === 0;
    }
}
