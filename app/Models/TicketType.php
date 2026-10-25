<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'price',
        'quantity',
        'sold',
        'sale_start',
        'sale_end',
        'max_per_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:0',
            'quantity' => 'integer',
            'sold' => 'integer',
            'max_per_order' => 'integer',
            'sale_start' => 'datetime',
            'sale_end' => 'datetime',
        ];
    }

    // Accessor: remaining = quantity - sold
    protected function remaining(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, $this->quantity - $this->sold)
        );
    }

    public function isOnSale(): bool
    {
        $now = now();
        return $this->event->isPublished()
            && $this->event->start_time > $now
            && $now->gte($this->sale_start)
            && $now->lte($this->sale_end)
            && $this->remaining > 0;
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
