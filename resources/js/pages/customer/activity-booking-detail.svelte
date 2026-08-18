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

    const formatStatus = (status: string) => {
        switch (status) {
            case 'pending':
                return { text: 'Pending', bg: '#fff3cd', color: '#856404' };
            case 'confirmed':
                return { text: 'Confirmed', bg: '#d1ecf1', color: '#0c5460' };
            case 'completed':
                return { text: 'Completed', bg: '#d4edda', color: '#155724' };
            case 'cancelled':
                return { text: 'Cancelled', bg: '#f8d7da', color: '#721c24' };
            default:
                return { text: status, bg: '#e2e3e5', color: '#383d41' };
        }
    };

    const formatPaymentStatus = (status: string) => {
        switch (status) {
            case 'pending':
                return { text: 'Awaiting Payment', bg: '#fff3cd', color: '#856404' };
            case 'paid':
                return { text: 'Paid', bg: '#d4edda', color: '#155724' };
            case 'failed':
                return { text: 'Payment Failed', bg: '#f8d7da', color: '#721c24' };
            case 'expired':
                return { text: 'Payment Expired', bg: '#f8d7da', color: '#721c24' };
            default:
                return { text: status, bg: '#e2e3e5', color: '#383d41' };
        }
    };

    let statusInfo = $derived(formatStatus(booking.status));
    let paymentInfo = $derived(formatPaymentStatus(booking.payment_status));
</script>

<AppHead title="Booking Details - {booking.booking_code} | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section style="padding: 60px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Header -->
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <Link href="/customer/profile" class="text-decoration-none" style="color: #64748b;">
                                <i class="fas fa-arrow-left me-2"></i>Back to Profile
                            </Link>
                            <h2 class="fw-bold mb-0 mt-2">Booking Details</h2>
                        </div>
                        <div class="text-end">
                            <span class="badge px-3 py-2" style="background-color: {statusInfo.bg}; color: {statusInfo.color}; font-size: 0.85rem;">
                                {statusInfo.text}
                            </span>
                        </div>
                    </div>

                    <!-- Booking Info Card -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                                <div>
                                    <small class="text-muted">Booking Reference</small>
                                    <h4 class="fw-bold mb-0" style="color: #dc2626;">{booking.booking_code}</h4>
                                </div>
                                <div class="text-end">
                                    <small class="text-muted">Payment Status</small>
                                    <div>
                                        <span class="badge px-3 py-2" style="background-color: {paymentInfo.bg}; color: {paymentInfo.color};">
                                            {paymentInfo.text}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Activity Details -->
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="d-flex align-items-start gap-3 p-3 bg-light rounded-3">
                                        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                                            <i class="fas fa-hiking text-primary fs-5"></i>
                                        </div>
                                        <div>
                                            <small class="text-muted">Activity</small>
                                            <h5 class="fw-bold mb-0">{booking.activity?.title}</h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block">Date</small>
                                        <span class="fw-bold">
                                            {new Date(booking.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block">Participants</small>
                                        <span class="fw-bold">{booking.pax} pax</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block">Price per Person</small>
                                        <span class="fw-bold">{formatRp(Number(booking.price_per_pax))}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block">Total Price</small>
                                        <span class="fw-bold fs-5" style="color: #dc2626;">{formatRp(Number(booking.total_price))}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Info -->
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Customer Information</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Name</small>
                                    <span class="fw-medium">{booking.customer_name}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Email</small>
                                    <span class="fw-medium">{booking.customer_email}</span>
                                </div>
                                {#if booking.customer_phone}
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Phone</small>
                                        <span class="fw-medium">{booking.customer_phone}</span>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>

                    {#if booking.notes}
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">Special Requests</h5>
                                <p class="text-muted mb-0">{booking.notes}</p>
                            </div>
                        </div>
                    {/if}

                    <!-- Actions -->
                    {#if booking.payment_status === 'pending' && booking.payment_reference}
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-4 text-center">
                                <p class="text-muted mb-3">Complete your payment to confirm this booking.</p>
                                <a href={booking.payment_reference} class="btn btn-danger btn-lg px-5">
                                    <i class="fas fa-credit-card me-2"></i>Pay Now
                                </a>
                                <p class="text-muted small mt-3 mb-0">
                                    You will be redirected to Xendit to complete payment.
                                </p>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
