<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            [
                'name' => 'Trung tâm Hội nghị Quốc gia Hà Nội',
                'address' => 'Số 57 Đường Phạm Hùng, Mễ Trì, Nam Từ Liêm',
                'city' => 'Hà Nội',
                'capacity' => 3800,
            ],
            [
                'name' => 'Nhà thi đấu Quân khu 7',
                'address' => 'Số 202 Hoàng Văn Thụ, Phường 9, Phú Nhuận',
                'city' => 'TP. Hồ Chí Minh',
                'capacity' => 5000,
            ],
            [
                'name' => 'Cung Thể thao Tiên Sơn',
                'address' => 'Đường Phan Đăng Lưu, Hòa Cường Bắc, Hải Châu',
                'city' => 'Đà Nẵng',
                'capacity' => 4500,
            ],
            [
                'name' => 'Nhà hát Hòa Bình',
                'address' => 'Số 240 Đường Ba Tháng Hai, Phường 12, Quận 10',
                'city' => 'TP. Hồ Chí Minh',
                'capacity' => 2500,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::firstOrCreate(
                ['name' => $venue['name']],
                $venue
            );
        }
    }
}
