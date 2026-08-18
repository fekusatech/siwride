<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; background: #fff; }
        .ticket { width: 100%; max-width: 500px; margin: 0 auto; padding: 30px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 22px; font-weight: 700; color: #1e293b; margin-bottom: 15px; }
        .route-display { text-align: center; margin-bottom: 8px; }
        .route-point { font-size: 14px; font-weight: 700; color: #1e293b; letter-spacing: 0.5px; }
        .route-arrow { font-size: 16px; color: #dc2626; margin: 0 10px; }
        .transit-info { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; }
        .divider { border: none; border-top: 2px dashed #e2e8f0; margin: 20px 0; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
        .info-label { font-size: 11px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.3px; width: 40%; }
        .info-value { font-size: 14px; font-weight: 600; color: #1e293b; text-align: right; }
        .qr-section { text-align: center; margin: 25px 0; }
        .qr-box { width: 120px; height: 120px; margin: 0 auto 10px; border: 2px solid #1e293b; display: flex; align-items: center; justify-content: center; }
        .qr-id { font-size: 12px; color: #64748b; font-weight: 600; letter-spacing: 1px; }
        .notice { text-align: center; font-size: 12px; color: #64748b; margin: 20px 0; line-height: 1.6; }
        .notice strong { color: #1e293b; }
        .thank-you { text-align: center; font-size: 20px; font-weight: 800; color: #1e293b; margin-top: 25px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>Depart Ticket</h1>
        </div>

        <div class="route-display">
            <span class="route-point">{{ strtoupper($order->pickup_address) }}</span>
            <span class="route-arrow">&#8594;</span>
            <span class="route-point">{{ strtoupper($order->dropoff_address) }}</span>
        </div>
        <div class="transit-info">Transit: Direct</div>

        <hr class="divider">

        <table class="info-table">
            <tr>
                <td class="info-label">Name</td>
                <td class="info-value">{{ strtoupper($order->customer_name) }}</td>
            </tr>
            @if($order->vehicleCategory)
            <tr>
                <td class="info-label">Vehicle</td>
                <td class="info-value">{{ strtoupper($order->vehicleCategory->title) }} {{ $order->passengers ?? '1' }} PERSON</td>
            </tr>
            @endif
            <tr>
                <td class="info-label">Category</td>
                <td class="info-value">ADULT</td>
            </tr>
            <tr>
                <td class="info-label">Nationality</td>
                <td class="info-value">-</td>
            </tr>
            <tr>
                <td class="info-label">Date of Birth</td>
                <td class="info-value">-</td>
            </tr>
            <tr>
                <td class="info-label">Date</td>
                <td class="info-value">{{ \Carbon\Carbon::parse($order->date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td class="info-label">Time</td>
                <td class="info-value">{{ \Carbon\Carbon::parse($order->time)->format('h:i A') }}</td>
            </tr>
        </table>

        <div class="qr-section">
            <div class="qr-box">
                <span style="font-size: 11px; font-weight: 700; color: #1e293b; letter-spacing: 1px;">QR CODE</span>
            </div>
            <div class="qr-id">{{ $order->booking_code }}</div>
        </div>

        <div class="notice">
            <p><strong>Passenger please arrive 45 minutes before departure.</strong></p>
            <p style="margin-top: 8px;">Ticket does not include harbor tax</p>
        </div>

        <div class="thank-you">THANK YOU</div>
    </div>
</body>
</html>
