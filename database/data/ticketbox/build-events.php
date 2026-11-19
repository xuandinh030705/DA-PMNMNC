<?php

use App\Support\TextFormatter;
use Illuminate\Support\Str;

/**
 * Chuyển dữ liệu Ticketbox (bản gốc lấy từ repo EzTicket-Website,
 * nhánh develop-Frontend, thư mục importDataTicketBox) sang định dạng
 * phù hợp với schema của ứng dụng Event Ticket (Laravel).
 *
 * Cách chạy:
 *   php database/data/ticketbox/build-events.php
 *
 * Đầu vào : source/ticketbox_merged.json
 * Đầu ra  : events.json + images/*.jpg (banner đã chuẩn hoá 16:9)
 */
$baseDir = __DIR__;
$projectRoot = dirname(__DIR__, 3);
if (is_file($projectRoot.'/vendor/autoload.php')) {
    require $projectRoot.'/vendor/autoload.php';
}

$sourceFile = $baseDir.'/source/ticketbox_merged.json';
$imagesDir = $baseDir.'/images';
$outputFile = $baseDir.'/events.json';

if (! is_file($sourceFile)) {
    fwrite(STDERR, "Không tìm thấy dữ liệu nguồn: {$sourceFile}\n");
    exit(1);
}

if (! is_dir($imagesDir) && ! mkdir($imagesDir, 0777, true) && ! is_dir($imagesDir)) {
    fwrite(STDERR, "Không tạo được thư mục ảnh: {$imagesDir}\n");
    exit(1);
}

$raw = json_decode(file_get_contents($sourceFile), true, 512, JSON_THROW_ON_ERROR);
$allEvents = array_map(static fn (array $row) => $row['data']['result'], $raw);

/**
 * Danh sách sự kiện được chọn (index trong file nguồn) kèm thiết lập hiển thị.
 * capacity: sức chứa ước tính dùng để chia tồn kho cho các hạng vé.
 */
$picks = [
    0 => ['category' => 'nghe-thuat-san-khau', 'capacity' => 588],
    5 => ['category' => 'am-nhac-hoa-nhac', 'capacity' => 3000],
    16 => ['category' => 'am-nhac-hoa-nhac', 'capacity' => 900],
    1 => ['category' => 'tham-quan-trai-nghiem', 'capacity' => 500],
    34 => ['category' => 'tham-quan-trai-nghiem', 'capacity' => 1200],
    2 => ['category' => 'nghe-thuat-san-khau', 'capacity' => 800],
    10 => ['category' => 'nghe-thuat-san-khau', 'capacity' => 500],
    42 => ['category' => 'nghe-thuat-san-khau', 'capacity' => 1500],
    19 => ['category' => 'nghe-thuat-san-khau', 'capacity' => 2500],
    26 => ['category' => 'hoi-thao-workshop', 'capacity' => 60],
    37 => ['category' => 'cong-nghe-trien-lam', 'capacity' => 600],
    51 => ['category' => 'hoi-thao-workshop', 'capacity' => 300],
    33 => ['category' => 'the-thao-giai-dau', 'capacity' => 3500],
    41 => ['category' => 'the-thao-giai-dau', 'capacity' => 400],
];

// Tỉ trọng tồn kho theo hạng vé (giá thấp -> cao), tổng = 1.
$stockWeights = [0.45, 0.25, 0.15, 0.08, 0.05, 0.02];

$normalizeCity = static function (string $address): string {
    $city = trim((string) preg_replace('/\s+/u', ' ', $address));
    $parts = array_map('trim', explode(',', $city));
    $city = end($parts) ?: $city;

    return match (true) {
        str_contains($city, 'Hồ Chí Minh') => 'TP. Hồ Chí Minh',
        str_contains($city, 'Hà Nội') => 'Hà Nội',
        str_contains($city, 'Đà Nẵng') => 'Đà Nẵng',
        str_contains($city, 'Cần Thơ') => 'Cần Thơ',
        str_contains($city, 'Hải Phòng') => 'Hải Phòng',
        default => trim((string) preg_replace('/^(Thành phố|Tỉnh|TP\.?)\s+/u', '', $city)),
    };
};

$plainText = static function (?string $html, int $limit = 1400): string {
    $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // Chuẩn hoá kiểu chữ: bỏ chữ "đậm" Unicode, ký tự vô hình, gộp khoảng trắng.
    $text = TextFormatter::clean($text);
    if (mb_strlen($text) <= $limit) {
        return $text;
    }

    $cut = mb_substr($text, 0, $limit);
    $lastSpace = mb_strrpos($cut, ' ');

    return rtrim($lastSpace ? mb_substr($cut, 0, $lastSpace) : $cut, ' ,.;:-').'...';
};

$normalizeTierName = static function (string $name): string {
    $name = trim($name);
    $name = (string) preg_replace('/^\[[^\]]*\]\s*/u', '', $name);              // [Ngày thường] ...
    $name = (string) preg_replace('/\s*[-–]\s*EARLY\s*BIRD\s*\)?\s*$/iu', '', $name); // - EARLY BIRD
    $name = (string) preg_replace('/\s*\((?:EARLY\s*BIRD|OPENING|SPOTLIGHT|FINAL)\s*ROUND\)\s*$/iu', '', $name);
    $name = (string) preg_replace('/\s*\((?:EARLY\s*BIRD|EARLYBIRD)\)\s*$/iu', '', $name);

    return trim((string) preg_replace('/\s+/u', ' ', $name));
};

$pickTiers = static function (array $event, int $capacity) use ($normalizeTierName, $stockWeights): array {
    $groups = [];
    foreach ($event['showings'] ?? [] as $showing) {
        foreach ($showing['ticketTypes'] ?? [] as $tier) {
            $name = $normalizeTierName((string) $tier['name']);
            if ($name === '' || ! empty($tier['isFree'])) {
                continue;
            }
            $price = (int) round((float) $tier['price']);
            if ($price <= 0) {
                continue;
            }
            $max = (int) ($tier['maxQtyPerOrder'] ?? 10);

            if (! isset($groups[$name])) {
                $groups[$name] = ['name' => $name, 'price' => $price, 'max' => $max];
            } else {
                $groups[$name]['price'] = min($groups[$name]['price'], $price);
                $groups[$name]['max'] = max($groups[$name]['max'], $max);
            }
        }
    }

    if ($groups === []) {
        return [];
    }

    $tiers = array_values($groups);
    usort($tiers, static fn ($a, $b) => $a['price'] <=> $b['price']);

    // Quá nhiều hạng vé: gộp các hạng trùng giá (A1/A2/B...) cho danh sách gọn.
    if (count($tiers) > 6) {
        $unique = [];
        foreach ($tiers as $tier) {
            if (! isset($unique[$tier['price']])) {
                $unique[$tier['price']] = $tier;
            }
        }
        $tiers = array_values($unique);
    }

    // Vẫn quá 6 hạng: chọn rải đều từ hạng rẻ nhất đến đắt nhất.
    if (count($tiers) > 6) {
        $picked = [];
        $last = count($tiers) - 1;
        for ($i = 0; $i < 6; $i++) {
            $picked[] = $tiers[(int) round($i * $last / 5)];
        }
        $tiers = array_values(array_unique($picked, SORT_REGULAR));
        usort($tiers, static fn ($a, $b) => $a['price'] <=> $b['price']);
    }

    $count = count($tiers);
    $weights = array_slice($stockWeights, 0, $count);
    $weightSum = array_sum($weights);
    $remaining = $capacity;
    $result = [];

    foreach ($tiers as $index => $tier) {
        $quantity = $index === $count - 1
            ? $remaining
            : max(10, (int) round($capacity * $weights[$index] / $weightSum));
        $quantity = min($quantity, max(10, $remaining - 10 * ($count - $index - 1)));
        $remaining -= $quantity;

        $maxPerOrder = max(1, min(10, $tier['max']));
        if (preg_match('/trẻ em|thiếu nhi|child/i', $tier['name'])) {
            $maxPerOrder = min($maxPerOrder, 4);
        }

        $result[] = [
            'name' => $tier['name'],
            'price' => $tier['price'],
            'quantity' => $quantity,
            'max_per_order' => $maxPerOrder,
        ];
    }

    return $result;
};

/**
 * Tạo ảnh banner 1600x900: nền mờ phủ kín khung, poster gốc đặt giữa
 * để không bị cắt mất chữ/nhân vật khi hiển thị dạng banner ngang.
 */
$makeBanner = static function (string $binary, string $target): bool {
    $src = @imagecreatefromstring($binary);
    if (! $src) {
        return false;
    }

    $width = imagesx($src);
    $height = imagesy($src);
    $canvasW = 1600;
    $canvasH = 900;

    $canvas = imagecreatetruecolor($canvasW, $canvasH);

    // Nền: scale cover + blur + tối nhẹ.
    $scale = max($canvasW / $width, $canvasH / $height);
    $bgW = (int) ceil($width * $scale);
    $bgH = (int) ceil($height * $scale);
    $bg = imagecreatetruecolor($bgW, $bgH);
    imagecopyresampled($bg, $src, 0, 0, 0, 0, $bgW, $bgH, $width, $height);
    $bgX = (int) (($canvasW - $bgW) / 2);
    $bgY = (int) (($canvasH - $bgH) / 2);
    for ($i = 0; $i < 12; $i++) {
        imagefilter($bg, IMG_FILTER_GAUSSIAN_BLUR);
    }
    imagecopy($canvas, $bg, $bgX, $bgY, 0, 0, $bgW, $bgH);
    imagedestroy($bg);

    $overlay = imagecolorallocatealpha($canvas, 10, 15, 30, 40);
    imagefilledrectangle($canvas, 0, 0, $canvasW, $canvasH, $overlay);

    // Tiền cảnh: giữ trọn poster, scale theo chiều cao khung.
    $fgScale = $canvasH / $height;
    $fgW = (int) round($width * $fgScale);
    $fgH = $canvasH;
    if ($fgW > $canvasW) {
        $fgScale = $canvasW / $width;
        $fgW = $canvasW;
        $fgH = (int) round($height * $fgScale);
    }
    $fg = imagecreatetruecolor($fgW, $fgH);
    imagecopyresampled($fg, $src, 0, 0, 0, 0, $fgW, $fgH, $width, $height);
    imagecopy(
        $canvas,
        $fg,
        (int) (($canvasW - $fgW) / 2),
        (int) (($canvasH - $fgH) / 2),
        0,
        0,
        $fgW,
        $fgH
    );
    imagedestroy($fg);
    imagedestroy($src);

    $ok = imagejpeg($canvas, $target, 86);
    imagedestroy($canvas);

    return $ok;
};

$download = static function (string $url): ?string {
    $request = static function (string $url, bool $verifyPeer): array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_USERAGENT => 'EventTicketSeeder/1.0',
            CURLOPT_SSL_VERIFYPEER => $verifyPeer,
            CURLOPT_SSL_VERIFYHOST => $verifyPeer ? 2 : 0,
        ]);
        $data = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errorNo = curl_errno($ch);
        curl_close($ch);

        return [$data, $status, $errorNo];
    };

    [$data, $status, $errorNo] = $request($url, true);

    // Một số máy Windows có proxy nội bộ chèn chứng chỉ tự ký: thử lại
    // không xác thực SSL (chỉ dùng cho script tạo dữ liệu cục bộ).
    if (($data === false || $status !== 200) && in_array($errorNo, [35, 51, 58, 60], true)) {
        [$data, $status] = $request($url, false);
    }

    return ($data !== false && $status === 200) ? $data : null;
};

$skipImages = in_array('--skip-images', $argv ?? [], true);

$output = [];
$usedSlugs = [];
$usedEmails = [];

foreach ($picks as $index => $config) {
    $source = $allEvents[$index] ?? null;
    if (! $source) {
        fwrite(STDERR, "Bỏ qua index {$index}: không có dữ liệu.\n");

        continue;
    }

    $slug = trim((string) preg_replace('/[^a-z0-9-]+/', '-', strtolower((string) ($source['url'] ?? ''))), '-');
    if ($slug === '') {
        $slug = 'su-kien-'.$index;
    }
    $baseSlug = $slug;
    $suffix = 2;
    while (isset($usedSlugs[$slug])) {
        $slug = $baseSlug.'-'.$suffix++;
    }
    $usedSlugs[$slug] = true;

    $start = new DateTimeImmutable($source['startTime'], new DateTimeZone('UTC'));
    $end = new DateTimeImmutable($source['endTime'] ?? $source['startTime'], new DateTimeZone('UTC'));
    $start = $start->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
    $end = $end->setTimezone(new DateTimeZone('Asia/Ho_Chi_Minh'));
    if ($end < $start) {
        $end = $start->modify('+2 hours');
    }

    $orgName = trim((string) preg_replace('/\s+/u', ' ', (string) $source['orgName']));
    if ($orgName === '') {
        $orgName = 'Ban tổ chức Ticketbox';
    }
    $emailSlug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower(Str::ascii($orgName))), '-');
    $emailSlug = $emailSlug !== '' ? mb_substr($emailSlug, 0, 40) : 'organizer';
    $email = $emailSlug.'@ticketbox.local';
    $emailSuffix = 2;
    while (isset($usedEmails[$email])) {
        $email = $emailSlug.'-'.$emailSuffix++.'@ticketbox.local';
    }
    $usedEmails[$email] = true;

    $capacity = (int) $config['capacity'];
    $tiers = $pickTiers($source, $capacity);
    if ($tiers === []) {
        fwrite(STDERR, "Bỏ qua '{$source['title']}': không có hạng vé hợp lệ.\n");

        continue;
    }

    $imagePath = 'images/'.$slug.'.jpg';
    $absoluteImage = $baseDir.'/'.$imagePath;
    $imageOk = false;
    if ($skipImages && is_file($absoluteImage)) {
        $imageOk = true;
    } else {
        $downloaded = $download((string) $source['bannerURL']);
        if ($downloaded !== null) {
            $imageOk = $makeBanner($downloaded, $absoluteImage);
        }
    }
    if (! $imageOk) {
        fwrite(STDERR, "CẢNH BÁO: không tải/xử lý được ảnh cho '{$source['title']}' ({$source['bannerURL']}).\n");
    }

    $address = trim((string) preg_replace('/\s+/u', ' ', (string) $source['address']));
    $venueName = trim((string) preg_replace('/\s+/u', ' ', (string) $source['venue']));

    $output[] = [
        'source_id' => (int) $source['id'],
        'source_url' => 'https://ticketbox.vn/event/'.$source['url'],
        'title' => trim((string) preg_replace('/\s+/u', ' ', (string) $source['title'])),
        'slug' => $slug,
        'category' => $config['category'],
        'description' => $plainText($source['description'] ?? ''),
        'banner' => $imageOk ? 'events/ticketbox/'.$slug.'.jpg' : null,
        'image_source' => $source['bannerURL'],
        'venue' => [
            'name' => $venueName,
            'address' => $address,
            'city' => $normalizeCity($address),
            'capacity' => $capacity,
        ],
        'organizer' => [
            'name' => $orgName,
            'email' => $email,
            'logo' => $source['orgLogoURL'] ?? null,
        ],
        'start_time' => $start->format('Y-m-d H:i:s'),
        'end_time' => $end->format('Y-m-d H:i:s'),
        'min_price' => (int) $source['minTicketPrice'],
        'status' => 'published',
        'ticket_types' => $tiers,
    ];
}

$payload = [
    'source' => 'https://github.com/dangquang2k5/EzTicket-Website (nhánh develop-Frontend, importDataTicketBox/ticketbox_merged.json)',
    'generated_at' => (new DateTimeImmutable('now', new DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
    'timezone' => 'Asia/Ho_Chi_Minh',
    'events' => $output,
];

file_put_contents(
    $outputFile,
    json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
);

echo 'Đã tạo '.count($output)." sự kiện -> {$outputFile}\n";
foreach ($output as $event) {
    echo sprintf(
        "  - %-60s | %-24s | %d hạng vé | %s\n",
        mb_substr($event['title'], 0, 58),
        $event['venue']['city'],
        count($event['ticket_types']),
        $event['banner'] ?? 'KHÔNG CÓ ẢNH'
    );
}
