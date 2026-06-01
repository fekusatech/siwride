<script lang="ts">
    import { page, useForm, router } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import TimePicker from '@/components/TimePicker.svelte';
    import { getMinPickupTime, formatEarliestTime, isPickupTimeValid } from '@/lib/pickupTime';
    import { formatRupiah } from '@/lib/utils';

    interface VehicleCategory {
        id: number;
        title: string;
        description: string;
        passenger_capacity: number | null;
        luggage_capacity: number | null;
        advantages: string[] | null;
        base_price: string;
        price_per_km: string;
        image_url: string;
        vehicle_type: string;
    }

    let { transfer, vehicleCategory } = $props<{
        transfer: { pickup: string; dropoff: string; date: string; time: string; passengers: string };
        vehicleCategory: VehicleCategory | null;
    }>();

    // Steps: 1=Transfer Details, 2=Passenger Info, 3=Extras, 4=Summary, 5=Payment
    let currentStep = $state(1);
    let stepError = $state('');
    let isValidatingEmail = $state(false);
    let emailError = $state('');
    let showPassword = $state(false);
    let showConfirmPassword = $state(false);

    const settings = $derived(page.props.settings as any);
    const EXTRAS = $derived(
        Array.isArray(settings.booking_extras) 
            ? settings.booking_extras.map(e => ({ ...e, price: Number(e.price) || 0 }))
            : []
    );

    const PAYMENT_METHODS = [
        { id: 'visa',        label: 'Visa',       icon: 'fab fa-cc-visa',       color: '#1a1f71' },
        { id: 'mastercard',  label: 'Mastercard', icon: 'fab fa-cc-mastercard', color: '#eb001b' },
        { id: 'paypal',      label: 'PayPal',     icon: 'fab fa-cc-paypal',     color: '#003087' },
        { id: 'apple_pay',   label: 'Apple Pay',  icon: 'fab fa-apple-pay',     color: '#000'    },
        { id: 'google_pay',  label: 'Google Pay', icon: 'fab fa-google-pay',    color: '#4285f4' },
        { id: 'cash',        label: 'Cash',       icon: 'fas fa-money-bill-wave', color: '#16a34a' },
    ];

    let selectedExtras = $state<string[]>([]);
    let agreedToTerms = $state(false);

    const form = useForm({
        customer_name: '',
        email: '',
        customer_phone: '',
        pickup_address: transfer?.pickup || '',
        dropoff_address: transfer?.dropoff || '',
        pickup_latitude: '' as string | number,
        pickup_longitude: '' as string | number,
        dropoff_latitude: '' as string | number,
        dropoff_longitude: '' as string | number,
        exact_distance_km: null as number | null,
        date: transfer?.date || '',
        time: transfer?.time || '',
        passengers: transfer?.passengers || '1',
        vehicle_category_id: vehicleCategory?.id ? String(vehicleCategory.id) : '',
        vehicle_type: vehicleCategory?.vehicle_type || '',
        notes: '',
        extras: [] as { label: string; price: number }[],
        create_account: false,
        password: '',
        password_confirmation: '',
        payment_method: 'cash',
    });

    // Sync extras into form
    $effect(() => {
        form.extras = selectedExtras.map((id) => {
            const extra = EXTRAS.find((e) => e.id === id);
            return { label: extra?.label ?? id, price: extra?.price ?? 0 };
        });
    });

    // Pre-fill from auth customer
    let useProfileData = $state(false);
    $effect(() => {
        if (useProfileData && (page.props as any).auth?.customer) {
            const c = (page.props as any).auth.customer;
            form.customer_name = c.name;
            form.email = c.email;
            if (c.phone) form.customer_phone = c.phone;
        }
    });

    /** Estimated zone-based price returned by /booking/estimate-price (null = no zone match). */
    let estimatedBasePrice = $state<number | null>(null);
    let priceZoneInfo = $state<{ pickup_zone: string | null; dropoff_zone: string | null; distance_km: number | null }>({
        pickup_zone: null,
        dropoff_zone: null,
        distance_km: null,
    });
    let exactDistanceStr = $state<string | null>(null);
    let exactDurationStr = $state<string | null>(null);
    let isPricingLoaded = $state(false);
    let zoneError = $state<string | null>(null);

    let fullPickupAddress = $state<string | null>(null);
    let fullDropoffAddress = $state<string | null>(null);
    let flightNumber = $state('');

    /** Geocodes an address string and returns {lat, lng, formatted} or null. */
    async function geocodeAddress(address: string): Promise<{ lat: number; lng: number, formatted: string } | null> {
        return new Promise((resolve) => {
            try {
                const geocoder = new (window as any).google.maps.Geocoder();
                geocoder.geocode(
                    { address, region: 'id', bounds: { south: -8.95, west: 114.4, north: -8.06, east: 115.72 } },
                    (results: any, status: string) => {
                        if (status === 'OK' && results && results[0]) {
                            const loc = results[0].geometry.location;
                            resolve({ lat: loc.lat(), lng: loc.lng(), formatted: results[0].formatted_address });
                        } else {
                            resolve(null);
                        }
                    }
                );
            } catch {
                resolve(null);
            }
        });
    }

    /** Calls the estimate-price API and stores the result. */
    async function fetchPriceEstimate(pLat: number, pLng: number, dLat: number, dLng: number, exactDistanceKm: number | null = null) {
        try {
            let url = `/booking/estimate-price?pickup_latitude=${pLat}&pickup_longitude=${pLng}&dropoff_latitude=${dLat}&dropoff_longitude=${dLng}`;
            if (exactDistanceKm !== null) {
                url += `&exact_distance_km=${exactDistanceKm}`;
            }
            const res = await fetch(url, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) {
                if (res.status === 422) {
                    const errData = await res.json();
                    zoneError = errData.error;
                }
                return;
            }
            zoneError = null;
            const data = await res.json();
            priceZoneInfo = { pickup_zone: data.pickup_zone, dropoff_zone: data.dropoff_zone, distance_km: data.distance_km };
            // Find price for the current vehicle category
            const match = data.prices?.find((p: any) => p.id === vehicleCategory?.id);
            if (match?.estimated_price != null) {
                estimatedBasePrice = match.estimated_price;
            }
        } catch {
            // silently ignore — will fall back to price_per_km × avg distance
        } finally {
            isPricingLoaded = true;
        }
    }

    /** Geocodes both addresses then fetches the price estimate. */
    async function geocodeAndEstimate() {
        const pickupAddr = transfer?.pickup;
        const dropoffAddr = transfer?.dropoff;
        if (!pickupAddr || !dropoffAddr) { isPricingLoaded = true; return; }

        // Wait for google maps to be available (it's loaded async)
        let waited = 0;
        while (typeof (window as any).google === 'undefined' && waited < 5000) {
            await new Promise((r) => setTimeout(r, 300));
            waited += 300;
        }
        if (typeof (window as any).google === 'undefined') { isPricingLoaded = true; return; }

        const [pickupCoords, dropoffCoords] = await Promise.all([
            geocodeAddress(pickupAddr),
            geocodeAddress(dropoffAddr),
        ]);

        let fullPickupAddr: string | null = null;
        let fullDropoffAddr: string | null = null;

        if (pickupCoords) {
            form.pickup_latitude  = pickupCoords.lat;
            form.pickup_longitude = pickupCoords.lng;
            fullPickupAddr = pickupCoords.formatted;
            fullPickupAddress = fullPickupAddr;
        }
        if (dropoffCoords) {
            form.dropoff_latitude  = dropoffCoords.lat;
            form.dropoff_longitude = dropoffCoords.lng;
            fullDropoffAddr = dropoffCoords.formatted;
            fullDropoffAddress = fullDropoffAddr;
        }

        if (pickupCoords && dropoffCoords) {
            let exactDistanceKm: number | null = null;
            try {
                const service = new (window as any).google.maps.DistanceMatrixService();
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
                        }
                    );
                });
                const element = dmResponse.rows[0].elements[0];
                if (element.status === 'OK') {
                    exactDistanceKm = element.distance.value / 1000;
                    exactDistanceStr = element.distance.text;
                    exactDurationStr = element.duration.text;
                    form.exact_distance_km = exactDistanceKm;
                }
            } catch (err) {
                // ignore
            }
            await fetchPriceEstimate(pickupCoords.lat, pickupCoords.lng, dropoffCoords.lat, dropoffCoords.lng, exactDistanceKm);
        } else {
            isPricingLoaded = true;
        }
    }

    // Computed totals
    let extrasTotal = $derived(
        selectedExtras.reduce((sum, id) => {
            const extra = EXTRAS.find((e) => e.id === id);
            return sum + (extra?.price ?? 0);
        }, 0)
    );
    /**
     * Base price: prefer zone-estimated price; fall back to price_per_km × 40 km average;
     * final fallback is 0 (shows 'Calculating...' until resolved).
     */
    let basePrice = $derived(
        estimatedBasePrice !== null
            ? estimatedBasePrice
            : vehicleCategory
                ? parseFloat(vehicleCategory.price_per_km) * 40   // rough avg until API responds
                : 0
    );
    let totalPrice = $derived(basePrice + extrasTotal);

    /** Reactive clock — ticks every minute so the earliest-time notice stays fresh. */
    let now = $state(new Date());

    let clockInterval: ReturnType<typeof setInterval>;

    /** Minimum pickup time — recalculates every minute via the `now` clock. */
    const minTime = $derived(getMinPickupTime(form.date, now));

    // Default route info
    const routeInfo = { distance: '0 km', travelTime: '0 min' };

    function formatDisplayDate(isoDate: string): string {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-').map(Number);
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return `${d} ${months[m - 1]} ${y}`;
    }

    const validateName = (e: Event) => {
        const t = e.target as HTMLInputElement;
        t.value = t.value.replace(/[^A-Za-z\s.'\-]/g, '');
        form.customer_name = t.value;
    };
    const validatePhone = (e: Event) => {
        const t = e.target as HTMLInputElement;
        t.value = t.value.replace(/[^0-9+\s\-]/g, '');
        form.customer_phone = t.value;
    };
    const validateEmailInput = (e: Event) => {
        const t = e.target as HTMLInputElement;
        t.value = t.value.replace(/\s/g, '');
        form.email = t.value;
        if (t.value.length > 0) {
            emailError = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t.value) ? '' : 'Please enter a valid email address';
        } else {
            emailError = '';
        }
    };

    async function nextStep() {
        stepError = '';
        if (currentStep === 1) {
            if (!form.pickup_address) { stepError = 'Please provide a pickup location.'; return; }
            if (!form.dropoff_address) { stepError = 'Please provide a destination.'; return; }
            if (!form.date) { stepError = 'Please select a transfer date.'; return; }
            if (!form.time) { stepError = 'Please select a pickup time.'; return; }
            if (!isPickupTimeValid(form.date, form.time, now)) {
                stepError = `Your selected time is too early. Earliest pickup for today is ${formatEarliestTime(form.date, now)}.`;
                return;
            }
            currentStep = 2;
        } else if (currentStep === 2) {
            if (!form.customer_name || form.customer_name.length < 3) { stepError = 'Please enter a valid full name.'; return; }
            if (!form.email || emailError) { stepError = 'Please enter a valid email address.'; return; }
            if (form.create_account && (!form.password || form.password.length < 8)) { stepError = 'Password must be at least 8 characters.'; return; }
            if (form.create_account && form.password !== form.password_confirmation) { stepError = 'Passwords do not match.'; return; }
            if (!(page.props as any).auth?.customer && form.create_account) {
                isValidatingEmail = true;
                try {
                    const res = await fetch('/booking/validate-email', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '' },
                        body: JSON.stringify({ email: form.email, create_account: form.create_account }),
                    });
                    const data = await res.json();
                    if (!data.valid) { stepError = data.message || 'Email validation failed.'; isValidatingEmail = false; return; }
                } catch { stepError = 'Unable to validate email. Please try again.'; isValidatingEmail = false; return; }
                isValidatingEmail = false;
            }
            currentStep = 3;
        } else if (currentStep === 3) {
            currentStep = 4;
        } else if (currentStep === 4) {
            currentStep = 5;
        }
    }

    function prevStep() {
        stepError = '';
        if (currentStep > 1) currentStep--;
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (currentStep !== 5) return;
        if (!form.payment_method) { stepError = 'Please select a payment method.'; return; }
        if (!agreedToTerms) { stepError = 'Please agree to the Terms & Conditions.'; return; }
        form.transform((data) => {
            let combinedNotes = data.notes || '';
            if (flightNumber) {
                combinedNotes = `Flight Number: ${flightNumber}\n\n${combinedNotes}`;
            }
            return {
                ...data,
                notes: combinedNotes.trim(),
            };
        }).post('/orders', {
            onSuccess: () => { form.reset(); },
        });
    }

    const stepLabels = ['Transfer Details', 'Passenger Info', 'Extras', 'Summary', 'Payment'];

    onMount(() => {
        // Tick every 60 seconds so the "Earliest pickup" notice and validation
        // stay accurate while the customer fills in the form — no page refresh needed.
        clockInterval = setInterval(() => {
            now = new Date();
        }, 60_000);

        // Geocode pickup/dropoff and fetch zone-based price estimate
        geocodeAndEstimate();
    });

    onDestroy(() => {
        clearInterval(clockInterval);
    });
</script>

<AppHead title="Checkout - Siwride" />
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
            <h2 class="page-header__title bw-split-in-right">Complete Your Booking</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/booking">Book a Transfer</a></li>
                <li><span>Checkout</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 60px 0 100px; background: #f4f7f9;">
        <div class="container">
            <div class="checkout-layout">

                <!-- Left: Multi-step form -->
                <div class="checkout-main">
                    <!-- Stepper -->
                    <div class="stepper">
                        {#each stepLabels as label, i}
                            <div class="stepper-item {currentStep > i + 1 ? 'completed' : ''} {currentStep === i + 1 ? 'active' : ''}">
                                <div class="stepper-circle">
                                    {#if currentStep > i + 1}
                                        <i class="fas fa-check"></i>
                                    {:else}
                                        {i + 1}
                                    {/if}
                                </div>
                                <span class="stepper-label">{label}</span>
                            </div>
                            {#if i < stepLabels.length - 1}
                                <div class="stepper-line {currentStep > i + 1 ? 'active' : ''}"></div>
                            {/if}
                        {/each}
                    </div>

                    {#if stepError}
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i> {stepError}
                        </div>
                    {/if}

                    <form onsubmit={handleSubmit}>

                        <!-- STEP 1: Transfer Details -->
                        {#if currentStep === 1}
                        <div class="step-card">
                            <div class="step-card__header">
                                <h4>Step 1. Transfer Details</h4>
                                <p>Verify your pickup and drop-off information</p>
                            </div>
                            <div class="step-card__body">
                                <div class="helper-notice">
                                    <i class="fas fa-info-circle"></i>
                                    Please check your actual address. If you see a mistake you can correct it here.
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Departure (Pickup) *</label>
                                        <LocationSearchInput id="co_pickup" bind:value={form.pickup_address} placeholder="Hotel name, area, or landmark..." variant="premium" required />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Destination (Drop-off) *</label>
                                        <LocationSearchInput id="co_dropoff" bind:value={form.dropoff_address} placeholder="Beach, temple, area..." variant="premium" required />
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Transfer Date *</label>
                                        <DatePicker
                                            id="co_date"
                                            bind:value={form.date}
                                            placeholder="Select pickup date"
                                            required
                                        />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Pickup Time *</label>
                                        <TimePicker
                                            id="co_time"
                                            bind:value={form.time}
                                            placeholder="Select pickup time"
                                            required
                                            minTime={minTime}
                                        />
                                        {#if minTime}
                                            <p class="co-time-notice" style="font-size:13px;">
                                                <i class="fas fa-info-circle"></i>
                                                Earliest pickup today: <strong>{formatEarliestTime(form.date, now)}</strong>
                                            </p>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <!-- STEP 2: Passenger Information -->
                        {#if currentStep === 2}
                        <div class="step-card">
                            <div class="step-card__header">
                                <h4>Step 2. Passenger Information</h4>
                                <p>Tell us who is travelling</p>
                            </div>
                            <div class="step-card__body">
                                {#if (page.props as any).auth?.customer}
                                    <div class="use-profile-box">
                                        <label class="checkbox-label">
                                            <input type="checkbox" bind:checked={useProfileData} />
                                            Use my saved profile information
                                        </label>
                                    </div>
                                {/if}
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Full Name *</label>
                                        <input type="text" value={form.customer_name} oninput={validateName} class="premium-input" placeholder="Enter your full name" minlength="3" maxlength="100" />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Email Address *</label>
                                        <input type="email" value={form.email} oninput={validateEmailInput} class="premium-input {emailError ? 'input-error' : ''}" placeholder="your.email@example.com" maxlength="100" />
                                        {#if emailError}<small class="error-text">{emailError}</small>{/if}
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Phone / WhatsApp</label>
                                        <input type="tel" value={form.customer_phone} oninput={validatePhone} class="premium-input" placeholder="+60 12 345 6789" maxlength="20" />
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Flight Number</label>
                                        <input type="text" bind:value={flightNumber} class="premium-input" placeholder="e.g. MH 123 (Optional)" maxlength="50" />
                                    </div>
                                </div>
                                {#if !(page.props as any).auth?.customer}
                                    <div class="create-account-box">
                                        <label class="checkbox-label">
                                            <input type="checkbox" bind:checked={form.create_account} />
                                            Create an account for faster booking next time
                                        </label>
                                        <span class="login-link">Already have an account? <a href="/customer/login">Login here</a></span>
                                    </div>
                                    {#if form.create_account}
                                        <div class="form-row" style="margin-top: 16px;">
                                            <div class="form-group">
                                                <label class="form-label">Password *</label>
                                                <div class="pw-wrap">
                                                    <input type={showPassword ? 'text' : 'password'} bind:value={form.password} class="premium-input" placeholder="Min. 8 characters" />
                                                    <button type="button" class="pw-toggle" onclick={() => showPassword = !showPassword}><i class="fas {showPassword ? 'fa-eye-slash' : 'fa-eye'}"></i></button>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label">Confirm Password *</label>
                                                <div class="pw-wrap">
                                                    <input type={showConfirmPassword ? 'text' : 'password'} bind:value={form.password_confirmation} class="premium-input" placeholder="Repeat password" />
                                                    <button type="button" class="pw-toggle" onclick={() => showConfirmPassword = !showConfirmPassword}><i class="fas {showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'}"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                {/if}
                            </div>
                        </div>
                        {/if}

                        <!-- STEP 3: Additional Services -->
                        {#if currentStep === 3}
                        <div class="step-card">
                            <div class="step-card__header">
                                <h4>Step 3. Additionally</h4>
                                <p>Enhance your transfer experience</p>
                            </div>
                            <div class="step-card__body">
                                <div class="extras-list">
                                    {#each EXTRAS as extra}
                                        <label class="extra-card {selectedExtras.includes(extra.id) ? 'extra-card--selected' : ''}">
                                            <input
                                                type="checkbox"
                                                class="extra-checkbox"
                                                checked={selectedExtras.includes(extra.id)}
                                                onchange={() => {
                                                    if (selectedExtras.includes(extra.id)) {
                                                        selectedExtras = selectedExtras.filter(id => id !== extra.id);
                                                    } else {
                                                        selectedExtras = [...selectedExtras, extra.id];
                                                    }
                                                }}
                                            />
                                            <div class="extra-card__content">
                                                <div class="extra-card__top">
                                                    <span class="extra-label">{extra.label}</span>
                                                    {#if extra.price > 0}
                                                        <span class="extra-price">+{formatRupiah(extra.price)}</span>
                                                    {:else}
                                                        <span class="extra-price extra-price--free">Free</span>
                                                    {/if}
                                                </div>
                                                <p class="extra-desc">{extra.description}</p>
                                            </div>
                                            <div class="extra-check-icon {selectedExtras.includes(extra.id) ? 'visible' : ''}">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        </label>
                                    {/each}
                                </div>
                                <div class="form-group" style="margin-top: 24px;">
                                    <label class="form-label">Comments to the order</label>
                                    <textarea bind:value={form.notes} rows="3" class="premium-input" placeholder="e.g. Non-standard luggage, special requirements..." style="resize: vertical;"></textarea>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <!-- STEP 4: Order Summary -->
                        {#if currentStep === 4}
                        <div class="step-card">
                            <div class="step-card__header">
                                <h4>Step 4. Order Summary</h4>
                                <p>Review your booking before proceeding to payment</p>
                            </div>
                            <div class="step-card__body">
                                <div class="summary-section">
                                    <h6 class="summary-section-title">Transfer Route</h6>
                                    <div class="summary-row" style="flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; width: 100%;">
                                            <span class="summary-label"><i class="fas fa-map-marker-alt text-danger"></i> Pickup</span>
                                            <span class="summary-value" style="text-align: right;">{form.pickup_address}</span>
                                        </div>
                                        {#if fullPickupAddress && fullPickupAddress !== form.pickup_address}
                                            <small class="text-muted" style="font-size: 0.75rem; text-align: right; width: 100%;">{fullPickupAddress}</small>
                                        {/if}
                                    </div>
                                    <div class="summary-row" style="flex-direction: column; align-items: flex-start; gap: 0.25rem;">
                                        <div style="display: flex; justify-content: space-between; width: 100%;">
                                            <span class="summary-label"><i class="fas fa-flag-checkered text-success"></i> Drop-off</span>
                                            <span class="summary-value" style="text-align: right;">{form.dropoff_address}</span>
                                        </div>
                                        {#if fullDropoffAddress && fullDropoffAddress !== form.dropoff_address}
                                            <small class="text-muted" style="font-size: 0.75rem; text-align: right; width: 100%;">{fullDropoffAddress}</small>
                                        {/if}
                                    </div>
                                    <div class="summary-row">
                                        <span class="summary-label"><i class="fas fa-calendar-alt"></i> Date & Time</span>
                                        <span class="summary-value">{form.date} · {form.time}</span>
                                    </div>
                                    <div class="summary-row">
                                        <span class="summary-label"><i class="fas fa-users"></i> Passengers</span>
                                        <span class="summary-value">{form.passengers}</span>
                                    </div>
                                </div>
                                {#if vehicleCategory}
                                <div class="summary-section">
                                    <h6 class="summary-section-title">Vehicle</h6>
                                    <div class="summary-vehicle">
                                        <img src={vehicleCategory.image_url} alt={vehicleCategory.title} />
                                        <div>
                                            <strong>{vehicleCategory.title}</strong>
                                            <span>{vehicleCategory.passenger_capacity} passengers · {vehicleCategory.luggage_capacity} luggage</span>
                                        </div>
                                        <span class="summary-vehicle-price">{formatRupiah(basePrice)}</span>
                                    </div>
                                </div>
                                {/if}
                                {#if selectedExtras.length > 0}
                                <div class="summary-section">
                                    <h6 class="summary-section-title">Additional Services</h6>
                                    {#each selectedExtras as id}
                                        {@const extra = EXTRAS.find(e => e.id === id)}
                                        {#if extra}
                                        <div class="summary-row">
                                            <span class="summary-label"><i class="fas fa-plus-circle text-success"></i> {extra.label}</span>
                                            <span class="summary-value">{extra.price > 0 ? '+' + formatRupiah(extra.price) : 'Free'}</span>
                                        </div>
                                        {/if}
                                    {/each}
                                </div>
                                {/if}
                                <div class="summary-total">
                                    <span>Total</span>
                                    <span class="total-amount">{formatRupiah(totalPrice)}</span>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <!-- STEP 5: Payment -->
                        {#if currentStep === 5}
                        <div class="step-card">
                            <div class="step-card__header">
                                <h4>Step 5. Payment</h4>
                                <p>Choose how you'd like to pay for your transfer</p>
                            </div>
                            <div class="step-card__body">
                                <div class="payment-methods">
                                    {#each PAYMENT_METHODS as method}
                                        <label class="method-card {form.payment_method === method.id ? 'method-card--selected' : ''}">
                                            <input type="radio" name="payment_method" value={method.id} bind:group={form.payment_method} class="method-radio" />
                                            <div class="method-icon" style="color: {method.color};">
                                                <i class="{method.icon}"></i>
                                            </div>
                                            <span class="method-label">{method.label}</span>
                                            <div class="method-check {form.payment_method === method.id ? 'visible' : ''}">
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
                                        The service is provided by Siwride. By completing this booking you accept our cancellation and refund policy.
                                    </p>
                                </div>

                                <div class="security-badges">
                                    <span><i class="fas fa-shield-alt"></i> SSL Secured</span>
                                    <span><i class="fas fa-lock"></i> 256-bit Encryption</span>
                                    <span><i class="fas fa-check-circle"></i> Safe Checkout</span>
                                </div>
                            </div>
                        </div>
                        {/if}

                        <!-- Navigation Buttons -->
                        <div class="step-nav">
                            {#if currentStep > 1}
                                <button type="button" class="btn-back" onclick={prevStep}>
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            {:else}
                                <a href="/booking?pickup={encodeURIComponent(transfer?.pickup || '')}&dropoff={encodeURIComponent(transfer?.dropoff || '')}&date={transfer?.date || ''}&time={encodeURIComponent(transfer?.time || '')}&passengers={transfer?.passengers || '1'}" class="btn-back">
                                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                                </a>
                            {/if}

                            {#if currentStep < 5}
                                <button type="button" class="btn-next" onclick={nextStep} disabled={isValidatingEmail}>
                                    {#if isValidatingEmail}
                                        Validating... <i class="fas fa-spinner fa-spin"></i>
                                    {:else}
                                        Continue <i class="fas fa-arrow-right"></i>
                                    {/if}
                                </button>
                            {:else}
                                <button type="submit" class="btn-submit" disabled={form.processing || !form.payment_method || !agreedToTerms || !!zoneError}>
                                    {#if form.processing}
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    {:else}
                                        <i class="fas fa-lock"></i> Pay {formatRupiah(totalPrice)} Securely
                                    {/if}
                                </button>
                            {/if}
                        </div>
                    </form>
                </div>

                <!-- Right: Sticky Order Summary -->
                <div class="checkout-sidebar">
                    <div class="sidebar-card">

                        <!-- Vehicle -->
                        {#if vehicleCategory}
                            <div class="sidebar-vehicle">
                                <img src={vehicleCategory.image_url} alt={vehicleCategory.title} />
                                <div class="sidebar-vehicle-info">
                                    <h5>{vehicleCategory.title}</h5>
                                    <span>{vehicleCategory.passenger_capacity ?? '—'} pax · {vehicleCategory.luggage_capacity ?? '—'} bags</span>
                                </div>
                            </div>
                        {/if}

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-box">
                        <h4 class="sidebar-title">Order Summary</h4>
                        
                        {#if zoneError}
                            <div class="alert alert-danger p-3 mb-4 rounded-3 border-0">
                                <i class="fas fa-exclamation-triangle me-2"></i> {zoneError}
                            </div>
                        {/if}

                        <!-- Route -->
                        <div class="sidebar-section-title mt-0">Route</div>
                        <div class="sidebar-route">
                            <div class="sidebar-route-point" style="align-items: flex-start;">
                                <span class="sidebar-route-dot sidebar-route-dot--from" style="margin-top: 6px;"></span>
                                <div style="display: flex; flex-direction: column;">
                                    <span class="sidebar-route-text">{form.pickup_address || transfer?.pickup || '—'}</span>
                                    {#if fullPickupAddress && fullPickupAddress !== (form.pickup_address || transfer?.pickup)}
                                        <small class="route-full-address text-muted" style="margin-top: 2px;">{fullPickupAddress}</small>
                                    {/if}
                                </div>
                            </div>
                            <div class="sidebar-route-line"></div>
                            <div class="sidebar-route-point" style="align-items: flex-start;">
                                <span class="sidebar-route-dot sidebar-route-dot--to" style="margin-top: 6px;"></span>
                                <div style="display: flex; flex-direction: column;">
                                    <span class="sidebar-route-text">{form.dropoff_address || transfer?.dropoff || '—'}</span>
                                    {#if fullDropoffAddress && fullDropoffAddress !== (form.dropoff_address || transfer?.dropoff)}
                                        <small class="route-full-address text-muted" style="margin-top: 2px;">{fullDropoffAddress}</small>
                                    {/if}
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <!-- Trip details -->
                        <div class="sidebar-section-title">Trip Details</div>
                        <div class="sidebar-info-grid">
                            <div class="sidebar-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <span class="sidebar-info-label">Date</span>
                                    <span class="sidebar-info-value">{form.date ? formatDisplayDate(form.date) : '—'}</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <span class="sidebar-info-label">Pickup Time</span>
                                    <span class="sidebar-info-value">{form.time || '—'}</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <span class="sidebar-info-label">Passengers</span>
                                    <span class="sidebar-info-value">{form.passengers}</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-road"></i>
                                <div>
                                    <span class="sidebar-info-label">Distance</span>
                                    <span class="sidebar-info-value">
                                        {#if !isPricingLoaded}
                                            <i class="fas fa-spinner fa-spin"></i>
                                        {:else}
                                            {exactDistanceStr || (priceZoneInfo.distance_km ? priceZoneInfo.distance_km + ' km' : '0 km')}
                                        {/if}
                                    </span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-hourglass-half"></i>
                                <div>
                                    <span class="sidebar-info-label">Est. Duration</span>
                                    <span class="sidebar-info-value">
                                        {#if !isPricingLoaded}
                                            <i class="fas fa-spinner fa-spin"></i>
                                        {:else}
                                            {exactDurationStr || '0 min'}
                                        {/if}
                                    </span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-car"></i>
                                <div>
                                    <span class="sidebar-info-label">Transfer Type</span>
                                    <span class="sidebar-info-value">Private</span>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <!-- Pricing breakdown -->
                        <div class="sidebar-section-title">Price Breakdown</div>
                        {#if priceZoneInfo.pickup_zone && priceZoneInfo.dropoff_zone}
                            <div class="sidebar-zone-info">
                                <i class="fas fa-map-marker-alt"></i>
                                {priceZoneInfo.pickup_zone} → {priceZoneInfo.dropoff_zone}
                                {#if exactDistanceStr}
                                    &nbsp;· {exactDistanceStr}
                                {:else if priceZoneInfo.distance_km}
                                    &nbsp;· {priceZoneInfo.distance_km} km
                                {/if}
                            </div>
                        {/if}
                        <div class="sidebar-row">
                            <span>Vehicle ({vehicleCategory?.title ?? 'Transfer'})</span>
                            {#if !isPricingLoaded}
                                <span class="price-calculating"><i class="fas fa-spinner fa-spin"></i> Calculating...</span>
                            {:else}
                                <span>{formatRupiah(basePrice)}</span>
                            {/if}
                        </div>
                        {#each selectedExtras as id}
                            {@const extra = EXTRAS.find(e => e.id === id)}
                            {#if extra && extra.price > 0}
                            <div class="sidebar-row">
                                <span>{extra.label}</span>
                                <span>+{formatRupiah(extra.price)}</span>
                            </div>
                            {/if}
                        {/each}

                        <div class="sidebar-divider"></div>
                        <div class="sidebar-total">
                            <span>Total</span>
                            {#if !isPricingLoaded}
                                <span class="price-calculating"><i class="fas fa-spinner fa-spin"></i></span>
                            {:else}
                                <span>{formatRupiah(totalPrice)}</span>
                            {/if}
                        </div>

                        <div class="sidebar-note">
                            <i class="fas fa-shield-alt"></i>
                            Secure booking. No hidden fees.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) { .checkout-layout { grid-template-columns: 1fr; } }

    /* Stepper */
    .stepper {
        display: flex;
        align-items: center;
        margin-bottom: 28px;
        background: #fff;
        border-radius: 14px;
        padding: 20px 24px;
        border: 1px solid #eaeef2;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    }
    .stepper-item { display: flex; flex-direction: column; align-items: center; gap: 6px; }
    .stepper-circle {
        width: 40px; height: 40px; border-radius: 50%;
        background: #f1f5f9; color: #94a3b8;
        font-size: 16px; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.3s;
    }
    .stepper-item.active .stepper-circle { background: var(--travhub-base); color: #fff; box-shadow: 0 0 0 4px rgba(229,32,41,0.15); }
    .stepper-item.completed .stepper-circle { background: #10b981; color: #fff; }
    .stepper-label { font-size: 12px; font-weight: 600; color: #94a3b8; white-space: nowrap; }
    .stepper-item.active .stepper-label { color: var(--travhub-base); }
    .stepper-item.completed .stepper-label { color: #10b981; }
    .stepper-line { flex: 1; height: 3px; background: #f1f5f9; margin: 0 6px; margin-bottom: 22px; border-radius: 2px; transition: all 0.3s; }
    .stepper-line.active { background: #10b981; }
    @media (max-width: 576px) { .stepper-label { display: none; } .stepper-circle { width: 32px; height: 32px; font-size: 13px; } }

    /* Step Card */
    .step-card { background: #fff; border-radius: 16px; border: 1px solid #eaeef2; box-shadow: 0 4px 20px rgba(0,0,0,0.04); margin-bottom: 20px; }
    .step-card__header { padding: 24px 28px 10px; border-bottom: 1px solid #f0f4f8; }
    .step-card__header h4 { font-size: 20px; font-weight: 800; color: #1e293b; margin: 0; }
    .step-card__header p { font-size: 14px; color: #64748b; margin: 4px 0 0; }
    .step-card__body { padding: 28px; }

    /* Form */
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
    @media (max-width: 640px) { .form-row { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 8px; }
    .form-label { font-size: 13px; font-weight: 700; color: #334155; text-transform: uppercase; letter-spacing: 0.5px; }
    .premium-input {
        width: 100%; padding: 13px 18px;
        border: 2px solid #e2e8f0; background: #f8fafc;
        border-radius: 10px; font-size: 15px; font-weight: 500; color: #1e293b;
        transition: all 0.2s; outline: none;
    }
    .premium-input:focus { border-color: var(--travhub-base); background: #fff; box-shadow: 0 0 0 4px rgba(229,32,41,0.08); }
    .premium-input.input-error { border-color: #ef4444; background: #fef2f2; }
    .error-text { font-size: 12px; color: #ef4444; font-weight: 500; }
    .input-icon-wrap { position: relative; }
    .input-icon-r { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
    .helper-notice {
        padding: 12px 16px; background: #eff6ff; border: 1px solid #bfdbfe;
        border-radius: 10px; font-size: 14px; color: #1d4ed8;
        display: flex; align-items: flex-start; gap: 10px; margin-bottom: 20px;
    }
    .helper-notice i { margin-top: 2px; flex-shrink: 0; }

    /* Account boxes */
    .use-profile-box, .create-account-box {
        padding: 14px 18px; background: #f0fdf4; border: 1.5px dashed #86efac;
        border-radius: 10px; display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 10px; margin-bottom: 20px;
    }
    .create-account-box { background: #f8fafc; border-color: #cbd5e1; }
    .checkbox-label { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 14px; font-weight: 600; color: #334155; margin: 0; }
    .checkbox-label input { width: 18px; height: 18px; accent-color: var(--travhub-base); cursor: pointer; }
    .login-link { font-size: 13px; color: #64748b; }
    .login-link a { color: var(--travhub-base); font-weight: 700; }
    .pw-wrap { position: relative; }
    .pw-toggle { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; }
    .pw-toggle:hover { color: var(--travhub-base); }

    /* Extras */
    .extras-list { display: flex; flex-direction: column; gap: 12px; }
    .extra-card {
        display: flex; align-items: center; gap: 16px;
        padding: 16px 20px; border: 2px solid #e2e8f0; border-radius: 12px;
        cursor: pointer; transition: all 0.2s; background: #fff;
    }
    .extra-card:hover { border-color: #94a3b8; background: #f8fafc; }
    .extra-card--selected { border-color: var(--travhub-base); background: rgba(229,32,41,0.03); }
    .extra-checkbox { display: none; }
    .extra-card__content { flex: 1; }
    .extra-card__top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; }
    .extra-label { font-size: 15px; font-weight: 700; color: #1e293b; }
    .extra-price { font-size: 15px; font-weight: 700; color: var(--travhub-base); }
    .extra-price--free { color: #10b981; }
    .extra-desc { font-size: 13px; color: #64748b; margin: 0; }
    .extra-check-icon {
        width: 28px; height: 28px; border-radius: 50%;
        background: #f1f5f9; color: #94a3b8;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; transition: all 0.2s; flex-shrink: 0;
    }
    .extra-check-icon.visible { background: var(--travhub-base); color: #fff; }

    /* Summary */
    .summary-section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0f4f8; }
    .summary-section:last-of-type { border-bottom: none; }
    .summary-section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-bottom: 12px; }
    .summary-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 6px 0; }
    .summary-label { font-size: 14px; color: #64748b; display: flex; align-items: center; gap: 8px; }
    .summary-value { font-size: 14px; font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; }
    .summary-vehicle { display: flex; align-items: center; gap: 14px; }
    .summary-vehicle img { width: 80px; height: 60px; object-fit: contain; background: #f8fafc; border-radius: 8px; padding: 4px; }
    .summary-vehicle div { flex: 1; display: flex; flex-direction: column; gap: 4px; }
    .summary-vehicle strong { font-size: 15px; font-weight: 700; color: #1e293b; }
    .summary-vehicle span { font-size: 13px; color: #64748b; }
    .summary-vehicle-price { font-size: 18px; font-weight: 800; color: #1e293b; }
    .summary-total { display: flex; justify-content: space-between; align-items: center; padding: 16px 0 0; border-top: 2px solid #1e293b; margin-top: 8px; }
    .summary-total span:first-child { font-size: 16px; font-weight: 700; color: #1e293b; }
    .total-amount { font-size: 28px; font-weight: 800; color: var(--travhub-base); }

    /* Navigation */
    .step-nav { display: flex; justify-content: space-between; align-items: center; padding-top: 24px; }
    .btn-next, .btn-submit {
        background: var(--travhub-base); color: #fff;
        padding: 14px 32px; border: none; border-radius: 50px;
        font-size: 16px; font-weight: 700; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s; box-shadow: 0 6px 20px rgba(229,32,41,0.25);
    }
    .btn-next:hover:not(:disabled), .btn-submit:hover:not(:disabled) { background: #111; transform: translateY(-2px); }
    .btn-next:disabled, .btn-submit:disabled { background: #94a3b8; box-shadow: none; cursor: not-allowed; }
    .btn-back {
        background: #f1f5f9; color: #475569;
        padding: 14px 24px; border: none; border-radius: 50px;
        font-size: 15px; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.2s; text-decoration: none;
    }
    .btn-back:hover { background: #e2e8f0; color: #0f172a; }
    .alert-error {
        padding: 14px 18px; background: #fef2f2; border: 1px solid #fecaca;
        border-radius: 10px; font-size: 14px; color: #dc2626;
        display: flex; align-items: center; gap: 10px; margin-bottom: 20px;
    }


    /* Sidebar */
    .checkout-sidebar { position: sticky; top: 100px; }
    .sidebar-card {
        background: #fff; border-radius: 16px; border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0,0,0,0.06); padding: 24px;
    }
    .sidebar-vehicle { display: flex; align-items: center; gap: 14px; }
    .sidebar-vehicle img { width: 72px; height: 54px; object-fit: contain; background: #f8fafc; border-radius: 8px; padding: 4px; flex-shrink: 0; }
    .sidebar-vehicle-info h5 { font-size: 15px; font-weight: 700; color: #1e293b; margin: 0 0 3px; }
    .sidebar-vehicle-info span { font-size: 12px; color: #64748b; }
    .sidebar-divider { height: 1px; background: #f0f4f8; margin: 14px 0; }
    .sidebar-section-title {
        font-size: 10px; font-weight: 700; text-transform: uppercase;
        letter-spacing: 0.7px; color: #94a3b8; margin-bottom: 10px;
    }

    .sidebar-zone-info {
        font-size: 11px;
        color: #64748b;
        background: #f8fafc;
        padding: 5px 8px;
        border-radius: 6px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .sidebar-zone-info i { color: #94a3b8; font-size: 10px; }
    .price-calculating { color: #94a3b8; font-style: italic; }

    /* Route in sidebar */
    .sidebar-route { display: flex; flex-direction: column; gap: 0; }
    .sidebar-route-point { display: flex; align-items: flex-start; gap: 10px; }
    .sidebar-route-dot {
        width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; margin-top: 4px;
    }
    .sidebar-route-dot--from { background: var(--travhub-base); }
    .sidebar-route-dot--to   { background: #10b981; }
    .sidebar-route-text { font-size: 13px; font-weight: 500; color: #1e293b; line-height: 1.4; }
    .sidebar-route-line {
        width: 2px; height: 14px; margin-left: 4px;
        background: repeating-linear-gradient(to bottom, #cbd5e1 0, #cbd5e1 3px, transparent 3px, transparent 6px);
    }

    /* Trip details grid */
    .sidebar-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .sidebar-info-item {
        display: flex; align-items: flex-start; gap: 7px;
        background: #f8fafc; border-radius: 8px; padding: 8px 10px;
    }
    .sidebar-info-item i { font-size: 12px; color: var(--travhub-base); margin-top: 2px; flex-shrink: 0; }
    .sidebar-info-item div { display: flex; flex-direction: column; gap: 1px; min-width: 0; }
    .sidebar-info-label { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: #94a3b8; }
    .sidebar-info-value { font-size: 12px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    /* Price rows */
    .sidebar-row { display: flex; justify-content: space-between; font-size: 13px; color: #475569; padding: 4px 0; }
    .sidebar-total { display: flex; justify-content: space-between; align-items: center; font-size: 18px; font-weight: 800; color: #1e293b; }
    .sidebar-note { margin-top: 14px; font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 7px; }
    .sidebar-note i { color: #10b981; }

    /* ── Step 5: Payment ── */
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    @media (max-width: 480px) { .payment-methods { grid-template-columns: repeat(2, 1fr); } }
    .method-card {
        display: flex; flex-direction: column; align-items: center; gap: 7px;
        padding: 16px 12px; border: 2px solid #e2e8f0; border-radius: 12px;
        cursor: pointer; transition: all 0.2s; background: #fff; position: relative;
    }
    .method-card:hover { border-color: #94a3b8; background: #f8fafc; }
    .method-card--selected { border-color: var(--travhub-base); background: rgba(229,32,41,0.03); }
    .method-radio { display: none; }
    .method-icon { font-size: 30px; line-height: 1; }
    .method-label { font-size: 12px; font-weight: 700; color: #334155; }
    .method-check {
        position: absolute; top: 7px; right: 7px;
        width: 20px; height: 20px; border-radius: 50%;
        background: #f1f5f9; color: #94a3b8;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; transition: all 0.2s; opacity: 0;
    }
    .method-check.visible { background: var(--travhub-base); color: #fff; opacity: 1; }

    .terms-box {
        padding: 16px 18px; background: #f8fafc; border-radius: 12px;
        border: 1px solid #e2e8f0; margin-bottom: 20px;
    }
    .terms-label {
        display: flex; align-items: flex-start; gap: 10px;
        cursor: pointer; font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 8px;
    }
    .terms-checkbox { width: 17px; height: 17px; accent-color: var(--travhub-base); cursor: pointer; flex-shrink: 0; margin-top: 2px; }
    .terms-label a { color: var(--travhub-base); text-decoration: underline; }
    .terms-provider { font-size: 12px; color: #94a3b8; margin: 0; padding-left: 27px; line-height: 1.6; }

    .security-badges {
        display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;
        font-size: 12px; color: #94a3b8;
    }
    .security-badges span { display: flex; align-items: center; gap: 5px; }
    .security-badges i { color: #10b981; }

    .route-full-address {
        font-size: 11px;
        letter-spacing: 0.2px; 
        line-height: 1.4; 
    }
</style>
