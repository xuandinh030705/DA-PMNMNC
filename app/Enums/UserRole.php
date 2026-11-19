<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case ORGANIZER = 'organizer';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Quản trị viên',
            self::ORGANIZER => 'Ban tổ chức',
            self::CUSTOMER => 'Khách hàng',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::ADMIN => 'badge-role-admin',
            self::ORGANIZER => 'badge-role-organizer',
            self::CUSTOMER => 'badge-role-customer',
        };
    }
}
