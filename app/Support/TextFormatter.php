<?php

namespace App\Support;

use Normalizer;

/**
 * Chuẩn hoá và trình bày phần mô tả sự kiện.
 *
 * Mục tiêu: chữ hiển thị đồng nhất một kiểu chữ, không còn ký tự "trang trí"
 * dạng Unicode (chữ đậm giả, chữ full-width, ký tự vô hình...) nhưng giữ
 * nguyên toàn bộ nội dung chữ của mô tả.
 */
class TextFormatter
{
    /**
     * Các từ viết tắt thường gặp mà dấu chấm không phải là kết thúc câu.
     */
    private const ABBREVIATIONS = [
        'tp', 'p', 'q', 'tt', 'ns', 'nsnd', 'nsut', 'mr', 'mrs', 'ms', 'dr', 'st', 'v.v',
    ];

    /**
     * Làm sạch văn bản nhưng không đổi từ ngữ:
     * - NFKC: chuyển chữ "đậm/nghiêng" Unicode (𝐇, 𝐀, 𝟐...) và ký tự full-width
     *   về chữ thường, đồng thời ghép dấu tiếng Việt về dạng dựng sẵn (HÀ, Người...).
     * - Bỏ ký tự vô hình (zero-width, BOM, soft hyphen, điều khiển hướng chữ).
     * - Đưa các loại khoảng trắng đặc biệt về khoảng trắng thường, gộp khoảng trắng.
     */
    public static function clean(?string $text): string
    {
        $text = (string) $text;

        if ($text === '') {
            return '';
        }

        if (class_exists(Normalizer::class)) {
            $normalized = Normalizer::normalize($text, Normalizer::FORM_KC);
            if ($normalized !== false) {
                $text = $normalized;
            }
        }

        // Ký tự vô hình không mang nội dung hiển thị.
        $text = (string) preg_replace('/[\x{00AD}\x{200B}-\x{200F}\x{202A}-\x{202E}\x{2060}-\x{206F}\x{FEFF}]/u', '', $text);

        // Biểu tượng/emoji trang trí (khác kiểu chữ với nội dung): bỏ ký tự,
        // không đụng tới phần chữ.
        $text = (string) preg_replace('/[\x{1F000}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0E}\x{FE0F}]/u', ' ', $text);

        // Các loại khoảng trắng đặc biệt (NBSP, en/em space, ideographic space...).
        $text = (string) preg_replace('/[\x{00A0}\x{1680}\x{2000}-\x{200A}\x{202F}\x{205F}\x{3000}]/u', ' ', $text);

        // Gộp khoảng trắng và xuống dòng thừa.
        $text = (string) preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Chia mô tả thành các đoạn dễ đọc (mỗi đoạn vài câu) mà không thêm/bớt chữ.
     * Chỉ chèn thêm chỗ ngắt đoạn tại các dấu kết thúc câu.
     *
     * @return array<int, string>
     */
    public static function paragraphs(?string $text, int $sentencesPerParagraph = 3): array
    {
        $text = self::clean($text);

        if ($text === '') {
            return [];
        }

        $sentences = preg_split('/(?<=[.!?…])\s+(?=\p{Lu})/u', $text) ?: [];

        if ($sentences === []) {
            return [$text];
        }

        // Gộp lại các chỗ bị tách nhầm sau từ viết tắt: "TP. Hồ Chí Minh", "P. An Khánh"...
        $merged = [];
        foreach ($sentences as $sentence) {
            $previous = end($merged);
            if ($previous !== false && self::endsWithAbbreviation($previous)) {
                $merged[array_key_last($merged)] = $previous.' '.$sentence;

                continue;
            }
            $merged[] = $sentence;
        }

        $paragraphs = array_chunk($merged, max(1, $sentencesPerParagraph));

        return array_map(static fn (array $chunk) => implode(' ', $chunk), $paragraphs);
    }

    private static function endsWithAbbreviation(string $sentence): bool
    {
        if (! preg_match('/(?:^|\s)([a-z.]+)\.$/iu', $sentence, $matches)) {
            return false;
        }

        return in_array(mb_strtolower($matches[1]), self::ABBREVIATIONS, true);
    }
}
