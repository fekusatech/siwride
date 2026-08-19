@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Complete Your Payment
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        Your booking has been created. Please complete your payment to confirm your reservation.
    </p>

    {{-- Booking Code --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #fffbeb; border-radius: 10px; border: 1px solid #fde68a;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #92400e; margin: 0 0 4px 0;">Booking Reference</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #dc2626; margin: 0; letter-spacing: 1px;">{{ $order->booking_code }}</p>
            </td>
        </tr>
    </table>

    @if($isActivity)
        {{-- Activity Details --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td>
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Activity Details</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Activity</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->activity->title ?? 'Activity' }}</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Date</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($order->booking_date)->format('d M Y') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Participants</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->pax }} Pax</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Price per Person</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">IDR {{ number_format($order->price_per_pax, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @elseif($isService)
        {{-- Service Booking Details --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td>
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Service Booking Details</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Service</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->driverService->title ?? 'Service' }}</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Date</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($order->booking_date)->format('d M Y') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Participants</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->pax }} Pax</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Price per Person</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">IDR {{ number_format($order->price_per_pax, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    @else
        {{-- Route Info --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td>
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Route</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 12px 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="20" style="vertical-align: top; padding-top: 2px;">
                                <div style="width: 8px; height: 8px; background: #dc2626; border-radius: 50%;"></div>
                            </td>
                            <td style="padding-bottom: 10px;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #94a3b8; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0.3px;">Pickup</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->pickup_address }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td width="20" style="vertical-align: top; padding-top: 2px;">
                                <div style="width: 8px; height: 8px; background: #10b981; border-radius: 50%;"></div>
                            </td>
                            <td>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; color: #94a3b8; margin: 0 0 2px 0; text-transform: uppercase; letter-spacing: 0.3px;">Dropoff</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->dropoff_address }}</p>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Trip Details --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
            <tr>
                <td>
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Trip Details</p>
                </td>
            </tr>
            <tr>
                <td style="padding: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Date</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($order->date)->format('d M Y') }}</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Time</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($order->time)->format('H:i') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Passengers</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->passengers }} Pax</p>
                            </td>
                            <td width="50%" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Trip Type</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->trip_type === 'round_trip' ? 'Round Trip' : 'One Way' }}</p>
                            </td>
                        </tr>
                        @if($order->vehicleCategory)
                        <tr>
                            <td colspan="2" style="padding: 6px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Vehicle</p>
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $order->vehicleCategory->title }}</p>
                            </td>
                        </tr>
                        @endif
                    </table>
                </td>
            </tr>
        </table>
    @endif

    {{-- Price --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                @if($isService)
                    {{-- Service price breakdown --}}
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0;">Subtotal ({{ $order->pax }} pax × IDR {{ number_format($order->price_per_pax, 0, ',', '.') }})</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; font-weight: 600;">IDR {{ number_format($order->subtotal, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        @if($order->voucher_code)
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #16a34a; margin: 0;">Discount ({{ $order->voucher_code }})</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #16a34a; margin: 0; font-weight: 600;">− IDR {{ number_format($order->discount_amount, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 3px 0; border-top: 1px solid #e2e8f0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 700;">Total</p>
                            </td>
                            <td style="padding: 3px 0; border-top: 1px solid #e2e8f0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 800;">IDR {{ number_format($order->total_amount, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #dc2626; margin: 0; font-weight: 600;">Bayar sekarang (DP {{ rtrim(rtrim(number_format($order->dp_percent, 2, '.', ''), '0'), '.') }}%)</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #dc2626; margin: 0; font-weight: 700;">IDR {{ number_format($order->dp_amount, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0;">Sisa tunai ke driver</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0; font-weight: 600;">IDR {{ number_format($order->remaining_cash, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                    </table>
                @elseif($isActivity)
                    {{-- Activity price breakdown --}}
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0;">Subtotal ({{ $order->pax }} pax × IDR {{ number_format($order->price_per_pax, 0, ',', '.') }})</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; font-weight: 600;">IDR {{ number_format($order->subtotal, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        @if($order->voucher_code)
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #16a34a; margin: 0;">Discount ({{ $order->voucher_code }})</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #16a34a; margin: 0; font-weight: 600;">− IDR {{ number_format($order->discount_amount, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td style="padding: 3px 0; border-top: 1px solid #e2e8f0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 700;">Total</p>
                            </td>
                            <td style="padding: 3px 0; border-top: 1px solid #e2e8f0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 800;">IDR {{ number_format($order->total_price, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #dc2626; margin: 0; font-weight: 600;">Bayar sekarang (DP {{ rtrim(rtrim(number_format($order->dp_percent, 2, '.', ''), '0'), '.') }}%)</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #dc2626; margin: 0; font-weight: 700;">IDR {{ number_format($order->dp_amount, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 0;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0;">Sisa tunai</p>
                            </td>
                            <td style="padding: 3px 0; text-align: right;">
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0; font-weight: 600;">IDR {{ number_format($order->remaining_cash, 0, ',', '.') }}</p>
                            </td>
                        </tr>
                    </table>
                @else
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 8px 0;">Amount to Pay</p>
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 28px; font-weight: 800; color: #dc2626; margin: 0;">IDR {{ number_format($isActivity ? $order->total_price : $order->price, 0, ',', '.') }}</p>
                @endif
            </td>
        </tr>
    </table>

    {{-- CTA Button --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <a href="{{ $paymentUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    Pay Now
                </a>
            </td>
        </tr>
    </table>

    {{-- Expiry Notice --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
        <tr>
            <td style="padding: 14px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    <strong>⏰ Payment expires in 24 hours.</strong><br>
                    Please complete your payment before the deadline. Your booking will be automatically cancelled if payment is not received.
                </p>
            </td>
        </tr>
    </table>

    {{-- Alternative Link --}}
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #94a3b8; margin: 0 0 10px 0; line-height: 1.5;">
        If the button above doesn't work, copy and paste this link into your browser:
    </p>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #2563eb; margin: 0; word-break: break-all; line-height: 1.5;">
        {{ $paymentUrl }}
    </p>

    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 20px 0 0 0; line-height: 1.6; text-align: center;">
        If you did not make this booking, please ignore this email.
    </p>
@endsection
