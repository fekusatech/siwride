{{-- Email Footer - SIWRide --}}
{{-- Usage: @include('emails.footer') --}}

<!-- SIWRIDE EMAIL FOOTER -->
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 30px;">
    <tr>
        <td style="padding: 0;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top: 1px solid #e5e7eb;">
                <tr>
                    <td style="padding: 30px 0 20px 0;">

                        {{-- Logo --}}
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding-right: 8px; vertical-align: middle;">
                                    <img src="{{ url('assets/images/siwride_logo.png') }}" alt="SIWRide" width="48" height="48" style="display: block; border-radius: 50%;">
                                </td>
                                <td style="vertical-align: middle;">
                                    <span style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 22px; font-weight: 700; color: #dc2626; letter-spacing: -0.5px;">SIWRide</span>
                                </td>
                            </tr>
                        </table>

                        {{-- Tagline --}}
                        <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #6b7280; margin: 8px 0 20px 0; line-height: 1.5;">
                            Your trusted airport transfer &amp; ride service in Bali.
                        </p>

                        {{-- Contact Info --}}
                        <table cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 20px;">
                            <tr>
                                <td style="padding: 3px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #374151; line-height: 1.6;">
                                    <strong>Email:</strong>&nbsp;
                                    <a href="mailto:info@siwride.com" style="color: #2563eb; text-decoration: none;">info@siwride.com</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #374151; line-height: 1.6;">
                                    <strong>Office:</strong>&nbsp;+62 361 XXX XXX
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #374151; line-height: 1.6;">
                                    <strong>WhatsApp:</strong>&nbsp;
                                    <a href="https://wa.me/6281234567890" style="color: #2563eb; text-decoration: none;">+62 812-3456-7890</a>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 3px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; color: #374151; line-height: 1.6;">
                                    <strong>Web:</strong>&nbsp;
                                    <a href="https://siwride.com" style="color: #2563eb; text-decoration: none;">www.siwride.com</a>
                                </td>
                            </tr>
                        </table>

                        {{-- Social Icons --}}
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td style="padding-right: 10px;">
                                    <a href="https://linkedin.com/company/siwride" style="text-decoration: none;">
                                        <img src="https://img.icons8.com/ios-filled/24/0077b5/linkedin.png" alt="LinkedIn" width="24" height="24" style="display: block; border: 0;">
                                    </a>
                                </td>
                                <td style="padding-right: 10px;">
                                    <a href="https://instagram.com/siwride" style="text-decoration: none;">
                                        <img src="https://img.icons8.com/ios-filled/24/e4405f/instagram-new.png" alt="Instagram" width="24" height="24" style="display: block; border: 0;">
                                    </a>
                                </td>
                                <td>
                                    <a href="https://google.com/maps/siwride" style="text-decoration: none;">
                                        <img src="https://img.icons8.com/ios-filled/24/4285f4/google-logo.png" alt="Google" width="24" height="24" style="display: block; border: 0;">
                                    </a>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>
            </table>

            {{-- Environmental Message --}}
            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border-top: 1px solid #e5e7eb;">
                <tr>
                    <td style="padding: 15px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #9ca3af; line-height: 1.5;">
                        Please consider the environment before printing this e-mail.
                    </td>
                </tr>
            </table>

            {{-- Copyright --}}
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tr>
                    <td style="padding: 0 0 20px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #9ca3af; line-height: 1.6;">
                        &copy; {{ date('Y') }} SIWRide. All rights reserved. This e-mail and any attachments are confidential and intended solely for the addressee.
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>
