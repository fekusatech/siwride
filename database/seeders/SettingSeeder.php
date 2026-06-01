<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::truncate();

        $settings = [
            'company_phone' => '+62 811-3810-5600',
            'company_email' => 'info@siwride.com',
            'company_address' => 'Jl. Mahendradatta selatan GG. Robby William no 99, Bali, Indonesia',
            'company_facebook' => 'https://facebook.com/siwride',
            'company_twitter' => 'https://twitter.com/siwride',
            'company_instagram' => 'https://instagram.com/siwride',
            'company_linkedin' => 'https://linkedin.com/company/siwride',
            
            'hero_welcome_text' => 'Premium Bali Transport',
            'hero_title' => 'Your Journey, Our Priority',
            'hero_subtitle' => 'Experience the beauty of Bali with our professional drivers and premium vehicles. Safe, comfortable, and reliable.',
            
            'services_title' => 'Our Premium Services',
            'services_subtitle' => 'What We Offer',
            'our_services' => json_encode([
                [
                    'title' => 'Airport Transfers',
                    'description' => 'Start and end your trip right. Our driver will be waiting at the arrival gate with your name sign.',
                    'icon' => 'icon-traveler-with-a-suitcase-1'
                ],
                [
                    'title' => 'Daily Charters',
                    'description' => 'Hire a car and driver for the whole day. Explore Bali at your own pace with unlimited mileage within designated zones.',
                    'icon' => 'flaticon-check'
                ],
                [
                    'title' => 'Point-to-Point',
                    'description' => 'Direct transfers between your hotel, beach clubs, restaurants, or any destination in Bali.',
                    'icon' => 'icon-traveler-with-a-suitcase-1'
                ],
                [
                    'title' => 'Event Transport',
                    'description' => 'Coordinated transportation for weddings, conferences, or group tours. Premium vans available.',
                    'icon' => 'flaticon-check'
                ]
            ]),

            'coverage_area_title' => 'Where We Operate',
            
            'destinations_title' => 'Must-Visit Locations',
            'destinations_subtitle' => 'Explore the Best of Bali',
            'popular_destinations' => json_encode([
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
                    'name' => 'Tanah Lot',
                    'location' => 'Tabanan, Bali',
                    'img' => '/assets/images/destination/tanahlot-temple.jpg',
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
                [
                    'name' => 'Lempuyang Temple',
                    'location' => 'Karangasem, Bali',
                    'img' => '/assets/images/destination/lempuyang.jpg',
                ]
            ]),

            'why_choose_us_title' => 'Why Choose Siwride?',
            'why_choose_us_subtitle' => 'The Smart Choice',
            'why_choose_us_text' => 'We are committed to providing exceptional transport services across Bali. With our well-maintained fleet and professional drivers, your safety and comfort are guaranteed.',
            'why_choose_us_passenger_count' => '15k+',
            'why_choose_us_features' => json_encode([
                [
                    'title' => 'Professional Drivers',
                    'text' => 'Fully licensed, English-speaking drivers who know the best routes.',
                    'icon' => 'icon-traveler-with-a-suitcase-1'
                ],
                [
                    'title' => 'Transparent Pricing',
                    'text' => 'Fixed rates with no hidden fees, tolls, or parking charges added later.',
                    'icon' => 'flaticon-check'
                ],
                [
                    'title' => 'Pristine Vehicles',
                    'text' => 'Clean, air-conditioned, and regularly serviced cars for your comfort.',
                    'icon' => 'icon-traveler-with-a-suitcase-1'
                ]
            ]),

            'customer_testimonials' => json_encode([
                [
                    'name' => 'Sarah Miller',
                    'country' => 'Australia',
                    'comment' => 'Booking my airport transfer via Siwride was the best decision. The driver was already there with a sign, and the car was incredibly clean and fresh!',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-1.jpg',
                ],
                [
                    'name' => 'James Chen',
                    'country' => 'Singapore',
                    'comment' => 'We used the SUV for a full day trip to Ubud. Our driver knew exactly where to go and was very patient with our children. Highly recommended!',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-2.jpg',
                ],
                [
                    'name' => 'Linda Wagner',
                    'country' => 'Germany',
                    'comment' => 'Safe driving and easy booking. I felt very secure as a solo traveler. The transparent pricing is a huge plus compared to other local options.',
                    'rating' => 5,
                    'img' => '/assets/images/resources/why-choose-one-author-3.jpg',
                ],
            ]),

            'faq_items' => json_encode([
                [
                    'question' => 'How do I book a ride?',
                    'answer' => 'You can book easily through our website by entering your pickup location, destination, and preferred time. You will receive an instant confirmation.',
                ],
                [
                    'question' => 'Are tolls and parking included?',
                    'answer' => 'Yes, all our prices are final and include toll road fees and standard parking charges. There are no hidden costs.',
                ],
                [
                    'question' => 'Do you provide child car seats?',
                    'answer' => 'Yes, we provide child seats upon request for free. Please make sure to add it in the booking extras during checkout.',
                ],
                [
                    'question' => 'What happens if my flight is delayed?',
                    'answer' => 'Our drivers track your flight status based on the flight number provided. We will adjust the pickup time automatically without extra charges.',
                ],
                [
                    'question' => 'Can I cancel or change my booking?',
                    'answer' => 'Yes, you can cancel or modify your booking up to 24 hours before the scheduled pickup time for a full refund.',
                ],
            ]),

            'terms_content' => '<h4 class="mb-3">1. Introduction</h4>
<p class="mb-5">Welcome to Siwride. These Terms and Conditions govern your use of our transportation services in Bali. By booking a ride, you agree to comply with and be bound by these terms.</p>

<h4 class="mb-3">2. Booking & Cancellations</h4>
<p class="mb-3">All bookings made through the Siwride platform are subject to availability. Upon successful booking, you will receive a confirmation code and booking details.</p>
<ul class="mb-5" style="list-style-type: none; padding-left: 0;">
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> Free cancellation is available up to 24 hours before your scheduled pickup time.
    </li>
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> Cancellations made within 24 hours of the pickup time may be subject to a cancellation fee equivalent to 50% of the total fare.
    </li>
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> "No-shows" will be charged the full amount of the booked service.
    </li>
</ul>

<h4 class="mb-3">3. Fares & Payments</h4>
<p class="mb-5">All fares are displayed in Indonesian Rupiah (IDR). Fares cover toll fees, parking, and specific driver amenities unless otherwise marked on custom charter bookings.</p>

<h4 class="mb-3">4. Passengers & Luggage</h4>
<p class="mb-5">Siwride adheres strictly to vehicle capacity limits to ensure the safety and comfort of all our passengers. Our drivers reserve the right to refuse service if the number of passengers or amount of luggage heavily exceeds the vehicle\'s safely designated limits.</p>

<h4 class="mb-3">5. Conduct & Liability</h4>
<p class="mb-5">Passengers are expected to maintain respectful behavior towards our drivers. Smoking, consumption of illegal substances, and carrying hazardous materials inside Siwride vehicles are strictly prohibited. Siwride is not liable for items left behind in the vehicles.</p>',

            'privacy_content' => '<h4 class="mb-3">1. Information We Collect</h4>
<p class="mb-3">When you use the Siwride platform to book trips around Bali, we may collect various types of information, including, but not limited to:</p>
<ul class="mb-5" style="list-style-type: none; padding-left: 0;">
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Personal Information:</strong> Your name, phone number, and email address used for booking and communication.
    </li>
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Trip Details:</strong> Pickup locations, drop-off locations, date, schedule, and requested amenities.
    </li>
    <li style="margin-bottom: 10px; position: relative; padding-left: 20px;">
        <i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Technical Data:</strong> IP address, device types, browser information, and interactions with our website to help us improve the platform.
    </li>
</ul>

<h4 class="mb-3">2. How We Use Your Information</h4>
<p class="mb-5">Your data primarily enables us to successfully match you with our drivers and confirm your requested bookings. We also utilize this information to send booking confirmations, communicate important updates regarding your ride, process payments, and respond to your customer support requests.</p>

<h4 class="mb-3">3. Data Sharing and Disclosure</h4>
<p class="mb-5">Siwride respects your privacy and will never sell your personal data. However, we do share specific necessary information (like your pickup details and phone number) exclusively with your assigned driver so they can safely fulfill their service. We may also share data with third-party service providers (like payment gateways) under strict confidentiality agreements.</p>

<h4 class="mb-3">4. Cookies and Local Storage</h4>
<p class="mb-5">Our website utilizes "cookies" and local browser storage to enhance your browsing experience, remember your preferences, and maintain seamless functionality across sessions. By browsing our website, you consent to our use of these simple tracking technologies. You can always disable cookies through your browser settings, though doing so may limit your ability to book a ride smoothly.</p>

<h4 class="mb-3">5. Data Deletion & Your Rights</h4>
<p>You have the full right to access, amend, or request the deletion of any personal data Siwride holds about you at any time. Simply contact our support team at our official email, and we will honor your request promptly inline with applicable data protection laws.</p>',

            'booking_extras' => json_encode([
                ['id' => 'english_driver', 'label' => 'English-speaking driver', 'description' => 'A driver with fluent English communication ability.', 'price' => 50000],
                ['id' => 'water', 'label' => 'Drinking water', 'description' => 'A bottle of still water (0.5L) per passenger.', 'price' => 15000],
                ['id' => 'pets', 'label' => 'I am travelling with pets', 'description' => 'Pets must be kept in a carrier.', 'price' => 0],
                ['id' => 'child_seat', 'label' => 'Child Car Seat', 'description' => 'Safe and comfortable car seat for toddlers.', 'price' => 0],
                ['id' => 'flower_garland', 'label' => 'Flower Garland Welcome', 'description' => 'Traditional Balinese flower garland upon arrival.', 'price' => 75000],
            ]),
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }
    }
}
