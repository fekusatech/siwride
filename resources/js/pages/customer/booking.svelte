<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import TimePicker from '@/components/TimePicker.svelte';
    import {
        getMinPickupTime,
        formatEarliestTime,
        isPickupTimeValid,
    } from '@/lib/pickupTime';
    import { formatTime12 } from '@/lib/pickupTime';
    import { formatRupiah } from '@/lib/utils';

    /** Reactive clock — ticks every minute so earliest-time notices stay fresh. */
    let now = $state(new Date());

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
        price_per_km: string;
        image_url: string;
        vehicle_type: string;
        examples: string | null;
    }

    let {
        prefill,
        vehicleCategories = [],
        allVehicleCategories = [],
    } = $props<{
        prefill: {
            pickup: string;
            dropoff: string;
            date: string;
            time: string;
            passengers: string;
            trip_type?: string;
            return_date?: string;
            return_time?: string;
        };
        vehicleCategories: VehicleCategory[];
        allVehicleCategories: VehicleCategory[];
    }>();

    // Route form state
    let pickup = $state(prefill?.pickup || '');
    let dropoff = $state(prefill?.dropoff || '');
    let date = $state(prefill?.date || '');
    let time = $state(prefill?.time || '');
    let passengerCount = $state(parseInt(prefill?.passengers || '1'));
    let tripType = $state<'one_way' | 'round_trip'>(
        (prefill?.trip_type as 'one_way' | 'round_trip') || 'one_way',
    );
    let returnDate = $state(prefill?.return_date || '');
    let returnTime = $state(prefill?.return_time || '');

    const isRoundTrip = $derived(tripType === 'round_trip');

    let pickupAddress = $state<string>('');
    let dropoffAddress = $state<string>('');

    // Compute minimum time based on current time + buffer
    const minTime = $derived.by(() => {
        if (!date || !now) return '';
        const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;
        
        if (date === todayStr) {
            // Buffer in minutes
            const bufferMinutes = 30;
            const target = new Date(now.getTime() + bufferMinutes * 60000);
            return `${String(target.getHours()).padStart(2, '0')}:${String(target.getMinutes()).padStart(2, '0')}`;
        }
        return '';
    });

    // Compute minimum return time based on departure time + safe gap (e.g. 3 hours)
    const returnMinTime = $derived.by(() => {
        if (!isRoundTrip || !date || !time || !returnDate) return '';
        
        if (returnDate === date) {
            // If returning on the same day, enforce a safe gap
            const gapHours = 3;
            const [hours, minutes] = time.split(':').map(Number);
            const returnHours = hours + gapHours;
            if (returnHours >= 24) {
                // If it pushes to next day, maybe they shouldn't return same day, 
                // but we cap it at 23:59 for safety in the picker.
                return '23:59';
            }
            return `${String(returnHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        }
        return '';
    });

    const returnDateError = $derived.by(() => {
        if (isRoundTrip && date && returnDate && returnDate < date) {
            return 'Return date cannot be before departure date.';
        }
        return null;
    });

    const timeError = $derived.by(() => {
        if (time && !isPickupTimeValid(date, time, now)) {
            return `Please select a pickup time at least 30 minutes from now. Earliest available: ${formatEarliestTime(date, now)}`;
        }
        return null;
    });

    const returnTimeError = $derived.by(() => {
        if (isRoundTrip && date && returnDate === date && time && returnTime) {
            if (returnMinTime && returnTime < returnMinTime) {
                return `Return time must be at least ${returnMinTime}`;
            }
        }
        return null;
    });

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
        const [y, m, d] = isoDate.split('-').map(Number);
        const dateObj = new Date(y, m - 1, d);
        const months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
        return `${d} ${months[m - 1]} ${y}`;
    }

    // Dynamic Pricing state
    let estimatedPrices = $state<Record<number, number>>({});
    let isPricingLoaded = $state(false);
    let priceZoneInfo = $state<{
        pickup_zone: string | null;
        dropoff_zone: string | null;
        distance_km: number | null;
    }>({
        pickup_zone: null,
        dropoff_zone: null,
        distance_km: null,
    });

    // Dynamic route info
    let exactDistanceStr = $state<string | null>(null);
    let exactDurationStr = $state<string | null>(null);

    let zoneError = $state<string | null>(null);

    /** Geocodes an address string and returns {lat, lng} or null. */
    async function geocodeAddress(
        address: string,
    ): Promise<{ lat: number; lng: number } | null> {
        return new Promise((resolve) => {
            try {
                const geocoder = new (window as any).google.maps.Geocoder();
                geocoder.geocode(
                    {
                        address,
                        region: 'id',
                        bounds: {
                            south: -8.95,
                            west: 114.4,
                            north: -8.06,
                            east: 115.72,
                        },
                    },
                    (results: any, status: string) => {
                        if (status === 'OK' && results && results[0]) {
                            const loc = results[0].geometry.location;
                            resolve({ lat: loc.lat(), lng: loc.lng() });
                        } else {
                            resolve(null);
                        }
                    },
                );
            } catch {
                resolve(null);
            }
        });
    }

    /** Calls the estimate-price API and stores the result. */
    async function fetchPriceEstimate(
        pLat: number,
        pLng: number,
        dLat: number,
        dLng: number,
        exactDistanceKm: number | null = null,
    ) {
        try {
            let url = `/booking/estimate-price?pickup_latitude=${pLat}&pickup_longitude=${pLng}&dropoff_latitude=${dLat}&dropoff_longitude=${dLng}`;
            if (exactDistanceKm !== null) {
                url += `&exact_distance_km=${exactDistanceKm}`;
            }
            const res = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!res.ok) {
                if (res.status === 422) {
                    const errData = await res.json();
                    zoneError = errData.error;
                }
                return;
            }
            zoneError = null;
            const data = await res.json();
            priceZoneInfo = {
                pickup_zone: data.pickup_zone,
                dropoff_zone: data.dropoff_zone,
                distance_km: data.distance_km,
            };

            const newPrices: Record<number, number> = {};
            if (data.prices) {
                data.prices.forEach((p: any) => {
                    if (p.estimated_price != null) {
                        newPrices[p.id] = p.estimated_price;
                    }
                });
            }
            estimatedPrices = newPrices;
        } catch {
            // silently ignore
        } finally {
            isPricingLoaded = true;
        }
    }

    /** Geocodes both addresses then fetches the price estimate. */
    async function geocodeAndEstimate() {
        if (!pickup || !dropoff) {
            isPricingLoaded = true;
            return;
        }

        isPricingLoaded = false;

        // Wait for google maps to be available
        let waited = 0;
        while (typeof (window as any).google === 'undefined' && waited < 5000) {
            await new Promise((r) => setTimeout(r, 300));
            waited += 300;
        }
        if (typeof (window as any).google === 'undefined') {
            isPricingLoaded = true;
            return;
        }

        const [pickupCoords, dropoffCoords] = await Promise.all([
            geocodeAddress(pickup),
            geocodeAddress(dropoff),
        ]);

        if (pickupCoords && dropoffCoords) {
            let exactDistanceKm: number | null = null;
            try {
                const service = new (
                    window as any
                ).google.maps.DistanceMatrixService();
                const dmResponse = await new Promise<any>((resolve, reject) => {
                    service.getDistanceMatrix(
                        {
                            origins: [pickupCoords],
                            destinations: [dropoffCoords],
                            travelMode: 'DRIVING',
                        },
                        (response: any, status: string) => {
                            if (status === 'OK') resolve(response);
                            else reject(status);
                        },
                    );
                });
                const element = dmResponse.rows[0].elements[0];
                if (element.status === 'OK') {
                    exactDistanceKm = element.distance.value / 1000;
                    exactDistanceStr = element.distance.text;
                    exactDurationStr = element.duration.text;
                }
            } catch (err) {
                // ignore
            }
            await fetchPriceEstimate(
                pickupCoords.lat,
                pickupCoords.lng,
                dropoffCoords.lat,
                dropoffCoords.lng,
                exactDistanceKm,
            );
        } else {
            isPricingLoaded = true;
        }
    }

    // Call geocode whenever pickup or dropoff changes (debounced)
    let debounceTimer: ReturnType<typeof setTimeout>;
    $effect(() => {
        const p = pickup;
        const d = dropoff;
        if (p && d) {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                geocodeAndEstimate();
            }, 1000);
        }
    });

    function getBasePrice(category: VehicleCategory): number {
        if (!pickup || !dropoff) return 0;
        if (estimatedPrices[category.id] !== undefined) {
            return estimatedPrices[category.id];
        }
        return 0; // Default to 0 instead of average if not loaded
    }

    // Default route info
    const routeInfo = {
        distance: '0 km',
        travelTime: '0 min',
        fromPrice: 0,
    };

    function handleRouteSearch(e: Event) {
        e.preventDefault();
        if (!pickup || !dropoff || !date) return;
        // Validate time when today is selected
        if (timeError) {
            alert(timeError);
            return;
        }
        
        if (returnDateError) {
            alert(returnDateError);
            return;
        }
        if (returnTimeError) {
            alert(returnTimeError);
            return;
        }

        // Re-fetch page with new params to filter vehicles
        router.get(
            '/booking/airport-transfer',
            {
                pickup,
                dropoff,
                date,
                time,
                passengers: String(passengerCount),
                trip_type: tripType,
                return_date: returnDate,
                return_time: returnTime,
            },
            { preserveScroll: true, replace: true },
        );
    }

    function selectVehicle(category: VehicleCategory) {
        if (timeError) {
            alert(timeError);
            return;
        }
        if (returnDateError) {
            alert(returnDateError);
            return;
        }
        if (returnTimeError) {
            alert(returnTimeError);
            return;
        }
        router.get('/booking/checkout', {
            vehicle_category_id: String(category.id),
            pickup,
            dropoff,
            date,
            time,
            passengers: String(passengerCount),
            trip_type: tripType,
            return_date: returnDate,
            return_time: returnTime,
        });
    }

    // ── Track Booking ────────────────────────────────────────────
    const authCustomer = $derived((page.props as any).auth?.customer ?? null);

    let trackCode = $state('');
    let trackResult = $state<null | {
        found: boolean;
        order?: any;
        message?: string;
    }>(null);
    let isTracking = $state(false);

    const formatStatus = (status: string) => {
        switch (status) {
            case 'pending':
                return { text: 'Pending', bg: '#fff3cd', color: '#856404' };
            case 'confirmed':
                return { text: 'Confirmed', bg: '#d1ecf1', color: '#0c5460' };
            case 'completed':
                return { text: 'Completed', bg: '#d4edda', color: '#155724' };
            case 'cancelled':
                return { text: 'Cancelled', bg: '#f8d7da', color: '#721c24' };
            default:
                return { text: status, bg: '#e2e3e5', color: '#383d41' };
        }
    };

    async function trackBooking(e: Event) {
        e.preventDefault();
        const code = trackCode.trim().toUpperCase();
        if (!code) return;
        isTracking = true;
        trackResult = null;
        try {
            const res = await fetch(
                `/booking/track?code=${encodeURIComponent(code)}`,
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );
            if (res.ok) {
                const data = await res.json();
                trackResult = { found: true, order: data.order };
            } else {
                trackResult = {
                    found: false,
                    message:
                        'Booking not found. Please check your booking code.',
                };
            }
        } catch {
            trackResult = {
                found: false,
                message: 'Unable to fetch booking. Please try again.',
            };
        } finally {
            isTracking = false;
        }
    }

    let clockInterval: ReturnType<typeof setInterval>;

    onMount(() => {
        document.body.classList.add('custom-cursor');

        // Tick every 60 seconds so the "Earliest pickup" notice stays accurate
        // while the customer fills in the form — no page refresh needed.
        clockInterval = setInterval(() => {
            now = new Date();
        }, 60_000);

        geocodeAndEstimate();
    });

    onDestroy(() => {
        document.body.classList.remove('custom-cursor');
        clearInterval(clockInterval);
    });
</script>

<AppHead title="Book a Transfer - Siwride" />
<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- Simple Service Header -->
    <div class="service-simple-header" style="padding: 40px 0 10px; background: #f4f7f9; border-bottom: 1px solid #eaeef2;">
        <div class="container">
            <h2 style="font-size: 26px; font-weight: 800; color: #1e293b; margin: 0;">Airport Transfer Booking</h2>
            <p style="color: #64748b; margin: 6px 0 0; font-size: 15px;">Book a private vehicle for your airport transfer or city trip.</p>
        </div>
    </div>

    <!-- Main Booking Section -->
    <section
        class="booking-main"
        style="padding: 40px 0 10px; background: #f4f7f9;"
    >
        <div class="container">
            <!-- SECTION A: Route Form -->
            <div class="booking-card mb-4">
                <div class="booking-card__header">
                    <div class="step-badge">1</div>
                    <div>
                        <h4 class="booking-card__title">
                            Your Transfer Details
                        </h4>
                        <p class="booking-card__subtitle">
                            Enter your route and travel information
                        </p>
                    </div>
                </div>

                <div class="booking-card__body">
                    <!-- Error / Notice -->
                    {#if zoneError}
                        <div
                            class="alert alert-danger p-3 mb-4 rounded-3 border-0"
                        >
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {zoneError}
                        </div>
                    {/if}

                    <!-- One-Way / Round-Trip Toggle -->
                    <div class="trip-type-toggle">
                        <button
                            type="button"
                            class="trip-type-btn {tripType === 'one_way' ? 'trip-type-btn--active' : ''}"
                            onclick={() => (tripType = 'one_way')}
                        >
                            <i class="fas fa-long-arrow-alt-right"></i>
                            One-Way
                        </button>
                        <button
                            type="button"
                            class="trip-type-btn {tripType === 'round_trip' ? 'trip-type-btn--active' : ''}"
                            onclick={() => (tripType = 'round_trip')}
                        >
                            <i class="fas fa-exchange-alt"></i>
                            Round-Trip
                        </button>
                        {#if isRoundTrip}
                            <span class="round-trip-info-badge">
                                <i class="fas fa-info-circle"></i>
                                Return route is automatically reversed
                            </span>
                        {/if}
                    </div>

                    <form onsubmit={handleRouteSearch}>
                        <!-- Row 1: Address fields — full width each -->
                        <div class="route-form-row route-form-row--locations">
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt text-danger"
                                    ></i>
                                    Pickup Location *
                                </label>
                                <LocationSearchInput
                                    id="booking_pickup"
                                    bind:value={pickup}
                                    bind:fullAddress={pickupAddress}
                                    placeholder="Hotel, airport, landmark..."
                                    variant="premium"
                                    required
                                />
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <i
                                        class="fas fa-flag-checkered text-success"
                                    ></i>
                                    Drop-off Location *
                                </label>
                                <LocationSearchInput
                                    id="booking_dropoff"
                                    bind:value={dropoff}
                                    bind:fullAddress={dropoffAddress}
                                    placeholder="Beach, temple, area..."
                                    variant="premium"
                                    required
                                />
                            </div>
                        </div>

                        <!-- Row 2: Date | Time | Passengers | Search -->
                        <div class="route-form-row route-form-row--search">
                            <div class="form-group form-group--date">
                                <label class="form-label">
                                    <i
                                        class="fas fa-calendar-alt"
                                        style="color: var(--travhub-base);"
                                    ></i>
                                    Departure Date *
                                </label>
                                <DatePicker
                                    id="booking_date"
                                    bind:value={date}
                                    placeholder="Select date"
                                    required
                                />
                            </div>
                            <div class="form-group form-group--time">
                                <label class="form-label">
                                    <i
                                        class="fas fa-clock"
                                        style="color: var(--travhub-base);"
                                    ></i>
                                    Departure Time *
                                </label>
                                <TimePicker
                                    id="booking_time"
                                    bind:value={time}
                                    placeholder="Select time"
                                    required
                                    {minTime}
                                />
                                {#if minTime}
                                    <p class="booking-time-notice">
                                        <i class="fas fa-info-circle"></i>
                                        Earliest pickup today:
                                        <strong
                                            >{formatEarliestTime(
                                                date,
                                                now,
                                            )}</strong
                                        >
                                    </p>
                                {/if}
                                {#if timeError}
                                    <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {timeError}</p>
                                {/if}
                            </div>
                            {#if isRoundTrip}
                                <div class="form-group form-group--date">
                                    <label class="form-label">
                                        <i
                                            class="fas fa-calendar-check"
                                            style="color: var(--travhub-base);"
                                        ></i>
                                        Return Date *
                                    </label>
                                    <DatePicker
                                        id="booking_return_date"
                                        bind:value={returnDate}
                                        placeholder="Select return date"
                                        required={isRoundTrip}
                                        minDate={date}
                                    />
                                    {#if returnDateError}
                                        <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {returnDateError}</p>
                                    {/if}
                                </div>
                            {/if}
                            
                            {#if isRoundTrip}
                                <div class="form-group form-group--time">
                                    <label class="form-label">
                                        <i
                                            class="fas fa-clock"
                                            style="color: #10b981;"
                                        ></i>
                                        Return Time *
                                    </label>
                                    <TimePicker
                                        id="booking_return_time"
                                        bind:value={returnTime}
                                        placeholder="Select return time"
                                        required={isRoundTrip}
                                        minTime={returnMinTime}
                                    />
                                    {#if returnTimeError}
                                        <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {returnTimeError}</p>
                                    {/if}
                                </div>
                            {/if}
                            <div class="form-group">
                                <label class="form-label">
                                    <i
                                        class="fas fa-users"
                                        style="color: var(--travhub-base);"
                                    ></i>
                                    Passengers
                                </label>
                                <div class="passenger-counter">
                                    <button
                                        type="button"
                                        class="counter-btn"
                                        onclick={() => {
                                            if (passengerCount > 1)
                                                passengerCount--;
                                        }}
                                        aria-label="Decrease passengers"
                                    >
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="counter-value">
                                        <i class="fas fa-user counter-icon"></i>
                                        {passengerCount}
                                    </span>
                                    <button
                                        type="button"
                                        class="counter-btn"
                                        onclick={() => {
                                            if (passengerCount < 50)
                                                passengerCount++;
                                        }}
                                        aria-label="Increase passengers"
                                    >
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group form-group--action">
                                <label
                                    class="form-label form-label--hidden"
                                    id="btnlabel"
                                    aria-hidden="true">&nbsp;</label 
                                >
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
                            <h4 class="booking-card__title">
                                Route Information
                            </h4>
                            <p class="booking-card__subtitle">
                                Estimated details for your transfer
                            </p>
                        </div>
                    </div>
                    <div class="booking-card__body">
                        <div class="route-info-card">
                            <!-- Dynamic Pricing / Info Content -->
                            <div class="route-info-route">
                                <div class="route-point">
                                    <div
                                        class="route-dot route-dot--from"
                                    ></div>
                                    <div class="route-point-text">
                                        <span class="route-label">FROM</span>
                                        <span class="route-address"
                                            >{pickup}</span
                                        >
                                        {#if pickupAddress && pickupAddress !== pickup}
                                            <small
                                                class="route-full-address text-muted d-block mt-1"
                                                >{pickupAddress}</small
                                            >
                                        {/if}
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
                                        <span class="route-address"
                                            >{dropoff}</span
                                        >
                                        {#if dropoffAddress && dropoffAddress !== dropoff}
                                            <small
                                                class="route-full-address text-muted d-block mt-1"
                                                >{dropoffAddress}</small
                                            >
                                        {/if}
                                    </div>
                                </div>
                            </div>
                            <div class="route-info-stats">
                                <div class="route-stat">
                                    <i class="fas fa-road"></i>
                                    <div>
                                        <span class="stat-label">Distance</span>
                                        <span class="stat-value">
                                            {#if !isPricingLoaded}
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                            {:else if exactDistanceStr}
                                                {exactDistanceStr}
                                            {:else if priceZoneInfo.distance_km}
                                                {priceZoneInfo.distance_km} km
                                            {:else}
                                                {routeInfo.distance}
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                <div class="route-stat">
                                    <i class="fas fa-clock"></i>
                                    <div>
                                        <span class="stat-label"
                                            >Travel Time</span
                                        >
                                        <span class="stat-value">
                                            {#if !isPricingLoaded}
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                            {:else if exactDurationStr}
                                                {exactDurationStr}
                                            {:else}
                                                {routeInfo.travelTime}
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                <div class="route-stat">
                                    <i class="fas fa-tag"></i>
                                    <div>
                                        <span class="stat-label"
                                            >Price From</span
                                        >
                                        <span
                                            class="stat-value stat-value--price"
                                        >
                                            {#if !isPricingLoaded}
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                            {:else if filteredCategories.length > 0}
                                                {formatRupiah(
                                                    getBasePrice(
                                                        filteredCategories[0],
                                                    ),
                                                )}
                                            {:else}
                                                {formatRupiah(
                                                    routeInfo.fromPrice,
                                                )}
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                {#if date}
                                    <div class="route-stat">
                                        <i class="fas fa-calendar-check"></i>
                                        <div>
                                            <span class="stat-label"
                                                >Transfer Date</span
                                            >
                                            <span class="stat-value"
                                                >{formatDate(date)}
                                                {time ? ' · ' + time : ''}</span
                                            >
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
                            <h5>
                                No vehicles available for {passengerCount} passengers
                            </h5>
                            <p>
                                Please reduce the passenger count or contact us
                                for a custom arrangement.
                            </p>
                        </div>
                    {:else}
                        <div class="vehicle-grid">
                            {#each filteredCategories as category}
                                <div class="vehicle-card">
                                    <div class="vehicle-card__image">
                                        <img
                                            src={category.image_url}
                                            alt={category.title}
                                        />
                                        <!-- {#if category.vehicle_type === 'premium'}
                                            <div class="vehicle-badge vehicle-badge--premium">Premium</div>
                                        {:else if category.vehicle_type === 'special'}
                                            <div class="vehicle-badge vehicle-badge--eco">Eco</div>
                                        {/if} -->
                                    </div>
                                    <div class="vehicle-card__body">
                                        <div class="vehicle-card__title-row">
                                            <h5 class="vehicle-card__title">
                                                {category.title}
                                            </h5>
                                            {#if category.examples}
                                                <div
                                                    class="vehicle-tooltip-wrap"
                                                >
                                                    <button
                                                        type="button"
                                                        class="vehicle-tooltip-btn"
                                                        aria-label="Example vehicles"
                                                    >
                                                        <i
                                                            class="fas fa-info-circle"
                                                        ></i>
                                                    </button>
                                                    <div
                                                        class="vehicle-tooltip"
                                                        role="tooltip"
                                                    >
                                                        <span
                                                            class="vehicle-tooltip__label"
                                                            >Example vehicles</span
                                                        >
                                                        <span
                                                            class="vehicle-tooltip__value"
                                                            >{category.examples}</span
                                                        >
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                        <div class="vehicle-card__specs">
                                            <span class="spec-item">
                                                <i class="fas fa-users"></i>
                                                {category.passenger_capacity ??
                                                    '—'} Passengers
                                            </span>
                                            <span class="spec-item">
                                                <i class="fas fa-suitcase"></i>
                                                {category.luggage_capacity ??
                                                    '—'} Luggage
                                            </span>
                                        </div>
                                        {#if category.advantages && category.advantages.length > 0}
                                            <ul
                                                class="vehicle-card__advantages"
                                            >
                                                {#each category.advantages.slice(0, 3) as adv}
                                                    <li>
                                                        <i
                                                            class="fas fa-check-circle"
                                                        ></i>
                                                        {adv}
                                                    </li>
                                                {/each}
                                            </ul>
                                        {/if}
                                    </div>
                                    <div class="vehicle-card__footer">
                                        <div class="vehicle-card__price">
                                            {#if isRoundTrip}
                                                <span class="price-from">Round-Trip</span>
                                            {:else}
                                                <span class="price-from">From</span>
                                            {/if}
                                            {#if !isPricingLoaded}
                                                <span
                                                    class="price-amount price-calculating"
                                                    ><i
                                                        class="fas fa-spinner fa-spin"
                                                    ></i></span
                                                >
                                            {:else}
                                                <span class="price-amount"
                                                    >{formatRupiah(
                                                        isRoundTrip
                                                            ? getBasePrice(category) * 2
                                                            : getBasePrice(category),
                                                    )}</span
                                                >
                                            {/if}
                                            {#if isRoundTrip && isPricingLoaded && getBasePrice(category) > 0}
                                                <span class="price-each-way">{formatRupiah(getBasePrice(category))} × 2</span>
                                            {/if}
                                        </div>
                                        <button
                                            type="button"
                                            class="select-btn"
                                            onclick={() =>
                                                selectVehicle(category)}
                                            disabled={!pickup ||
                                                !dropoff ||
                                                !date ||
                                                !!zoneError}
                                        >
                                            Select
                                        </button>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    {#if !pickup || !dropoff || !date}
                        <div class="fill-route-notice">
                            <i class="fas fa-info-circle"></i>
                            Please fill in your pickup location, destination, and
                            date above to select a vehicle.
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <!-- Track Booking + Order History Section -->
    <section style="padding: 10px 0 100px; background: #f4f7f9;">
        <div class="container">
            <div class="track-history-grid">
                <!-- Track Booking Card -->
                <div class="booking-card track-card">
                    <div class="booking-card__header">
                        <div class="track-icon-badge">
                            <i class="fas fa-search-location"></i>
                        </div>
                        <div>
                            <h4 class="booking-card__title">
                                Track Your Booking
                            </h4>
                            <p class="booking-card__subtitle">
                                Enter your booking code to check the status
                            </p>
                        </div>
                    </div>
                    <div class="booking-card__body">
                        <form class="track-form" onsubmit={trackBooking}>
                            <div class="track-input-row">
                                <input
                                    id="track_booking_code"
                                    type="text"
                                    class="track-input"
                                    bind:value={trackCode}
                                    placeholder="e.g. SWABC123"
                                    maxlength="20"
                                    oninput={(e) => {
                                        trackCode = e.currentTarget.value
                                            .toUpperCase()
                                            .replace(/[^A-Z0-9]/g, '');
                                    }}
                                />
                                <button
                                    type="submit"
                                    class="track-btn"
                                    disabled={isTracking || !trackCode.trim()}
                                >
                                    {#if isTracking}
                                        <i class="fas fa-spinner fa-spin"></i> Tracking...
                                    {:else}
                                        <i class="fas fa-search"></i> Track
                                    {/if}
                                </button>
                            </div>
                        </form>

                        {#if trackResult}
                            {#if trackResult.found && trackResult.order}
                                {@const order = trackResult.order}
                                {@const statusInfo = formatStatus(order.status)}
                                <div class="track-result">
                                    <div class="track-result__header">
                                        <span class="track-booking-code"
                                            >{order.booking_code}</span
                                        >
                                        <span
                                            class="track-status-badge"
                                            style="background-color: {statusInfo.bg}; color: {statusInfo.color};"
                                        >
                                            {statusInfo.text}
                                        </span>
                                    </div>
                                    <div class="track-result__body">
                                        <div class="track-info-row">
                                            <i class="fas fa-user"></i>
                                            <span>{order.customer_name}</span>
                                        </div>
                                        <div class="track-info-row">
                                            <i
                                                class="fas fa-map-marker-alt text-danger"
                                            ></i>
                                            <span class="track-addr"
                                                >{order.pickup_address}</span
                                            >
                                        </div>
                                        <div class="track-info-row">
                                            <i
                                                class="fas fa-flag-checkered text-success"
                                            ></i>
                                            <span class="track-addr"
                                                >{order.dropoff_address}</span
                                            >
                                        </div>
                                        <div class="track-info-row">
                                            <i
                                                class="fas fa-calendar-alt"
                                                style="color: var(--travhub-base);"
                                            ></i>
                                            <span
                                                >{order.date} · {order.time
                                                    ? formatTime12(order.time)
                                                    : '—'}</span
                                            >
                                        </div>
                                        {#if order.driver_name}
                                            <div class="track-info-row">
                                                <i
                                                    class="fas fa-id-badge"
                                                    style="color: var(--travhub-base);"
                                                ></i>
                                                <span
                                                    >Driver: <strong
                                                        >{order.driver_name}</strong
                                                    ></span
                                                >
                                            </div>
                                        {/if}
                                    </div>
                                    <a
                                        href="/booking/{order.booking_code}"
                                        class="track-detail-link"
                                    >
                                        View Full Details <i
                                            class="fas fa-chevron-right"
                                        ></i>
                                    </a>
                                </div>
                            {:else}
                                <div class="track-not-found">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {trackResult.message}
                                </div>
                            {/if}
                        {/if}
                    </div>
                </div>

                <!-- Order History Shortcut -->
                <div class="history-shortcut-card">
                    {#if authCustomer}
                        <div class="history-card history-card--loggedin">
                            <div class="booking-card__header">

                                <div class="history-card__info">
                                    <p class="history-card__greeting">
                                        Hello, <strong
                                            >{authCustomer.name.split(
                                                ' ',
                                            )[0]}</strong
                                        > 👋
                                    </p>
                                    <p class="history-card__sub">
                                        View all your past and upcoming rides in one
                                        place.
                                    </p>
                                </div>
                            </div>
                            <div class="booking-card__body"> 
                                <a
                                    href="/customer/profile"
                                    class="history-card__btn"
                                >
                                    <i class="fas fa-history"></i> My Order History
                                </a>
                            </div>
                        </div>
                    {:else}
                        <div class="history-card history-card--guest">
                            <div class="booking-card__header">
                                <div class="track-icon-badge">
                                    <i class="fas fa-clipboard-list"></i>
                                </div>
                                <div>
                                    <h4 class="booking-card__title">
                                        Track all your rides
                                    </h4>
                                    <p class="booking-card__subtitle">
                                        Log in to your account to access your full
                                    order history.
                                    </p>
                                </div>
                            </div>
                            <div class="booking-card__body"> 
                                <div class="history-card__actions">
                                    <a
                                        href="/customer/login"
                                        class="history-btn history-btn--primary"
                                    >
                                        <i class="fas fa-sign-in-alt"></i> Log In
                                    </a>
                                    <a
                                        href="/customer/register"
                                        class="history-btn history-btn--secondary"
                                    >
                                        <i class="fas fa-user-plus"></i> Register
                                    </a>
                                </div>
                            </div>
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
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        overflow: visible;
    }
    .booking-card__header {
        width: 100%;
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px 28px;
        border-bottom: 1px solid #f0f4f8;
    }
    .step-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--travhub-base, #e52029);
        color: #fff;
        font-size: 16px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .booking-card__title {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .booking-card__subtitle {
        font-size: 13px;
        color: #64748b;
        margin: 2px 0 0;
    }
    .booking-card__body {
        padding: 24px 28px;
    }
    @media (max-width: 768px) {
        .booking-card__header,
        .booking-card__body {
            padding: 16px;
        }
    }

    /* ── Route Form — Two rows ── */

    /* Row 1: Pickup | Drop-off (equal halves, full width) */
    .route-form-row--locations {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 16px;
    }

    .form-group--time {
        max-width: 250px;
        flex: 1;
    }
    .form-group--date {
        min-width: 250px;
        flex: 1;
    }

    /* Row 2: Date | Time | Passengers | Button (compact horizontal) */
    .route-form-row--search {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-start; /* changed from flex-end to allow errors to grow downwards */
    }
    .route-form-row--search > .form-group {
        flex: 1;
        min-width: 200px;
    }
    .route-form-row--search > .form-group--action {
        flex: 0 0 auto;
    }

    /* Tablet: let row 2 wrap to 2 columns */
    @media (max-width: 1024px) {
        .route-form-row--search > .form-group {
            min-width: calc(50% - 16px);
        }
        /* .form-group--action {
            grid-column: span 2;
        } */
        /* Button is now on its own row — no label offset needed */
        .search-btn {
            margin-top: 0;
        }
    }

    /* Mobile: everything stacks */
    @media (max-width: 640px) {
        .route-form-row--locations {
            grid-template-columns: 1fr;
        }
        .route-form-row--search > .form-group {
            min-width: 100%;
            max-width: none;
        }
        .form-group--action {
            grid-column: span 1;
        }
        /* Cancel the label-offset margin when stacked */
        .search-btn {
            margin-top: 0 !important;
        }
        #btnlabel {
            display: none;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .form-group--action {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
    }
    .form-label {
        font-size: 11px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
        /* Fixed label height so all controls' inputs start at the same vertical position */
        min-height: 18px;
    }
    .form-label--hidden {
        visibility: hidden;
    }

    /* ── Passenger Counter — compact ── */
    .passenger-counter {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 13px 12px; /* same vertical padding as .dp-input / .tp-input */
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
        box-sizing: border-box;
    }
    .counter-btn {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        border: 1.5px solid #cbd5e1;
        background: #fff;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .counter-btn:hover {
        border-color: var(--travhub-base);
        color: var(--travhub-base);
    }
    .counter-value {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        flex: 1;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 5px;
    }
    .counter-icon {
        color: #64748b;
        font-size: 13px;
    }

    /* ── Search Button ── */
    .search-btn {
        width: 100%;
        padding: 13px 20px; /* same vertical padding as .dp-input / .tp-input */
        background: var(--travhub-base);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: all 0.3s;
        box-shadow: 0 4px 16px rgba(229, 32, 41, 0.25);
        white-space: nowrap;
        box-sizing: border-box;
        /* Offset by label height + gap so the button face aligns with the inputs */
        /* margin-top: calc(18px + 6px); */
        margin-top: 0;
    }
    .search-btn:hover {
        background: #111;
        transform: translateY(-1px);
    }

    /* ── Route Info Card ── */
    .route-info-card {
        display: flex;
        gap: 28px;
        align-items: stretch;
        flex-wrap: wrap;
    }
    .route-info-route {
        flex: 1;
        min-width: 220px;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .route-full-address {
        font-size: 11px;
        letter-spacing: 0.2px;
        line-height: 1.4;
    }

    /* ≤450px: stack route + stats vertically */
    @media (max-width: 450px) {
        .route-info-card {
            flex-direction: column;
            gap: 16px;
        }
        .route-info-route {
            min-width: 0;
            flex: none;
        }
        .route-info-stats {
            flex: none;
            width: 100%;
        }
    }
    .route-point {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .route-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .route-dot--from {
        background: var(--travhub-base);
    }
    .route-dot--to {
        background: #10b981;
    }
    .route-point-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .route-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #94a3b8;
    }
    .route-address {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        line-height: 1.4;
    }
    .route-connector {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 4px 0 4px 5px;
    }
    .route-line-v {
        width: 2px;
        height: 14px;
        background: repeating-linear-gradient(
            to bottom,
            #cbd5e1 0,
            #cbd5e1 4px,
            transparent 4px,
            transparent 8px
        );
    }
    .route-car-badge {
        background: #f1f5f9;
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 12px;
        color: #64748b;
    }

    /* ── Route Stats ── */
    .route-info-stats {
        flex: 2;
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #eef2f5;
        overflow: hidden;
    }
    .route-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 16px 20px;
    }
    .route-stat i {
        font-size: 20px;
        color: var(--travhub-base);
        flex-shrink: 0;
    }
    .route-stat div {
        display: flex;
        flex-direction: column;
        gap: 1px;
    }
    .stat-label {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .stat-value {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .stat-value--price {
        color: var(--travhub-base);
    }

    /* Vertical dividers between cells on desktop — right border on all but last */
    .route-stat:not(:last-child) {
        border-right: 1px solid #e2e8f0;
    }

    /* Below 1200px: 2×2 grid */
    @media (max-width: 1200px) {
        .route-info-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        /* Reset right borders, use bottom borders for row separation */
        .route-stat:not(:last-child) {
            border-right: none;
        }
        /* Right border on odd cells (left column) */
        .route-stat:nth-child(odd) {
            border-right: 1px solid #e2e8f0;
        }
        /* Bottom border on top row cells */
        .route-stat:nth-child(-n + 2) {
            border-bottom: 1px solid #e2e8f0;
        }
    }

    /* Mobile: single column */
    @media (max-width: 768px) {
        .route-info-stats {
            grid-template-columns: 1fr;
            padding: 0;
        }
        .route-stat {
            padding: 12px 14px;
            gap: 10px;
        }
        .route-stat:nth-child(odd) {
            border-right: none;
        }
        .route-stat:nth-child(-n + 2) {
            border-bottom: none;
        }
        .route-stat:not(:last-child) {
            border-bottom: 1px solid #e2e8f0;
        }
        .route-stat i {
            font-size: 16px;
        }
        .stat-label {
            font-size: 10px;
        }
        .stat-value {
            font-size: 13px;
        }
    }

    /* ≤450px: 2×2 grid (Distance+Travel Time / Price From+Transfer Date) */
    @media (max-width: 450px) {
        .route-info-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        /* Reset single-column bottom borders */
        .route-stat:not(:last-child) {
            border-bottom: none;
            border-right: none;
        }
        /* Right border on left-column cells */
        .route-stat:nth-child(odd) {
            border-right: 1px solid #e2e8f0;
        }
        /* Bottom border between the two rows */
        .route-stat:nth-child(-n + 2) {
            border-bottom: 1px solid #e2e8f0;
        }
    }

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
        box-shadow: 0 12px 35px rgba(229, 32, 41, 0.1);
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
    .vehicle-card:hover .vehicle-card__image img {
        transform: scale(1.05);
    }
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
    .vehicle-badge--premium {
        background: #fef3c7;
        color: #92400e;
    }
    .vehicle-badge--eco {
        background: #d1fae5;
        color: #065f46;
    }
    .vehicle-card__body {
        padding: 20px;
        flex: 1;
    }
    .vehicle-card__title {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 12px;
    }

    /* ── Vehicle title row + tooltip ── */
    .vehicle-card__title-row {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        margin-bottom: 12px;
    }
    .vehicle-card__title-row .vehicle-card__title {
        margin-bottom: 0;
        flex: 1;
    }
    .vehicle-tooltip-wrap {
        position: relative;
        flex-shrink: 0;
        margin-top: 3px; /* optical alignment with title baseline */
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
    .vehicle-tooltip-btn:hover {
        color: var(--travhub-base);
    }
    .vehicle-tooltip {
        display: none;
        position: absolute;
        top: calc(100% + 8px);
        right: 0;
        z-index: 100;
        background: #1e293b;
        color: #fff;
        border-radius: 10px;
        padding: 10px 14px;
        min-width: 180px;
        max-width: 240px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        pointer-events: none;
        /* Arrow */
        &::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 6px;
            border-left: 6px solid transparent;
            border-right: 6px solid transparent;
            border-bottom: 6px solid #1e293b;
        }
    }
    .vehicle-tooltip__label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
        margin-bottom: 4px;
    }
    .vehicle-tooltip__value {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #f1f5f9;
        line-height: 1.4;
    }
    /* Show on hover/focus */
    .vehicle-tooltip-wrap:hover .vehicle-tooltip,
    .vehicle-tooltip-btn:focus + .vehicle-tooltip {
        display: block;
    }
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
    .spec-item i {
        color: var(--travhub-base);
        font-size: 14px;
    }
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
    .vehicle-card__advantages li i {
        color: #10b981;
        font-size: 12px;
    }
    .vehicle-card__footer {
        padding: 16px 20px;
        border-top: 1px solid #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #fafbfc;
    }
    .vehicle-card__price {
        display: flex;
        flex-direction: column;
    }
    .price-from {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 600;
        text-transform: uppercase;
    }
    .price-amount {
        font-size: 24px;
        font-weight: 800;
        color: #1e293b;
        line-height: 1;
    }
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
    .select-btn:hover:not(:disabled) {
        background: #111;
    }
    .select-btn:disabled {
        background: #cbd5e1;
        cursor: not-allowed;
    }

    /* ── Notices ── */
    .no-vehicles {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .no-vehicles i {
        font-size: 48px;
        margin-bottom: 16px;
        display: block;
    }
    .no-vehicles h5 {
        font-size: 18px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }
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
    .fill-route-notice i {
        color: #f59e0b;
        font-size: 16px;
    }
    .booking-time-notice {
        margin-top: 1px;
        font-size: 11px;
        color: #92400e;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 0;
    }
    .booking-time-notice i {
        color: #d97706;
        font-size: 11px;
    }
    .booking-time-notice strong {
        font-weight: 800;
        color: #78350f;
    }

    /* ── Track + History Grid ── */
    .track-history-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-top: 24px;
    }
    @media (max-width: 900px) {
        .track-history-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Track icon badge */
    .track-icon-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--travhub-base);
        color: #fff;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* Track Form */
    .track-form {
        margin-bottom: 0;
    }
    .track-input-row {
        display: flex;
        gap: 10px;
    }
    .track-input {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        background: #f8fafc;
        color: #1e293b;
        transition: border-color 0.2s;
        outline: none;
    }
    .track-input:focus {
        border-color: var(--travhub-base);
        background: #fff;
    }
    .track-input::placeholder {
        font-weight: 400;
        letter-spacing: 0.5px;
        color: #94a3b8;
    }
    .track-btn {
        padding: 12px 22px;
        background: var(--travhub-base);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        display: flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(229, 32, 41, 0.25);
    }
    .track-btn:hover:not(:disabled) {
        background: #111;
        transform: translateY(-1px);
    }
    .track-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Track Result */
    .track-result {
        margin-top: 20px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        animation: fadeInUp 0.3s ease;
    }
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .track-result__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: #1e293b;
        gap: 10px;
    }
    .track-booking-code {
        font-size: 15px;
        font-weight: 800;
        color: #fff;
        letter-spacing: 1.5px;
    }
    .track-status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        flex-shrink: 0;
    }
    .track-result__body {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .track-info-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: #334155;
    }
    .track-info-row i {
        width: 16px;
        flex-shrink: 0;
        margin-top: 2px;
        font-size: 13px;
    }
    .track-addr {
        line-height: 1.4;
        font-size: 13px;
    }
    .track-detail-link {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 16px;
        background: #fff;
        border-top: 1px solid #e2e8f0;
        font-size: 13px;
        font-weight: 700;
        color: var(--travhub-base);
        text-decoration: none;
        transition: background 0.2s;
    }
    .track-detail-link:hover {
        background: #f1f5f9;
    }
    .track-not-found {
        margin-top: 16px;
        padding: 14px 16px;
        background: #fef3f3;
        border: 1px solid #fecaca;
        border-radius: 10px;
        font-size: 14px;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .track-not-found i {
        font-size: 16px;
        flex-shrink: 0;
    }

    /* ── History Shortcut ── */
    .history-shortcut-card {
        display: flex;
        flex-direction: column;
    }

    .history-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        /* padding: 28px; */
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        /* gap: 20px; */
        height: 100%;
    }

    /* Logged-in state */
    .history-card--loggedin {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-color: transparent;
    }
    .history-card__avatar {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--travhub-base), #ff6b6b);
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .history-card__greeting {
        font-size: 18px;
        font-weight: 600;
        color: #e2e8f0;
        margin: 0 0 4px;
    }
    .history-card__greeting strong {
        color: #fff;
    }
    .history-card--loggedin .history-card__sub {
        color: #94a3b8;
        font-size: 14px;
        margin: 0;
    }
    .history-card__btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--travhub-base);
        color: #fff;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(229, 32, 41, 0.35);
    }
    .history-card__btn:hover {
        background: #c81a22;
        transform: translateY(-2px);
    }

    /* Guest state */
    .history-card--guest {
        background: #fff;
    }
    .history-card__icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: rgba(229, 32, 41, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .history-card__icon i {
        font-size: 24px;
        color: var(--travhub-base);
    }
    .history-card__title {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 8px;
    }
    .history-card__sub {
        font-size: 14px;
        color: #64748b;
        margin: 0;
        margin-top: -10px !important;
        line-height: 1.6;
    }
    .history-card__actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    .history-btn {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 11px 20px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s;
    }
    .history-btn--primary {
        background: var(--travhub-base);
        color: #fff;
        box-shadow: 0 4px 16px rgba(229, 32, 41, 0.25);
    }
    .history-btn--primary:hover {
        background: #c81a22;
        transform: translateY(-1px);
    }
    .history-btn--secondary {
        background: #f1f5f9;
        color: #334155;
        border: 2px solid #e2e8f0;
    }
    .history-btn--secondary:hover {
        background: #e2e8f0;
    }

    /* ── Trip Type Toggle ── */
    .trip-type-toggle {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }
    .trip-type-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 18px;
        border-radius: 50px;
        border: 1.5px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .trip-type-btn:hover {
        border-color: var(--travhub-base, #e52029);
        color: var(--travhub-base, #e52029);
    }
    .trip-type-btn--active {
        background: var(--travhub-base, #e52029);
        border-color: var(--travhub-base, #e52029);
        color: #fff;
        box-shadow: 0 4px 12px rgba(229, 32, 41, 0.25);
    }
    .round-trip-info-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
        color: #0369a1;
        background: #e0f2fe;
        padding: 4px 12px;
        border-radius: 50px;
        border: 1px solid #bae6fd;
    }
    .price-each-way {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 2px;
    }
</style>
