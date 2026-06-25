<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import RideSharingLocationSearchInput from '@/components/RideSharingLocationSearchInput.svelte';

    type Location = { id: number; name: string; address?: string };
    type Schedule = { id: number; departure_time: string; days: string[]; quota: number; specific_departure_time?: string; estimated_minutes?: number; vehicle_category?: { title: string, capacity: string, passenger_capacity: number } };

    function isDayAvailable(days: string[]) {
        if (!date) return true;
        const selectedDate = new Date(date);
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const selectedDayName = dayNames[selectedDate.getDay()];
        return Array.isArray(days) ? days.includes(selectedDayName) : String(days).includes(selectedDayName);
    }

    function formatDuration(minutes: number | null) {
        if (!minutes) return '';
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        if (h > 0 && m > 0) return `${h}h ${m}m`;
        if (h > 0) return `${h}h`;
        return `${m}m`;
    }

    type AvailableRoute = {
        id: number;
        name: string;
        price: number;
        schedules: Schedule[];
    };
    type Search = {
        date: string;
        pickup_location_id: string | number;
        dropoff_location_id: string | number;
        passengers: number;
    };

    let {
        locations = [],
        availableRoutes = [],
        search = {
            date: '',
            pickup_location_id: '',
            dropoff_location_id: '',
            passengers: 1,
        },
    } = $props<{
        locations: Location[];
        availableRoutes: AvailableRoute[];
        search: Search;
    }>();

    const auth = $derived(page.props.auth);

    // Form state — pre-filled from hero form search params
    let date = $state(search.date ?? '');
    let pickupLocationId = $state(String(search.pickup_location_id ?? ''));
    let dropoffLocationId = $state(String(search.dropoff_location_id ?? ''));
    let passengers = $state(search.passengers ?? 1);

    const selectedPickup = $derived(
        locations.find((l) => String(l.id) === pickupLocationId),
    );
    const selectedDropoff = $derived(
        locations.find((l) => String(l.id) === dropoffLocationId),
    );
</script>

<AppHead title="Ride Sharing Booking | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header {auth} />

    <!-- Simple Service Header -->
    <div class="service-simple-header" style="padding: 40px 0 10px; background: #f7f9fa; border-bottom: 1px solid #eaeef2;">
        <div class="container">
            <h2 style="font-size: 26px; font-weight: 800; color: #1e293b; margin: 0;">Ride Sharing Booking</h2>
            <p style="color: #64748b; margin: 6px 0 0; font-size: 15px;">Find and book a seat in a shared vehicle along established routes.</p>
        </div>
    </div>

    <!-- Main Content -->
    <section class="rs-page" style="padding: 40px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="row gutter-y-30">

                <!-- Left: Search / Filter Form -->
                <div class="col-lg-4">
                    <div class="rs-search-card">
                        <!-- Card Header -->
                        <div class="rs-card-header">
                            <div class="rs-card-header__icon">
                                <i class="fas fa-bus"></i>
                            </div>
                            <div class="rs-card-header__text">
                                <h4>Search Rides</h4>
                                <p>Select your route &amp; schedule</p>
                            </div>
                        </div>

                        <!-- Info Badge -->
                        <div class="rs-info-badge">
                            <span>You are booking a <strong>seat in a shared ride</strong> — not a private trip.</span>
                        </div>

                        <form class="rs-form" action="/ride-sharing" method="GET">

                            <!-- Departure Date -->
                            <div class="rs-form__control">
                                <div class="rs-form__label-row">
                                    <i class="icon icon-calendar-1"></i>
                                    <label for="rs_page_date">Departure Date *</label>
                                </div>
                                <input type="hidden" name="date" value={date} />
                                <DatePicker
                                    id="rs_page_date"
                                    bind:value={date}
                                    placeholder="Select travel date"
                                    required
                                    hideIcon
                                    hideChevron
                                />
                            </div>

                            <!-- Pickup Location -->
                            <div class="rs-form__control">
                                <div class="rs-form__label-row">
                                    <i class="icon icon-pin-2"></i>
                                    <label for="rs_pickup">Pickup Location *</label>
                                </div>
                                <RideSharingLocationSearchInput
                                    id="rs_page_pickup"
                                    name="pickup_location_id"
                                    bind:value={pickupLocationId}
                                    locations={locations}
                                    placeholder="Search departure city..."
                                    required
                                />
                            </div>

                            <!-- Drop-off Location -->
                            <div class="rs-form__control">
                                <div class="rs-form__label-row">
                                    <i class="icon icon-traveler-with-a-suitcase-1"></i>
                                    <label for="rs_dropoff">Drop-off Location *</label>
                                </div>
                                <RideSharingLocationSearchInput
                                    id="rs_page_dropoff"
                                    name="dropoff_location_id"
                                    bind:value={dropoffLocationId}
                                    locations={locations}
                                    placeholder="Search destination city..."
                                    required
                                />
                            </div>

                            <!-- Passengers -->
                            <div class="rs-form__control">
                                <div class="rs-form__label-row">
                                    <i class="fas fa-users icon"></i>
                                    <label>Passengers</label>
                                </div>
                                <div class="rs-passenger-counter">
                                    <button
                                        type="button"
                                        class="rs-counter-btn"
                                        onclick={() => { if (passengers > 1) passengers--; }}
                                        aria-label="Decrease passengers"
                                    >−</button>
                                    <span class="rs-counter-value">{passengers}</span>
                                    <input type="hidden" name="passengers" value={passengers} id="rs_page_passengers" />
                                    <button
                                        type="button"
                                        class="rs-counter-btn"
                                        onclick={() => passengers++}
                                        aria-label="Increase passengers"
                                    >+</button>
                                </div>
                            </div>

                            <button type="submit" class="travhub-btn rs-submit-btn">
                                <span>Search Routes</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Search Results -->
                <div class="col-lg-8">
                    {#if !search.pickup_location_id || !search.dropoff_location_id}
                        <!-- Empty State -->
                        <div class="rs-empty-state">
                            <div class="rs-empty-state__icon">
                                <i class="fas fa-bus"></i>
                            </div>
                            <h4>Where to?</h4>
                            <p>Select your pickup and dropoff location on the left to see available routes.</p>
                        </div>
                    {:else if availableRoutes.length === 0}
                        <div class="rs-empty-state">
                            <div class="rs-empty-state__icon">
                                <i class="fas fa-bus"></i>
                            </div>
                            <h4>No Routes Found</h4>
                            <p>We couldn't find any travel routes from <strong>{selectedPickup?.name}</strong> to <strong>{selectedDropoff?.name}</strong>. Please try another destination.</p>
                        </div>
                    {:else}
                        <div class="rs-results-header">
                            <h3>Available Routes</h3>
                            <p>Showing routes from <strong>{selectedPickup?.name}</strong> to <strong>{selectedDropoff?.name}</strong></p>
                        </div>

                        {#each availableRoutes as route}
                            <div class="rs-route-card">
                                <div class="rs-route-card__header">
                                    <div class="rs-route-card__name">
                                        <i class="fas fa-route"></i>
                                        <span>{selectedPickup?.name} - {selectedDropoff?.name}</span>
                                    </div>
                                </div>

                                <div class="rs-route-card__body">
                                    {#if route.schedules.length === 0}
                                        <p class="rs-no-schedule">No schedules available for this route.</p>
                                    {:else}
                                        <p class="rs-schedules-label">
                                            <i class="fas fa-clock me-1"></i> Available Schedules:
                                        </p>
                                        <div class="rs-schedules-grid">
                                            {#each route.schedules as schedule}
                                                <div class="rs-schedule-card">
                                                    <div class="rs-schedule-card__time">
                                                        <strong>{schedule.specific_departure_time ? schedule.specific_departure_time.substring(0, 5) : '-'}</strong>
                                                    </div>
                                                    {#if schedule.estimated_minutes && schedule.specific_arrival_time}
                                                        <div class="rs-schedule-card__travel-time">
                                                            <span>Arrival at <strong>{schedule.specific_arrival_time.substring(0, 5)}</strong> <span class="text-muted fw-normal">({formatDuration(schedule.estimated_minutes)})</span></span>
                                                        </div>
                                                    {/if}

                                                    
                                                    
                                                    {#if schedule.vehicle_category}
                                                        <div class="rs-schedule-card__vehicle">
                                                            <i class="fas fa-car"></i>
                                                            <span>{schedule.vehicle_category.title}</span>
                                                            <div class="vehicle-tooltip-wrap">
                                                                <button type="button" class="vehicle-tooltip-btn" aria-label="Vehicle details">
                                                                    <i class="fas fa-info-circle"></i>
                                                                </button>
                                                                <div class="vehicle-tooltip" role="tooltip">
                                                                    <span class="vehicle-tooltip__label">Capacity</span>
                                                                    <span class="vehicle-tooltip__value mb-2">{schedule.vehicle_category.passenger_capacity} seats</span>
                                                                    
                                                                    <!-- {#if schedule.vehicle_category.description}
                                                                        <span class="vehicle-tooltip__label">Description</span>
                                                                        <span class="vehicle-tooltip__value mb-2">{schedule.vehicle_category.description}</span>
                                                                    {/if} -->

                                                                    {#if schedule.vehicle_category.advantages && schedule.vehicle_category.advantages.length > 0}
                                                                        <span class="vehicle-tooltip__label">Advantages</span>
                                                                        <span class="vehicle-tooltip__value">{schedule.vehicle_category.advantages.join(', ')}</span>
                                                                    {/if}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    {/if}
                                                    <!-- <div class="rs-schedule-card__price">
                                                        <span>Rp {new Intl.NumberFormat('id-ID').format(schedule.price)} <small class="text-muted fw-normal">/ seat</small></span>
                                                    </div> -->

                                                    <div class="rs-schedule-card__quota {schedule.quota > 0 ? '' : 'rs-schedule-card__quota--full'}">
                                                        {#if schedule.quota > 0}
                                                            {schedule.quota} seat{schedule.quota === 1 ? '' : 's'} available
                                                        {:else}
                                                            Fully booked
                                                        {/if}
                                                    </div>

                                                    <form action="/booking/sharing-ride/checkout" method="GET">
                                                        <input type="hidden" name="service" value="sharing-ride" />
                                                        <input type="hidden" name="rs_route_id" value={route.id} />
                                                        <input type="hidden" name="rs_schedule_id" value={schedule.id} />
                                                        <input type="hidden" name="pickup_location_id" value={pickupLocationId} />
                                                        <input type="hidden" name="dropoff_location_id" value={dropoffLocationId} />
                                                        <input type="hidden" name="date" value={date} />
                                                        <input type="hidden" name="passengers" value={passengers} />
                                                        <input type="hidden" name="price" value={route.price} />

                                                        <button type="submit" class="rs-select-btn">
                                                            <span>Rp {new Intl.NumberFormat('id-ID').format(schedule.price)} <small>/seat</small></span>
                                                            <i class="fas fa-arrow-right"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            {/each}
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/each}
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* =============================================
       SEARCH CARD
    ============================================= */
    .rs-search-card {
        background: #fff;
        padding: 32px 28px;
        border-radius: 16px;
        box-shadow: 0 8px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0e8e8;
        position: sticky;
        top: 100px;
    }

    /* Card Header */
    .rs-card-header {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 20px;
    }
    .rs-card-header__icon {
        width: 52px;
        height: 52px;
        background: #fff0f0;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #e52029;
        flex-shrink: 0;
    }
    .rs-card-header__text h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
        color: #111;
        line-height: 1.2;
    }
    .rs-card-header__text p {
        margin: 2px 0 0;
        font-size: 13px;
        color: #888;
    }

    /* Info Badge */
    .rs-info-badge {
        background: #fff5f5;
        border-left: 3px solid #e52029;
        border-radius: 0 8px 8px 0;
        padding: 10px 14px;
        font-size: 12.5px;
        color: #555;
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 22px;
        line-height: 1.5;
    }
    .rs-info-badge i {
        margin-top: 2px;
        flex-shrink: 0;
        color: #e52029;
    }
    .rs-info-badge strong {
        color: #333;
    }

    /* Form Controls — hero-section style */
    .rs-form__control {
        position: relative;
        padding-bottom: 16px;
        margin-bottom: 16px;
        border-bottom: 1px solid #eee;
    }
    .rs-form__control:last-of-type {
        border-bottom: none;
        margin-bottom: 22px;
    }

    .rs-form__label-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }
    .rs-form__label-row .icon,
    .rs-form__label-row i {
        display: inline-block;
        font-size: 16px;
        color: #aaa;
        line-height: 1;
        transition: color 0.3s;
    }
    .rs-form__control:hover .rs-form__label-row .icon,
    .rs-form__control:hover .rs-form__label-row i,
    .rs-form__control:focus-within .rs-form__label-row .icon,
    .rs-form__control:focus-within .rs-form__label-row i {
        color: #e52029;
    }

    .rs-form__control label {
        display: inline-block;
        font-size: 11px;
        font-weight: 700;
        color: #999;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        margin-bottom: 0;
    }

    /* Override DatePicker & RideSharingLocationSearchInput inputs — light bg */
    :global(.rs-form .rs-form__control input[type="text"]),
    :global(.rs-form .rs-form__control .rs-location-search-field input) {
        background: transparent !important;
        border: none !important;
        border-bottom: 1px solid #ddd !important;
        border-radius: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        color: #222 !important;
        font-size: 15px !important;
        font-weight: 500 !important;
        padding: 4px 0 6px !important;
        width: 100% !important;
        transition: border-color 0.3s !important;
    }
    :global(.rs-form .rs-form__control input[type="text"]:focus),
    :global(.rs-form .rs-form__control .rs-location-search-field input:focus) {
        border-bottom-color: #e52029 !important;
    }
    :global(.rs-form .rs-form__control input[type="text"]::placeholder),
    :global(.rs-form .rs-form__control .rs-location-search-field input::placeholder) {
        color: #aaa !important;
        font-weight: 400 !important;
        font-size: 14px !important;
    }
    /* Clear button on light bg */
    :global(.rs-form .rs-clear-btn) {
        color: #bbb !important;
    }
    :global(.rs-form .rs-clear-btn:hover) {
        color: #e52029 !important;
    }
    /* Dropdown popup */
    :global(.rs-form .rs-location-search-dropdown) {
        top: calc(100% + 8px) !important;
    }

    /* Passenger counter */
    .rs-passenger-counter {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-top: 4px;
    }
    .rs-counter-btn {
        background: #f5f5f5;
        border: 1px solid #ddd;
        color: #333;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        transition: background 0.2s, border-color 0.2s, color 0.2s;
        padding-bottom: 2px;
    }
    .rs-counter-btn:hover {
        background: #fff0f0;
        border-color: #e52029;
        color: #e52029;
    }
    .rs-counter-value {
        font-size: 16px;
        font-weight: 600;
        color: #222;
        min-width: 24px;
        text-align: center;
    }

    /* Submit button */
    .rs-submit-btn {
        width: 100%;
        justify-content: center;
        border-radius: 50px !important;
    }

    /* =============================================
       RESULTS AREA
    ============================================= */

    /* Empty State */
    .rs-empty-state {
        background: #fff;
        border-radius: 16px;
        border: 2px dashed #f0d0d2;
        padding: 60px 30px;
        text-align: center;
    }
    .rs-empty-state__icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #fff0f0;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        font-size: 36px;
        color: #e52029;
    }
    .rs-empty-state h4 {
        color: #333;
        font-weight: 700;
        margin-bottom: 8px;
    }
    .rs-empty-state p {
        color: #888;
        max-width: 380px;
        margin: 0 auto;
        font-size: 14px;
        line-height: 1.6;
    }

    /* Results Header */
    .rs-results-header {
        margin-bottom: 24px;
    }
    .rs-results-header h3 {
        font-weight: 700;
        font-size: 24px;
        margin-bottom: 4px;
        color: #111;
    }
    .rs-results-header p {
        color: #666;
        font-size: 14px;
        margin: 0;
    }
    .rs-results-header strong {
        color: #e52029;
    }

    /* Route Card */
    .rs-route-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
        margin-bottom: 24px;
        overflow: hidden;
        border: 1px solid #f5e0e0;
        transition: box-shadow 0.3s, transform 0.3s;
    }
    .rs-route-card:hover {
        box-shadow: 0 12px 40px rgba(229, 32, 41, 0.12);
        transform: translateY(-2px);
    }

    .rs-route-card__header {
        background: linear-gradient(135deg, #c0111a 0%, #e52029 100%);
        padding: 16px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }
    .rs-route-card__name {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #fff;
        font-weight: 700;
        font-size: 16px;
    }
    .rs-route-card__name i {
        font-size: 20px;
        opacity: 0.9;
    }
    .rs-route-card__price {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        font-weight: 700;
        font-size: 15px;
        padding: 6px 16px;
        border-radius: 50px;
        white-space: nowrap;
        backdrop-filter: blur(4px);
    }
    .rs-route-card__price small {
        font-weight: 400;
        opacity: 0.85;
        font-size: 12px;
    }

    .rs-route-card__body {
        padding: 20px 24px;
        background: #fafafa;
    }
    .rs-no-schedule {
        text-align: center;
        color: #999;
        font-size: 14px;
        padding: 20px 0;
        margin: 0;
    }
    .rs-schedules-label {
        font-weight: 700;
        font-size: 13px;
        color: #444;
        margin-bottom: 14px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Schedules Grid */
    .rs-schedules-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 12px;
    }

    /* Schedule Card */
    .rs-schedule-card {
        background: #fff;
        border: 1.5px solid #eee;
        border-radius: 12px;
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        transition: border-color 0.25s, box-shadow 0.25s;
        position: relative;
    }
    .rs-schedule-card:hover {
        border-color: #e52029;
        box-shadow: 0 4px 16px rgba(229, 32, 41, 0.1);
    }

    .rs-schedule-card__time {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #e52029;
    }
    .rs-schedule-card__time strong {
        font-size: 23px;
        font-weight: 800;
        color: #111;
        line-height: 1;
        /* letter-spacing: -0.5px; */
    }
    .rs-schedule-card__travel-time {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #555;
    }

    .rs-schedule-card__price {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 600;
        color: #e53e3e;
    }

    .rs-schedule-card__time i,
    .rs-schedule-card__travel-time i {
        color: #e53e3e;
        font-size: 1.15rem;
        width: 18px;
        text-align: left;
        flex-shrink: 0;
    }

    .rs-schedule-card__travel-time strong {
        color: #111;
        font-weight: 700;
    }

    .rs-schedule-card__vehicle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        color: #555;
    }

    .rs-schedule-card__quota {
        position: absolute;
        top: 14px;
        right: 16px;
        font-size: 11px;
        color: #2d9e5f;
        font-weight: 600;
        background: transparent;
        padding: 0;
    }
    .rs-schedule-card__quota--full {
        color: #e53e3e;
    }

    /* Select Seat Button */
    .rs-select-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        padding: 9px 16px;
        background: #e52029;
        color: #fff;
        border: none;
        border-radius: 50px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.25s, transform 0.2s;
        margin-top: 4px;
        letter-spacing: 0.2px;
    }
    .rs-select-btn:hover {
        background: #c0111a;
        transform: translateY(-1px);
    }
    .rs-select-btn i {
        font-size: 15px;
        transition: transform 0.2s;
    }
    .rs-select-btn:hover i {
        transform: translateX(3px);
    }

    /* ── Vehicle Tooltip ── */
    .vehicle-tooltip-wrap {
        position: relative;
        flex-shrink: 0;
        margin-left: 2px;
        display: inline-flex;
        align-items: center;
    }
    .vehicle-tooltip-btn {
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        color: #94a3b8;
        font-size: 15px;
        line-height: 1;
        display: flex;
        align-items: center;
        transition: color 0.2s;
    }
    /* .vehicle-tooltip-btn:hover {
        color: var(--travhub-base, #e52029);
    } */
    .vehicle-tooltip {
        display: none;
        position: absolute;
        bottom: calc(100% + 8px);
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
        background: #1e293b;
        color: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        min-width: 180px;
        max-width: 240px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        pointer-events: none;
        text-align: left;
    }
    .vehicle-tooltip::after {
        content: '';
        position: absolute;
        bottom: -6px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 6px solid transparent;
        border-right: 6px solid transparent;
        border-top: 6px solid #1e293b;
    }
    .vehicle-tooltip__label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
        margin-bottom: 2px;
    }
    .vehicle-tooltip__value {
        display: block;
        font-size: 12.5px;
        font-weight: 500;
        color: #f1f5f9;
        line-height: 1.4;
    }
    .vehicle-tooltip-wrap:hover .vehicle-tooltip,
    .vehicle-tooltip-btn:focus + .vehicle-tooltip {
        display: block;
    }
</style>
