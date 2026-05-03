<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
</script>

<AppHead title="Terms & Conditions - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">
                Terms & Conditions
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Terms</span></li>
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
                                Siwride Standard Terms & Conditions
                            </h3>
                            <p style="color: #666; font-size: 15px;">
                                Last Updated: October 2024
                            </p>
                        </div>

                        <div class="legal-text-block">
                            <h4 class="mb-3">1. Introduction</h4>
                            <p class="mb-5">
                                Welcome to Siwride. These Terms and Conditions
                                govern your use of our website and
                                transportation services in Bali. By booking a
                                ride or utilizing our services, you agree to
                                comply with and be bound by these terms. If you
                                do not agree with any part of these terms,
                                please refrain from using our services.
                            </p>

                            <h4 class="mb-3">2. Booking & Cancellations</h4>
                            <p class="mb-3">
                                All bookings made through the Siwride platform
                                are subject to availability. Upon successful
                                booking, you will receive a confirmation code
                                and booking details.
                            </p>
                            <ul
                                class="mb-5"
                                style="list-style-type: none; padding-left: 0;"
                            >
                                <li
                                    style="margin-bottom: 10px; position: relative; padding-left: 20px;"
                                >
                                    <i
                                        class="flaticon-check"
                                        style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"
                                    ></i> Free cancellation is available up to 24
                                    hours before your scheduled pickup time.
                                </li>
                                <li
                                    style="margin-bottom: 10px; position: relative; padding-left: 20px;"
                                >
                                    <i
                                        class="flaticon-check"
                                        style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"
                                    ></i> Cancellations made within 24 hours of the
                                    pickup time may be subject to a cancellation fee
                                    equivalent to 50% of the total fare.
                                </li>
                                <li
                                    style="margin-bottom: 10px; position: relative; padding-left: 20px;"
                                >
                                    <i
                                        class="flaticon-check"
                                        style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"
                                    ></i> "No-shows" will be charged the full amount
                                    of the booked service.
                                </li>
                            </ul>

                            <h4 class="mb-3">3. Fares & Payments</h4>
                            <p class="mb-5">
                                All fares are displayed in Indonesian Rupiah
                                (IDR) unless stated otherwise. Fares cover toll
                                fees, parking, and specific driver amenities
                                unless otherwise marked on custom charter
                                bookings. Payment must be completed via our
                                designated gateways or to the driver directly if
                                paying by Cash on Delivery (where applicable).
                            </p>

                            <h4 class="mb-3">4. Passengers & Luggage</h4>
                            <p class="mb-5">
                                Siwride adheres strictly to vehicle capacity
                                limits to ensure the safety and comfort of all
                                our passengers. Our drivers reserve the right to
                                refuse service if the number of passengers or
                                amount of luggage heavily exceeds the vehicle's
                                safely designated limits. In such cases, no
                                refund will be issued if the ride cannot
                                proceed.
                            </p>

                            <h4 class="mb-3">5. Conduct & Liability</h4>
                            <p class="mb-5">
                                Passengers are expected to maintain respectful
                                behavior towards our drivers. Smoking,
                                consumption of illegal substances, and carrying
                                hazardous materials inside Siwride vehicles are
                                strictly prohibited. Siwride is not liable for
                                items left behind in the vehicles, though our
                                team will make extensive efforts to recover and
                                return any reported lost properties.
                            </p>

                            <h4 class="mb-3">6. Modifications</h4>
                            <p>
                                Siwride reserves the right to modify these Terms
                                and Conditions at any given time. Changes take
                                immediate effect upon being published on this
                                page. Your continued use of our services
                                indicates an agreement to any revisions of these
                                clauses.
                            </p>
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
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }
    .legal-text-block p {
        color: #666;
        line-height: 1.8;
        font-size: 16px;
    }
</style>
