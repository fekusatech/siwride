<!DOCTYPE html>
@php 
    $isDashboard = request()->is('dashboard*') || request()->is('admin*') || request()->is('login-admin*') || request()->is('c/*') || request()->is('login*'); 
    $favicon = \App\Models\Setting::getValue('favicon');
    $faviconUrl = $favicon ? asset('storage/' . $favicon) : null;
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" 
    @if($isDashboard)
        data-bs-theme="light" 
        data-menu-color="dark" 
        data-topbar-color="light"
        data-layout-mode="fluid"
        data-sidenav-size="default"
    @else
        @class(['dark' => ($appearance ?? 'system') == 'dark'])
    @endif
>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link rel="icon" href="{{ $faviconUrl ?? '/favicon.ico' }}" sizes="any">
        @if(!$faviconUrl)
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        @endif
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />


        @if(!$isDashboard)
            <!-- Travelhub Frontend Assets -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@400..700&family=Geologica:wght@100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
            
            <link rel="stylesheet" href="/assets/vendors/bootstrap/css/bootstrap.min.css">
            <link rel="stylesheet" href="/assets/vendors/bootstrap-select/bootstrap-select.min.css">
            <link rel="stylesheet" href="/assets/vendors/animate/animate.min.css">
            <link rel="stylesheet" href="/assets/vendors/fontawesome/css/all.min.css">
            <link rel="stylesheet" href="/assets/vendors/jquery-ui/jquery-ui.css">
            <link rel="stylesheet" href="/assets/vendors/jarallax/jarallax.css">
            <link rel="stylesheet" href="/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.css">
            <link rel="stylesheet" href="/assets/vendors/daterangepicker-master/daterangepicker.css">
            <link rel="stylesheet" href="/assets/vendors/travhub-icons/style.css">
            <link rel="stylesheet" href="/assets/vendors/travhub-icons2/style.css" />
            <link rel="stylesheet" href="/assets/vendors/slick/slick.css">
            <link rel="stylesheet" href="/assets/vendors/owl-carousel/css/owl.carousel.min.css">
            <link rel="stylesheet" href="/assets/vendors/owl-carousel/css/owl.theme.default.min.css">
            <link rel="stylesheet" href="/assets/css/travhub.css" />

            <script>
                // Preloader Fallback: Hide after 5 seconds if script fails
                setTimeout(function() {
                    var preloader = document.querySelector('.preloader');
                    if (preloader && preloader.style.display !== 'none') {
                        console.warn('Preloader stuck? Force hiding...');
                        preloader.style.display = 'none';
                    }
                }, 5000);
            </script>
        @else
            <!-- Boron Admin Assets -->
            <link rel="shortcut icon" href="{{ $faviconUrl ?? '/assets-admin/images/favicon.ico' }}">
            <link href="/assets-admin/css/vendor.min.css" rel="stylesheet" type="text/css" />
            <link href="/assets-admin/css/app.min.css" rel="stylesheet" type="text/css" id="app-style" />
            <link href="/assets-admin/css/icons.min.css" rel="stylesheet" type="text/css" />
            <script>
                /**
                * Boron Admin Configuration initializer
                * Prevents ThemeCustomizer errors in config.js and app.js
                */
                window.__BORON_CONFIG__ = {
                    "theme": "light",
                    "topbar": { "color": "light" },
                    "menu": { "color": "dark" },
                    "sidenav": { "size": "default", "user": true },
                    "layout": { "mode": "fluid" }
                };
            </script>
            <script src="/assets-admin/js/config.js"></script>
        @endif

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
        @if($isDashboard)
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @endif
        
        <x-inertia::head>
            <title>{{ $page['props']['settings']['business_name'] ?? config('app.name', 'Laravel') }}</title>
            @if(isset($page['props']['settings']['favicon']))
                <link rel="icon" type="image/x-icon" href="/storage/{{ $page['props']['settings']['favicon'] }}">
            @endif
        </x-inertia::head>
    </head>
    <body class="{{ $isDashboard ? '' : 'font-sans antialiased' }}">
        <x-inertia::app />
        
        @if(!$isDashboard)
            <!-- Travelhub Frontend Scripts -->
            <script src="/assets/vendors/jquery/jquery-3.7.1.min.js"></script>
            <script src="/assets/vendors/daterangepicker-master/moment.min.js"></script>
            <script>
                if (typeof window.moment !== 'function' && window.moment && window.moment.default) {
                    window.moment = window.moment.default;
                }
            </script>
            <script src="/assets/vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
            <script src="/assets/vendors/bootstrap-select/bootstrap-select.min.js"></script>
            <script src="/assets/vendors/jarallax/jarallax.min.js"></script>
            <script src="/assets/vendors/jquery-ui/jquery-ui.js"></script>
            <script src="/assets/vendors/jquery-ajaxchimp/jquery.ajaxchimp.min.js"></script>
            <script src="/assets/vendors/jquery-appear/jquery.appear.min.js"></script>
            <script src="/assets/vendors/jquery-magnific-popup/jquery.magnific-popup.min.js"></script>
            <script src="/assets/vendors/jquery-validate/jquery.validate.min.js"></script>
            <script src="/assets/vendors/wnumb/wNumb.min.js"></script>
            <script src="/assets/vendors/daterangepicker-master/daterangepicker.js"></script>
            <script src="/assets/vendors/owl-carousel/js/owl.carousel.min.js"></script>
            <script src="/assets/vendors/wow/wow.js"></script>
            <script src="/assets/vendors/imagesloaded/imagesloaded.min.js"></script>
            <script src="/assets/vendors/isotope/isotope.js"></script>
            <script src="/assets/vendors/simpleParallax/simpleParallax.min.js"></script>
            <script src="/assets/vendors/slick/slick.min.js"></script>
            <script src="/assets/vendors/countdown/countdown.min.js"></script>
            <script src="/assets/vendors/gsap/gsap.js"></script>
            <script src="/assets/vendors/gsap/scrolltrigger.min.js"></script>
            <script src="/assets/vendors/gsap/splittext.min.js"></script>
            <script src="/assets/vendors/gsap/travhub-split.js"></script>
            <script src="/assets/js/travhub.js"></script>
        @else
            <!-- Boron Admin Scripts -->
            <script src="/assets-admin/js/vendor.min.js"></script>
            <script src="/assets-admin/js/app.js"></script>
        @endif
    </body>
</html>
