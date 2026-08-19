@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Booking Confirmed!
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        Your booking has been successfully confirmed. Here are your booking details:
    </p>

    {{-- Booking Code --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 4px 0;">Booking Reference</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #dc2626; margin: 0; letter-spacing: 1px;">{{ $order->booking_code }}</p>
            </td>
        </tr>
    </table>

    @if(isset($isService) && $isService)
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

        {{-- Payment Breakdown --}}
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
            <tr>
                <td style="padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 8px 0; font-weight: 600;">Payment Summary</p>
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
                                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #dc2626; margin: 0; font-weight: 600;">Dibayar sekarang (DP {{ rtrim(rtrim(number_format($order->dp_percent, 2, '.', ''), '0'), '.') }}%)</p>
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

    {{-- Passenger Info --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
        <tr>
            <td>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Passenger</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0 0 4px 0; font-weight: 600;">{{ $order->customer_name }}</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 0;">{{ $order->customer_email }}</p>
                @if($order->customer_phone)
                    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 4px 0 0 0;">{{ $order->customer_phone }}</p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Round Trip Return Info --}}
    @if($isRoundTrip && $linkedOrder)
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
        <tr>
            <td>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #10b981; margin: 0 0 10px 0; font-weight: 600;">Return Trip</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px; background: #fff; border: 1px solid #d1fae5; border-radius: 8px;">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td width="50%" style="padding: 4px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Date</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($linkedOrder->date)->format('d M Y') }}</p>
                        </td>
                        <td width="50%" style="padding: 4px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Time</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; font-weight: 600;">{{ \Carbon\Carbon::parse($linkedOrder->time)->format('H:i') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2" style="padding: 4px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Route</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #1e293b; margin: 0; font-weight: 600;">{{ $linkedOrder->pickup_address }} → {{ $linkedOrder->dropoff_address }}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @endif

    {{-- Price Summary --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 8px 0;">Total Paid</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 28px; font-weight: 800; color: #1e293b; margin: 0;">IDR {{ number_format($grandTotal, 0, ',', '.') }}</p>
            </td>
        </tr>
    </table>
    @endif

    {{-- CTA --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td style="text-align: center; padding: 0 0 10px 0;">
                <a href="{{ (isset($isService) && $isService) ? url('/services/' . $order->booking_code . '/booking-detail') : url('/booking/' . $order->booking_code) }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    View Booking Details
                </a>
            </td>
        </tr>
    </table>

    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 20px 0 0 0; line-height: 1.6; text-align: center;">
        Please arrive 45 minutes before departure time. If you have any questions, feel free to contact us.
    </p>
@endsection
