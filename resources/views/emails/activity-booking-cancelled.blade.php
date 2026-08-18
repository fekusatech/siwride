@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Booking Cancelled
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        Your activity booking has been cancelled. If you have any questions, please contact us.
    </p>

    {{-- Booking Code --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #fef2f2; border-radius: 10px; border: 1px solid #fecaca;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #991b1b; margin: 0 0 4px 0;">Booking Reference</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #dc2626; margin: 0; letter-spacing: 1px;">{{ $booking->booking_code }}</p>
            </td>
        </tr>
    </table>

    {{-- Activity Details --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
        <tr>
            <td>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 10px 0; font-weight: 600;">Cancelled Activity</p>
            </td>
        </tr>
        <tr>
            <td style="padding: 14px; background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td style="padding: 6px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Activity</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; color: #1e293b; margin: 0; font-weight: 700;">{{ $booking->activity->title ?? 'Activity' }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td width="50%" style="padding: 6px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Date</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $booking->booking_date->format('d M Y') }}</p>
                        </td>
                        <td width="50%" style="padding: 6px 0;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #94a3b8; margin: 0 0 2px 0;">Participants</p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $booking->pax }} pax</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Refund Notice --}}
    @if($booking->payment_status === 'paid')
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 14px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    <strong>Refund Information</strong><br>
                    Since you have already paid, a refund will be processed within 3-5 business days to your original payment method.
                </p>
            </td>
        </tr>
    </table>
    @endif

    {{-- CTA Button --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <a href="{{ url('/') }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    Browse Activities
                </a>
            </td>
        </tr>
    </table>

    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 20px 0 0 0; line-height: 1.6; text-align: center;">
        If you have any questions, please contact us at info@siwride.com
    </p>
@endsection
