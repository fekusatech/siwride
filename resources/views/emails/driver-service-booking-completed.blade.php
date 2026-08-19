@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Service Booking Completed
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        Cash received has been recorded and this service booking is now complete.
    </p>

    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td style="padding: 16px; background: #ecfdf5; border-radius: 10px; border: 1px solid #a7f3d0;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #065f46; margin: 0 0 4px 0;">Booking Reference</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 800; color: #059669; margin: 0; letter-spacing: 1px;">{{ $booking->booking_code }}</p>
            </td>
        </tr>
    </table>
@endsection
