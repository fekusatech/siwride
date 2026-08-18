@extends('emails.layout')

@section('content')
    <h2 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #1e293b; margin: 0 0 8px 0;">
        Reset Your Password
    </h2>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #64748b; margin: 0 0 24px 0; line-height: 1.6;">
        We received a request to reset your password for your SIWRide account.
    </p>

    {{-- Email Info --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="padding: 14px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 4px 0;">Email Address</p>
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; color: #1e293b; margin: 0; font-weight: 600;">{{ $email }}</p>
            </td>
        </tr>
    </table>

    {{-- CTA Button --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 24px;">
        <tr>
            <td style="text-align: center; padding: 10px 0;">
                <a href="{{ $resetUrl }}" style="display: inline-block; padding: 14px 32px; background-color: #dc2626; color: #ffffff; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px; font-weight: 700; text-decoration: none; border-radius: 8px;">
                    Reset Password
                </a>
            </td>
        </tr>
    </table>

    {{-- Expiry Notice --}}
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom: 20px;">
        <tr>
            <td style="padding: 14px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px;">
                <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #92400e; margin: 0; line-height: 1.5;">
                    <strong>⏰ This link expires in 60 minutes.</strong><br>
                    If you did not request a password reset, please ignore this email. Your password will remain unchanged.
                </p>
            </td>
        </tr>
    </table>

    {{-- Alternative Link --}}
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #94a3b8; margin: 0 0 10px 0; line-height: 1.5;">
        If the button above doesn't work, copy and paste this link into your browser:
    </p>
    <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #2563eb; margin: 0; word-break: break-all; line-height: 1.5;">
        {{ $resetUrl }}
    </p>
@endsection
