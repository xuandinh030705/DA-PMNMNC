<?php

namespace App\Services;

use App\Models\Ticket;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Writer\SvgWriter;
use JsonException;

class QrTicketService
{
    private const QR_SIZE = 320;

    private const QR_MARGIN = 8;

    public function __construct(
        private readonly PngWriter $pngWriter = new PngWriter,
        private readonly SvgWriter $svgWriter = new SvgWriter,
    ) {}

    /**
     * Get the signed JSON payload encoded inside the ticket QR.
     */
    public function payloadFor(Ticket $ticket): string
    {
        return $ticket->qr_payload ?: Ticket::buildQrPayload($ticket->ticket_code);
    }

    /**
     * Render the ticket QR as a PNG binary string.
     */
    public function png(Ticket $ticket): string
    {
        return $this->pngWriter->write($this->qrCode($ticket))->getString();
    }

    /**
     * Render the ticket QR as an inline PNG data URI, ready for <img src="...">.
     */
    public function pngDataUri(Ticket $ticket): string
    {
        return $this->pngWriter->write($this->qrCode($ticket))->getDataUri();
    }

    /**
     * Render the ticket QR as an SVG markup string.
     */
    public function svg(Ticket $ticket): string
    {
        return $this->svgWriter->write($this->qrCode($ticket))->getString();
    }

    /**
     * Render the ticket QR as an inline SVG data URI, ready for <img src="...">.
     */
    public function svgDataUri(Ticket $ticket): string
    {
        return $this->svgWriter->write($this->qrCode($ticket))->getDataUri();
    }

    /**
     * Verify the HMAC-SHA256 signature stored with the ticket QR payload.
     */
    public function hasValidSignature(Ticket $ticket): bool
    {
        return $ticket->hasValidQrSignature();
    }

    /**
     * Verify a raw QR payload against its HMAC-SHA256 signature.
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        if ($payload === '' || $signature === '') {
            return false;
        }

        return hash_equals(Ticket::generateQrSignature($payload), $signature);
    }

    /**
     * Decode a scanned QR payload into its ticket code.
     *
     * @return array{v: int, ticket_code: string}|null
     */
    public function decodePayload(string $payload): ?array
    {
        try {
            $decoded = json_decode(trim($payload), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (! is_array($decoded) || ! isset($decoded['ticket_code']) || ! is_string($decoded['ticket_code'])) {
            return null;
        }

        return [
            'v' => (int) ($decoded['v'] ?? 1),
            'ticket_code' => $decoded['ticket_code'],
        ];
    }

    private function qrCode(Ticket $ticket): QrCode
    {
        return new QrCode(
            data: $this->payloadFor($ticket),
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: self::QR_SIZE,
            margin: self::QR_MARGIN,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
        );
    }
}
