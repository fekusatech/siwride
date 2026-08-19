<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link } from '@inertiajs/svelte';

    let { booking, assigned_driver } = $props<{
        booking: any;
        assigned_driver: {
            id: number;
            name: string;
            email: string;
            phone: string;
            image: string | null;
        } | null;
    }>();

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function initial(name: string): string {
        return name.trim().charAt(0).toUpperCase() || '?';
    }

    const formatStatus = (status: string) => {
        switch (status) {
            case 'pending':
                return { text: 'Pending', bg: '#fff3cd', color: '#856404' };
            case 'confirmed':
                return { text: 'Confirmed', bg: '#d4edda', color: '#155724' };
            case 'completed':
                return { text: 'Completed', bg: '#d1ecf1', color: '#0c5460' };
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
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <h2 class="fw-bold mb-0">Booking Details</h2>
                            <small class="text-muted">{booking.booking_code}</small>
                        </div>
                        <span class="badge px-3 py-2" style="background-color: {statusInfo.bg}; color: {statusInfo.color}; font-size: 0.85rem;">
                            {statusInfo.text}
                        </span>
                    </div>

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

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Service</small>
                                    <span class="fw-medium">{booking.driver_service?.title}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Date</small>
                                    <span>{new Date(booking.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Participants</small>
                                    <span>{booking.pax} pax</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Total</small>
                                    <span class="fw-bold text-primary">{formatRp(Number(booking.total_amount))}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Down Payment</small>
                                    <span>{formatRp(Number(booking.dp_amount))}</span>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block">Remaining Cash</small>
                                    <span>{formatRp(Number(booking.remaining_cash))}</span>
                                </div>
                                {#if booking.voucher_code}
                                    <div class="col-md-6">
                                        <small class="text-muted d-block">Promo</small>
                                        <span class="badge bg-success-subtle text-success">{booking.voucher_code}</span>
                                    </div>
                                {/if}
                            </div>
                        </div>
                    </div>

                    {#if assigned_driver}
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-3">
                                    <i class="fas fa-user-check me-2 text-primary"></i>Your Driver
                                </h5>
                                <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-3">
                                    <div class="flex-shrink-0">
                                        {#if assigned_driver.image}
                                            <img
                                                src={assigned_driver.image}
                                                alt={assigned_driver.name}
                                                style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover;"
                                            />
                                        {:else}
                                            <div
                                                class="d-flex align-items-center justify-content-center text-white fw-bold"
                                                style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, #dc2626, #7f1d1d); font-size: 1.5rem;"
                                            >
                                                {initial(assigned_driver.name)}
                                            </div>
                                        {/if}
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6">{assigned_driver.name}</div>
                                        <small class="text-muted d-block"><i class="fas fa-phone me-1"></i>{assigned_driver.phone ?? '—'}</small>
                                        <small class="text-muted d-block"><i class="fas fa-envelope me-1"></i>{assigned_driver.email}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {:else if booking.status === 'confirmed'}
                        <div class="card border-0 shadow-sm rounded-3 mb-4">
                            <div class="card-body p-4 text-center text-muted py-4">
                                <i class="fas fa-user-clock mb-2" style="font-size: 1.5rem;"></i>
                                <div>Driver akan di-assign setelah pembayaran terkonfirmasi.</div>
                            </div>
                        </div>
                    {/if}

                    <div class="text-center mt-4">
                        <Link href="/" class="btn btn-primary px-5">Back to Home</Link>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>