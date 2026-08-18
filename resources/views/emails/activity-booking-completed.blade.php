@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Activity Completed! ⭐
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        We hope you had an amazing experience! Thank you for choosing SIWRide.
    </p>

    {{-- Booking Code --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 16px; background: #f0f9ff; border-radius: 10px; border: 1px solid #bae6fd;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #0c4a6e; margin: 0 0 4px 0;">Booking Reference</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #0284c7; margin: 0; letter-spacing: 1px;">{{ $booking->booking_code }}</p>
            </td>
        </tr>
    </table>

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

    {{-- Review CTA --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 20px; background: linear-gradient(135deg, #fef3c7, #fde68a); border-radius: 12px; text-align: center;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 16px; font-weight: 700; color: #92400e; margin: 0 0 8px 0;">
                    How was your experience?
                </p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #a16207; margin: 0 0 16px 0;">
                    We'd love to hear your feedback! Share your review to help other travelers.
                </p>
                <a href="mailto:info@siwride.com?subject=Review: {{ urlencode($booking->activity->title ?? 'Activity') }}" style="display: inline-block; padding: 12px 28px; background-color: #f59e0b; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    Share Feedback
                </a>
            </td>
        </tr>
    </table>

    {{-- CTA Button --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <a href="{{ url('/') }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    Book Another Activity
                </a>
            </td>
        </tr>
    </table>

    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #64748b; margin: 20px 0 0 0; line-height: 1.6; text-align: center;">
        Thank you for choosing SIWRide. See you on your next adventure!
    </p>
@endsection
