<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
</script>

<AppHead title="Privacy Policy - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Privacy Policy</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Privacy</span></li>
            </ul>
        </div>
    </section>

    <!-- Legal Content Section -->
    <section class="legal-section pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div
                    class="col-lg-10 col-xl-8 wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div
                        class="legal-content"
                        style="background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f1f1f1;"
                    >
                        <div
                            class="text-center mb-5 pb-3"
                            style="border-bottom: 1px solid #eee;"
                        >
                            <h3
                                style="font-size: 32px; font-weight: 700; margin-bottom: 15px;"
                            >
                                Siwride Privacy Policy
                            </h3>
                            <!-- <p style="color: #666; font-size: 15px;">
                                Last Updated: October 2024
                            </p> -->
                        </div>

                        <div class="legal-text-block">
                            {#if settings?.privacy_content}
                                {@html settings.privacy_content}
                            {:else}
                                <p class="mb-4">
                                    Last Updated: September 2026
                                </p>

                                <h4 class="mb-3">1. Introduction</h4>
                                <p class="mb-4">
                                    Siwride ("we", "our", or "us") provides reliable transportation booking services, private rides, airport transfers, and tour services in Bali through our website and the <strong>Siwride Customer</strong> mobile application. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our mobile application.
                                </p>

                                <h4 class="mb-3">2. Information We Collect</h4>
                                <p class="mb-2">We collect information that you provide directly to us when creating an account, booking a ride, or communicating with us:</p>
                                <ul class="mb-4" style="line-height: 1.8; color: #555;">
                                    <li><strong>Personal Identification Data:</strong> Full name, email address, and phone number.</li>
                                    <li><strong>Booking & Travel Details:</strong> Pick-up location, drop-off destination, travel date and time, flight number (for airport transfers), number of passengers, and special requests.</li>
                                    <li><strong>Account Credentials:</strong> Password and secure authentication tokens if you register an account.</li>
                                    <li><strong>Device and Technical Information:</strong> Operating system version, app version, unique device identifiers, and network information to maintain app performance and security.</li>
                                </ul>

                                <h4 class="mb-3">3. How We Use Your Information</h4>
                                <p class="mb-2">We use the collected information for various business purposes, including:</p>
                                <ul class="mb-4" style="line-height: 1.8; color: #555;">
                                    <li>Processing and managing your ride bookings and itinerary.</li>
                                    <li>Connecting you with our vetted drivers to facilitate pickups and drop-offs.</li>
                                    <li>Facilitating secure online payments and invoicing via authorized payment gateways (Xendit).</li>
                                    <li>Sending booking confirmations, trip status updates, and digital receipts.</li>
                                    <li>Responding to your inquiries and providing customer support.</li>
                                    <li>Improving our app performance, user interface, and overall service quality.</li>
                                </ul>

                                <h4 class="mb-3">4. Third-Party Services and Data Sharing</h4>
                                <p class="mb-4">
                                    We do not sell, rent, or trade your personal information. We may share limited data with trusted third parties solely to operate our services:
                                    <br />• <strong>Assigned Drivers:</strong> Your name, phone number, and pickup/dropoff points are shared with your assigned driver solely to execute your trip.
                                    <br />• <strong>Payment Processors:</strong> We partner with Xendit to handle payment transactions securely. We do not store your credit card numbers or banking passwords on our servers.
                                </p>

                                <h4 class="mb-3">5. Data Security and Storage</h4>
                                <p class="mb-4">
                                    We implement robust technical and organizational security measures, including HTTPS encryption, secure session tokens, and encrypted local storage on mobile devices to protect your personal data from unauthorized access, loss, or alteration.
                                </p>

                                <h4 class="mb-3">6. Data Retention and Account Deletion</h4>
                                <p class="mb-4">
                                    We retain your data for as long as necessary to fulfill bookings, comply with legal obligations, and resolve disputes. You have the right to request access to, correction of, or permanent deletion of your personal data and account. To request account or data deletion, please contact us at <strong>support@siwride.com</strong> or via our customer support channels, and we will process your request promptly.
                                </p>

                                <h4 class="mb-3">7. Children's Privacy</h4>
                                <p class="mb-4">
                                    Our services are not intended for individuals under the age of 17. We do not knowingly collect personal information from children.
                                </p>

                                <h4 class="mb-3">8. Changes to This Privacy Policy</h4>
                                <p class="mb-4">
                                    We may update this Privacy Policy from time to time to reflect changes in our practices or applicable laws. Any updates will be posted on this page with a revised "Last Updated" date.
                                </p>

                                <h4 class="mb-3">9. Contact Us</h4>
                                <p class="mb-0">
                                    If you have questions or concerns about this Privacy Policy or how your data is handled, please contact us:
                                    <br />• <strong>Email:</strong> support@siwride.com / info@siwride.com
                                    <br />• <strong>Location:</strong> Bali, Indonesia
                                    <br />• <strong>Website:</strong> https://siwride.com
                                </p>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .legal-text-block h4 {
        font-size: 20px;
        font-weight: 700;
        color: #1a1a1a;
        margin-top: 25px;
    }
    .legal-text-block p,
    .legal-text-block li {
        color: #555;
        line-height: 1.8;
        font-size: 15px;
    }
</style>
