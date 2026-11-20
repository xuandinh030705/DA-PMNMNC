<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_code',
        'customer_name',
        'customer_email',
        'customer_phone',
        'total_amount',
        'status',
        'payment_method',
        'paid_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:0',
            'status' => OrderStatus::class,
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public static function generateUniqueOrderCode(): string
    {
        do {
            $code = 'ORD-'.strtoupper(Str::random(8));
        } while (static::where('order_code', $code)->exists());

        return $code;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tickets(): HasManyThrough
    {
        return $this->hasManyThrough(Ticket::class, OrderItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === OrderStatus::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this->isPending() && $this->expires_at && now()->gt($this->expires_at);
    }

    public function statusLabel(): string
    {
        return $this->status?->label() ?? '';
    }

    public function statusBadgeClass(): string
    {
        return $this->status?->badgeClass() ?? 'bg-secondary';
    }
}
