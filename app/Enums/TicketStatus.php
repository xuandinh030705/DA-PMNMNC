<?php

namespace App\Enums;

enum TicketStatus: string
{
    case VALID = 'valid';
    case USED = 'used';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::VALID => 'Hợp lệ',
            self::USED => 'Đã sử dụng',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::VALID => 'bg-success',
            self::USED => 'bg-info text-dark',
            self::CANCELLED => 'bg-danger',
        };
    }
}
