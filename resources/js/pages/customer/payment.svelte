<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { formatRupiah } from '@/lib/utils';

    interface OrderData {
        booking_code: string;
        customer_name: string;
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

    const PAYMENT_METHODS = [
        { id: 'visa', label: 'Visa', icon: 'fab fa-cc-visa', color: '#1a1f71' },
        { id: 'mastercard', label: 'Mastercard', icon: 'fab fa-cc-mastercard', color: '#eb001b' },
        { id: 'paypal', label: 'PayPal', icon: 'fab fa-cc-paypal', color: '#003087' },
        { id: 'apple_pay', label: 'Apple Pay', icon: 'fab fa-apple-pay', color: '#000' },
        { id: 'google_pay', label: 'Google Pay', icon: 'fab fa-google-pay', color: '#4285f4' },
    ];

    let selectedMethod = $state('');
    let agreedToTerms = $state(false);
    let isProcessing = $state(false);

    function handlePayment(e: Event) {
        e.preventDefault();
        if (!selectedMethod || !agreedToTerms || isProcessing) return;
        isProcessing = true;

        // Dummy payment — POST to process endpoint
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/booking/payment', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ booking_code, payment_method: selectedMethod }),
        }).then(() => {
            router.visit('/booking/payment-success?code=' + booking_code);
        }).catch(() => {
            router.visit('/booking/payment-success?code=' + booking_code);
        });
    }

    let totalPrice = $derived(order ? parseFloat(order.price) : 0);
</script>

<AppHead title="Payment - Siwride" />
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
            <h2 class="page-header__title bw-split-in-right">Payment</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/booking">Book a Transfer</a></li>
                <li><span>Payment</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 60px 0 100px; background: #f4f7f9;">
        <div class="container">
            <div class="payment-layout">

                <!-- Left: Payment Form -->
                <div class="payment-main">
                    <div class="payment-card">
                        <div class="payment-card__header">
                            <h4>Choose Payment Method</h4>
                            <p>Select how you'd like to pay for your transfer</p>
                        </div>
                        <div class="payment-card__body">
                            <form onsubmit={handlePayment}>
                                <div class="payment-methods">
                                    {#each PAYMENT_METHODS as method}
                                        <label class="method-card {selectedMethod === method.id ? 'method-card--selected' : ''}">
                                            <input type="radio" name="payment_method" value={method.id} bind:group={selectedMethod} class="method-radio" />
                                            <div class="method-icon" style="color: {method.color};">
                                                <i class="{method.icon}"></i>
                                            </div>
                                            <span class="method-label">{method.label}</span>
                                            <div class="method-check {selectedMethod === method.id ? 'visible' : ''}">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    {/each}
                                </div>

                                <div class="terms-box">
                                    <label class="terms-label">
                                        <input type="checkbox" bind:checked={agreedToTerms} class="terms-checkbox" />
                                        <span>
                                            I agree to the <a href="/terms" target="_blank">Terms &amp; Conditions</a>
                                        </span>
                                    </label>
                                    <p class="terms-provider">
                                        The service is provided by IT Travel LP (Suite 4054 Mitchell House, 5 Mitchell Street, Edinburgh, Scotland, EH6 7BD).
                                    </p>
                                </div>

                                <button
                                    type="submit"
                                    class="pay-btn"
                                    disabled={!selectedMethod || !agreedToTerms || isProcessing}
                                >
                                    {#if isProcessing}
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    {:else}
                                        <i class="fas fa-lock"></i>
                                        Pay {formatRupiah(totalPrice)} Securely
                                    {/if}
                                </button>

                                <div class="security-badges">
                                    <span><i class="fas fa-shield-alt"></i> SSL Secured</span>
                                    <span><i class="fas fa-lock"></i> 256-bit Encryption</span>
                                    <span><i class="fas fa-check-circle"></i> Safe Checkout</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="payment-sidebar">
                    <div class="summary-card">
                        <h5 class="summary-card__title">Order Summary</h5>

                        {#if order?.vehicle_category}
                            <div class="summary-vehicle">
                                <img src={order.vehicle_category.image_url} alt={order.vehicle_category.title} />
                                <div>
                                    <strong>{order.vehicle_category.title}</strong>
                                    <span>Vehicle</span>
                                </div>
                            </div>
                            <div class="summary-divider"></div>
                        {/if}

                        {#if order}
                            <div class="summary-row">
                                <span><i class="fas fa-map-marker-alt"></i> From</span>
                                <span>{order.pickup_address}</span>
                            </div>
                            <div class="summary-row">
                                <span><i class="fas fa-flag-checkered"></i> To</span>
                                <span>{order.dropoff_address}</span>
                            </div>
                            <div class="summary-row">
                                <span><i class="fas fa-calendar-alt"></i> Date</span>
                                <span>{order.date} · {order.time}</span>
                            </div>
                            <div class="summary-row">
                                <span><i class="fas fa-users"></i> Passengers</span>
                                <span>{order.passengers}</span>
                            </div>

                            {#if order.extras && order.extras.length > 0}
                                <div class="summary-divider"></div>
                                {#each order.extras as extra}
                                    <div class="summary-row">
                                        <span>{extra.label}</span>
                                        <span>{extra.price > 0 ? '+' + formatRupiah(extra.price) : 'Free'}</span>
                                    </div>
                                {/each}
                            {/if}
                        {/if}

                        <div class="summary-divider"></div>
                        <div class="summary-total-row">
                            <span>Total Amount</span>
                            <span class="total-price">{formatRupiah(totalPrice)}</span>
                        </div>

                        <div class="booking-ref">
                            <i class="fas fa-ticket-alt"></i>
                            Booking: <strong>{booking_code}</strong>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .payment-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) { .payment-layout { grid-template-columns: 1fr; } }

    .payment-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #eaeef2; box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .payment-card__header { padding: 24px 28px; border-bottom: 1px solid #f0f4f8; }
    .payment-card__header h4 { font-size: 22px; font-weight: 800; color: #1e293b; margin: 0; }
    .payment-card__header p { font-size: 14px; color: #64748b; margin: 4px 0 0; }
    .payment-card__body { padding: 28px; }

    /* Payment Methods */
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 14px;
        margin-bottom: 28px;
    }
    .method-card {
        display: flex; flex-direction: column; align-items: center; gap: 8px;
        padding: 20px 16px; border: 2px solid #e2e8f0; border-radius: 12px;
        cursor: pointer; transition: all 0.2s; background: #fff; position: relative;
    }
    .method-card:hover { border-color: #94a3b8; background: #f8fafc; }
    .method-card--selected { border-color: var(--travhub-base); background: rgba(229,32,41,0.03); }
    .method-radio { display: none; }
    .method-icon { font-size: 36px; line-height: 1; }
    .method-label { font-size: 13px; font-weight: 700; color: #334155; }
    .method-check {
        position: absolute; top: 8px; right: 8px;
        width: 22px; height: 22px; border-radius: 50%;
        background: #f1f5f9; color: #94a3b8;
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; transition: all 0.2s; opacity: 0;
    }
    .method-check.visible { background: var(--travhub-base); color: #fff; opacity: 1; }

    /* Terms */
    .terms-box {
        padding: 20px; background: #f8fafc; border-radius: 12px;
        border: 1px solid #e2e8f0; margin-bottom: 24px;
    }
    .terms-label {
        display: flex; align-items: flex-start; gap: 12px;
        cursor: pointer; font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 10px;
    }
    .terms-checkbox { width: 18px; height: 18px; accent-color: var(--travhub-base); cursor: pointer; flex-shrink: 0; margin-top: 2px; }
    .terms-label a { color: var(--travhub-base); text-decoration: underline; }
    .terms-provider { font-size: 12px; color: #94a3b8; margin: 0; padding-left: 30px; line-height: 1.6; }

    /* Pay Button */
    .pay-btn {
        width: 100%; padding: 16px;
        background: var(--travhub-base); color: #fff;
        border: none; border-radius: 12px;
        font-size: 18px; font-weight: 700; cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 10px;
        transition: all 0.3s; box-shadow: 0 8px 25px rgba(229,32,41,0.3);
        margin-bottom: 20px;
    }
    .pay-btn:hover:not(:disabled) { background: #111; transform: translateY(-2px); box-shadow: 0 12px 30px rgba(0,0,0,0.2); }
    .pay-btn:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; transform: none; }

    .security-badges {
        display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;
        font-size: 12px; color: #94a3b8;
    }
    .security-badges span { display: flex; align-items: center; gap: 5px; }
    .security-badges i { color: #10b981; }

    /* Sidebar */
    .payment-sidebar { position: sticky; top: 100px; }
    .summary-card {
        background: #fff; border-radius: 16px;
        border: 1px solid #eaeef2; box-shadow: 0 4px 20px rgba(0,0,0,0.06);
        padding: 24px;
    }
    .summary-card__title { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 20px; }
    .summary-vehicle { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .summary-vehicle img { width: 80px; height: 60px; object-fit: contain; background: #f8fafc; border-radius: 8px; padding: 4px; }
    .summary-vehicle div { display: flex; flex-direction: column; gap: 4px; }
    .summary-vehicle strong { font-size: 15px; font-weight: 700; color: #1e293b; }
    .summary-vehicle span { font-size: 12px; color: #94a3b8; }
    .summary-divider { height: 1px; background: #f0f4f8; margin: 14px 0; }
    .summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; padding: 5px 0; font-size: 13px; }
    .summary-row span:first-child { color: #64748b; display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .summary-row span:last-child { font-weight: 600; color: #1e293b; text-align: right; }
    .summary-total-row { display: flex; justify-content: space-between; align-items: center; }
    .summary-total-row span:first-child { font-size: 16px; font-weight: 700; color: #1e293b; }
    .total-price { font-size: 28px; font-weight: 800; color: var(--travhub-base); }
    .booking-ref {
        margin-top: 16px; padding: 12px 16px;
        background: #f8fafc; border-radius: 10px;
        font-size: 13px; color: #64748b;
        display: flex; align-items: center; gap: 8px;
    }
    .booking-ref i { color: var(--travhub-base); }
    .booking-ref strong { color: #1e293b; font-family: monospace; font-size: 14px; }
</style>
