<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view the event in management.
     */
    public function view(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer() && $user->id === $event->organizer_id;
    }

    /**
     * Determine whether the user can update the event.
     * Organizer can only edit when draft.
     */
    public function update(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer()
            && $user->id === $event->organizer_id
            && $event->isDraft();
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isOrganizer()
            && $user->id === $event->organizer_id
            && $event->isDraft();
    }

    /**
     * Determine whether the user can submit the event for approval.
     */
    public function submit(User $user, Event $event): bool
    {
        return $user->isOrganizer()
            && $user->id === $event->organizer_id
            && $event->canBeSubmitted();
    }

    /**
     * Determine whether the user can approve the event.
     */
    public function approve(User $user, Event $event): bool
    {
        return $user->isAdmin() && $event->isPending();
    }

    /**
     * Determine whether the user can reject the event.
     */
    public function reject(User $user, Event $event): bool
    {
        return $user->isAdmin() && $event->isPending();
    }

    /**
     * Determine whether the user can cancel the event.
     */
    public function cancel(User $user, Event $event): bool
    {
        return $user->isAdmin() && ! $event->isCancelled();
    }
}
