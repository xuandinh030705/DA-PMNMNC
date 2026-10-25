<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Âm nhạc & Hòa nhạc',
                'description' => 'Các buổi biểu diễn âm nhạc đỉnh cao, liveshow và concert hoành tráng.',
            ],
            [
                'name' => 'Hội thảo & Workshop',
                'description' => 'Các sự kiện chia sẻ kiến thức chuyên sâu, kết nối doanh nghiệp và nâng cao kỹ năng.',
            ],
            [
                'name' => 'Thể thao & Giải đấu',
                'description' => 'Các giải chạy marathon, bóng đá, e-sports và hoạt động thể thao sôi động.',
            ],
            [
                'name' => 'Nghệ thuật & Sân khấu',
                'description' => 'Vở kịch, triển lãm tranh, hòa nhạc thính phòng và nghệ thuật đương đại.',
            ],
            [
                'name' => 'Công nghệ & Triển lãm',
                'description' => 'Triển lãm thiết bị công nghệ hiện đại, ngày hội khởi nghiệp và đổi mới sáng tạo.',
            ],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(
                ['name' => $cat['name']],
                [
                    'slug' => Str::slug($cat['name']),
                    'description' => $cat['description'],
                ]
            );
        }
    }
}
