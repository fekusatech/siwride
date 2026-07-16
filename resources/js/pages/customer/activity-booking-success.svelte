<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link } from '@inertiajs/svelte';

    let { booking } = $props<{ booking: any }>();

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }
</script>

<AppHead title="Booking Confirmed | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section style="padding: 100px 0; background: #f7f9fa;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow border-0 rounded-4 text-center p-5">
                        <div class="mb-4">
                            <div class="bg-success-subtle rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="ti ti-circle-check text-success" style="font-size: 2.5rem;"></i>
                            </div>
                        </div>

                        <h3 class="fw-bold mb-1">Booking Received!</h3>
                        <p class="text-muted mb-4">Your booking is pending payment confirmation.</p>

                        <div class="bg-light rounded-3 p-4 mb-4 text-start">
                            <div class="row g-2">
                                <div class="col-12">
                                    <small class="text-muted d-block">Booking Code</small>
                                    <code class="fs-5">{booking.booking_code}</code>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Activity</small>
                                    <span class="fw-medium">{booking.activity?.title}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Date</small>
                                    <span>{new Date(booking.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}</span>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Participants</small>
                                    <span>{booking.pax} pax</span>
                                </div>
                                <div class="col-12">
                                    <small class="text-muted d-block">Total Paid</small>
                                    <span class="fw-bold fs-5 text-primary">{formatRp(Number(booking.total_price))}</span>
                                </div>
                            </div>
                        </div>

                        <p class="text-muted small mb-4">
                            A confirmation will be sent to <strong>{booking.customer_email}</strong> once payment is verified.
                        </p>

                        <Link href="/" class="btn btn-primary px-5">Back to Home</Link>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
