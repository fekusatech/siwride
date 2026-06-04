<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { formatRupiah } from '@/lib/utils';

    interface OrderData {
        booking_code: string;
        customer_name: string;
        email: string;
        pickup_address: string;
        dropoff_address: string;
        date: string;
        time: string;
        passengers: number;
        extras: { label: string; price: number }[];
        price: string;
        vehicle_category: { title: string; image_url: string; base_price: string } | null;
    }

    let { booking_code, order } = $props<{ booking_code: string; order: OrderData | null }>();

    let totalPrice = $derived(order ? parseFloat(order.price) : 0);


</script>

<AppHead title="Booking Confirmed - Siwride" />
<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Booking Confirmed</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Booking Confirmed</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 80px 0 100px; background: #f4f7f9; min-height: 60vh;">
        <div class="container">
            <div class="success-layout">

                <!-- Success Header -->
                <div class="success-header">
                    <div class="success-icon {order?.payment_status === 'pending' ? 'bg-warning' : ''}">
                        <i class="fas {order?.payment_status === 'pending' ? 'fa-hourglass-half' : 'fa-check'}"></i>
                    </div>
                    <h2>{order?.payment_status === 'pending' ? 'Waiting for Payment' : 'Booking Confirmed'}!</h2>
                    
                    {#if order?.payment_status === 'pending'}
                        <p>Please complete your payment before the time expires so we can confirm your order.</p>
                        
                        {#if order?.payment_reference && order.payment_reference.startsWith('http')}
                            <div style="margin: 20px 0; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                                <a href={order.payment_reference} target="_blank" class="btn-book-again" style="text-decoration:none;">
                                    Open Payment Page / Change Method in Xendit <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>

                        {:else if order?.payment_reference}
                            <div class="payment-instruction-box">
                                <span class="code-label">Payment Method: {order.payment_method}</span>
                                <div class="va-box">
                                    <span class="va-number">{order.payment_reference}</span>
                                    <button class="btn-copy" aria-label="Copy VA" onclick={() => { navigator.clipboard.writeText(order.payment_reference); alert('VA Number copied!'); }}>
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                                {#if order?.payment_expiry}
                                    <span class="expiry-text"><i class="fas fa-clock"></i> Pay before: {order.payment_expiry}</span>
                                {/if}
                            </div>
                        {/if}
                    {:else}
                        <p>Your transfer has been booked successfully. We'll be in touch shortly.</p>
                    {/if}
                    
                    <div class="booking-code-badge" style="margin-top:20px;">
                        <span class="code-label">Booking Reference</span>
                        <span class="code-value">{booking_code}</span>
                    </div>
                </div>

                <!-- Details Card -->
                {#if order}
                <div class="success-card">
                    <div class="success-card__section">
                        <h6 class="section-title">Transfer Details</h6>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-map-marker-alt text-danger"></i> Pickup</span>
                            <span class="detail-value">{order.pickup_address}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-flag-checkered text-success"></i> Drop-off</span>
                            <span class="detail-value">{order.dropoff_address}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-calendar-alt"></i> Date & Time</span>
                            <span class="detail-value">{order.date} at {order.time}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label"><i class="fas fa-users"></i> Passengers</span>
                            <span class="detail-value">{order.passengers}</span>
                        </div>
                    </div>

                    {#if order.vehicle_category}
                    <div class="success-card__section">
                        <h6 class="section-title">Vehicle</h6>
                        <div class="vehicle-row">
                            <img src={order.vehicle_category.image_url} alt={order.vehicle_category.title} />
                            <span>{order.vehicle_category.title}</span>
                        </div>
                    </div>
                    {/if}

                    {#if order.extras && order.extras.length > 0}
                    <div class="success-card__section">
                        <h6 class="section-title">Additional Services</h6>
                        {#each order.extras as extra}
                            <div class="detail-row">
                                <span class="detail-label"><i class="fas fa-check-circle text-success"></i> {extra.label}</span>
                                <span class="detail-value">{extra.price > 0 ? '+' + formatRupiah(extra.price) : 'Free'}</span>
                            </div>
                        {/each}
                    </div>
                    {/if}

                    <div class="success-card__total">
                        <span>Total Paid</span>
                        <span class="total-amount">{formatRupiah(totalPrice)}</span>
                    </div>
                </div>
                {/if}

                <!-- What's Next -->
                <div class="whats-next">
                    <h5><i class="fas fa-info-circle"></i> What happens next?</h5>
                    <ul>
                        <li><i class="fas fa-check"></i> Our team will review and confirm your booking</li>
                        <li><i class="fas fa-check"></i> A confirmation email will be sent to {order?.email || 'your email'}</li>
                        <li><i class="fas fa-check"></i> A professional driver will be assigned to your trip</li>
                        <li><i class="fas fa-check"></i> You'll receive driver details before your transfer</li>
                    </ul>
                </div>

                <!-- Actions -->
                <div class="success-actions">
                    <Link href="/" class="btn-home">
                        <i class="fas fa-home"></i> Back to Home
                    </Link>
                    <Link href="/booking" class="btn-book-again">
                        <i class="fas fa-plus"></i> Book Another Transfer
                    </Link>
                </div>

            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .success-layout {
        max-width: 680px;
        margin: 0 auto;
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Success Header */
    .success-header {
        text-align: center;
        padding: 40px 20px 32px;
        background: #fff;
        border-radius: 20px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .success-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        color: #fff; font-size: 36px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
        box-shadow: 0 8px 25px rgba(16,185,129,0.3);
    }
    .success-header h2 { font-size: 28px; font-weight: 800; color: #1e293b; margin: 0 0 8px; }
    .success-header p { font-size: 16px; color: #64748b; margin: 0 0 24px; }
    .booking-code-badge {
        display: inline-flex; flex-direction: column; align-items: center; gap: 4px;
        background: #1e293b; color: #fff; padding: 14px 32px; border-radius: 12px;
    }
    .code-label { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
    .code-value { font-size: 24px; font-weight: 800; font-family: monospace; letter-spacing: 2px; }

    /* Details Card */
    .success-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #eaeef2; box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .success-card__section { padding: 20px 28px; border-bottom: 1px solid #f0f4f8; }
    .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin: 0 0 12px; }
    .detail-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 6px 0; }
    .detail-label { font-size: 14px; color: #64748b; display: flex; align-items: center; gap: 8px; white-space: nowrap; }
    .detail-value { font-size: 14px; font-weight: 600; color: #1e293b; text-align: right; }
    .vehicle-row { display: flex; align-items: center; gap: 14px; }
    .vehicle-row img { width: 80px; height: 55px; object-fit: contain; background: #f8fafc; border-radius: 8px; padding: 4px; }
    .vehicle-row span { font-size: 15px; font-weight: 700; color: #1e293b; }
    .success-card__total {
        padding: 20px 28px;
        display: flex; justify-content: space-between; align-items: center;
        background: #f8fafc;
    }
    .success-card__total span:first-child { font-size: 16px; font-weight: 700; color: #1e293b; }
    .total-amount { font-size: 28px; font-weight: 800; color: var(--travhub-base); }

    /* What's Next */
    .whats-next {
        background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 14px; padding: 24px 28px;
    }
    .whats-next h5 { font-size: 16px; font-weight: 700; color: #1d4ed8; margin: 0 0 14px; display: flex; align-items: center; gap: 8px; }
    .whats-next ul { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
    .whats-next li { font-size: 14px; color: #1e40af; display: flex; align-items: center; gap: 10px; }
    .whats-next li i { color: #10b981; font-size: 13px; }

    /* Actions */
    .success-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .btn-home, .btn-book-again {
        padding: 14px 28px; border-radius: 50px;
        font-size: 15px; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s; text-decoration: none;
    }
    .btn-home { background: #1e293b; color: #fff; }
    .btn-home:hover { background: #0f172a; transform: translateY(-2px); }
    .btn-book-again { background: var(--travhub-base); color: #fff; box-shadow: 0 6px 20px rgba(229,32,41,0.25); }
    .btn-book-again:hover { background: #c41820; transform: translateY(-2px); }

    /* Payment Instruction Box */
    .bg-warning { background: linear-gradient(135deg, #f59e0b, #d97706); box-shadow: 0 8px 25px rgba(245,158,11,0.3); }
    .payment-instruction-box {
        background: #fefce8; border: 1px solid #fef08a; padding: 20px; border-radius: 12px;
        margin: 20px auto; max-width: 400px; display: flex; flex-direction: column; gap: 8px;
    }
    .payment-ref-container { display: flex; align-items: center; justify-content: center; gap: 12px; }
    .btn-copy { background: none; border: none; color: #64748b; cursor: pointer; font-size: 18px; padding: 4px; }
    .btn-copy:hover { color: var(--travhub-base); }
    .expiry-text { font-size: 12px; color: #b45309; font-weight: 600; margin-top: 8px; }
    .btn-cancel {
        background-color: #f1f1f1;
        color: #333;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-cancel:hover {
        background-color: #e0e0e0;
    }
    .btn-submit {
        background-color: var(--travhub-base);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-submit:hover {
        background-color: var(--travhub-base-dark);
    }
    .change-payment-box {
        background: #fdfdfd;
        border: 1px solid #eee;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }
    .change-payment-box h4 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 16px;
        color: #333;
    }
</style>
