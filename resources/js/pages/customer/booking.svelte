<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';

    interface VehicleCategory {
        id: number;
        slug: string;
        title: string;
        description: string;
        capacity: string;
        passenger_capacity: number | null;
        luggage_capacity: number | null;
        advantages: string[] | null;
        base_price: string;
        image_url: string;
        vehicle_type: string;
    }

    let {
        prefill,
        vehicleCategories = [],
        allVehicleCategories = [],
    } = $props<{
        prefill: { pickup: string; dropoff: string; date: string; time: string; passengers: string };
        vehicleCategories: VehicleCategory[];
        allVehicleCategories: VehicleCategory[];
    }>();

    // Route form state
    let pickup = $state(prefill?.pickup || '');
    let dropoff = $state(prefill?.dropoff || '');
    let date = $state(prefill?.date || '');
    let time = $state(prefill?.time || '');
    let passengerCount = $state(parseInt(prefill?.passengers || '1'));
    let dateDisplay = $state('');

    // Filtered categories (reactive to passengerCount)
    let filteredCategories = $state<VehicleCategory[]>(vehicleCategories);

    $effect(() => {
        filteredCategories = allVehicleCategories.filter((cat) => {
            if (!cat.passenger_capacity) return true;
            return cat.passenger_capacity >= passengerCount;
        });
    });

    function formatDate(isoDate: string): string {
        if (!isoDate) return '';
        const d = new Date(isoDate + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
    }

    // Dummy route info
    const routeInfo = {
        distance: '61 km',
        travelTime: '~1 h 30 min',
        fromPrice: 35,
    };

    function handleRouteSearch(e: Event) {
        e.preventDefault();
        if (!pickup || !dropoff || !date) return;
        // Re-fetch page with new params to filter vehicles
        router.get('/booking', {
            pickup,
            dropoff,
            date,
            time,
            passengers: String(passengerCount),
        }, { preserveScroll: true, replace: true });
    }

    function selectVehicle(category: VehicleCategory) {
        router.get('/booking/checkout', {
            vehicle_category_id: String(category.id),
            pickup,
            dropoff,
            date,
            time,
            passengers: String(passengerCount),
        });
    }

    onMount(() => {
        document.body.classList.add('custom-cursor');

        if ((window as any).$) {
            const jQuery = (window as any).$;
            setTimeout(() => {
                let dateEl = jQuery('#booking_date');
                if (dateEl.length && dateEl.daterangepicker) {
                    dateEl.daterangepicker({
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        timePicker: false,
                        minDate: new Date(),
                        locale: { format: 'D MMM YYYY' },
                    });
                    dateEl.on('apply.daterangepicker', function (this: HTMLElement, ev: any, picker: any) {
                        jQuery(this).val(picker.startDate.format('D MMM YYYY'));
                        jQuery('#booking_date_iso').val(picker.startDate.format('YYYY-MM-DD'));
                        date = picker.startDate.format('YYYY-MM-DD');
                        dateDisplay = picker.startDate.format('D MMM YYYY');
                    });
                }
            }, 100);
        }
    });

    onDestroy(() => {
        document.body.classList.remove('custom-cursor');
        if ((window as any).$) {
            let dateEl = (window as any).$('#booking_date');
            if (dateEl.length && dateEl.data('daterangepicker')) {
                dateEl.data('daterangepicker').remove();
            }
        }
    });
</script>

<AppHead title="Book a Transfer - Siwride" />
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
            <h2 class="page-header__title bw-split-in-right">Book a Transfer</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Book a Transfer</span></li>
            </ul>
        </div>
    </section>

    <!-- Main Booking Section -->
    <section class="booking-main" style="padding: 60px 0 100px; background: #f4f7f9;">
        <div class="container">

            <!-- SECTION A: Route Form -->
            <div class="booking-card mb-4">
                <div class="booking-card__header">
                    <div class="step-badge">1</div>
                    <div>
                        <h4 class="booking-card__title">Your Transfer Details</h4>
                        <p class="booking-card__subtitle">Enter your route and travel information</p>
                    </div>
                </div>
                <div class="booking-card__body">
                    <form onsubmit={handleRouteSearch}>
                        <div class="route-form-grid">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-danger"></i>
                                    Pickup Location *
                                </label>
                                <LocationSearchInput
                                    id="booking_pickup"
                                    bind:value={pickup}
                                    placeholder="Hotel, airport, landmark..."
                                    variant="premium"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-flag-checkered text-success"></i>
                                    Drop-off Location *
                                </label>
                                <LocationSearchInput
                                    id="booking_dropoff"
                                    bind:value={dropoff}
                                    placeholder="Beach, temple, area..."
                                    variant="premium"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-calendar-alt" style="color: var(--travhub-base);"></i>
                                    Transfer Date *
                                </label>
                                <div class="input-icon-wrapper">
                                    <input
                                        id="booking_date"
                                        type="text"
                                        class="premium-input"
                                        placeholder="Select date"
                                        value={dateDisplay || (date ? date : '')}
                                        autocomplete="off"
                                        readonly
                                        style="cursor: pointer;"
                                        required
                                    />
                                    <input type="hidden" id="booking_date_iso" value={date} />
                                    <i class="fas fa-calendar-alt input-icon-right"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-clock" style="color: var(--travhub-base);"></i>
                                    Pickup Time *
                                </label>
                                <div class="input-icon-wrapper">
                                    <input
                                        type="time"
                                        class="premium-input"
                                        bind:value={time}
                                        required
                                    />
                                    <i class="fas fa-clock input-icon-right"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-users" style="color: var(--travhub-base);"></i>
                                    Passengers
                                </label>
                                <div class="passenger-counter">
                                    <button type="button" class="counter-btn" onclick={() => { if (passengerCount > 1) passengerCount--; }}>
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="counter-value">{passengerCount} {passengerCount === 1 ? 'Passenger' : 'Passengers'}</span>
                                    <button type="button" class="counter-btn" onclick={() => { if (passengerCount < 50) passengerCount++; }}>
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group form-group--action">
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                    Search Vehicles
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- SECTION B: Route Information -->
            {#if pickup && dropoff}
            <div class="booking-card mb-4">
                <div class="booking-card__header">
                    <div class="step-badge">2</div>
                    <div>
                        <h4 class="booking-card__title">Route Information</h4>
                        <p class="booking-card__subtitle">Estimated details for your transfer</p>
                    </div>
                </div>
                <div class="booking-card__body">
                    <div class="route-info-card">
                        <div class="route-info-route">
                            <div class="route-point">
                                <div class="route-dot route-dot--from"></div>
                                <div class="route-point-text">
                                    <span class="route-label">FROM</span>
                                    <span class="route-address">{pickup}</span>
                                </div>
                            </div>
                            <div class="route-connector">
                                <div class="route-line-v"></div>
                                <div class="route-car-badge">
                                    <i class="fas fa-car"></i>
                                </div>
                                <div class="route-line-v"></div>
                            </div>
                            <div class="route-point">
                                <div class="route-dot route-dot--to"></div>
                                <div class="route-point-text">
                                    <span class="route-label">TO</span>
                                    <span class="route-address">{dropoff}</span>
                                </div>
                            </div>
                        </div>
                        <div class="route-info-stats">
                            <div class="route-stat">
                                <i class="fas fa-road"></i>
                                <div>
                                    <span class="stat-label">Distance</span>
                                    <span class="stat-value">{routeInfo.distance}</span>
                                </div>
                            </div>
                            <div class="route-stat-divider"></div>
                            <div class="route-stat">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <span class="stat-label">Travel Time</span>
                                    <span class="stat-value">{routeInfo.travelTime}</span>
                                </div>
                            </div>
                            <div class="route-stat-divider"></div>
                            <div class="route-stat">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <span class="stat-label">Price From</span>
                                    <span class="stat-value stat-value--price">${routeInfo.fromPrice}</span>
                                </div>
                            </div>
                            {#if date}
                            <div class="route-stat-divider"></div>
                            <div class="route-stat">
                                <i class="fas fa-calendar-check"></i>
                                <div>
                                    <span class="stat-label">Transfer Date</span>
                                    <span class="stat-value">{formatDate(date)} {time ? ' · ' + time : ''}</span>
                                </div>
                            </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
            {/if}

            <!-- SECTION C: Vehicle Categories -->
            <div class="booking-card">
                <div class="booking-card__header">
                    <div class="step-badge">3</div>
                    <div>
                        <h4 class="booking-card__title">Choose Your Vehicle</h4>
                        <p class="booking-card__subtitle">
                            {#if passengerCount > 1}
                                Showing vehicles for {passengerCount} passengers
                            {:else}
                                Select the vehicle that suits your needs
                            {/if}
                        </p>
                    </div>
                </div>
                <div class="booking-card__body">
                    {#if filteredCategories.length === 0}
                        <div class="no-vehicles">
                            <i class="fas fa-car-crash"></i>
                            <h5>No vehicles available for {passengerCount} passengers</h5>
                            <p>Please reduce the passenger count or contact us for a custom arrangement.</p>
                        </div>
                    {:else}
                        <div class="vehicle-grid">
                            {#each filteredCategories as category}
                                <div class="vehicle-card">
                                    <div class="vehicle-card__image">
                                        <img src={category.image_url} alt={category.title} />
                                        {#if category.vehicle_type === 'premium'}
                                            <div class="vehicle-badge vehicle-badge--premium">Premium</div>
                                        {:else if category.vehicle_type === 'special'}
                                            <div class="vehicle-badge vehicle-badge--eco">Eco</div>
                                        {/if}
                                    </div>
                                    <div class="vehicle-card__body">
                                        <h5 class="vehicle-card__title">{category.title}</h5>
                                        <div class="vehicle-card__specs">
                                            <span class="spec-item">
                                                <i class="fas fa-users"></i>
                                                {category.passenger_capacity ?? '—'} Passengers
                                            </span>
                                            <span class="spec-item">
                                                <i class="fas fa-suitcase"></i>
                                                {category.luggage_capacity ?? '—'} Luggage
                                            </span>
                                        </div>
                                        {#if category.advantages && category.advantages.length > 0}
                                            <ul class="vehicle-card__advantages">
                                                {#each category.advantages.slice(0, 3) as adv}
                                                    <li><i class="fas fa-check-circle"></i> {adv}</li>
                                                {/each}
                                            </ul>
                                        {/if}
                                    </div>
                                    <div class="vehicle-card__footer">
                                        <div class="vehicle-card__price">
                                            <span class="price-from">From</span>
                                            <span class="price-amount">${parseFloat(category.base_price).toFixed(0)}</span>
                                        </div>
                                        <button
                                            type="button"
                                            class="select-btn"
                                            onclick={() => selectVehicle(category)}
                                            disabled={!pickup || !dropoff || !date}
                                        >
                                            Select — ${parseFloat(category.base_price).toFixed(0)}
                                        </button>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    {#if !pickup || !dropoff || !date}
                        <div class="fill-route-notice">
                            <i class="fas fa-info-circle"></i>
                            Please fill in your pickup location, destination, and date above to select a vehicle.
                        </div>
                    {/if}
                </div>
            </div>

        </div>
    </section>

    <Footer />
</div>

<style>
    /* ── Booking Card ── */
    .booking-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        overflow: visible;
    }
    .booking-card__header {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 24px 32px;
        border-bottom: 1px solid #f0f4f8;
    }
    .step-badge {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--travhub-base, #e52029);
        color: #fff;
        font-size: 18px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .booking-card__title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .booking-card__subtitle {
        font-size: 14px;
        color: #64748b;
        margin: 2px 0 0;
    }
    .booking-card__body {
        padding: 28px 32px;
    }
    @media (max-width: 768px) {
        .booking-card__header, .booking-card__body { padding: 20px; }
    }

    /* ── Route Form Grid ── */
    .route-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }
    @media (max-width: 768px) {
        .route-form-grid { grid-template-columns: 1fr; }
    }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-group--action { display: flex; flex-direction: column; justify-content: flex-end; }
    .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .premium-input {
        width: 100%;
        padding: 13px 18px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s;
        outline: none;
    }
    .premium-input:focus {
        border-color: var(--travhub-base);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(229,32,41,0.08);
    }
    .input-icon-wrapper { position: relative; }
    .input-icon-right {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .passenger-counter {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }
    .counter-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
    }
    .counter-btn:hover { border-color: var(--travhub-base); color: var(--travhub-base); }
    .counter-value { font-size: 15px; font-weight: 600; color: #1e293b; flex: 1; text-align: center; }
    .search-btn {
        width: 100%;
        padding: 14px;
        background: var(--travhub-base);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(229,32,41,0.25);
    }
    .search-btn:hover { background: #111; transform: translateY(-1px); }

    /* ── Route Info Card ── */
    .route-info-card {
        display: flex;
        gap: 32px;
        align-items: stretch;
        flex-wrap: wrap;
    }
    .route-info-route {
        flex: 1;
        min-width: 260px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .route-point {
        display: flex;
        align-items: flex-start;
        gap: 14px;
    }
    .route-dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .route-dot--from { background: var(--travhub-base); }
    .route-dot--to { background: #10b981; }
    .route-point-text { display: flex; flex-direction: column; gap: 2px; }
    .route-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #94a3b8; }
    .route-address { font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.4; }
    .route-connector {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 6px 0 6px 6px;
    }
    .route-line-v {
        width: 2px;
        height: 16px;
        background: repeating-linear-gradient(to bottom, #cbd5e1 0, #cbd5e1 4px, transparent 4px, transparent 8px);
    }
    .route-car-badge {
        background: #f1f5f9;
        border-radius: 20px;
        padding: 4px 12px;
        font-size: 13px;
        color: #64748b;
    }
    .route-info-stats {
        flex: 2;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 0;
        background: #f8fafc;
        border-radius: 12px;
        padding: 20px 24px;
        border: 1px solid #eef2f5;
    }
    .route-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 20px;
        flex: 1;
        min-width: 140px;
    }
    .route-stat i { font-size: 22px; color: var(--travhub-base); }
    .route-stat div { display: flex; flex-direction: column; gap: 2px; }
    .stat-label { font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
    .stat-value { font-size: 16px; font-weight: 700; color: #1e293b; }
    .stat-value--price { color: var(--travhub-base); }
    .route-stat-divider { width: 1px; height: 40px; background: #e2e8f0; }

    /* ── Vehicle Grid ── */
    .vehicle-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
    }
    .vehicle-card {
        border: 2px solid #eaeef2;
        border-radius: 16px;
        overflow: hidden;
        background: #fff;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
    }
    .vehicle-card:hover {
        border-color: var(--travhub-base);
        box-shadow: 0 12px 35px rgba(229,32,41,0.1);
        transform: translateY(-4px);
    }
    .vehicle-card__image {
        position: relative;
        height: 200px;
        background: #f8fafc;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .vehicle-card__image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        transition: transform 0.4s;
    }
    .vehicle-card:hover .vehicle-card__image img { transform: scale(1.05); }
    .vehicle-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .vehicle-badge--premium { background: #fef3c7; color: #92400e; }
    .vehicle-badge--eco { background: #d1fae5; color: #065f46; }
    .vehicle-card__body { padding: 20px; flex: 1; }
    .vehicle-card__title { font-size: 18px; font-weight: 800; color: #1e293b; margin: 0 0 12px; }
    .vehicle-card__specs {
        display: flex;
        gap: 16px;
        margin-bottom: 14px;
    }
    .spec-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        font-weight: 600;
        color: #475569;
    }
    .spec-item i { color: var(--travhub-base); font-size: 14px; }
    .vehicle-card__advantages {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .vehicle-card__advantages li {
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .vehicle-card__advantages li i { color: #10b981; font-size: 12px; }
    .vehicle-card__footer {
        padding: 16px 20px;
        border-top: 1px solid #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fafbfc;
    }
    .vehicle-card__price { display: flex; flex-direction: column; }
    .price-from { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
    .price-amount { font-size: 24px; font-weight: 800; color: #1e293b; line-height: 1; }
    .select-btn {
        padding: 10px 20px;
        background: var(--travhub-base);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .select-btn:hover:not(:disabled) { background: #111; }
    .select-btn:disabled { background: #cbd5e1; cursor: not-allowed; }

    /* ── Notices ── */
    .no-vehicles {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .no-vehicles i { font-size: 48px; margin-bottom: 16px; display: block; }
    .no-vehicles h5 { font-size: 18px; font-weight: 700; color: #475569; margin-bottom: 8px; }
    .fill-route-notice {
        margin-top: 20px;
        padding: 14px 20px;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 10px;
        font-size: 14px;
        color: #92400e;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .fill-route-notice i { color: #f59e0b; font-size: 16px; }
</style>
