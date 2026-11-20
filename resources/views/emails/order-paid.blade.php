<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đơn hàng #{{ $order->order_code }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 20px 0;
            line-height: 1.6;
        }
        .email-container {
            max-width: 620px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            border: 1px solid #e5e7eb;
        }
        .email-header {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            color: #ffffff;
            padding: 30px 25px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 700;
        }
        .email-header p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 25px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
        }
        .order-badge {
            background-color: #e8f4fd;
            border-left: 4px solid #0d6efd;
            padding: 12px 16px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .info-table th, .info-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }
        .info-table th {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            width: 35%;
        }
        .ticket-card {
            background-color: #fafafa;
            border: 1px dashed #cbd5e1;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 12px;
        }
        .ticket-code {
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
            color: #0d6efd;
            font-size: 16px;
            letter-spacing: 1px;
        }
        .btn-view {
            display: inline-block;
            background-color: #0d6efd;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
            text-align: center;
        }
        .notice-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 14px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1>EVENT TICKET</h1>
            <p>Xác nhận thanh toán đơn hàng thành công</p>
        </div>

        <div class="email-body">
            <div class="greeting">
                Xin chào <strong>{{ $order->customer_name }}</strong>,
            </div>

            <p>Cảm ơn bạn đã lựa chọn và mua vé tại <strong>EventTicket</strong>. Đơn hàng của bạn đã được thanh toán thành công và vé điện tử đã sẵn sàng để sử dụng.</p>

            <div class="order-badge">
                <strong>Mã đơn hàng:</strong> <span style="font-family: monospace; font-size: 16px; color: #0d6efd; font-weight: bold;">{{ $order->order_code }}</span><br>
                <strong>Thời gian thanh toán:</strong> {{ $order->paid_at ? $order->paid_at->format('H:i - d/m/Y') : now()->format('H:i - d/m/Y') }}
            </div>

            <table class="info-table">
                <tr>
                    <th>Người nhận vé</th>
                    <td>{{ $order->customer_name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $order->customer_email }}</td>
                </tr>
                @if ($order->customer_phone)
                <tr>
                    <th>Số điện thoại</th>
                    <td>{{ $order->customer_phone }}</td>
                </tr>
                @endif
                <tr>
                    <th>Hình thức thanh toán</th>
                    <td>{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }}</td>
                </tr>
                <tr>
                    <th>Tổng số tiền</th>
                    <td><strong style="color: #dc3545; font-size: 15px;">{{ number_format($order->total_amount, 0, ',', '.') }} đ</strong></td>
                </tr>
            </table>

            <h3 style="font-size: 16px; margin: 25px 0 12px 0; color: #1e293b;">Danh sách vé điện tử</h3>

            @foreach ($order->orderItems as $item)
                @php
                    $event = $item->ticketType->event;
                @endphp
                <div style="margin-bottom: 16px;">
                    <div style="font-weight: bold; color: #0f172a; font-size: 15px;">{{ $event->title }}</div>
                    <div style="font-size: 13px; color: #64748b; margin-top: 2px;">
                        <span>Thời gian: {{ $event->start_time->format('H:i - d/m/Y') }}</span> | 
                        <span>Địa điểm: {{ $event->venue->name }} ({{ $event->venue->address }})</span>
                    </div>
                    <div style="font-size: 13px; margin: 4px 0 8px 0;">
                        <strong>Loại vé:</strong> {{ $item->ticketType->name }}
                        @if ($item->ticketType->zone)
                            <span style="background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px; font-size: 11px;">{{ $item->ticketType->zone }}</span>
                        @endif
                        - Số lượng: {{ $item->quantity }} vé
                    </div>

                    @foreach ($item->tickets as $ticket)
                        <div class="ticket-card">
                            <table style="width: 100%;">
                                <tr>
                                    <td>
                                        <div style="font-size: 12px; color: #64748b;">MÃ VÉ ĐIỂM DANH:</div>
                                        <div class="ticket-code">{{ $ticket->ticket_code }}</div>
                                    </td>
                                    <td style="text-align: right; vertical-align: middle;">
                                        <span style="background: #dcfce7; color: #15803d; font-size: 12px; font-weight: bold; padding: 4px 10px; border-radius: 20px;">
                                            Sẵn sàng check-in
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach

            <div class="notice-box">
                <strong>Đính kèm vé PDF:</strong> Vé điện tử khổ A5 chứa mã QR Code bảo mật đã được tạo và đính kèm trực tiếp trong email này. Quý khách vui lòng tải về hoặc in ra để xuất trình tại cửa kiểm soát.
            </div>

            <div style="text-align: center; margin: 30px 0 10px 0;">
                <a href="{{ route('orders.show', $order->order_code) }}" class="btn-view">
                    Xem Chi Tiết Đơn Hàng & Vé Trên Website
                </a>
            </div>
        </div>

        <div class="email-footer">
            <p style="margin: 0 0 5px 0;">Đây là email tự động gửi từ hệ thống <strong>EventTicket</strong>.</p>
            <p style="margin: 0;">Nếu quý khách cần hỗ trợ thêm, vui lòng liên hệ ban tổ chức hoặc hotline hỗ trợ.</p>
        </div>
    </div>
</body>
</html>
