<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { Link } from '@inertiajs/svelte';

    const { order } = $props();

    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
    };

    const formatStatus = (status: string) => {
        switch(status) {
            case 'pending': return { text: 'Pending', bg: '#fff3cd', color: '#856404', icon: 'fa-clock' };
            case 'confirmed': return { text: 'Confirmed', bg: '#d1ecf1', color: '#0c5460', icon: 'fa-check-circle' };
            case 'completed': return { text: 'Completed', bg: '#d4edda', color: '#155724', icon: 'fa-flag-checkered' };
            case 'cancelled': return { text: 'Cancelled', bg: '#f8d7da', color: '#721c24', icon: 'fa-times-circle' };
            default: return { text: status, bg: '#e2e3e5', color: '#383d41', icon: 'fa-info-circle' };
        }
    };

    const statusInfo = formatStatus(order.status);
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
            <h2 class="page-header__title bw-split-in-right">Booking Details</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><Link href="/">Home</Link></li>
                <li><Link href="/customer/profile">Dashboard</Link></li>
                <li><span>{order.booking_code}</span></li>
            </ul>
        </div>
    </section>

    <!-- Details Section -->
    <section style="padding: 80px 0 100px; background-color: #f8fafc; min-height: 60vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Elegant Ticket Card -->
                    <div class="ticket-card premium-shadow">
                        <div class="ticket-header" style="background-color: {statusInfo.bg}; border-bottom: 2px dashed #e2e8f0;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 style="margin: 0; font-weight: 800; color: #1e293b;">{order.booking_code}</h4>
                                    <p style="margin: 0; font-size: 14px; color: #64748b;">Booking Reference</p>
                                </div>
                                <div class="status-badge" style="color: {statusInfo.color}; border: 1px solid {statusInfo.color}; padding: 6px 16px; border-radius: 20px; font-weight: 700; background: rgba(255,255,255,0.5);">
                                    <i class="fas {statusInfo.icon} mr-1"></i> {statusInfo.text}
                                </div>
                            </div>
                        </div>

                        <div class="ticket-body">
                            <!-- Route Info -->
                            <div class="route-container mb-4 pb-4 border-bottom">
                                <div class="route-point">
                                    <div class="icon-circle text-primary bg-primary-light"><i class="fas fa-map-marker-alt"></i></div>
                                    <div class="point-details">
                                        <span class="label">Pickup Location</span>
                                        <h5 class="location">{order.pickup_address}</h5>
                                    </div>
                                </div>
                                
                                <div class="route-line-wrapper">
                                    <div class="route-line"></div>
                                    <div class="route-distance"><i class="fas fa-car-side"></i></div>
                                </div>

                                <div class="route-point">
                                    <div class="icon-circle text-success bg-success-light"><i class="fas fa-flag-checkered"></i></div>
                                    <div class="point-details">
                                        <span class="label">Drop-off Location</span>
                                        <h5 class="location">{order.dropoff_address}</h5>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta Info Grid -->
                            <div class="row mb-4 pb-4 border-bottom">
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt text-muted"></i>
                                        <span class="meta-label">Date</span>
                                        <h6 class="meta-value">{formatDate(order.date)}</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="meta-item">
                                        <i class="fas fa-clock text-muted"></i>
                                        <span class="meta-label">Time</span>
                                        <h6 class="meta-value">{order.time.substring(0,5)}</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="meta-item">
                                        <i class="fas fa-users text-muted"></i>
                                        <span class="meta-label">Passengers</span>
                                        <h6 class="meta-value">{order.passengers} Pax</h6>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="meta-item">
                                        <i class="fas fa-money-bill-wave text-muted"></i>
                                        <span class="meta-label">Price</span>
                                        <h6 class="meta-value" style="color: var(--travhub-base);">
                                            {#if order.price > 0}
                                                Rp {order.price.toLocaleString('id-ID')}
                                            {:else}
                                                TBD
                                            {/if}
                                        </h6>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer & Notes -->
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h6 class="section-title">Passenger Details</h6>
                                    <p class="detail-text"><i class="fas fa-user text-muted mr-2"></i> {order.customer?.name || 'Guest'}</p>
                                    {#if order.customer?.phone}
                                        <p class="detail-text"><i class="fas fa-phone-alt text-muted mr-2"></i> {order.customer.phone}</p>
                                    {/if}
                                </div>
                                <div class="col-md-6">
                                    <h6 class="section-title">Additional Notes</h6>
                                    {#if order.notes}
                                        <p class="detail-text" style="background: #f1f5f9; padding: 12px; border-radius: 8px;">{order.notes}</p>
                                    {:else}
                                        <p class="detail-text text-muted">No additional notes provided.</p>
                                    {/if}
                                </div>
                            </div>

                            {#if order.driver}
                            <!-- Driver Info if assigned -->
                            <div class="driver-info mt-4 pt-4 border-top">
                                <h6 class="section-title mb-3">Assigned Driver</h6>
                                <div class="d-flex align-items-center p-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                    <div class="driver-avatar mr-3" style="width: 50px; height: 50px; background: #cbd5e1; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; color: white;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-1" style="font-size: 16px; font-weight: 700;">{order.driver.name}</h5>
                                        <a href="https://wa.me/{order.driver.phone.replace(/[^0-9]/g, '')}" target="_blank" rel="noopener noreferrer" class="text-success" style="font-size: 14px; font-weight: 600;">
                                            <i class="fab fa-whatsapp mr-1"></i> Contact Driver
                                        </a>
                                    </div>
                                </div>
                            </div>
                            {/if}

                        </div>
                        <div class="ticket-footer text-center">
                            <Link href="/customer/profile" class="travhub-btn"><span>Back to Dashboard</span></Link>
                        </div>
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
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.02);
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

    /* Route Styles */
    .route-point {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .icon-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }

    .bg-primary-light { background: #e0e7ff; }
    .bg-success-light { background: #dcfce7; }

    .point-details .label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .point-details .location {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        line-height: 1.4;
    }

    .route-line-wrapper {
        margin: 15px 0 15px 19px;
        position: relative;
        height: 40px;
    }

    .route-line {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 2px;
        background: repeating-linear-gradient(to bottom, #cbd5e1 0, #cbd5e1 4px, transparent 4px, transparent 8px);
    }

    .route-distance {
        position: absolute;
        left: 20px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 14px;
        color: #94a3b8;
        background: #fff;
        padding: 4px 10px;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
    }

    /* Meta Items */
    .meta-item {
        display: flex;
        flex-direction: column;
    }

    .meta-item i {
        font-size: 18px;
        margin-bottom: 8px;
    }

    .meta-label {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .meta-value {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .section-title {
        font-size: 14px;
        text-transform: uppercase;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }

    .detail-text {
        font-size: 15px;
        color: #334155;
        font-weight: 500;
        margin-bottom: 8px;
    }

    @media (max-width: 768px) {
        .ticket-header, .ticket-body, .ticket-footer {
            padding: 25px 20px;
        }
    }
</style>
