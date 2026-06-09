<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';

    type Location = { id: number; name: string };
    type Schedule = { id: number; departure_time: string; days: string; quota: number; specific_departure_time?: string; estimated_minutes?: number; vehicle_category?: { title: string, passenger_capacity: number } };
    function isDayAvailable(daysString: string) {
        if (!date) return true; // If no date selected, show all
        const selectedDate = new Date(date);
        const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        const selectedDayName = dayNames[selectedDate.getDay()];
        return daysString.includes(selectedDayName);
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
                    <div class="rs-search-card" style="background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.05);">
                        <div class="rs-search-card__header mb-4" style="display: flex; gap: 15px; align-items: center;">
                            <i class="flaticon-bus" style="font-size: 40px; color: #e52029;"></i>
                            <div>
                                <h4 style="margin: 0; font-size: 20px; font-weight: 700;">Search Rides</h4>
                                <p style="margin: 0; font-size: 14px; color: #666;">Select your route &amp; schedule</p>
                            </div>
                        </div>

                        <!-- Shared ride notice -->
                        <div class="rs-shared-notice mb-4" style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 13px; color: #444; border-left: 4px solid #e52029;">
                            <i class="fas fa-info-circle me-1" style="color: #e52029;"></i>
                            <span>Routes &amp; schedules are predefined by the operator. You are booking a <strong>seat in a shared ride</strong>, not a private trip.</span>
                        </div>

                        <form class="rs-search-form" action="/ride-sharing" method="GET">
                            <!-- Departure Date -->
                            <div class="rs-form-group mb-3">
                                <label for="rs_page_date" class="form-label fw-bold">
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
                            <div class="rs-form-group mb-3">
                                <label for="rs_page_pickup" class="form-label fw-bold">
                                    <i class="icon-pin-2"></i> Pickup Location *
                                </label>
                                <select
                                    id="rs_page_pickup"
                                    name="pickup_location_id"
                                    bind:value={pickupLocationId}
                                    required
                                    class="form-select"
                                >
                                    <option value="" disabled>Select pickup location</option>
                                    {#each locations as loc}
                                        <option value={String(loc.id)}>{loc.name}</option>
                                    {/each}
                                </select>
                            </div>

                            <!-- Dropoff Location -->
                            <div class="rs-form-group mb-3">
                                <label for="rs_page_dropoff" class="form-label fw-bold">
                                    <i class="icon-pin-2"></i> Dropoff Location *
                                </label>
                                <select
                                    id="rs_page_dropoff"
                                    name="dropoff_location_id"
                                    bind:value={dropoffLocationId}
                                    required
                                    class="form-select"
                                >
                                    <option value="" disabled>Select destination</option>
                                    {#each locations as loc}
                                        <option value={String(loc.id)}>{loc.name}</option>
                                    {/each}
                                </select>
                            </div>

                            <!-- Passengers -->
                            <div class="rs-form-group mb-4">
                                <label for="rs_page_passengers" class="form-label fw-bold">
                                    <i class="icon-traveler-with-a-suitcase-1"></i> Passengers
                                </label>
                                <div class="d-flex align-items-center justify-content-between p-2 border rounded">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light rounded-circle"
                                        onclick={() => { if (passengers > 1) passengers--; }}
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="fw-bold fs-5">{passengers}</span>
                                    <input type="hidden" name="passengers" value={passengers} id="rs_page_passengers" />
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-light rounded-circle"
                                        onclick={() => passengers++}
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill">
                                Search Routes
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Right: Search Results -->
                <div class="col-lg-8">
                    {#if !search.pickup_location_id || !search.dropoff_location_id}
                        <!-- Empty State -->
                        <div class="text-center py-5" style="background: #fff; border-radius: 12px; border: 1px dashed #ddd;">
                            <img src="/assets/images/shapes/plane.png" alt="Travel" style="width: 100px; opacity: 0.3; margin-bottom: 20px; filter: grayscale(1);" />
                            <h4 style="color: #666;">Where to?</h4>
                            <p style="color: #999; max-width: 300px; margin: 0 auto;">Select your pickup and dropoff location on the left to see available routes.</p>
                        </div>
                    {:else if availableRoutes.length === 0}
                        <div class="text-center py-5" style="background: #fff; border-radius: 12px; border: 1px dashed #ddd;">
                            <i class="flaticon-bus" style="font-size: 60px; color: #ccc; margin-bottom: 20px; display: block;"></i>
                            <h4 style="color: #666;">No Routes Found</h4>
                            <p style="color: #999; max-width: 400px; margin: 0 auto;">We couldn't find any travel routes from <strong>{selectedPickup?.name}</strong> to <strong>{selectedDropoff?.name}</strong>. Please try another destination.</p>
                        </div>
                    {:else}
                        <div class="mb-4 d-flex justify-content-between align-items-end">
                            <div>
                                <h3 class="mb-1" style="font-weight: 700; font-size: 24px;">Available Routes</h3>
                                <p class="mb-0 text-muted">Showing routes from <strong>{selectedPickup?.name}</strong> to <strong>{selectedDropoff?.name}</strong></p>
                            </div>
                        </div>

                        {#each availableRoutes as route}
                            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                                    <h5 class="mb-0 text-white"><i class="ti ti-route me-2"></i> {route.name}</h5>
                                    <div class="d-flex align-items-center gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="badge bg-white text-primary rounded-pill px-3 py-2 fs-6">
                                            Rp {new Intl.NumberFormat('id-ID').format(route.price)} / seat
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body bg-light">
                                    {#if route.schedules.length === 0}
                                        <p class="text-muted text-center my-3">No schedules available for this route.</p>
                                    {:else}
                                        <p class="fw-bold mb-3">Available Schedules:</p>
                                        <div class="row g-3">
                                            {#each route.schedules as schedule}
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="border rounded p-3 bg-white h-100 position-relative" style="transition: all 0.3s;" onmouseenter={(e) => (e.currentTarget.style.borderColor = '#e52029')} onmouseleave={(e) => (e.currentTarget.style.borderColor = 'var(--bs-border-color)')}>
                                                        <div class="d-flex align-items-center mb-2">
                                                            <i class="ti ti-clock text-primary fs-4 me-2"></i>
                                                            <h4 class="mb-0 fw-bold">{schedule.specific_departure_time ? schedule.specific_departure_time.substring(0, 5) : '-'}</h4>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <p class="small text-muted mb-0"><i class="ti ti-calendar me-1"></i> {schedule.days}</p>
                                                            {#if schedule.estimated_minutes}
                                                                <span class="badge bg-light text-primary border"><i class="ti ti-hourglass-empty me-1"></i> {formatDuration(schedule.estimated_minutes)}</span>
                                                            {/if}
                                                        </div>
                                                        {#if schedule.vehicle_category}
                                                            <div class="mb-3 p-2 bg-light rounded d-flex align-items-center gap-2">
                                                                <i class="ti ti-car text-primary"></i>
                                                                <div class="small lh-1">
                                                                    <div class="fw-bold text-dark">{schedule.vehicle_category.title}</div>
                                                                    <div class="text-muted" style="font-size: 0.8em;">{schedule.vehicle_category.passenger_capacity} Seats Capacity</div>
                                                                </div>
                                                            </div>
                                                        {/if}
                                                        
                                                        <form action="/booking/checkout" method="GET">
                                                            <input type="hidden" name="service" value="sharing-ride" />
                                                            <input type="hidden" name="rs_route_id" value={route.id} />
                                                            <input type="hidden" name="rs_schedule_id" value={schedule.id} />
                                                            <input type="hidden" name="pickup_location_id" value={pickupLocationId} />
                                                            <input type="hidden" name="dropoff_location_id" value={dropoffLocationId} />
                                                            <input type="hidden" name="date" value={date} />
                                                            <input type="hidden" name="passengers" value={passengers} />
                                                            <input type="hidden" name="price" value={route.price} />
                                                            
                                                            <button type="submit" class="btn btn-outline-primary w-100 rounded-pill btn-sm fw-bold">
                                                                Select Seat
                                                            </button>
                                                        </form>
                                                    </div>
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
