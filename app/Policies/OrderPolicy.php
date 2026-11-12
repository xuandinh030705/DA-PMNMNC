<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->id === $order->user_id) {
            return true;
        }

        if ($user->isOrganizer()) {
            // Organizer can view if order has ticket types belonging to their events
            return $order->orderItems()->whereHas('ticketType.event', function ($query) use ($user) {
                $query->where('organizer_id', $user->id);
            })->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can pay for the order.
     */
    public function pay(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->isPending();
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        return $user->id === $order->user_id
            && $order->isPending();
    }
}
