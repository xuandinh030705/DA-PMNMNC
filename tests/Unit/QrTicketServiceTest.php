<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Services\QrTicketService;
use Tests\TestCase;

class QrTicketServiceTest extends TestCase
{
    private QrTicketService $qrTicketService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->qrTicketService = $this->app->make(QrTicketService::class);
    }

    public function test_payload_is_built_from_ticket_code_when_missing(): void
    {
        $ticket = new Ticket(['ticket_code' => 'TKT-ABCDEFGHIJ']);

        $this->assertSame(
            ['v' => 1, 'ticket_code' => 'TKT-ABCDEFGHIJ'],
            $this->qrTicketService->decodePayload($this->qrTicketService->payloadFor($ticket)),
        );
    }

    public function test_png_data_uri_contains_a_valid_png_image(): void
    {
        $ticket = new Ticket(['ticket_code' => 'TKT-PNG1234567']);

        $dataUri = $this->qrTicketService->pngDataUri($ticket);

        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);

        $binary = base64_decode(substr($dataUri, strlen('data:image/png;base64,')), true);

        $this->assertNotFalse($binary);
        $this->assertSame("\x89PNG", substr($binary, 0, 4));
    }

    public function test_svg_output_contains_svg_markup(): void
    {
        $ticket = new Ticket(['ticket_code' => 'TKT-SVG1234567']);

        $svg = $this->qrTicketService->svg($ticket);

        $this->assertStringContainsString('<svg', $svg);
        $this->assertStringContainsString('</svg>', $svg);
    }

    public function test_signature_is_valid_for_untampered_payload(): void
    {
        $payload = Ticket::buildQrPayload('TKT-SIGNED1234');

        $this->assertTrue(
            $this->qrTicketService->verifySignature($payload, Ticket::generateQrSignature($payload)),
        );
    }

    public function test_signature_is_rejected_when_payload_is_tampered(): void
    {
        $signature = Ticket::generateQrSignature(Ticket::buildQrPayload('TKT-ORIGINAL12'));
        $tamperedPayload = Ticket::buildQrPayload('TKT-TAMPERED12');

        $this->assertFalse($this->qrTicketService->verifySignature($tamperedPayload, $signature));
    }

    public function test_signature_is_rejected_when_empty(): void
    {
        $this->assertFalse($this->qrTicketService->verifySignature('', ''));
    }

    public function test_ticket_signature_helper_matches_service_verification(): void
    {
        $ticket = new Ticket(['ticket_code' => 'TKT-MODEL12345']);
        $ticket->qr_payload = Ticket::buildQrPayload($ticket->ticket_code);
        $ticket->qr_signature = Ticket::generateQrSignature($ticket->qr_payload);

        $this->assertTrue($this->qrTicketService->hasValidSignature($ticket));

        $ticket->qr_signature = str_repeat('0', 64);

        $this->assertFalse($this->qrTicketService->hasValidSignature($ticket));
    }

    public function test_decode_payload_rejects_plain_ticket_code(): void
    {
        $this->assertNull($this->qrTicketService->decodePayload('TKT-ABCDEFGHIJ'));
        $this->assertNull($this->qrTicketService->decodePayload('{"v":1}'));
        $this->assertNull($this->qrTicketService->decodePayload('not-json'));
    }

    public function test_payload_uses_existing_stored_qr_payload_when_present(): void
    {
        $storedPayload = '{"v":1,"ticket_code":"TKT-STORED1234"}';
        $ticket = new Ticket([
            'ticket_code' => 'TKT-IGNORED123',
            'qr_payload' => $storedPayload,
        ]);

        $this->assertSame($storedPayload, $this->qrTicketService->payloadFor($ticket));
    }

    public function test_png_and_svg_data_uri_outputs_are_valid(): void
    {
        $ticket = new Ticket(['ticket_code' => 'TKT-FORMAT1234']);

        $pngBinary = $this->qrTicketService->png($ticket);
        $this->assertSame("\x89PNG", substr($pngBinary, 0, 4));

        $svgDataUri = $this->qrTicketService->svgDataUri($ticket);
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $svgDataUri);

        $decodedSvg = base64_decode(substr($svgDataUri, strlen('data:image/svg+xml;base64,')), true);
        $this->assertNotFalse($decodedSvg);
        $this->assertStringContainsString('<svg', $decodedSvg);
        $this->assertStringContainsString('</svg>', $decodedSvg);
    }

    public function test_signature_is_rejected_when_app_key_changes(): void
    {
        $payload = Ticket::buildQrPayload('TKT-KEYROTATE1');
        $signature = Ticket::generateQrSignature($payload);

        $originalKey = config('app.key');

        try {
            config(['app.key' => 'base64:'.base64_encode(str_repeat('x', 32))]);

            $this->assertFalse($this->qrTicketService->verifySignature($payload, $signature));
        } finally {
            config(['app.key' => $originalKey]);
        }
    }

    public function test_has_valid_signature_returns_false_when_payload_or_signature_is_missing(): void
    {
        $missingSignature = new Ticket([
            'ticket_code' => 'TKT-NOSIG12345',
            'qr_payload' => Ticket::buildQrPayload('TKT-NOSIG12345'),
            'qr_signature' => null,
        ]);

        $missingPayload = new Ticket([
            'ticket_code' => 'TKT-NOPAY12345',
            'qr_payload' => null,
            'qr_signature' => str_repeat('a', 64),
        ]);

        $this->assertFalse($this->qrTicketService->hasValidSignature($missingSignature));
        $this->assertFalse($this->qrTicketService->hasValidSignature($missingPayload));
    }

    public function test_decode_payload_handles_whitespace_and_rejects_non_string_ticket_code(): void
    {
        $this->assertSame(
            ['v' => 1, 'ticket_code' => 'TKT-TRIMMED123'],
            $this->qrTicketService->decodePayload("  {\"ticket_code\":\"TKT-TRIMMED123\"} \n "),
        );

        $this->assertNull($this->qrTicketService->decodePayload('{"v":1,"ticket_code":123456}'));
        $this->assertNull($this->qrTicketService->decodePayload('{"v":1,"ticket_code":["TKT-ARRAY"]}'));
        $this->assertNull($this->qrTicketService->decodePayload('"TKT-SCALAR123"'));
    }
}
