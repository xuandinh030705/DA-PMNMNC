<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Vé điện tử {{ $ticket->ticket_code }}</title>
    <style>
        @page {
            margin: 16px 18px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #1f2937;
            margin: 0;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .brand {
            font-size: 16px;
            font-weight: bold;
            color: #4338ca;
            letter-spacing: 0.4px;
        }

        .brand-sub {
            font-size: 9px;
            color: #6b7280;
        }

        .doc-type {
            text-align: right;
            font-size: 10px;
            font-weight: bold;
            color: #4338ca;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .doc-code {
            text-align: right;
            font-size: 9px;
            color: #6b7280;
        }

        .divider {
            border-top: 2px solid #4338ca;
            font-size: 0;
            line-height: 0;
        }

        .event-title {
            font-size: 17px;
            font-weight: bold;
            color: #111827;
            margin: 12px 0 2px 0;
        }

        .event-meta {
            font-size: 10px;
            color: #4b5563;
            margin: 0;
        }

        .panel {
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
            padding: 10px 12px;
        }

        .label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .value {
            font-size: 11px;
            color: #111827;
            font-weight: bold;
        }

        .value-normal {
            font-size: 11px;
            color: #111827;
        }

        .muted {
            font-size: 9px;
            color: #6b7280;
        }

        .ticket-code {
            font-family: 'DejaVu Sans Mono', 'DejaVu Sans', monospace;
            font-size: 15px;
            font-weight: bold;
            color: #1d4ed8;
            letter-spacing: 1.2px;
        }

        .qr-frame {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: center;
            background-color: #ffffff;
        }

        .qr-frame img {
            width: 150px;
            height: 150px;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 9px;
            font-weight: bold;
            color: #ffffff;
            background-color: #16a34a;
        }

        .badge-used {
            background-color: #0284c7;
        }

        .badge-cancelled {
            background-color: #dc2626;
        }

        .zone-badge {
            display: inline-block;
            padding: 1px 6px;
            font-size: 9px;
            color: #374151;
            background-color: #e5e7eb;
        }

        .note {
            font-size: 9px;
            color: #4b5563;
            border-top: 1px dashed #9ca3af;
            padding-top: 6px;
            margin-top: 10px;
        }

        .footer {
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
            margin-top: 8px;
        }

        .spacer {
            font-size: 0;
            line-height: 0;
            height: 8px;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="width: 55%; vertical-align: middle;">
                <div class="brand">EventTicket</div>
                <div class="brand-sub">Hệ thống bán vé sự kiện trực tuyến</div>
            </td>
            <td style="width: 45%; vertical-align: middle;">
                <div class="doc-type">Vé điện tử / E-Ticket</div>
                <div class="doc-code">Đơn hàng: {{ $order->order_code }}</div>
            </td>
        </tr>
    </table>

    <div class="divider">&nbsp;</div>

    <div class="event-title">{{ $event->title }}</div>
    <p class="event-meta">
        {{ $event->start_time->format('H:i - d/m/Y') }} &nbsp;|&nbsp;
        {{ $event->venue->name }}@if ($event->venue->address) - {{ $event->venue->address }}@endif
    </p>

    <div class="spacer">&nbsp;</div>

    <table>
        <tr>
            <td style="width: 62%; vertical-align: top; padding-right: 10px;">
                <div class="panel">
                    <table>
                        <tr>
                            <td style="width: 32%; padding: 3px 0;">
                                <span class="label">Mã vé</span>
                            </td>
                            <td style="padding: 3px 0;">
                                <span class="ticket-code">{{ $ticket->ticket_code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <span class="label">Hạng vé</span>
                            </td>
                            <td style="padding: 3px 0;">
                                <span class="value">{{ $ticketType->name }}</span>
                                <span class="muted">({{ number_format((float) $ticketType->price, 0, ',', '.') }} đ)</span>
                                @if ($ticketType->zone)
                                    <span class="zone-badge">{{ $ticketType->zone }}</span>
                                @endif
                            </td>
                        </tr>
                        @if ($ticketType->description)
                            <tr>
                                <td style="padding: 3px 0; vertical-align: top;">
                                    <span class="label">Quyền lợi</span>
                                </td>
                                <td style="padding: 3px 0;">
                                    <span class="value-normal">{{ $ticketType->description }}</span>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td style="padding: 3px 0;">
                                <span class="label">Khách tham dự</span>
                            </td>
                            <td style="padding: 3px 0;">
                                <span class="value-normal">{{ $order->customer_name }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <span class="label">Liên hệ</span>
                            </td>
                            <td style="padding: 3px 0;">
                                <span class="value-normal">{{ $order->customer_email }}@if ($order->customer_phone) - {{ $order->customer_phone }}@endif</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <span class="label">Trạng thái</span>
                            </td>
                            <td style="padding: 3px 0;">
                                <span class="badge {{ $ticket->status === \App\Models\Ticket::STATUS_USED ? 'badge-used' : ($ticket->status === \App\Models\Ticket::STATUS_CANCELLED ? 'badge-cancelled' : '') }}">
                                    {{ $ticket->statusLabel() }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                @if ($ticket->isUsed() && $ticket->checked_in_at)
                    <p class="muted" style="margin: 6px 0 0 0;">
                        Đã soát vé lúc {{ $ticket->checked_in_at->format('H:i - d/m/Y') }}@if ($ticket->checkedInBy) bởi {{ $ticket->checkedInBy->name }}@endif.
                    </p>
                @endif

                <div class="note">
                    Xuất trình mã QR này tại cổng soát vé để check-in. Mỗi mã vé chỉ có giá trị sử dụng một lần.
                    Vui lòng không chia sẻ mã QR cho người khác.
                </div>
            </td>
            <td style="width: 38%; vertical-align: top;">
                <div class="qr-frame">
                    <img src="{{ $qrCodeDataUri }}" alt="Mã QR vé {{ $ticket->ticket_code }}">
                    <div class="muted" style="margin-top: 6px;">Quét mã để soát vé</div>
                    <div class="ticket-code" style="font-size: 11px;">{{ $ticket->ticket_code }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Vé được tạo tự động bởi EventTicket - Nhóm 8 - Lớp 68PM2. Vui lòng giữ vé để đối chiếu khi cần thiết.
    </div>
</body>
</html>
