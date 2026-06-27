{{-- Email Layout - SIWRide --}}
{{-- Usage: @extends('emails.layout') @section('content') ... @endsection --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ $subject ?? 'SIWRide' }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f3f4f6;">
        <tr>
            <td align="center" style="padding: 40px 16px;">
                <table cellpadding="0" cellspacing="0" border="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);">

                    {{-- Header --}}
                    <tr>
                        <td style="padding: 24px 30px; background-color: #dc2626; text-align: center;">
                            <a href="https://siwride.com" style="text-decoration: none;">
                                <img src="{{ url('assets/images/siwride_logo.png') }}" alt="SIWRide" width="44" height="44" style="display: inline-block; border-radius: 50%; vertical-align: middle;">
                                <span style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 20px; font-weight: 700; color: #ffffff; letter-spacing: -0.3px; vertical-align: middle; margin-left: 10px;">SIWRide</span>
                            </a>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 30px;">
                            @yield('content')
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 0 30px 10px 30px;">
                            @include('emails.footer')
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
