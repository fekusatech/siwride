<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { booking_code, order } = $props<{
        booking_code: string;
        order: {
            customer_name: string;
            email: string;
            pickup_address: string;
            dropoff_address: string;
            date: string;
            time: string;
            vehicle_type: string;
            passengers: number;
        } | null;
    }>();

    const vehicleLabels: Record<string, string> = {
        economy: 'Economy / Standard',
        premium: 'Premium SUV / Sedan',
        van: 'Van / Minibus',
        bus: 'Mid/Large Bus',
        special: 'Special Vehicle',
    };
</script>

<AppHead title="Booking Confirmed - Siwride" />

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
                Booking Confirmed
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/booking">Booking</a></li>
                <li><span>Success</span></li>
            </ul>
        </div>
    </section>

    <!-- Success Section -->
    <section
        class="booking-section"
        style="padding: 100px 0; background: #f8f9fa; min-height: 60vh;"
    >
        <div class="container">
            <div class="row">
                <div
                    class="col-lg-8 col-xl-6 mx-auto wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div class="text-center mb-5">
                        <div
                            style="width: 100px; height: 100px; background: #28a745; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;"
                        >
                            <i
                                class="flaticon-check"
                                style="font-size: 50px; color: white;"
                            ></i>
                        </div>
                        <h3
                            style="font-size: 32px; font-weight: 700; color: #222; margin-bottom: 15px;"
                        >
                            Thank You, {order?.customer_name || 'Guest'}!
                        </h3>
                        <p style="color: #666; font-size: 18px;">
                            Your booking request has been received successfully.
                        </p>
                    </div>

                    <div
                        class="booking-form"
                        style="background: white; border-radius: 20px; padding: 40px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #f1f1f1;"
                    >
                        <div class="text-center mb-4">
                            <h4
                                style="font-size: 18px; color: #666; margin-bottom: 10px;"
                            >
                                Your Booking Code
                            </h4>
                            <div
                                style="background: var(--travhub-base); color: white; padding: 15px 30px; border-radius: 10px; font-size: 28px; font-weight: 700; font-family: monospace; display: inline-block;"
                            >
                                {booking_code}
                            </div>
                            <p
                                style="color: #888; font-size: 14px; margin-top: 15px;"
                            >
                                Please save this code for your reference
                            </p>
                        </div>

                        {#if order}
                            <hr style="margin: 30px 0; border-color: #eee;" />

                            <h5
                                style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #444;"
                            >
                                Booking Details
                            </h5>

                            <div style="display: grid; gap: 15px;">
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;"
                                        >Pickup Location</span
                                    >
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{order.pickup_address}</span
                                    >
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;">Destination</span
                                    >
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{order.dropoff_address}</span
                                    >
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;">Date & Time</span
                                    >
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{order.date} at {order.time}</span
                                    >
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;"
                                        >Vehicle Type</span
                                    >
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{vehicleLabels[order.vehicle_type] ||
                                            order.vehicle_type}</span
                                    >
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;">Passengers</span>
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{order.passengers} person{order.passengers >
                                        1
                                            ? 's'
                                            : ''}</span
                                    >
                                </div>
                                <div
                                    style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f5f5f5;"
                                >
                                    <span style="color: #888;">Email</span>
                                    <span
                                        style="font-weight: 500; color: #333; text-align: right;"
                                        >{order.email}</span
                                    >
                                </div>
                            </div>
                        {/if}

                        <hr style="margin: 30px 0; border-color: #eee;" />

                        <div
                            style="background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px;"
                        >
                            <h6
                                style="font-weight: 600; margin-bottom: 10px; color: #444;"
                            >
                                What happens next?
                            </h6>
                            <ul
                                style="color: #666; font-size: 14px; line-height: 1.8; margin: 0; padding-left: 20px;"
                            >
                                <li>
                                    Our admin will review your booking request
                                </li>
                                <li>
                                    You will receive a confirmation email
                                    shortly
                                </li>
                                <li>A driver will be assigned to your trip</li>
                                <li>
                                    Payment details will be provided via email
                                </li>
                            </ul>
                        </div>

                        <div class="text-center">
                            <a
                                href="/"
                                class="travhub-btn"
                                style="margin-right: 10px;"
                            >
                                <span>Back to Home</span>
                            </a>
                            <a
                                href="/booking"
                                class="travhub-btn"
                                style="background-color: #222;"
                            >
                                <span>Book Another Ride</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
