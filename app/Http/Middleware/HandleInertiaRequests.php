<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use App\Models\VehicleCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $defaults = [
            'business_name' => config('app.name'),
            'logo' => null,
            'favicon' => null,
            'recaptcha_enabled' => '0',
            'recaptcha_site_key' => null,
            'recaptcha_secret_key' => null,
            
            // Company Info
            'company_phone' => '+62 812-3456-78',
            'company_email' => 'info@siwride.com',
            'company_address' => 'Bali, Indonesia',
            'company_facebook' => 'https://facebook.com',
            'company_twitter' => 'https://twitter.com',
            'company_instagram' => 'https://instagram.com',
            'company_linkedin' => 'https://linkedin.com',

            // Hero Section
            'hero_welcome_text' => 'Welcome to Siwride',
            'hero_title' => 'Hassle-Free Bali Travels with Siwride',
            'hero_subtitle' => 'Book your professional driver in advance and enjoy a comfortable, safe, and reliable journey across the beautiful island of Bali.',
            
            // Premium Services
            'services_title' => 'Our Premium Services',
            'services_subtitle' => 'We offer a wide range of services to cater to your every need.',
            'our_services' => [
                [
                    'title' => 'Airport Transfer',
                    'description' => 'Punctual pick-up and drop-off to and from Ngurah Rai Airport. Start or end your journey stress-free.',
                    'icon' => 'flaticon-signpost',
                    'img' => '/assets/images/services/airport-transfer.jpg',
                ],
                [
                    'title' => 'Point-to-Point Ride',
                    'description' => 'Quick and reliable everyday rides to take you from your hotel to the beach, restaurant, or anywhere in between.',
                    'icon' => 'icon-pin-2',
                    'img' => '/assets/images/services/point-to-point-ride.jpg',
                ],
                [
                    'title' => 'Intercity Travel',
                    'description' => 'Comfortable long-distance travel across Bali for those planning to move between distant regions like Ubud to Uluwatu.',
                    'icon' => 'flaticon-distance',
                    'img' => '/assets/images/services/intercity-travel.webp',
                ],
                [
                    'title' => 'Corporate Travel',
                    'description' => 'Premium transportation solutions for business delegates, meetings, and corporate events with top-tier services.',
                    'icon' => 'icon-traveler-with-a-suitcase-1',
                    'img' => '/assets/images/services/corporate-travel.jpg',
                ],
                [
                    'title' => 'Hotel Transfer',
                    'description' => 'Convenient hotel transfer services for comfortable travel to and from your accommodation.',
                    'icon' => 'fa fa-hotel',
                    'img' => '/assets/images/services/hotel-transfer.jpg',
                ],
            ],

            // Coverage Area
            'coverage_area_title' => 'Our Coverage Area',
            'coverage_area_image' => null, // Default to null, handle via UI upload if needed

            // Popular Destinations
            // FAQ
            'faq_items' => [
                [
                    'question' => 'How do I book a ride with Siwride?',
                    'answer' => 'Booking is extremely simple! You can easily book a ride by navigating to our Booking page, entering your pickup and drop-off locations, selecting your vehicle, and confirming. Alternatively, you can contact us directly for custom arrangements.',
                ],
                [
                    'question' => 'Are your drivers fluent in English?',
                    'answer' => 'Yes, all Siwride drivers undergo strict vetting processes and are fluent in English. They are not only professional drivers but can also serve as knowledgeable local guides to enhance your Bali experience.',
                ],
            ],

            // Legal Pages
            'terms_content' => '<h4 class="mb-3">1. Introduction</h4><p class="mb-5">Welcome to Siwride. These Terms and Conditions govern your use of our website and transportation services in Bali. By booking a ride or utilizing our services, you agree to comply with and be bound by these terms.</p>',
            'privacy_content' => '<h4 class="mb-3">1. Information Collection</h4><p class="mb-5">We collect information from you when you book a ride on our site, subscribe to our newsletter, respond to a survey or fill out a form.</p>',

            // Booking Extras
            'booking_extras' => [
                ['id' => 'english_driver', 'label' => 'English-speaking driver', 'description' => 'A driver with basic English communication ability.', 'price' => 30000],
                ['id' => 'water', 'label' => 'Drinking water', 'description' => 'A bottle of still water (0.5L).', 'price' => 20000],
                ['id' => 'pets', 'label' => 'I am travelling with pets', 'description' => 'Pets must be kept in a carrier. Additional charges may apply.', 'price' => 0],
            ],
            'destinations_subtitle' => 'Explore the most beautiful places in Bali with our professional drivers.',
            'popular_destinations' => [
                [
                    'name' => 'Tanah Lot Temple',
                    'location' => 'Tabanan, Bali',
                    'img' => '/assets/images/destination/tanahlot-temple.jpg',
                ],
                [
                    'name' => 'Uluwatu Temple',
                    'location' => 'South Kuta, Bali',
                    'img' => '/assets/images/destination/uluwatu-temple.webp',
                ],
                [
                    'name' => 'Tegallalang Rice Terrace',
                    'location' => 'Ubud, Bali',
                    'img' => '/assets/images/destination/tegallalang.webp',
                ],
                [
                    'name' => 'Lempuyang Temple',
                    'location' => 'Karangasem, Bali',
                    'img' => '/assets/images/destination/lempuyang.jpg',
                ],
                [
                    'name' => 'Seminyak Beach',
                    'location' => 'Kuta, Bali',
                    'img' => '/assets/images/destination/seminyak.webp',
                ],
                [
                    'name' => 'Mount Batur',
                    'location' => 'Kintamani, Bali',
                    'img' => '/assets/images/destination/mount-batur.jpg',
                ],
            ],

            // Why Choose Us
            'why_choose_us_title' => 'Why Siwride?',
            'why_choose_us_subtitle' => 'Your Premium Ride Partner',
            'why_choose_us_text' => 'Experience hassle-free, comfortable, and safe transportation across Bali with our highly-rated drivers and diverse fleet. We prioritize your comfort above all else.',
            'why_choose_us_features' => [
                [
                    'title' => 'Professional Drivers',
                    'text' => 'All our drivers are highly trained, vetted, and dedicated to ensuring your smooth and safe journey everywhere you go.',
                    'icon' => 'icon-traveler-with-a-suitcase-1'
                ],
                [
                    'title' => 'Transparent Pricing',
                    'text' => 'No hidden fees or unexpected surges! Enjoy high-quality service at fair, fixed rates every time you ride.',
                    'icon' => 'flaticon-check'
                ]
            ],
            'why_choose_us_passenger_count' => '10k+',
            
            // Testimonials
            'customer_testimonials' => [
                [
                    'name' => 'Sarah Miller',
                    'country' => 'Australia',
                    'comment' => 'Booking my airport transfer via Siwride was the best decision. The driver was already there with a sign, and the car was incredibly clean and fresh!',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-1.png',
                ],
                [
                    'name' => 'James Chen',
                    'country' => 'Hong Kong',
                    'comment' => 'We used the SUV for a full day trip to Ubud. Our driver knew exactly where to go and was very patient with our children. Highly recommended!',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-5.png',
                ],
                [
                    'name' => 'Linda Wagner',
                    'country' => 'Germany',
                    'comment' => 'Safe driving and easy booking. I felt very secure as a solo traveler. The transparent pricing is a huge plus compared to other local options.',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-4.png',
                ],
                [
                    'name' => 'David Singh',
                    'country' => 'India',
                    'comment' => 'Excellent service! The app was easy to use and the pickup was on time. The driver was professional and the car was very comfortable.',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-3.png',
                ],
                [
                    'name' => 'Emilia Clarke',
                    'country' => 'UK',
                    'comment' => 'Siwride is my go-to for Bali travel. Reliable, transparent, and superior fleet quality compared to others. Worth every penny!',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-2.png',
                ],
            ]
        ];

        $settings = Setting::values($defaults);

        // Decode JSON arrays if they are strings
        $jsonKeys = ['our_services', 'popular_destinations', 'why_choose_us_features', 'customer_testimonials', 'faq_items', 'booking_extras'];
        foreach ($jsonKeys as $key) {
            if (is_string($settings[$key])) {
                $settings[$key] = json_decode($settings[$key], true) ?? $defaults[$key];
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'customer' => Auth::guard('customer')->user(),
            ],
            'settings' => [
                ...$settings,
                'updated_at' => Setting::query()->max('updated_at'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'google_maps_api_key' => config('services.google.maps_api_key'),
            'vehicleCategories' => VehicleCategory::all(),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
                'warning' => $request->session()->get('warning'),
                'info' => $request->session()->get('info'),
            ],
        ];
    }
}
