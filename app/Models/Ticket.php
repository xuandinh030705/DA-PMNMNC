<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Ticket extends Model
{
    use HasFactory;

    public const STATUS_VALID = 'valid';

    public const STATUS_USED = 'used';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'order_item_id',
        'ticket_code',
        'qr_payload',
        'qr_signature',
        'status',
        'checked_in_at',
        'checked_in_by',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket): void {
            if (empty($ticket->qr_payload)) {
                $ticket->qr_payload = static::buildQrPayload($ticket->ticket_code);
            }

            if (empty($ticket->qr_signature)) {
                $ticket->qr_signature = static::generateQrSignature($ticket->qr_payload);
            }
        });
    }

    public static function buildQrPayload(string $ticketCode): string
    {
        return json_encode([
            'v' => 1,
            'ticket_code' => $ticketCode,
        ], JSON_THROW_ON_ERROR);
    }

    public static function generateQrSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    public function hasValidQrSignature(): bool
    {
        if (empty($this->qr_payload) || empty($this->qr_signature)) {
            return false;
        }

        return hash_equals(static::generateQrSignature($this->qr_payload), $this->qr_signature);
    }

    public static function generateUniqueTicketCode(): string
    {
        do {
            $code = 'TKT-'.strtoupper(Str::random(10));
        } while (static::where('ticket_code', $code)->exists());

        return $code;
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_VALID => 'Hợp lệ',
            self::STATUS_USED => 'Đã sử dụng',
            self::STATUS_CANCELLED => 'Đã hủy',
            default => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_VALID => 'bg-success',
            self::STATUS_USED => 'bg-info text-dark',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-secondary',
        };
    }
}
