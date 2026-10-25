<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1 Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Quản Trị Viên Hệ Thống',
                'password' => Hash::make('password'),
                'phone' => '0901234567',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
            ]
        );

        // 2 Organizers
        User::firstOrCreate(
            ['email' => 'organizer1@example.com'],
            [
                'name' => 'Ban Tổ Chức Sao Mai',
                'password' => Hash::make('password'),
                'phone' => '0912345678',
                'role' => User::ROLE_ORGANIZER,
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'organizer2@example.com'],
            [
                'name' => 'Công Ty Âm Nhạc Trẻ',
                'password' => Hash::make('password'),
                'phone' => '0923456789',
                'role' => User::ROLE_ORGANIZER,
                'is_active' => true,
            ]
        );

        // 5 Customers
        $customerNames = [
            'Nguyễn Văn An',
            'Trần Thị Bình',
            'Lê Hoàng Cường',
            'Phạm Thúy Dung',
            'Hoàng Minh Đức',
        ];

        foreach ($customerNames as $index => $name) {
            $num = $index + 1;
            User::firstOrCreate(
                ['email' => "customer{$num}@example.com"],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'phone' => "093456789{$num}",
                    'role' => User::ROLE_CUSTOMER,
                    'is_active' => true,
                ]
            );
        }
    }
}
