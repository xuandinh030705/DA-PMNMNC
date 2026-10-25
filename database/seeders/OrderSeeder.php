<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $c1 = User::where('email', 'customer1@example.com')->first();
        $c2 = User::where('email', 'customer2@example.com')->first();
        $c3 = User::where('email', 'customer3@example.com')->first();

        $ticketTypes = TicketType::whereHas('event', fn($q) => $q->where('status', 'published'))->get();
        if ($ticketTypes->isEmpty()) {
            return;
        }

        $tt1 = $ticketTypes[0];
        $tt2 = $ticketTypes[1] ?? $ticketTypes[0];

        // 1. Order Paid (Customer 1): 2 tickets of tt1
        $qty1 = 2;
        $subtotal1 = $qty1 * $tt1->price;
        $order1 = Order::create([
            'user_id' => $c1->id,
            'order_code' => Order::generateUniqueOrderCode(),
            'customer_name' => $c1->name,
            'customer_email' => $c1->email,
            'customer_phone' => $c1->phone,
            'total_amount' => $subtotal1,
            'status' => Order::STATUS_PAID,
            'payment_method' => 'bank_transfer',
            'paid_at' => now()->subHours(2),
            'expires_at' => null,
        ]);

        $item1 = OrderItem::create([
            'order_id' => $order1->id,
            'ticket_type_id' => $tt1->id,
            'quantity' => $qty1,
            'unit_price' => $tt1->price,
            'subtotal' => $subtotal1,
        ]);

        $tt1->increment('sold', $qty1);

        // Ticket 1: used (check-in)
        Ticket::create([
            'order_item_id' => $item1->id,
            'ticket_code' => Ticket::generateUniqueTicketCode(),
            'status' => Ticket::STATUS_USED,
            'checked_in_at' => now()->subHour(),
        ]);

        // Ticket 2: valid
        Ticket::create([
            'order_item_id' => $item1->id,
            'ticket_code' => Ticket::generateUniqueTicketCode(),
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
        ]);

        // 2. Order Paid (Customer 2): 1 ticket of tt2
        $qty2 = 1;
        $subtotal2 = $qty2 * $tt2->price;
        $order2 = Order::create([
            'user_id' => $c2->id,
            'order_code' => Order::generateUniqueOrderCode(),
            'customer_name' => $c2->name,
            'customer_email' => $c2->email,
            'customer_phone' => $c2->phone,
            'total_amount' => $subtotal2,
            'status' => Order::STATUS_PAID,
            'payment_method' => 'momo',
            'paid_at' => now()->subMinutes(30),
            'expires_at' => null,
        ]);

        $item2 = OrderItem::create([
            'order_id' => $order2->id,
            'ticket_type_id' => $tt2->id,
            'quantity' => $qty2,
            'unit_price' => $tt2->price,
            'subtotal' => $subtotal2,
        ]);

        $tt2->increment('sold', $qty2);

        Ticket::create([
            'order_item_id' => $item2->id,
            'ticket_code' => Ticket::generateUniqueTicketCode(),
            'status' => Ticket::STATUS_VALID,
            'checked_in_at' => null,
        ]);

        // 3. Order Pending (Customer 3)
        $qty3 = 1;
        $subtotal3 = $qty3 * $tt1->price;
        $order3 = Order::create([
            'user_id' => $c3->id,
            'order_code' => Order::generateUniqueOrderCode(),
            'customer_name' => $c3->name,
            'customer_email' => $c3->email,
            'customer_phone' => $c3->phone,
            'total_amount' => $subtotal3,
            'status' => Order::STATUS_PENDING,
            'payment_method' => null,
            'paid_at' => null,
            'expires_at' => now()->addMinutes(15),
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'ticket_type_id' => $tt1->id,
            'quantity' => $qty3,
            'unit_price' => $tt1->price,
            'subtotal' => $subtotal3,
        ]);

        $tt1->increment('sold', $qty3);

        // 4. Order Cancelled (Customer 1)
        $qty4 = 1;
        $subtotal4 = $qty4 * $tt2->price;
        $order4 = Order::create([
            'user_id' => $c1->id,
            'order_code' => Order::generateUniqueOrderCode(),
            'customer_name' => $c1->name,
            'customer_email' => $c1->email,
            'customer_phone' => $c1->phone,
            'total_amount' => $subtotal4,
            'status' => Order::STATUS_CANCELLED,
            'payment_method' => null,
            'paid_at' => null,
            'expires_at' => now()->subMinutes(50),
        ]);

        OrderItem::create([
            'order_id' => $order4->id,
            'ticket_type_id' => $tt2->id,
            'quantity' => $qty4,
            'unit_price' => $tt2->price,
            'subtotal' => $subtotal4,
        ]);
        // Sold was returned when cancelled, so no sold increment here
    }
}
