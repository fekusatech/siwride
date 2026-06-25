<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import { formatTime12 } from '@/lib/pickupTime';
    import { formatRupiah } from '@/lib/utils';

    const { order } = $props();

    let paymentResult = $state<'success' | 'failed' | null>(null);

    let isCancelling = $state(false);
    let isRetrying = $state(false);
    let cancellationMessage = $state<string | null>(null);
    let cancellationError = $state<string | null>(null);

    onMount(() => {
        const params = new URLSearchParams(window.location.search);
        const payment = params.get('payment');
        if (payment === 'success') {
            paymentResult = 'success';
            // Remove query param from URL without reload
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
        } else if (payment === 'failed') {
            paymentResult = 'failed';
            const cleanUrl = window.location.pathname;
            window.history.replaceState({}, '', cleanUrl);
        }
    });

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric',
        });
    };

    const formatTime = (timeString: string) => {
        // timeString format: "09:00" or "09:00:00"
        const parts = timeString.split(':');
        const hours = parseInt(parts[0], 10);
        const minutes = parts[1];
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        return `${displayHours.toString().padStart(2, '0')}:${minutes} ${ampm}`;
    };

    const calculatePaymentDeadline = (date: string, time: string) => {
        const [year, month, day] = date
            .split('-')
            .map((part) => parseInt(part, 10));
        const [hour, minute] = time
            .split(':')
            .map((part) => parseInt(part, 10));

        if (
            Number.isNaN(year) ||
            Number.isNaN(month) ||
            Number.isNaN(day) ||
            Number.isNaN(hour) ||
            Number.isNaN(minute)
        ) {
            return new Date(NaN);
        }

        const pickupDateTime = new Date(year, month - 1, day, hour, minute, 0);
        return new Date(pickupDateTime.getTime() - 10 * 60000); // 10 minutes before
    };

    const getFormattedDeadline = (date: string, time: string) => {
        const deadline = calculatePaymentDeadline(date, time);

        if (Number.isNaN(deadline.getTime())) {
            return 'Unknown';
        }

        const hours = deadline.getHours().toString().padStart(2, '0');
        const minutes = deadline.getMinutes().toString().padStart(2, '0');

        return formatTime12(`${hours}:${minutes}`);
    };

    const formatStatus = (status: string) => {
        switch (status) {
            case 'pending':
                return {
                    text: 'Pending',
                    bg: '#fff3cd',
                    color: '#856404',
                    icon: 'fa-clock',
                };
            case 'confirmed':
                return {
                    text: 'Confirmed',
                    bg: '#d1ecf1',
                    color: '#0c5460',
                    icon: 'fa-check-circle',
                };
            case 'completed':
                return {
                    text: 'Completed',
                    bg: '#d4edda',
                    color: '#155724',
                    icon: 'fa-flag-checkered',
                };
            case 'cancelled':
                return {
                    text: 'Cancelled',
                    bg: '#f8d7da',
                    color: '#721c24',
                    icon: 'fa-times-circle',
                };
            case 'paid':
                return {
                    text: 'Paid',
                    bg: '#d4edda',
                    color: '#155724',
                    icon: 'fa-check-circle',
                };
            default:
                return {
                    text: status,
                    bg: '#e2e3e5',
                    color: '#383d41',
                    icon: 'fa-info-circle',
                };
        }
    };

    const statusInfo = $derived(formatStatus(order.status));

    const linkedOrder = $derived(order.linked_order ?? order.linkedOrder ?? null);
    const ordersToDisplay = $derived(
        linkedOrder ? [order, linkedOrder] : [order]
    );
    const isRoundTrip = $derived(ordersToDisplay.length > 1);

    const grandTotal = $derived(
        ordersToDisplay.reduce((sum, o) => sum + Number(o.price || 0), 0)
    );

    // Check if order is pending and unpaid
    const isPendingAndUnpaid = $derived(
        order.status === 'pending' && order.payment_status !== 'paid',
    );

    // Check if order can be cancelled
    const canCancelOrder = $derived(isPendingAndUnpaid);
    const hasPaymentReferenceUrl = $derived(
        order.status === 'pending' &&
            order.payment_status !== 'paid' &&
            typeof order.payment_reference === 'string' &&
            order.payment_reference.startsWith('http'),
    );
    const showPendingPaymentSection = $derived(
        order.status === 'pending' && order.payment_status !== 'paid',
    );

    // Get payment deadline string
    const paymentDeadlineString = $derived(
        getFormattedDeadline(order.date, order.time),
    );

    const splitAddress = (address: string) => {
        if (!address) return { short: '—', detail: '' };
        const parts = address.split(',');
        if (parts.length > 1) {
            return {
                short: parts[0].trim(),
                detail: address,
            };
        }
        return { short: address, detail: '' };
    };

    const handleCancelOrder = async () => {
        if (!canCancelOrder) return;

        isCancelling = true;
        cancellationError = null;
        cancellationMessage = null;

        try {
            const response = await fetch(
                `/booking/${order.booking_code}/cancel`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                },
            );

            const data = await response.json();

            if (response.ok) {
                cancellationMessage =
                    data.message || 'Order has been cancelled successfully.';
                // Reload the page after a short delay to show the message
                setTimeout(() => {
                    router.reload();
                }, 1500);
            } else {
                cancellationError = data.message || 'Failed to cancel order.';
            }
        } catch (error) {
            cancellationError = 'An error occurred while cancelling the order.';
            console.error('Cancellation error:', error);
        } finally {
            isCancelling = false;
        }
    };

    const handleRetryPayment = async () => {
        isRetrying = true;
        cancellationError = null;

        try {
            const response = await fetch(
                `/booking/${order.booking_code}/retry-payment`,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                    },
                },
            );

            const data = await response.json();

            if (response.ok && data.payment_url) {
                window.location.href = data.payment_url;
            } else {
                cancellationError = data.message || 'Failed to create payment. Please check Xendit API key in admin settings.';
                isRetrying = false;
            }
        } catch (error) {
            cancellationError = 'An error occurred while retrying payment.';
            console.error('Retry payment error:', error);
            isRetrying = false;
        }
    };
    const displayBookingCode = $derived(
        isRoundTrip 
            ? `${ordersToDisplay[0].booking_code} & ${ordersToDisplay[1].booking_code}`
            : order.booking_code
    );

</script>

<AppHead title={`Booking ${displayBookingCode} - Siwride`} />
<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- Details Section -->
    <section
        style="padding: 40px 0 100px; background-color: #f8fafc; min-height: 60vh;"
    >
        <div class="container">
            <div class="simple-page-header" style="margin-bottom: 30px;">
                <div style="font-size: 13px; color: #64748b; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                    <Link href="/" style="color: #64748b; text-decoration: none;">Home</Link>
                    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                    <Link href="/customer/profile" style="color: #64748b; text-decoration: none;">Dashboard</Link>
                    <i class="fas fa-chevron-right" style="font-size: 10px;"></i>
                    <span style="color: var(--travhub-base); font-weight: 600;">{displayBookingCode}</span>
                </div>
                <h2 style="font-size: 28px; font-weight: 800; color: #1e293b; margin: 0;">Booking Details</h2>
            </div>

            <div class="checkout-layout">
                <div class="main-content">
                    <!-- Payment Result Banner -->
                    {#if paymentResult === 'success'}
                        <div class="payment-success-banner">
                            <div class="payment-banner-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="payment-banner-text">
                                <strong>Payment Successful!</strong>
                                <p>Your booking has been confirmed. We'll send a confirmation to your email shortly.</p>
                            </div>
                        </div>
                    {:else if paymentResult === 'failed'}
                        <div class="payment-failed-banner">
                            <div class="payment-banner-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="payment-banner-text">
                                <strong>Payment Failed</strong>
                                <p>Your payment was not completed. You can retry payment below.</p>
                            </div>
                        </div>
                    {/if}

                    <!-- Ticket Cards -->
                    {#each ordersToDisplay as tripOrder, index}
                        {@const tripPickup = splitAddress(tripOrder.pickup_address)}
                        {@const tripDropoff = splitAddress(tripOrder.dropoff_address)}
                        <div class="ticket-card premium-shadow" style={index > 0 ? 'margin-top: 24px;' : ''}>

                        <div
                            class="ticket-header"
                            style="background-color: {formatStatus(tripOrder.status).bg}; border-bottom: 2px dashed #e2e8f0;"
                        >
                            <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 10px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    {#if isRoundTrip}
                                        <span class="trip-leg-badge {index === 0 ? 'trip-leg-badge--outbound' : 'trip-leg-badge--return'}">
                                            <i class="fas {index === 0 ? 'fa-plane-departure' : 'fa-plane-arrival'} mr-1"></i>
                                            {index === 0 ? 'Outbound Trip' : 'Return Trip'}
                                        </span>
                                    {/if}
                                    <h4 style="margin: 0; font-weight: 800; color: #1e293b;">
                                        {tripOrder.booking_code}
                                    </h4>
                                    <p style="margin: 0; font-size: 14px; color: #64748b;">
                                        Booking Reference
                                    </p>
                                </div>
                                <div
                                    class="status-badge"
                                    style="color: {formatStatus(tripOrder.status).color}; border: 1px solid {formatStatus(tripOrder.status).color}; padding: 6px 16px; border-radius: 20px; font-weight: 700; background: rgba(255,255,255,0.5);"
                                >
                                    <i class="fas {formatStatus(tripOrder.status).icon} mr-1"></i>
                                    {formatStatus(tripOrder.status).text}
                                </div>
                            </div>
                        </div>

                        <div class="ticket-body">
                            <!-- Route Info -->
                            <div class="sidebar-section-title mt-0">Route</div>
                            <div class="sidebar-route mb-4 pb-4 border-bottom">
                                <div class="sidebar-route-point" style="align-items: flex-start;">
                                    <span class="sidebar-route-dot sidebar-route-dot--from" style="margin-top: 6px;"></span>
                                    <div style="display: flex; flex-direction: column;">
                                        <span class="sidebar-route-text">{tripPickup.short}</span>
                                        {#if tripPickup.detail && tripPickup.detail !== tripPickup.short}
                                            <small class="route-full-address text-muted" style="margin-top: 2px;">{tripPickup.detail}</small>
                                        {/if}
                                        {#if tripOrder.pickup_notes}
                                            <small class="route-full-address text-muted" style="margin-top: 2px; color:var(--travhub-base) !important;">
                                                <i class="fas fa-info-circle"></i> {tripOrder.pickup_notes}
                                            </small>
                                        {/if}
                                    </div>
                                </div>
                                <div class="sidebar-route-line"></div>
                                <div class="sidebar-route-point" style="align-items: flex-start;">
                                    <span class="sidebar-route-dot sidebar-route-dot--to" style="margin-top: 6px;"></span>
                                    <div style="display: flex; flex-direction: column;">
                                        <span class="sidebar-route-text">{tripDropoff.short}</span>
                                        {#if tripDropoff.detail && tripDropoff.detail !== tripDropoff.short}
                                            <small class="route-full-address text-muted" style="margin-top: 2px;">{tripDropoff.detail}</small>
                                        {/if}
                                        {#if tripOrder.dropoff_notes}
                                            <small class="route-full-address text-muted" style="margin-top: 2px; color:var(--travhub-base) !important;">
                                                <i class="fas fa-info-circle"></i> {tripOrder.dropoff_notes}
                                            </small>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <!-- Trip details grid -->
                            <div class="sidebar-section-title">
                                Trip Details
                            </div>
                            <div
                                class="sidebar-info-grid mb-4 pb-4 border-bottom"
                            >
                                <div class="sidebar-info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Date</span
                                        >
                                        <span class="sidebar-info-value"
                                            >{formatDate(tripOrder.date)}</span
                                        >
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Pickup Time</span
                                        >
                                        <span class="sidebar-info-value"
                                            >{formatTime12(tripOrder.time)}</span
                                        >
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-users"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Passengers</span
                                        >
                                        <span class="sidebar-info-value"
                                            >{tripOrder.passengers} Pax</span
                                        >
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-car"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Transfer Type</span
                                        >
                                        <span class="sidebar-info-value"
                                            >Private</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- Customer & Notes -->
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <div class="sidebar-section-title">
                                        Passenger Details
                                    </div>
                                    <p class="detail-text">
                                        <i class="fas fa-user text-muted mr-2"
                                        ></i>
                                        {tripOrder.customer_name || 'Guest'}
                                    </p>
                                    <p class="detail-text">
                                        <i
                                            class="fas fa-envelope text-muted mr-2"
                                        ></i>
                                        {tripOrder.customer_email ||
                                            'No email provided'}
                                    </p>
                                    {#if tripOrder.customer_phone}
                                        <p class="detail-text">
                                            <i
                                                class="fas fa-phone-alt text-muted mr-2"
                                            ></i>
                                            {tripOrder.customer_phone}
                                        </p>
                                    {/if}
                                </div>
                                <div class="col-md-6">
                                    <div class="sidebar-section-title">
                                        Additional Info
                                    </div>
                                    {#if tripOrder.notes}
                                        <div
                                            class="detail-text"
                                            style="background: #f1f5f9; padding: 12px; border-radius: 8px;"
                                        >
                                            <strong>Notes:</strong><br />
                                            {tripOrder.notes}
                                        </div>
                                    {/if}
                                    {#if tripOrder.flight_number}
                                        <div
                                            class="detail-text"
                                            style="background: #e0f2fe; padding: 12px; border-radius: 8px; margin-top: 10px;"
                                        >
                                            <strong
                                                ><i class="fas fa-plane-arrival"
                                                ></i> Flight:</strong
                                            >
                                            {tripOrder.flight_number}
                                        </div>
                                    {/if}
                                </div>
                            </div>

                            {#if tripOrder.driver}
                                <!-- Driver Info if assigned -->
                                <div class="driver-info mt-4 pt-4 border-top">
                                    <h6 class="section-title mb-3">
                                        Assigned Driver
                                    </h6>
                                    <div
                                        class="d-flex align-items-center p-3 rounded"
                                        style="background: #f8fafc; border: 1px solid #e2e8f0;"
                                    >
                                        <div
                                            class="driver-avatar mr-3"
                                            style="width: 50px; height: 50px; background: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;"
                                        >
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div>
                                            <h5
                                                class="mb-1"
                                                style="font-size: 16px; font-weight: 700;"
                                            >
                                                {tripOrder.driver.name}
                                            </h5>
                                            <a
                                                href="https://wa.me/{tripOrder.driver.phone.replace(
                                                    /[^0-9]/g,
                                                    '',
                                                )}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="text-success"
                                                style="font-size: 14px; font-weight: 600;"
                                            >
                                                <i class="fab fa-whatsapp mr-1"
                                                ></i> Contact Driver
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            {/if}
                        </div>

                        
                            {#if index === ordersToDisplay.length - 1}
                                <!-- Inserted warnings back here -->
                                <!-- Automatic Cancellation Warning (for pending unpaid orders) -->
                        {#if isPendingAndUnpaid}
                            <div
                                class="warning-banner"
                                style="padding: 16px 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 8px; margin-bottom: 16px;"
                            >
                                <div
                                    style="display: flex; gap: 10px; align-items: flex-start;"
                                >
                                    <i
                                        class="fas fa-exclamation-triangle"
                                        style="color: #856404; margin-top: 2px; flex-shrink: 0;"
                                    ></i>
                                    <div style="flex: 1;">
                                        <strong style="color: #856404;"
                                            >Payment Deadline</strong
                                        >
                                        <p
                                            style="margin: 6px 0 0; color: #856404; font-size: 14px;"
                                        >
                                            This order will be automatically
                                            cancelled if payment is not
                                            completed before {paymentDeadlineString}.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        {#if cancellationMessage}
                            <div
                                style="padding: 16px 20px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 8px; margin-bottom: 16px;"
                            >
                                <div
                                    style="display: flex; gap: 10px; align-items: center;"
                                >
                                    <i
                                        class="fas fa-check-circle"
                                        style="color: #155724;"
                                    ></i>
                                    <p
                                        style="margin: 0; color: #155724; font-weight: 500;"
                                    >
                                        {cancellationMessage}
                                    </p>
                                </div>
                            </div>
                        {/if}

                        {#if cancellationError}
                            <div
                                style="padding: 16px 20px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 8px; margin-bottom: 16px;"
                            >
                                <div
                                    style="display: flex; gap: 10px; align-items: center;"
                                >
                                    <i
                                        class="fas fa-exclamation-circle"
                                        style="color: #721c24;"
                                    ></i>
                                    <p
                                        style="margin: 0; color: #721c24; font-weight: 500;"
                                    >
                                        {cancellationError}
                                    </p>
                                </div>
                            </div>
                        {/if}

                        {#if showPendingPaymentSection}
                            <div
                                class="ticket-payment-section text-center"
                                style="padding: 20px 40px; background: #fff3cd; border-top: 1px solid #ffeeba;"
                            >
                                <h5
                                    style="color: #856404; margin-bottom: 15px; font-weight: 700;"
                                >
                                    Waiting for Payment
                                </h5>
                                <div
                                    style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;"
                                >
                                    {#if hasPaymentReferenceUrl}
                                        <a
                                            href={order.payment_reference}
                                            target="_blank"
                                            class="btn-pay-now"
                                            style="background:var(--travhub-base); color:white; padding:10px 20px; border-radius:8px; text-decoration:none; font-weight:700;"
                                        >
                                            Pay Now / Change Method in Xendit <i
                                                class="fas fa-external-link-alt ml-1"
                                            ></i>
                                        </a>
                                    {:else}
                                        <button
                                            onclick={handleRetryPayment}
                                            disabled={isRetrying}
                                            style="background:var(--travhub-base); color:white; padding:10px 20px; border-radius:8px; border:none; font-weight:700; cursor:{isRetrying ? 'not-allowed' : 'pointer'}; opacity:{isRetrying ? 0.7 : 1};"
                                        >
                                            {#if isRetrying}
                                                <i class="fas fa-spinner fa-spin mr-1"></i>Processing...
                                            {:else}
                                                <i class="fas fa-redo mr-1"></i>Retry Payment
                                            {/if}
                                        </button>
                                    {/if}
                                    {#if canCancelOrder}
                                        <button
                                            onclick={handleCancelOrder}
                                            disabled={isCancelling}
                                            class="btn-cancel-order"
                                            style="background:#dc3545; color:white; padding:10px 20px; border-radius:8px; border:none; font-weight:700; cursor:{isCancelling
                                                ? 'not-allowed'
                                                : 'pointer'}; opacity:{isCancelling
                                                ? 0.7
                                                : 1};"
                                        >
                                            {#if isCancelling}
                                                <i
                                                    class="fas fa-spinner fa-spin mr-1"
                                                ></i>Cancelling...
                                            {:else}
                                                <i class="fas fa-times mr-1"
                                                ></i>Cancel Order
                                            {/if}
                                        </button>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                        <div
                            class="ticket-footer text-center"
                            style="border-top: 1px dashed #e2e8f0;"
                        >
                            <Link href="/customer/profile" class="travhub-btn"
                                ><span>Back to Dashboard</span></Link
                            >
                        </div>
                            {/if}
                        </div>
                    {/each}
                    </div>

                <!-- Sidebar for Order Summary -->
                <div class="checkout-sidebar">
                    <div class="sidebar-card">
                        {#if order.vehicle_category}
                            <div class="sidebar-vehicle mb-3">
                                <img
                                    src={order.vehicle_category.image_url}
                                    alt={order.vehicle_category.title}
                                />
                                <div class="sidebar-vehicle-info">
                                    <h5>{order.vehicle_category.title}</h5>
                                    <span
                                        >{order.vehicle_category
                                            .passenger_capacity ?? '—'} pax · {order
                                            .vehicle_category
                                            .luggage_capacity ?? '—'} bags</span
                                    >
                                </div>
                            </div>
                        {/if}

                        <h4
                            class="sidebar-title mt-3 mb-3"
                            style="font-size:18px; font-weight:800;"
                        >
                            Order Summary
                        </h4>

                        {#if isRoundTrip}
                            <!-- Round-trip: show per-leg breakdown -->
                            {#each ordersToDisplay as legOrder, legIndex}
                                <div class="sidebar-section-title" style="margin-top: {legIndex > 0 ? '14px' : '0'}">
                                    <i class="fas {legIndex === 0 ? 'fa-plane-departure' : 'fa-plane-arrival'} mr-1" style="color: {legIndex === 0 ? 'var(--travhub-base)' : '#10b981'};"></i>
                                    {legIndex === 0 ? 'Outbound' : 'Return'}
                                </div>
                                <div class="sidebar-row">
                                    <span>Vehicle</span>
                                    <span>
                                        {#if legOrder.price > 0 && legOrder.extras && legOrder.extras.length > 0}
                                            {formatRupiah(legOrder.price - legOrder.extras.reduce((s, e) => s + (e.price || 0), 0))}
                                        {:else}
                                            {formatRupiah(legOrder.price)}
                                        {/if}
                                    </span>
                                </div>
                                {#if legOrder.extras && legOrder.extras.length > 0}
                                    {#each legOrder.extras as extra}
                                        <div class="sidebar-row">
                                            <span>{extra.label}</span>
                                            <span>+{formatRupiah(extra.price)}</span>
                                        </div>
                                    {/each}
                                {/if}
                                <div class="sidebar-row" style="font-weight: 700; color: #334155;">
                                    <span>Subtotal</span>
                                    <span>{formatRupiah(Number(legOrder.price))}</span>
                                </div>
                            {/each}
                        {:else}
                            <!-- Single trip breakdown -->
                            <div class="sidebar-section-title">Price Breakdown</div>
                            <div class="sidebar-row">
                                <span>Vehicle</span>
                                <span>
                                    {#if order.price > 0 && order.extras && order.extras.length > 0}
                                        {formatRupiah(order.price - order.extras.reduce((s, e) => s + (e.price || 0), 0))}
                                    {:else}
                                        {formatRupiah(order.price)}
                                    {/if}
                                </span>
                            </div>
                            {#if order.extras && order.extras.length > 0}
                                {#each order.extras as extra}
                                    <div class="sidebar-row">
                                        <span>{extra.label}</span>
                                        <span>+{formatRupiah(extra.price)}</span>
                                    </div>
                                {/each}
                            {/if}
                        {/if}

                        <div class="sidebar-divider"></div>
                        <div class="sidebar-total">
                            <span>{isRoundTrip ? 'Grand Total (2 legs)' : 'Total'}</span>
                            <span>{formatRupiah(grandTotal)}</span>
                        </div>

                        {#if order.payment_method}
                            <div class="sidebar-note mt-3">
                                <i
                                    class="fas fa-money-check-alt"
                                    style="color:var(--travhub-base);"
                                ></i>
                                Payment Method:
                                <strong>{order.payment_method}</strong>
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .payment-success-banner,
    .payment-failed-banner {
        display: flex;
        align-items: flex-start;
        gap: 16px;
        padding: 20px 24px;
        border-radius: 16px;
        margin-bottom: 24px;
        animation: slideDown 0.4s ease;
    }
    .payment-success-banner {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1.5px solid #6ee7b7;
    }
    .payment-failed-banner {
        background: linear-gradient(135deg, #fef2f2, #fecaca);
        border: 1.5px solid #fca5a5;
    }
    .payment-banner-icon {
        font-size: 32px;
        flex-shrink: 0;
        line-height: 1;
    }
    .payment-success-banner .payment-banner-icon { color: #059669; }
    .payment-failed-banner .payment-banner-icon { color: #dc2626; }
    .payment-banner-text strong {
        display: block;
        font-size: 17px;
        font-weight: 800;
        margin-bottom: 4px;
    }
    .payment-success-banner .payment-banner-text strong { color: #065f46; }
    .payment-failed-banner .payment-banner-text strong { color: #991b1b; }
    .payment-banner-text p {
        margin: 0;
        font-size: 14px;
    }
    .payment-success-banner .payment-banner-text p { color: #047857; }
    .payment-failed-banner .payment-banner-text p { color: #b91c1c; }
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .premium-shadow {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.02);
        overflow: hidden;
    }

    .trip-leg-badge {
        display: inline-flex;
        align-items: center;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 3px 10px;
        border-radius: 50px;
        width: fit-content;
    }
    .trip-leg-badge--outbound {
        background: rgba(229, 32, 41, 0.1);
        color: var(--travhub-base);
    }
    .trip-leg-badge--return {
        background: rgba(16, 185, 129, 0.1);
        color: #065f46;
    }

    .ticket-header {
        padding: 30px 40px;
    }

    .ticket-body {
        padding: 40px;
        background: #fff;
    }

    .ticket-footer {
        padding: 20px 40px 30px;
        background: #fff;
    }

    /* Checkout Layout Styles */
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
    }

    .checkout-sidebar {
        position: sticky;
        top: 100px;
    }
    .sidebar-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        padding: 24px;
    }
    .sidebar-vehicle {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .sidebar-vehicle img {
        width: 72px;
        height: 54px;
        object-fit: contain;
        background: #f8fafc;
        border-radius: 8px;
        padding: 4px;
        flex-shrink: 0;
    }
    .sidebar-vehicle-info h5 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 3px;
    }
    .sidebar-vehicle-info span {
        font-size: 12px;
        color: #64748b;
    }
    .sidebar-divider {
        height: 1px;
        background: #f0f4f8;
        margin: 14px 0;
    }
    .sidebar-section-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #94a3b8;
        margin-bottom: 10px;
    }

    .sidebar-route {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .sidebar-route-point {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .sidebar-route-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .sidebar-route-dot--from {
        background: var(--travhub-base);
    }
    .sidebar-route-dot--to {
        background: #10b981;
    }
    .sidebar-route-text {
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.4;
    }
    .sidebar-route-line {
        width: 2px;
        height: 14px;
        margin-left: 4px;
        background: repeating-linear-gradient(
            to bottom,
            #cbd5e1 0,
            #cbd5e1 3px,
            transparent 3px,
            transparent 6px
        );
    }
    .route-full-address {
        font-size: 11px;
    }

    .sidebar-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .sidebar-info-item {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        background: #f8fafc;
        border-radius: 8px;
        padding: 8px 10px;
    }
    .sidebar-info-item i {
        font-size: 12px;
        color: var(--travhub-base);
        margin-top: 2px;
        flex-shrink: 0;
    }
    .sidebar-info-item div {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }
    .sidebar-info-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #94a3b8;
    }
    .sidebar-info-value {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .sidebar-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #475569;
        padding: 4px 0;
    }
    .sidebar-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
    }
    .sidebar-note {
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    @media (max-width: 768px) {
        .ticket-header,
        .ticket-body,
        .ticket-footer {
            padding: 25px 20px;
        }
    }
</style>
