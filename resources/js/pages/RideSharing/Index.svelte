<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';

    type Location = { id: number; name: string; area: string };
    type Schedule = { id: number; departure_time: string; label: string };
    type Search = {
        date: string;
        pickup_location_id: string | number;
        dropoff_location_id: string | number;
        schedule_id: string | number;
        passengers: number;
    };

    let {
        locations = [],
        schedules = [],
        search = {
            date: '',
            pickup_location_id: '',
            dropoff_location_id: '',
            schedule_id: '',
            passengers: 1,
        },
    } = $props<{
        locations: Location[];
        schedules: Schedule[];
        search: Search;
    }>();

    const auth = $derived(page.props.auth);

    // Form state — pre-filled from hero form search params
    let date = $state(search.date ?? '');
    let pickupLocationId = $state(String(search.pickup_location_id ?? ''));
    let dropoffLocationId = $state(String(search.dropoff_location_id ?? ''));
    let scheduleId = $state(String(search.schedule_id ?? ''));
    let passengers = $state(search.passengers ?? 1);

    const selectedPickup = $derived(
        locations.find((l) => String(l.id) === pickupLocationId),
    );
    const selectedDropoff = $derived(
        locations.find((l) => String(l.id) === dropoffLocationId),
    );
    const selectedSchedule = $derived(
        schedules.find((s) => String(s.id) === scheduleId),
    );

    /** Group locations by area for the select optgroups. */
    const locationsByArea = $derived(
        locations.reduce(
            (acc, loc) => {
                if (!acc[loc.area]) acc[loc.area] = [];
                acc[loc.area].push(loc);
                return acc;
            },
            {} as Record<string, Location[]>,
        ),
    );
</script>

<AppHead title="Ride Sharing Booking | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header {auth} />

    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">
                Ride Sharing
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Ride Sharing</span></li>
            </ul>
        </div>
    </section>

    <!-- Main Content -->
    <section class="rs-page pt-120 pb-120">
        <div class="container">
            <div class="row gutter-y-30">

                <!-- Left: Search / Filter Form -->
                <div class="col-lg-4">
                    <div class="rs-search-card">
                        <div class="rs-search-card__header">
                            <i class="flaticon-bus"></i>
                            <div>
                                <h4>Search Rides</h4>
                                <p>Select your route &amp; schedule</p>
                            </div>
                        </div>

                        <!-- Shared ride notice -->
                        <div class="rs-shared-notice">
                            <i class="fas fa-info-circle"></i>
                            <span>Routes &amp; schedules are predefined by the operator. You are booking a <strong>seat in a shared ride</strong>, not a private trip.</span>
                        </div>

                        <form class="rs-search-form" action="/ride-sharing" method="GET">

                            <!-- Departure Date -->
                            <div class="rs-form-group">
                                <label for="rs_page_date">
                                    <i class="icon-calendar-1"></i> Departure Date *
                                </label>
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
                            <div class="rs-form-group">
                                <label for="rs_page_pickup">
                                    <i class="icon-pin-2"></i> Pickup Location *
                                </label>
                                <select
                                    id="rs_page_pickup"
                                    name="pickup_location_id"
                                    bind:value={pickupLocationId}
                                    required
                                    class="rs-form-select"
                                >
                                    <option value="" disabled>Select pickup location</option>
                                    {#each Object.entries(locationsByArea) as [area, locs]}
                                        <optgroup label={area}>
                                            {#each locs as loc}
                                                <option value={String(loc.id)}>{loc.name}</option>
                                            {/each}
                                        </optgroup>
                                    {/each}
                                </select>
                            </div>

                            <!-- Dropoff Location -->
                            <div class="rs-form-group">
                                <label for="rs_page_dropoff">
                                    <i class="icon-pin-2"></i> Dropoff Location *
                                </label>
                                <select
                                    id="rs_page_dropoff"
                                    name="dropoff_location_id"
                                    bind:value={dropoffLocationId}
                                    required
                                    class="rs-form-select"
                                >
                                    <option value="" disabled>Select dropoff location</option>
                                    {#each Object.entries(locationsByArea) as [area, locs]}
                                        <optgroup label={area}>
                                            {#each locs as loc}
                                                <option value={String(loc.id)}>{loc.name}</option>
                                            {/each}
                                        </optgroup>
                                    {/each}
                                </select>
                            </div>

                            <!-- Departure Time -->
                            <div class="rs-form-group">
                                <label for="rs_page_schedule">
                                    <i class="fas fa-clock"></i> Departure Time *
                                </label>
                                <select
                                    id="rs_page_schedule"
                                    name="schedule_id"
                                    bind:value={scheduleId}
                                    required
                                    class="rs-form-select"
                                >
                                    <option value="" disabled>Select departure time</option>
                                    {#each schedules as sch}
                                        <option value={String(sch.id)}>{sch.label}</option>
                                    {/each}
                                </select>
                            </div>

                            <!-- Passengers -->
                            <div class="rs-form-group">
                                <label for="rs_page_pax">
                                    <i class="icon-traveler-with-a-suitcase-1"></i> Passengers
                                </label>
                                <div class="rs-counter">
                                    <button
                                        type="button"
                                        class="rs-counter__btn"
                                        onclick={() => { if (passengers > 1) passengers--; }}
                                    >−</button>
                                    <span class="rs-counter__val">{passengers}</span>
                                    <input type="hidden" name="passengers" value={passengers} id="rs_page_pax" />
                                    <button
                                        type="button"
                                        class="rs-counter__btn"
                                        onclick={() => passengers++}
                                    >+</button>
                                </div>
                            </div>

                            <button type="submit" class="travhub-btn rs-search-btn">
                                <span>Search Available Rides</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Results / Selected Trip Summary -->
                <div class="col-lg-8">
                    {#if selectedPickup && selectedDropoff && selectedSchedule && date}
                        <!-- Trip summary card -->
                        <div class="rs-trip-card wow fadeInUp" data-wow-duration="800ms">
                            <div class="rs-trip-card__badge">
                                <i class="fas fa-users"></i> Shared Ride
                            </div>
                            <div class="rs-trip-card__route">
                                <div class="rs-trip-card__point rs-trip-card__point--from">
                                    <div class="rs-trip-card__dot rs-trip-card__dot--from"></div>
                                    <div>
                                        <span class="rs-trip-card__label">Pickup</span>
                                        <strong>{selectedPickup.name}</strong>
                                        <small>{selectedPickup.area}</small>
                                    </div>
                                </div>
                                <div class="rs-trip-card__line"></div>
                                <div class="rs-trip-card__point rs-trip-card__point--to">
                                    <div class="rs-trip-card__dot rs-trip-card__dot--to"></div>
                                    <div>
                                        <span class="rs-trip-card__label">Dropoff</span>
                                        <strong>{selectedDropoff.name}</strong>
                                        <small>{selectedDropoff.area}</small>
                                    </div>
                                </div>
                            </div>

                            <div class="rs-trip-card__meta">
                                <div class="rs-trip-card__meta-item">
                                    <i class="icon-calendar-1"></i>
                                    <div>
                                        <span>Date</span>
                                        <strong>{date}</strong>
                                    </div>
                                </div>
                                <div class="rs-trip-card__meta-item">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <span>Departure</span>
                                        <strong>{selectedSchedule.label}</strong>
                                    </div>
                                </div>
                                <div class="rs-trip-card__meta-item">
                                    <i class="icon-traveler-with-a-suitcase-1"></i>
                                    <div>
                                        <span>Passengers</span>
                                        <strong>{passengers}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="rs-trip-card__cta">
                                <p class="rs-trip-card__info">
                                    <i class="fas fa-info-circle"></i>
                                    Contact us via WhatsApp or fill out the booking form below to confirm your seat.
                                </p>
                                <div class="rs-trip-card__actions">
                                    <a
                                        href="https://wa.me/?text={encodeURIComponent(`Hi Siwride! I'd like to book a shared ride:\n\nFrom: ${selectedPickup.name}\nTo: ${selectedDropoff.name}\nDate: ${date}\nDeparture: ${selectedSchedule.label}\nPassengers: ${passengers}`)}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="travhub-btn rs-whatsapp-btn"
                                    >
                                        <i class="fab fa-whatsapp"></i>
                                        <span>Book via WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    {:else}
                        <!-- Empty state -->
                        <div class="rs-empty-state">
                            <div class="rs-empty-state__icon">
                                <i class="flaticon-bus"></i>
                            </div>
                            <h3>Find Your Shared Ride</h3>
                            <p>
                                Select your departure date, pickup &amp; dropoff locations, and preferred departure time on the left to find available shared rides.
                            </p>
                            <div class="rs-empty-state__features">
                                <div class="rs-empty-state__feature">
                                    <i class="flaticon-check"></i>
                                    <span>Predefined routes by the operator</span>
                                </div>
                                <div class="rs-empty-state__feature">
                                    <i class="flaticon-check"></i>
                                    <span>Fixed departure schedules</span>
                                </div>
                                <div class="rs-empty-state__feature">
                                    <i class="flaticon-check"></i>
                                    <span>Affordable shared fares</span>
                                </div>
                                <div class="rs-empty-state__feature">
                                    <i class="flaticon-check"></i>
                                    <span>Multiple daily departures</span>
                                </div>
                            </div>
                        </div>
                    {/if}

                    <!-- How it works for Ride Sharing -->
                    <div class="rs-how-it-works">
                        <h5 class="rs-how-it-works__title">
                            <i class="fas fa-route"></i> How Ride Sharing Works
                        </h5>
                        <div class="rs-how-it-works__steps">
                            <div class="rs-step">
                                <div class="rs-step__num">1</div>
                                <div>
                                    <strong>Select Your Route</strong>
                                    <p>Choose from our predefined pickup &amp; dropoff locations serviced by Siwride operators.</p>
                                </div>
                            </div>
                            <div class="rs-step">
                                <div class="rs-step__num">2</div>
                                <div>
                                    <strong>Pick a Departure Time</strong>
                                    <p>Select from available daily departure schedules configured by the operator.</p>
                                </div>
                            </div>
                            <div class="rs-step">
                                <div class="rs-step__num">3</div>
                                <div>
                                    <strong>Book Your Seats</strong>
                                    <p>Contact us via WhatsApp to confirm your seat reservation in the shared vehicle.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* Search Card */
    .rs-search-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        border: 1px solid #f0f0f0;
        position: sticky;
        top: 100px;
    }
    .rs-search-card__header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 24px 28px 20px;
        background: linear-gradient(135deg, var(--travhub-base, #e52029) 0%, #c0392b 100%);
        color: #fff;
    }
    .rs-search-card__header i {
        font-size: 36px;
        opacity: 0.9;
    }
    .rs-search-card__header h4 {
        margin: 0 0 4px;
        font-size: 20px;
        font-weight: 700;
    }
    .rs-search-card__header p {
        margin: 0;
        font-size: 13px;
        opacity: 0.85;
    }

    .rs-shared-notice {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 18px;
        background: #fffbeb;
        border-bottom: 1px solid #fde68a;
        font-size: 12.5px;
        color: #92400e;
        line-height: 1.5;
    }
    .rs-shared-notice i {
        color: #d97706;
        margin-top: 2px;
        flex-shrink: 0;
    }
    .rs-shared-notice strong {
        color: #78350f;
    }

    .rs-search-form {
        padding: 24px 28px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    .rs-form-group label {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }
    .rs-form-group label i {
        color: var(--travhub-base, #e52029);
        font-size: 15px;
    }
    .rs-form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        font-size: 14px;
        background: #f9fafb;
        color: #333;
        outline: none;
        transition: border-color 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 16px;
        padding-right: 36px;
    }
    .rs-form-select:focus {
        border-color: var(--travhub-base, #e52029);
        background-color: #fff;
    }

    /* Passenger counter */
    .rs-counter {
        display: flex;
        align-items: center;
        gap: 16px;
        border: 1.5px solid #e5e7eb;
        border-radius: 8px;
        padding: 8px 14px;
        background: #f9fafb;
        width: fit-content;
    }
    .rs-counter__btn {
        background: var(--travhub-base, #e52029);
        color: #fff;
        border: none;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        font-size: 18px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        transition: opacity 0.2s;
    }
    .rs-counter__btn:hover {
        opacity: 0.85;
    }
    .rs-counter__val {
        font-size: 17px;
        font-weight: 600;
        min-width: 30px;
        text-align: center;
    }

    .rs-search-btn {
        width: 100%;
        justify-content: center;
    }

    /* Trip card */
    .rs-trip-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border: 1px solid #f0f0f0;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .rs-trip-card__badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--travhub-base, #e52029), #c0392b);
        color: #fff;
        padding: 8px 20px;
        font-size: 13px;
        font-weight: 600;
        width: 100%;
    }
    .rs-trip-card__route {
        padding: 28px 32px 20px;
    }
    .rs-trip-card__point {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 10px 0;
    }
    .rs-trip-card__dot {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .rs-trip-card__dot--from {
        background: var(--travhub-base, #e52029);
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.15);
    }
    .rs-trip-card__dot--to {
        background: #1e40af;
        box-shadow: 0 0 0 4px rgba(30, 64, 175, 0.15);
    }
    .rs-trip-card__line {
        width: 2px;
        height: 30px;
        background: linear-gradient(to bottom, var(--travhub-base, #e52029), #1e40af);
        margin-left: 6px;
        border-radius: 2px;
    }
    .rs-trip-card__label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #999;
        margin-bottom: 2px;
    }
    .rs-trip-card__point strong {
        display: block;
        font-size: 18px;
        font-weight: 700;
        color: #222;
        line-height: 1.2;
    }
    .rs-trip-card__point small {
        font-size: 13px;
        color: #888;
    }
    .rs-trip-card__meta {
        display: flex;
        gap: 0;
        border-top: 1px solid #f1f1f1;
        border-bottom: 1px solid #f1f1f1;
    }
    .rs-trip-card__meta-item {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-right: 1px solid #f1f1f1;
    }
    .rs-trip-card__meta-item:last-child {
        border-right: none;
    }
    .rs-trip-card__meta-item i {
        font-size: 22px;
        color: var(--travhub-base, #e52029);
    }
    .rs-trip-card__meta-item span {
        display: block;
        font-size: 11px;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .rs-trip-card__meta-item strong {
        font-size: 14px;
        font-weight: 700;
        color: #333;
    }
    .rs-trip-card__cta {
        padding: 24px 32px;
    }
    .rs-trip-card__info {
        font-size: 13.5px;
        color: #666;
        margin-bottom: 18px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        line-height: 1.6;
    }
    .rs-trip-card__info i {
        color: #3b82f6;
        margin-top: 2px;
    }
    .rs-trip-card__actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .rs-whatsapp-btn {
        background: linear-gradient(135deg, #22c55e, #16a34a) !important;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .rs-whatsapp-btn i {
        font-size: 18px;
    }

    /* Empty state */
    .rs-empty-state {
        background: #fff;
        border-radius: 16px;
        padding: 60px 40px;
        text-align: center;
        border: 1px solid #f0f0f0;
        box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
        margin-bottom: 30px;
    }
    .rs-empty-state__icon {
        width: 90px;
        height: 90px;
        background: rgba(229, 32, 41, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        font-size: 40px;
        color: var(--travhub-base, #e52029);
    }
    .rs-empty-state h3 {
        font-size: 26px;
        font-weight: 700;
        margin-bottom: 12px;
        color: #222;
    }
    .rs-empty-state p {
        color: #666;
        font-size: 16px;
        line-height: 1.7;
        max-width: 440px;
        margin: 0 auto 28px;
    }
    .rs-empty-state__features {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        max-width: 420px;
        margin: 0 auto;
    }
    .rs-empty-state__feature {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #555;
        font-weight: 500;
        text-align: left;
    }
    .rs-empty-state__feature i {
        color: #22c55e;
        font-size: 16px;
        flex-shrink: 0;
    }

    /* How it works */
    .rs-how-it-works {
        background: linear-gradient(135deg, #1e293b, #334155);
        border-radius: 16px;
        padding: 30px 32px;
        color: #fff;
    }
    .rs-how-it-works__title {
        font-size: 17px;
        font-weight: 700;
        margin-bottom: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .rs-how-it-works__title i {
        color: #fbbf24;
    }
    .rs-how-it-works__steps {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .rs-step {
        display: flex;
        gap: 16px;
        align-items: flex-start;
    }
    .rs-step__num {
        width: 36px;
        height: 36px;
        background: var(--travhub-base, #e52029);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        flex-shrink: 0;
    }
    .rs-step strong {
        display: block;
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
    }
    .rs-step p {
        margin: 0;
        font-size: 14px;
        color: rgba(255, 255, 255, 0.7);
        line-height: 1.6;
    }

    @media (max-width: 991px) {
        .rs-search-card {
            position: static;
        }
        .rs-trip-card__meta {
            flex-direction: column;
        }
        .rs-trip-card__meta-item {
            border-right: none;
            border-bottom: 1px solid #f1f1f1;
        }
        .rs-trip-card__meta-item:last-child {
            border-bottom: none;
        }
        .rs-empty-state__features {
            grid-template-columns: 1fr;
        }
    }
</style>
