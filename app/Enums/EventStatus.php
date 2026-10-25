<?php

namespace App\Enums;

enum EventStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Bản nháp',
            self::PENDING => 'Chờ duyệt',
            self::PUBLISHED => 'Đã duyệt / Công khai',
            self::CANCELLED => 'Đã hủy',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary',
            self::PENDING => 'bg-warning text-dark',
            self::PUBLISHED => 'bg-success',
            self::CANCELLED => 'bg-danger',
        };
    }
}
