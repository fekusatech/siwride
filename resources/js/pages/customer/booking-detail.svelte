<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link, useForm } from '@inertiajs/svelte';
    import { router } from '@inertiajs/svelte';
    import { formatTime12 } from '@/lib/pickupTime';
    import { formatRupiah } from '@/lib/utils';

    const { order } = $props();

    let isCancelling = $state(false);
    let cancellationMessage = $state<string | null>(null);
    let cancellationError = $state<string | null>(null);

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

    const pickupAddress = $derived(splitAddress(order.pickup_address));
    const dropoffAddress = $derived(splitAddress(order.dropoff_address));

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
</script>

<AppHead title={`Booking ${order.booking_code} - Siwride`} />
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
            <h2 class="page-header__title bw-split-in-right">
                Booking Details
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><Link href="/">Home</Link></li>
                <li><Link href="/customer/profile">Dashboard</Link></li>
                <li><span>{order.booking_code}</span></li>
            </ul>
        </div>
    </section>

    <!-- Details Section -->
    <section
        style="padding: 80px 0 100px; background-color: #f8fafc; min-height: 60vh;"
    >
        <div class="container">
            <div class="checkout-layout">
                <div class="main-content">
                    <!-- Elegant Ticket Card -->
                    <div class="ticket-card premium-shadow">
                        <div
                            class="ticket-header"
                            style="background-color: {statusInfo.bg}; border-bottom: 2px dashed #e2e8f0;"
                        >
                            <div
                                class="d-flex justify-content-between align-items-center"
                            >
                                <div>
                                    <h4
                                        style="margin: 0; font-weight: 800; color: #1e293b;"
                                    >
                                        {order.booking_code}
                                    </h4>
                                    <p
                                        style="margin: 0; font-size: 14px; color: #64748b;"
                                    >
                                        Booking Reference
                                    </p>
                                </div>
                                <div
                                    class="status-badge"
                                    style="color: {statusInfo.color}; border: 1px solid {statusInfo.color}; padding: 6px 16px; border-radius: 20px; font-weight: 700; background: rgba(255,255,255,0.5);"
                                >
                                    <i class="fas {statusInfo.icon} mr-1"></i>
                                    {statusInfo.text}
                                </div>
                            </div>
                        </div>

                        <div class="ticket-body">
                            <!-- Route Info -->
                            <div class="sidebar-section-title mt-0">Route</div>
                            <div class="sidebar-route mb-4 pb-4 border-bottom">
                                <div
                                    class="sidebar-route-point"
                                    style="align-items: flex-start;"
                                >
                                    <span
                                        class="sidebar-route-dot sidebar-route-dot--from"
                                        style="margin-top: 6px;"
                                    ></span>
                                    <div
                                        style="display: flex; flex-direction: column;"
                                    >
                                        <span class="sidebar-route-text"
                                            >{pickupAddress.short}</span
                                        >
                                        {#if pickupAddress.detail && pickupAddress.detail !== pickupAddress.short}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px;"
                                                >{pickupAddress.detail}</small
                                            >
                                        {/if}
                                        {#if order.pickup_notes}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px; color:var(--travhub-base) !important;"
                                                ><i class="fas fa-info-circle"
                                                ></i>
                                                {order.pickup_notes}</small
                                            >
                                        {/if}
                                    </div>
                                </div>
                                <div class="sidebar-route-line"></div>
                                <div
                                    class="sidebar-route-point"
                                    style="align-items: flex-start;"
                                >
                                    <span
                                        class="sidebar-route-dot sidebar-route-dot--to"
                                        style="margin-top: 6px;"
                                    ></span>
                                    <div
                                        style="display: flex; flex-direction: column;"
                                    >
                                        <span class="sidebar-route-text"
                                            >{dropoffAddress.short}</span
                                        >
                                        {#if dropoffAddress.detail && dropoffAddress.detail !== dropoffAddress.short}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px;"
                                                >{dropoffAddress.detail}</small
                                            >
                                        {/if}
                                        {#if order.dropoff_notes}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px; color:var(--travhub-base) !important;"
                                                ><i class="fas fa-info-circle"
                                                ></i>
                                                {order.dropoff_notes}</small
                                            >
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
                                            >{formatDate(order.date)}</span
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
                                            >{formatTime12(order.time)}</span
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
                                            >{order.passengers} Pax</span
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
                                        {order.customer_name || 'Guest'}
                                    </p>
                                    <p class="detail-text">
                                        <i
                                            class="fas fa-envelope text-muted mr-2"
                                        ></i>
                                        {order.customer_email ||
                                            'No email provided'}
                                    </p>
                                    {#if order.customer_phone}
                                        <p class="detail-text">
                                            <i
                                                class="fas fa-phone-alt text-muted mr-2"
                                            ></i>
                                            {order.customer_phone}
                                        </p>
                                    {/if}
                                </div>
                                <div class="col-md-6">
                                    <div class="sidebar-section-title">
                                        Additional Info
                                    </div>
                                    {#if order.notes}
                                        <div
                                            class="detail-text"
                                            style="background: #f1f5f9; padding: 12px; border-radius: 8px;"
                                        >
                                            <strong>Notes:</strong><br />
                                            {order.notes}
                                        </div>
                                    {/if}
                                    {#if order.flight_number}
                                        <div
                                            class="detail-text"
                                            style="background: #e0f2fe; padding: 12px; border-radius: 8px; margin-top: 10px;"
                                        >
                                            <strong
                                                ><i class="fas fa-plane-arrival"
                                                ></i> Flight:</strong
                                            >
                                            {order.flight_number}
                                        </div>
                                    {/if}
                                </div>
                            </div>

                            {#if order.driver}
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
                                                {order.driver.name}
                                            </h5>
                                            <a
                                                href="https://wa.me/{order.driver.phone.replace(
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
                    </div>
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

                        <div class="sidebar-section-title">Price Breakdown</div>
                        <div class="sidebar-row">
                            <span>Vehicle</span>
                            <span>
                                {#if order.price > 0 && order.extras}
                                    {formatRupiah(
                                        order.price -
                                            order.extras.reduce(
                                                (sum, e) =>
                                                    sum + (e.price || 0),
                                                0,
                                            ),
                                    )}
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

                        <div class="sidebar-divider"></div>
                        <div class="sidebar-total">
                            <span>Total</span>
                            <span>{formatRupiah(order.price)}</span>
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
    .premium-shadow {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0, 0, 0, 0.02);
        overflow: hidden;
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
