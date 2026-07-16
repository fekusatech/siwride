<script lang="ts">
    import { page, useForm, router } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import TimePicker from '@/components/TimePicker.svelte';
    import {
        getMinPickupTime,
        formatEarliestTime,
        formatTime12,
        isPickupTimeValid,
    } from '@/lib/pickupTime';
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
        transfer: {
            pickup: string;
            dropoff: string;
            date: string;
            time: string;
            passengers: string;
            trip_type?: string;
            return_date?: string;
            return_time?: string;
        };
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
    const recaptchaEnabled = $derived(settings.recaptcha_enabled === '1');
    const EXTRAS = $derived(
        Array.isArray(settings.booking_extras)
            ? settings.booking_extras.map((e) => ({
                  ...e,
                  price: Number(e.price) || 0,
              }))
            : [],
    );

    // const EXTRAS = $derived(
    //     Array.isArray(settings.booking_extras)
    //         ? settings.booking_extras.map(e => ({ ...e, price: Number(e.price) || 0 }))
    //         : []
    // );

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
        date: transfer?.date || '',
        time: transfer?.time || '',
        passengers: transfer?.passengers || '1',
        vehicle_category_id: vehicleCategory?.id
            ? String(vehicleCategory.id)
            : '',
        vehicle_type: vehicleCategory?.vehicle_type || '',
        notes: '',
        extras: [] as { label: string; price: number }[],
        create_account: false,
        password_confirmation: '',
        'g-recaptcha-response': '',
        trip_type: (transfer?.trip_type || 'one_way') as string,
        return_date: transfer?.return_date || '',
        return_time: transfer?.return_time || '',
    });

    const isRoundTrip = $derived(form.trip_type === 'round_trip');

    // Sync extras into form
    $effect(() => {
        form.extras = selectedExtras.map((id) => {
            const extra = EXTRAS.find((e) => e.id === id);
            return { label: extra?.label ?? id, price: extra?.price ?? 0 };
        });
    });

    // Pre-fill from auth customer — auto-fill when logged in
    let useProfileData = $state(false);

    // Auto-enable profile data if customer is logged in
    onMount(() => {
        if ((page.props as any).auth?.customer) {
            useProfileData = true;
        }
    });

    $effect(() => {
        if (useProfileData && (page.props as any).auth?.customer) {
            const c = (page.props as any).auth.customer;
            form.customer_name = c.name || '';
            form.email = c.email || '';
            if (c.phone) form.customer_phone = c.phone;
        } else if (!useProfileData) {
            form.customer_name = '';
            form.email = '';
            form.customer_phone = '';
        }
    });

    /** Estimated zone-based price returned by /booking/estimate-price (null = no zone match). */
    let estimatedBasePrice = $state<number | null>(null);
    let priceZoneInfo = $state<{
        pickup_zone: string | null;
        dropoff_zone: string | null;
        distance_km: number | null;
    }>({
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

    /** Geocodes an address string via the backend (Nominatim) and returns {lat, lng, formatted} or null. */
    async function geocodeAddress(
        address: string,
    ): Promise<{ lat: number; lng: number; formatted: string } | null> {
        try {
            const res = await fetch(
                `/locations/geocode?address=${encodeURIComponent(address)}`,
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );
            if (!res.ok) return null;
            const data = await res.json();
            return { lat: data.lat, lng: data.lng, formatted: data.formatted };
        } catch {
            return null;
        }
    }

    /** Calls the estimate-price API and stores the result. */
    async function fetchPriceEstimate(
        pLat: number,
        pLng: number,
        dLat: number,
        dLng: number,
    ) {
        try {
            const url = `/booking/estimate-price?pickup_latitude=${pLat}&pickup_longitude=${pLng}&dropoff_latitude=${dLat}&dropoff_longitude=${dLng}`;
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
            if (data.distance_km != null) {
                exactDistanceStr = `${data.distance_km} km`;
            }
            if (data.duration_minutes != null) {
                exactDurationStr = `${data.duration_minutes} min`;
            }
            // Find price for the current vehicle category
            const match = data.prices?.find(
                (p: any) => p.id === vehicleCategory?.id,
            );
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
        if (!pickupAddr || !dropoffAddr) {
            isPricingLoaded = true;
            return;
        }

        const [pickupCoords, dropoffCoords] = await Promise.all([
            geocodeAddress(pickupAddr),
            geocodeAddress(dropoffAddr),
        ]);

        if (pickupCoords) {
            form.pickup_latitude = pickupCoords.lat;
            form.pickup_longitude = pickupCoords.lng;
            fullPickupAddress = pickupCoords.formatted;
        }
        if (dropoffCoords) {
            form.dropoff_latitude = dropoffCoords.lat;
            form.dropoff_longitude = dropoffCoords.lng;
            fullDropoffAddress = dropoffCoords.formatted;
        }

        if (pickupCoords && dropoffCoords) {
            await fetchPriceEstimate(
                pickupCoords.lat,
                pickupCoords.lng,
                dropoffCoords.lat,
                dropoffCoords.lng,
            );
        } else {
            isPricingLoaded = true;
        }
    }

    // Computed totals
    let extrasTotal = $derived(
        selectedExtras.reduce((sum, id) => {
            const extra = EXTRAS.find((e) => e.id === id);
            return sum + (extra?.price ?? 0);
        }, 0),
    );
    /**
     * Base price: prefer zone-estimated price; fall back to price_per_km × 40 km average;
     * final fallback is 0 (shows 'Calculating...' until resolved).
     */
    let basePrice = $derived(
        estimatedBasePrice !== null
            ? estimatedBasePrice
            : vehicleCategory
              ? parseFloat(vehicleCategory.price_per_km) * 40 // rough avg until API responds
              : 0,
    );
    /**
     * Compute final price:
     * - One-way: basePrice + extrasTotal
     * - Round-trip: (basePrice + extrasTotal) x 2
     */
    const totalPrice = $derived.by(() => {
        const basePrice = estimatedBasePrice || 0;
        return isRoundTrip ? (basePrice + extrasTotal) * 2 : basePrice + extrasTotal;
    });

    /** Reactive clock — ticks every minute so the earliest-time notice stays fresh. */
    let now = $state(new Date());

    let clockInterval: ReturnType<typeof setInterval>;

    /** Minimum pickup time — recalculates every minute via the `now` clock. */
    const minTime = $derived(getMinPickupTime(form.date, now));

    const timeError = $derived.by(() => {
        if (form.time && !isPickupTimeValid(form.date, form.time, now)) {
            return `Please select a pickup time at least 30 minutes from now. Earliest available: ${formatEarliestTime(form.date, now)}`;
        }
        return null;
    });

    const returnDateError = $derived.by(() => {
        if (isRoundTrip && form.date && form.return_date && form.return_date < form.date) {
            return 'Return date cannot be before departure date.';
        }
        return null;
    });

    const returnMinTime = $derived.by(() => {
        if (!isRoundTrip || !form.date || !form.time || !form.return_date) return '';
        if (form.return_date === form.date) {
            const gapHours = 3;
            const [hours, minutes] = form.time.split(':').map(Number);
            const returnHours = hours + gapHours;
            if (returnHours >= 24) return '23:59';
            return `${String(returnHours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        }
        return '';
    });

    const returnTimeError = $derived.by(() => {
        if (isRoundTrip && form.date && form.return_date === form.date && form.time && form.return_time) {
            if (returnMinTime && form.return_time < returnMinTime) {
                return `Return time must be at least ${returnMinTime}`;
            }
        }
        return null;
    });

    // Default route info
    const routeInfo = { distance: '0 km', travelTime: '0 min' };

    function formatDisplayDate(isoDate: string): string {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-').map(Number);
        const months = [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'Jun',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec',
        ];
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
            emailError = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(t.value)
                ? ''
                : 'Please enter a valid email address';
        } else {
            emailError = '';
        }
    };

    function editTransferDetails() {
        router.get('/booking/airport-transfer', {
            pickup: form.pickup_address,
            dropoff: form.dropoff_address,
            date: form.date,
            time: form.time,
            passengers: form.passengers,
            trip_type: form.trip_type,
            return_date: form.return_date,
            return_time: form.return_time,
        });
    }

    async function nextStep() {
        stepError = '';
        if (currentStep === 1) {
            if (timeError) {
                stepError = timeError;
                return;
            }
            if (returnDateError) {
                stepError = returnDateError;
                return;
            }
            if (returnTimeError) {
                stepError = returnTimeError;
                return;
            }
            if (!form.pickup_address) {
                stepError = 'Please provide a pickup location.';
                return;
            }
            if (!form.dropoff_address) {
                stepError = 'Please provide a destination.';
                return;
            }
            if (!form.date) {
                stepError = 'Please select a transfer date.';
                return;
            }
            if (!form.time) {
                stepError = 'Please select a pickup time.';
                return;
            }
            currentStep = 2;
        } else if (currentStep === 2) {
            if (!form.customer_name || form.customer_name.length < 3) {
                stepError = 'Please enter a valid full name.';
                return;
            }
            if (!form.email || emailError) {
                stepError = 'Please enter a valid email address.';
                return;
            }
            if (
                form.create_account &&
                (!form.password || form.password.length < 8)
            ) {
                stepError = 'Password must be at least 8 characters.';
                return;
            }
            if (
                form.create_account &&
                form.password !== form.password_confirmation
            ) {
                stepError = 'Passwords do not match.';
                return;
            }
            if (!(page.props as any).auth?.customer && form.create_account) {
                isValidatingEmail = true;
                try {
                    const res = await fetch('/booking/validate-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN':
                                document
                                    .querySelector('meta[name="csrf-token"]')
                                    ?.getAttribute('content') || '',
                        },
                        body: JSON.stringify({
                            email: form.email,
                            create_account: form.create_account,
                        }),
                    });
                    const data = await res.json();
                    if (!data.valid) {
                        stepError = data.message || 'Email validation failed.';
                        isValidatingEmail = false;
                        return;
                    }
                } catch {
                    stepError = 'Unable to validate email. Please try again.';
                    isValidatingEmail = false;
                    return;
                }
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

    function submitForm() {
        form.transform((data) => {
            let combinedNotes = data.notes || '';
            if (flightNumber) {
                combinedNotes = `Flight Number: ${flightNumber}\n\n${combinedNotes}`;
            }

            let finalPickup = data.pickup_address;
            if (
                fullPickupAddress &&
                fullPickupAddress !== data.pickup_address
            ) {
                if (fullPickupAddress.startsWith(data.pickup_address)) {
                    finalPickup = fullPickupAddress;
                } else {
                    finalPickup = `${data.pickup_address}, ${fullPickupAddress}`;
                }
            }

            let finalDropoff = data.dropoff_address;
            if (
                fullDropoffAddress &&
                fullDropoffAddress !== data.dropoff_address
            ) {
                if (fullDropoffAddress.startsWith(data.dropoff_address)) {
                    finalDropoff = fullDropoffAddress;
                } else {
                    finalDropoff = `${data.dropoff_address}, ${fullDropoffAddress}`;
                }
            }

            return {
                ...data,
                pickup_address: finalPickup,
                dropoff_address: finalDropoff,
                notes: combinedNotes.trim(),
            };
        }).post('/orders', {
            onSuccess: () => {
                form.reset();
            },
            onFinish: () => {
                form['g-recaptcha-response'] = '';
                if (
                    recaptchaEnabled &&
                    window.grecaptcha &&
                    recaptchaWidgetId !== null
                ) {
                    try {
                        window.grecaptcha.reset(recaptchaWidgetId);
                    } catch {
                        /* ignore */
                    }
                    recaptchaWidgetId = null;
                }
            },
        });
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (currentStep === 4) {
            if (!agreedToTerms) {
                stepError =
                    'You must agree to the Terms & Conditions to proceed.';
                return;
            }
            if (recaptchaEnabled && !form['g-recaptcha-response']) {
                stepError = 'Please complete the reCAPTCHA verification.';
                return;
            }
            submitForm();
            return;
        }
        nextStep();
    }

    let recaptchaWidgetId = $state<number | null>(null);

    /**
     * Explicitly renders the reCAPTCHA widget inside #checkout-recaptcha-container.
     * Called after the DOM element is guaranteed to exist (step 4 is active).
     */
    function renderRecaptchaWidget() {
        if (!recaptchaEnabled) return;
        const container = document.getElementById(
            'checkout-recaptcha-container',
        );
        if (!container) return;

        // Reset existing widget if already rendered
        if (recaptchaWidgetId !== null && window.grecaptcha) {
            try {
                window.grecaptcha.reset(recaptchaWidgetId);
            } catch {
                /* ignore */
            }
            return;
        }

        if (window.grecaptcha && window.grecaptcha.render) {
            recaptchaWidgetId = window.grecaptcha.render(container, {
                sitekey: settings.recaptcha_site_key,
                callback: (token: string) => {
                    form['g-recaptcha-response'] = token;
                },
                'expired-callback': () => {
                    form['g-recaptcha-response'] = '';
                },
            });
        } else {
            // Script not yet loaded — inject it with an onload callback
            if (!document.querySelector('script[src*="recaptcha/api.js"]')) {
                (window as any).onCheckoutRecaptchaLoad = () => {
                    const el = document.getElementById(
                        'checkout-recaptcha-container',
                    );
                    if (el && window.grecaptcha) {
                        recaptchaWidgetId = window.grecaptcha.render(el, {
                            sitekey: settings.recaptcha_site_key,
                            callback: (token: string) => {
                                form['g-recaptcha-response'] = token;
                            },
                            'expired-callback': () => {
                                form['g-recaptcha-response'] = '';
                            },
                        });
                    }
                };
                const script = document.createElement('script');
                script.src =
                    'https://www.google.com/recaptcha/api.js?onload=onCheckoutRecaptchaLoad&render=explicit';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }
        }
    }

    // When user reaches step 4, render the reCAPTCHA widget after DOM updates
    $effect(() => {
        if (currentStep === 4 && recaptchaEnabled) {
            // Use a microtask to wait for Svelte DOM update
            setTimeout(() => renderRecaptchaWidget(), 50);
        }
    });

    const stepLabels = [
        'Transfer Details',
        'Passenger Info',
        'Extras',
        'Confirmation',
    ];

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
            <h2 class="page-header__title bw-split-in-right">
                Complete Your Booking
            </h2>
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
                            <div
                                class="stepper-item {currentStep > i + 1
                                    ? 'completed'
                                    : ''} {currentStep === i + 1
                                    ? 'active'
                                    : ''}"
                            >
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
                                <div
                                    class="stepper-line {currentStep > i + 1
                                        ? 'active'
                                        : ''}"
                                ></div>
                            {/if}
                        {/each}
                    </div>

                    {#if stepError}
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {stepError}
                        </div>
                    {/if}

                    <form onsubmit={handleSubmit}>
                        <!-- STEP 1: Transfer Details -->
                        {#if currentStep === 1}
                            <div class="step-card">
                                <div class="step-card__header">
                                    <h4>Step 1. Transfer Details</h4>
                                    <p>
                                        Verify your pickup and drop-off
                                        information
                                    </p>
                                </div>
                                <div class="step-card__body">
                                    <!-- Hidden round-trip fields -->
                                    <input type="hidden" bind:value={form.trip_type} />
                                    <input type="hidden" bind:value={form.return_date} />
                                    <input type="hidden" bind:value={form.return_time} />

                                    {#if isRoundTrip}
                                        <div class="co-round-trip-badge">
                                            <i class="fas fa-exchange-alt"></i>
                                            <span><strong>Round-Trip Booking</strong> — Outbound + Return included in this order</span>
                                        </div>
                                    {/if}
                                    <div class="helper-notice">
                                        <i class="fas fa-info-circle"></i>
                                        Please check your actual address. If you see
                                        a mistake you can correct it here.
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <label class="form-label mb-0">Departure (Pickup) *</label>
                                                <button type="button" onclick={editTransferDetails} style="background: none; border: none; color: var(--travhub-base); font-size: 11px; font-weight: 700; cursor: pointer; padding: 0; text-transform: uppercase;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                            <input
                                                id="co_pickup"
                                                type="text"
                                                value={form.pickup_address}
                                                class="premium-input"
                                                readonly
                                                style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <label class="form-label mb-0">Destination (Drop-off) *</label>
                                                <button type="button" onclick={editTransferDetails} style="background: none; border: none; color: var(--travhub-base); font-size: 11px; font-weight: 700; cursor: pointer; padding: 0; text-transform: uppercase;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            </div>
                                            <input
                                                id="co_dropoff"
                                                type="text"
                                                value={form.dropoff_address}
                                                class="premium-input"
                                                readonly
                                                style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;"
                                            />
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Departure Date *</label
                                            >
                                            <DatePicker
                                                id="co_date"
                                                bind:value={form.date}
                                                placeholder="Select pickup date"
                                                required
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Departure Time *</label
                                            >
                                            <TimePicker
                                                id="co_time"
                                                bind:value={form.time}
                                                placeholder="Select pickup time"
                                                required
                                                {minTime}
                                            />
                                            {#if minTime}
                                                <p
                                                    class="co-time-notice"
                                                    style="font-size:13px;"
                                                >
                                                    <i
                                                        class="fas fa-info-circle"
                                                    ></i>
                                                    Earliest pickup today:
                                                    <strong
                                                        >{formatEarliestTime(
                                                            form.date,
                                                            now,
                                                        )}</strong
                                                    >
                                                </p>
                                            {/if}
                                            {#if timeError}
                                                <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {timeError}</p>
                                            {/if}
                                        </div>
                                    </div>
                                    {#if isRoundTrip}
                                        <div class="co-return-trip-section">
                                            <div class="co-return-trip-header">
                                                <i class="fas fa-undo-alt"></i>
                                                Return Trip Details
                                            </div>
                                            <div class="form-row">
                                                <div class="form-group">
                                                    <label class="form-label">Return Date *</label>
                                                    <DatePicker
                                                        id="co_return_date"
                                                        bind:value={form.return_date}
                                                        placeholder="Select return date"
                                                        required={isRoundTrip}
                                                        minDate={form.date}
                                                    />
                                                    {#if returnDateError}
                                                        <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {returnDateError}</p>
                                                    {/if}
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label">Return Pickup Time *</label>
                                                    <TimePicker
                                                        id="co_return_time"
                                                        bind:value={form.return_time}
                                                        placeholder="Select return time"
                                                        required={isRoundTrip}
                                                        minTime={returnMinTime}
                                                    />
                                                    {#if returnTimeError}
                                                        <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {returnTimeError}</p>
                                                    {/if}
                                                </div>
                                            </div>
                                            <p class="co-return-note">
                                                <i class="fas fa-info-circle"></i>
                                                Return route is automatically reversed: <strong>{form.dropoff_address || transfer?.dropoff || '—'}</strong> → <strong>{form.pickup_address || transfer?.pickup || '—'}</strong>
                                            </p>
                                        </div>
                                    {/if}
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
                                                <input
                                                    type="checkbox"
                                                    bind:checked={
                                                        useProfileData
                                                    }
                                                />
                                                Use my saved profile information
                                            </label>
                                        </div>
                                    {/if}
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Full Name *</label
                                            >
                                            <input
                                                type="text"
                                                value={form.customer_name}
                                                oninput={validateName}
                                                class="premium-input"
                                                placeholder="Enter your full name"
                                                minlength="3"
                                                maxlength="100"
                                                disabled={useProfileData}
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Email Address *</label
                                            >
                                            <input
                                                type="email"
                                                value={form.email}
                                                oninput={validateEmailInput}
                                                class="premium-input {emailError
                                                    ? 'input-error'
                                                    : ''}"
                                                placeholder="your.email@example.com"
                                                maxlength="100"
                                                disabled={useProfileData}
                                            />
                                            {#if emailError}<small
                                                    class="error-text"
                                                    >{emailError}</small
                                                >{/if}
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Phone / WhatsApp</label
                                            >
                                            <input
                                                type="tel"
                                                value={form.customer_phone}
                                                oninput={validatePhone}
                                                class="premium-input"
                                                placeholder="+60 12 345 6789"
                                                maxlength="20"
                                                disabled={useProfileData && !!(page.props as any).auth?.customer?.phone}
                                            />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label"
                                                >Flight Number</label
                                            >
                                            <input
                                                type="text"
                                                bind:value={flightNumber}
                                                class="premium-input"
                                                placeholder="e.g. MH 123 (Optional)"
                                                maxlength="50"
                                            />
                                        </div>
                                    </div>
                                    {#if !(page.props as any).auth?.customer}
                                        <div class="create-account-box">
                                            <label class="checkbox-label">
                                                <input
                                                    type="checkbox"
                                                    bind:checked={
                                                        form.create_account
                                                    }
                                                />
                                                Create an account for faster booking
                                                next time
                                            </label>
                                            <span class="login-link"
                                                >Already have an account? <a
                                                    href="/customer/login"
                                                    >Login here</a
                                                ></span
                                            >
                                        </div>
                                        {#if form.create_account}
                                            <div
                                                class="form-row"
                                                style="margin-top: 16px;"
                                            >
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        >Password *</label
                                                    >
                                                    <div class="pw-wrap">
                                                        <input
                                                            type={showPassword
                                                                ? 'text'
                                                                : 'password'}
                                                            bind:value={
                                                                form.password
                                                            }
                                                            class="premium-input"
                                                            placeholder="Min. 8 characters"
                                                        />
                                                        <button
                                                            type="button"
                                                            class="pw-toggle"
                                                            onclick={() =>
                                                                (showPassword =
                                                                    !showPassword)}
                                                            ><i
                                                                class="fas {showPassword
                                                                    ? 'fa-eye-slash'
                                                                    : 'fa-eye'}"
                                                            ></i></button
                                                        >
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        >Confirm Password *</label
                                                    >
                                                    <div class="pw-wrap">
                                                        <input
                                                            type={showConfirmPassword
                                                                ? 'text'
                                                                : 'password'}
                                                            bind:value={
                                                                form.password_confirmation
                                                            }
                                                            class="premium-input"
                                                            placeholder="Repeat password"
                                                        />
                                                        <button
                                                            type="button"
                                                            class="pw-toggle"
                                                            onclick={() =>
                                                                (showConfirmPassword =
                                                                    !showConfirmPassword)}
                                                            ><i
                                                                class="fas {showConfirmPassword
                                                                    ? 'fa-eye-slash'
                                                                    : 'fa-eye'}"
                                                            ></i></button
                                                        >
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
                                            <label
                                                class="extra-card {selectedExtras.includes(
                                                    extra.id,
                                                )
                                                    ? 'extra-card--selected'
                                                    : ''}"
                                            >
                                                <input
                                                    type="checkbox"
                                                    class="extra-checkbox"
                                                    checked={selectedExtras.includes(
                                                        extra.id,
                                                    )}
                                                    onchange={() => {
                                                        if (
                                                            selectedExtras.includes(
                                                                extra.id,
                                                            )
                                                        ) {
                                                            selectedExtras =
                                                                selectedExtras.filter(
                                                                    (id) =>
                                                                        id !==
                                                                        extra.id,
                                                                );
                                                        } else {
                                                            selectedExtras = [
                                                                ...selectedExtras,
                                                                extra.id,
                                                            ];
                                                        }
                                                    }}
                                                />
                                                <div
                                                    class="extra-card__content"
                                                >
                                                    <div
                                                        class="extra-card__top"
                                                    >
                                                        <span
                                                            class="extra-label"
                                                            >{extra.label}</span
                                                        >
                                                        {#if extra.price > 0}
                                                            <span
                                                                class="extra-price"
                                                                >+{formatRupiah(
                                                                    isRoundTrip ? extra.price * 2 : extra.price,
                                                                )}</span
                                                            >
                                                        {:else}
                                                            <span
                                                                class="extra-price extra-price--free"
                                                                >Free</span
                                                            >
                                                        {/if}
                                                    </div>
                                                    <p class="extra-desc">
                                                        {extra.description}
                                                    </p>
                                                </div>
                                                <div
                                                    class="extra-check-icon {selectedExtras.includes(
                                                        extra.id,
                                                    )
                                                        ? 'visible'
                                                        : ''}"
                                                >
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            </label>
                                        {/each}
                                    </div>
                                    <div
                                        class="form-group"
                                        style="margin-top: 24px;"
                                    >
                                        <label class="form-label"
                                            >Comments to the order</label
                                        >
                                        <textarea
                                            bind:value={form.notes}
                                            rows="3"
                                            class="premium-input"
                                            placeholder="e.g. Non-standard luggage, special requirements..."
                                            style="resize: vertical;"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <!-- STEP 4: CONFIRMATION -->
                        {#if currentStep === 4}
                            <div class="step-card bw-split-in-up">
                                <div class="step-card__header">
                                    <h4>Confirmation</h4>
                                    <p>Review your booking and confirm.</p>
                                </div>
                                <div class="step-card__body">
                                    <div
                                        class="terms-box"
                                        style="margin-top: 10px;"
                                    >
                                        <label class="terms-label">
                                            <input
                                                type="checkbox"
                                                bind:checked={agreedToTerms}
                                                class="terms-checkbox"
                                            />
                                            <span>
                                                I agree to the <a
                                                    href="/terms"
                                                    target="_blank"
                                                    >Terms &amp; Conditions</a
                                                > of service
                                            </span>
                                        </label>
                                        <p
                                            class="terms-provider"
                                            style="margin-top: 15px; font-size: 14px; color: #64748b;"
                                        >
                                            By clicking "Complete Booking", you
                                            will be redirected to Xendit's
                                            secure payment gateway to choose
                                            your preferred payment method and
                                            complete the transaction.
                                        </p>
                                    </div>

                                    {#if recaptchaEnabled}
                                        <div
                                            class="recaptcha-wrapper"
                                            style="margin-top: 20px;"
                                        >
                                            <div
                                                id="checkout-recaptcha-container"
                                            ></div>
                                            {#if form.errors['g-recaptcha-response']}
                                                <div
                                                    class="error-text"
                                                    style="margin-top: 6px; color: #dc3545; font-size: 14px;"
                                                >
                                                    {form.errors[
                                                        'g-recaptcha-response'
                                                    ]}
                                                </div>
                                            {/if}
                                        </div>
                                    {/if}

                                    <div
                                        class="security-badges"
                                        style="margin-top: 24px; display: flex; gap: 15px; flex-wrap: wrap; color: #64748b; font-size: 13px;"
                                    >
                                        <span
                                            ><i
                                                class="fas fa-shield-alt text-success"
                                            ></i> SSL Secured</span
                                        >
                                        <span
                                            ><i class="fas fa-lock text-primary"
                                            ></i> Powered by Xendit</span
                                        >
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <!-- Navigation Buttons -->
                        <div class="step-nav">
                            {#if currentStep > 1}
                                <button
                                    type="button"
                                    class="btn-back"
                                    onclick={prevStep}
                                >
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            {:else}
                                <a
                                    href="/booking/airport-transfer?pickup={encodeURIComponent(
                                        transfer?.pickup || '',
                                    )}&dropoff={encodeURIComponent(
                                        transfer?.dropoff || '',
                                    )}&date={transfer?.date ||
                                        ''}&time={encodeURIComponent(
                                        transfer?.time || '',
                                    )}&passengers={transfer?.passengers || '1'}"
                                    class="btn-back"
                                >
                                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                                </a>
                            {/if}

                            {#if currentStep < 4}
                                <button
                                    type="button"
                                    class="btn-next"
                                    onclick={nextStep}
                                    disabled={isValidatingEmail}
                                >
                                    {#if isValidatingEmail}
                                        Validating... <i
                                            class="fas fa-spinner fa-spin"
                                        ></i>
                                    {:else}
                                        Continue <i class="fas fa-arrow-right"
                                        ></i>
                                    {/if}
                                </button>
                            {:else}
                                {#if form.errors.payment_method}
                                    <div
                                        class="checkout-error-alert"
                                        style="color: #e74c3c; background: #fadbd8; padding: 10px; border-radius: 6px; margin-bottom: 15px; width: 100%; text-align: left;"
                                    >
                                        <i class="fas fa-exclamation-circle"
                                        ></i>
                                        {form.errors.payment_method}
                                    </div>
                                {/if}
                                <button
                                    type="submit"
                                    class="btn-submit"
                                    disabled={form.processing ||
                                        !agreedToTerms ||
                                        !!zoneError}
                                >
                                    {#if form.processing}
                                        <i class="fas fa-spinner fa-spin"></i> Processing...
                                    {:else}
                                        <i class="fas fa-lock"></i> Pay {formatRupiah(
                                            totalPrice,
                                        )} Securely
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
                                <img
                                    src={vehicleCategory.image_url}
                                    alt={vehicleCategory.title}
                                />
                                <div class="sidebar-vehicle-info">
                                    <h5>{vehicleCategory.title}</h5>
                                    <span
                                        >{vehicleCategory.passenger_capacity ??
                                            '—'} pax · {vehicleCategory.luggage_capacity ??
                                            '—'} bags</span
                                    >
                                </div>
                            </div>
                        {/if}

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-box">
                            <h4 class="sidebar-title">Order Summary</h4>

                            {#if zoneError}
                                <div
                                    class="alert alert-danger p-3 mb-4 rounded-3 border-0"
                                >
                                    <i class="fas fa-exclamation-triangle me-2"
                                    ></i>
                                    {zoneError}
                                </div>
                            {/if}

                            <!-- Route -->
                            <div class="sidebar-section-title mt-0">Route</div>
                            <div class="sidebar-route">
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
                                            >{form.pickup_address ||
                                                transfer?.pickup ||
                                                '—'}</span
                                        >
                                        {#if fullPickupAddress && fullPickupAddress !== (form.pickup_address || transfer?.pickup)}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px;"
                                                >{fullPickupAddress}</small
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
                                            >{form.dropoff_address ||
                                                transfer?.dropoff ||
                                                '—'}</span
                                        >
                                        {#if fullDropoffAddress && fullDropoffAddress !== (form.dropoff_address || transfer?.dropoff)}
                                            <small
                                                class="route-full-address text-muted"
                                                style="margin-top: 2px;"
                                                >{fullDropoffAddress}</small
                                            >
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar-divider"></div>

                            <!-- Trip details -->
                            <div class="sidebar-section-title">
                                Trip Details
                            </div>
                            <div class="sidebar-info-grid">
                                <div class="sidebar-info-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Date</span
                                        >
                                        <span class="sidebar-info-value"
                                            >{form.date
                                                ? formatDisplayDate(form.date)
                                                : '—'}</span
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
                                            >{form.time
                                                ? formatTime12(form.time)
                                                : '—'}</span
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
                                            >{form.passengers}</span
                                        >
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-road"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Distance</span
                                        >
                                        <span class="sidebar-info-value">
                                            {#if !isPricingLoaded}
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                            {:else}
                                                {exactDistanceStr ||
                                                    (priceZoneInfo.distance_km
                                                        ? priceZoneInfo.distance_km +
                                                          ' km'
                                                        : '0 km')}
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Est. Duration</span
                                        >
                                        <span class="sidebar-info-value">
                                            {#if !isPricingLoaded}
                                                <i
                                                    class="fas fa-spinner fa-spin"
                                                ></i>
                                            {:else}
                                                {exactDurationStr || '0 min'}
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                <div class="sidebar-info-item">
                                    <i class="fas fa-car"></i>
                                    <div>
                                        <span class="sidebar-info-label"
                                            >Transfer Type</span
                                        >
                                        <span class="sidebar-info-value"
                                            >Private
                                            {#if isRoundTrip}
                                                <span class="sidebar-round-trip-tag">Round-Trip</span>
                                            {/if}
                                        </span>
                                    </div>
                                </div>
                                {#if isRoundTrip && form.return_date}
                                    <div class="sidebar-info-item">
                                        <i class="fas fa-undo-alt" style="color: #10b981;"></i>
                                        <div>
                                            <span class="sidebar-info-label">Return Date</span>
                                            <span class="sidebar-info-value">{formatDisplayDate(form.return_date)}</span>
                                        </div>
                                    </div>
                                {/if}
                                {#if isRoundTrip && form.return_time}
                                    <div class="sidebar-info-item">
                                        <i class="fas fa-clock" style="color: #10b981;"></i>
                                        <div>
                                            <span class="sidebar-info-label">Return Time</span>
                                            <span class="sidebar-info-value">{formatTime12(form.return_time)}</span>
                                        </div>
                                    </div>
                                {/if}
                            </div>

                            <div class="sidebar-divider"></div>

                            <!-- Pricing breakdown -->
                            <div class="sidebar-section-title">
                                Price Breakdown
                            </div>
                            <div class="sidebar-row">
                                <span
                                    >Vehicle ({vehicleCategory?.title ??
                                        'Transfer'})
                                    {#if isRoundTrip}
                                        <span class="sidebar-row-sub">× 2 legs</span>
                                    {/if}
                                </span>
                                {#if !isPricingLoaded}
                                    <span class="price-calculating"
                                        ><i class="fas fa-spinner fa-spin"></i> Calculating...</span
                                    >
                                {:else if isRoundTrip}
                                    <span>{formatRupiah(basePrice * 2)}</span>
                                {:else}
                                    <span>{formatRupiah(basePrice)}</span>
                                {/if}
                            </div>
                            {#if isRoundTrip && isPricingLoaded && basePrice > 0}
                                <div class="sidebar-row sidebar-row--sub">
                                    <span style="font-size: 12px; color: #64748b;">Outbound + Return ({formatRupiah(basePrice)} each)</span>
                                    <span style="font-size: 12px; color: #64748b;"></span>
                                </div>
                            {/if}
                            {#each selectedExtras as id}
                                {@const extra = EXTRAS.find((e) => e.id === id)}
                                {#if extra && extra.price > 0}
                                    <div class="sidebar-row">
                                        <span
                                            >{extra.label}
                                            {#if isRoundTrip}
                                                <span class="sidebar-row-sub">× 2 legs</span>
                                            {/if}
                                        </span>
                                        <span>+{formatRupiah(isRoundTrip ? extra.price * 2 : extra.price)}</span>
                                    </div>
                                    {#if isRoundTrip}
                                        <div class="sidebar-row sidebar-row--sub" style="margin-top: -6px; margin-bottom: 8px;">
                                            <span style="font-size: 12px; color: #64748b;">Outbound + Return ({formatRupiah(extra.price)} each)</span>
                                            <span style="font-size: 12px; color: #64748b;"></span>
                                        </div>
                                    {/if}
                                {/if}
                            {/each}

                            <div class="sidebar-divider"></div>
                            <div class="sidebar-total">
                                <span>Total</span>
                                {#if !isPricingLoaded}
                                    <span class="price-calculating"
                                        ><i class="fas fa-spinner fa-spin"
                                        ></i></span
                                    >
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
    @media (max-width: 1024px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Stepper */
    .stepper {
        display: flex;
        align-items: center;
        margin-bottom: 28px;
        background: #fff;
        border-radius: 14px;
        padding: 20px 24px;
        border: 1px solid #eaeef2;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }
    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .stepper-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    .stepper-item.active .stepper-circle {
        background: var(--travhub-base);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.15);
    }
    .stepper-item.completed .stepper-circle {
        background: #10b981;
        color: #fff;
    }
    .stepper-label {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }
    .stepper-item.active .stepper-label {
        color: var(--travhub-base);
    }
    .stepper-item.completed .stepper-label {
        color: #10b981;
    }
    .stepper-line {
        flex: 1;
        height: 3px;
        background: #f1f5f9;
        margin: 0 6px;
        margin-bottom: 22px;
        border-radius: 2px;
        transition: all 0.3s;
    }
    .stepper-line.active {
        background: #10b981;
    }
    @media (max-width: 576px) {
        .stepper-label {
            display: none;
        }
        .stepper-circle {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }
    }

    /* Step Card */
    .step-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }
    .step-card__header {
        padding: 24px 28px 10px;
        border-bottom: 1px solid #f0f4f8;
    }
    .step-card__header h4 {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .step-card__header p {
        font-size: 14px;
        color: #64748b;
        margin: 4px 0 0;
    }
    .step-card__body {
        padding: 28px;
    }

    /* Form */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.08);
    }
    .premium-input.input-error {
        border-color: #ef4444;
        background: #fef2f2;
    }
    .error-text {
        font-size: 12px;
        color: #ef4444;
        font-weight: 500;
    }
    .input-icon-wrap {
        position: relative;
    }
    .input-icon-r {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    .helper-notice {
        padding: 12px 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        font-size: 14px;
        color: #1d4ed8;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }
    .helper-notice i {
        margin-top: 2px;
        flex-shrink: 0;
    }

    /* Account boxes */
    .use-profile-box,
    .create-account-box {
        padding: 14px 18px;
        background: #f0fdf4;
        border: 1.5px dashed #86efac;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .create-account-box {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    .checkbox-label input {
        width: 18px;
        height: 18px;
        accent-color: var(--travhub-base);
        cursor: pointer;
    }
    .login-link {
        font-size: 13px;
        color: #64748b;
    }
    .login-link a {
        color: var(--travhub-base);
        font-weight: 700;
    }
    .pw-wrap {
        position: relative;
    }
    .pw-toggle {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
    }
    .pw-toggle:hover {
        color: var(--travhub-base);
    }

    /* Extras */
    .extras-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .extra-card {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 20px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s;
        background: #fff;
    }
    .extra-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .extra-card--selected {
        border-color: var(--travhub-base);
        background: rgba(229, 32, 41, 0.03);
    }
    .extra-checkbox {
        display: none;
    }
    .extra-card__content {
        flex: 1;
    }
    .extra-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .extra-label {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .extra-price {
        font-size: 15px;
        font-weight: 700;
        color: var(--travhub-base);
    }
    .extra-price--free {
        color: #10b981;
    }
    .extra-desc {
        font-size: 13px;
        color: #64748b;
        margin: 0;
    }
    .extra-check-icon {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        transition: all 0.2s;
        flex-shrink: 0;
    }
    .extra-check-icon.visible {
        background: var(--travhub-base);
        color: #fff;
    }

    /* Summary */
    .summary-section {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f0f4f8;
    }
    .summary-section:last-of-type {
        border-bottom: none;
    }
    .summary-section-title {
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #94a3b8;
        margin-bottom: 12px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 6px 0;
    }
    .summary-label {
        font-size: 14px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .summary-value {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        text-align: right;
        max-width: 60%;
    }
    .summary-vehicle {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .summary-vehicle img {
        width: 80px;
        height: 60px;
        object-fit: contain;
        background: #f8fafc;
        border-radius: 8px;
        padding: 4px;
    }
    .summary-vehicle div {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .summary-vehicle strong {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
    }
    .summary-vehicle span {
        font-size: 13px;
        color: #64748b;
    }
    .summary-vehicle-price {
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
    }
    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 0 0;
        border-top: 2px solid #1e293b;
        margin-top: 8px;
    }
    .summary-total span:first-child {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }
    .total-amount {
        font-size: 28px;
        font-weight: 800;
        color: var(--travhub-base);
    }

    /* Navigation */
    .step-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
    }
    .btn-next,
    .btn-submit {
        background: var(--travhub-base);
        color: #fff;
        padding: 14px 32px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(229, 32, 41, 0.25);
    }
    .btn-next:hover:not(:disabled),
    .btn-submit:hover:not(:disabled) {
        background: #111;
        transform: translateY(-2px);
    }
    .btn-next:disabled,
    .btn-submit:disabled {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
    }
    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 14px 24px;
        border: none;
        border-radius: 50px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        text-decoration: none;
    }
    .btn-back:hover {
        background: #e2e8f0;
        color: #0f172a;
    }
    .alert-error {
        padding: 14px 18px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        font-size: 14px;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    /* Sidebar */
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
    .sidebar-zone-info i {
        color: #94a3b8;
        font-size: 10px;
    }
    .price-calculating {
        color: #94a3b8;
        font-style: italic;
    }

    /* Route in sidebar */
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

    /* Trip details grid */
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

    /* Price rows */
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
        margin-top: 14px;
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .sidebar-note i {
        color: #10b981;
    }

    /* ── Step 5: Payment (flat + accordion) ── */

    /* Direct method cards (Akulaku, AstraPay, Indomaret, Uangme) */
    .pay-direct-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
        margin-bottom: 14px;
    }
    @media (max-width: 500px) {
        .pay-direct-grid {
            grid-template-columns: 1fr;
        }
    }
    .pay-direct-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 10px;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pay-direct-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .pay-direct-card--selected {
        border-color: var(--travhub-base);
        background: rgba(229, 32, 41, 0.04);
        box-shadow: 0 0 0 3px rgba(229, 32, 41, 0.1);
    }
    .pay-direct-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pay-direct-logo {
        height: 28px;
        max-width: 80px;
        object-fit: contain;
        border-radius: 4px;
    }
    .pay-direct-badge {
        font-size: 10px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        white-space: nowrap;
    }
    .pay-direct-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .pay-direct-info strong {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .pay-direct-info span {
        font-size: 12px;
        color: #64748b;
    }

    /* VA Accordion */
    .va-accordion {
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 14px;
        transition: border-color 0.2s;
    }
    .va-accordion--selected {
        border-color: var(--travhub-base);
        box-shadow: 0 0 0 3px rgba(229, 32, 41, 0.1);
    }
    .va-accordion-trigger {
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: #fff;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
        text-align: left;
    }
    .va-accordion-trigger:hover {
        background: #f8fafc;
    }
    .va-trigger-left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .va-trigger-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eff6ff;
        color: #3b82f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .va-accordion--selected .va-trigger-icon {
        background: rgba(229, 32, 41, 0.08);
        color: var(--travhub-base);
    }
    .va-trigger-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .va-trigger-text strong {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }
    .va-trigger-text span {
        font-size: 12px;
        color: #64748b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 280px;
    }
    .va-trigger-right {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-shrink: 0;
        margin-left: 12px;
    }
    .va-count-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 20px;
        background: #f1f5f9;
        color: #475569;
    }
    .va-accordion--selected .va-count-badge {
        background: rgba(229, 32, 41, 0.1);
        color: var(--travhub-base);
    }
    .va-chevron {
        font-size: 12px;
        color: #94a3b8;
        transition: transform 0.2s;
    }
    .va-accordion-body {
        padding: 0 18px 18px;
        background: #fafafa;
        border-top: 1px solid #f0f4f8;
        animation: slideDown 0.18s ease;
    }
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .va-accordion-note {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 12px;
        color: #92400e;
        padding: 10px 0 12px;
    }
    .va-accordion-note i {
        color: #d97706;
    }
    .xendit-methods {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 12px;
        margin-bottom: 0;
    }
    .xendit-method-card {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }
    .xendit-method-card:hover {
        border-color: #94a3b8;
        background: #f8fafc;
    }
    .xendit-method-card--selected {
        border-color: var(--travhub-base);
        background: rgba(229, 32, 41, 0.04);
        box-shadow: 0 0 0 3px rgba(229, 32, 41, 0.1);
    }
    .method-radio {
        display: none;
    }
    .xendit-method-logo {
        width: 64px;
        height: 28px;
        object-fit: contain;
        border-radius: 4px;
        display: block;
    }
    .xendit-method-label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
        text-align: center;
    }
    .xendit-check {
        position: absolute;
        top: 6px;
        right: 6px;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        transition: all 0.2s;
        opacity: 0;
    }
    .xendit-check.visible {
        background: var(--travhub-base);
        color: #fff;
        opacity: 1;
    }

    .pay-select-hint {
        font-size: 13px;
        color: #94a3b8;
        text-align: center;
        padding: 8px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .terms-box {
        padding: 16px 18px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .terms-label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .terms-checkbox {
        width: 17px;
        height: 17px;
        accent-color: var(--travhub-base);
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .terms-label a {
        color: var(--travhub-base);
        text-decoration: underline;
    }
    .terms-provider {
        font-size: 12px;
        color: #94a3b8;
        margin: 0;
        padding-left: 27px;
        line-height: 1.6;
    }

    .security-badges {
        display: flex;
        justify-content: center;
        gap: 16px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #94a3b8;
    }
    .security-badges span {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .security-badges i {
        color: #10b981;
    }

    .route-full-address {
        font-size: 11px;
        letter-spacing: 0.2px;
        line-height: 1.4;
    }

    /* ── Round-Trip UI ── */
    .co-round-trip-badge {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 16px;
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border: 1px solid #6ee7b7;
        border-radius: 10px;
        margin-bottom: 16px;
        font-size: 14px;
        color: #065f46;
    }
    .co-round-trip-badge i {
        color: #10b981;
        font-size: 16px;
        flex-shrink: 0;
    }
    .co-return-trip-section {
        margin-top: 20px;
        padding: 16px;
        background: #f0fdf4;
        border: 1.5px dashed #6ee7b7;
        border-radius: 12px;
    }
    .co-return-trip-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 700;
        color: #065f46;
        margin-bottom: 14px;
    }
    .co-return-trip-header i {
        color: #10b981;
    }
    .co-return-note {
        font-size: 12px;
        color: #64748b;
        margin-top: 10px;
        margin-bottom: 0;
        line-height: 1.5;
    }
    .co-return-note i {
        color: #10b981;
        margin-right: 4px;
    }
    .sidebar-round-trip-tag {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        background: #10b981;
        color: #fff;
        padding: 1px 7px;
        border-radius: 50px;
        margin-left: 5px;
        vertical-align: middle;
        letter-spacing: 0.3px;
    }
    .sidebar-row-sub {
        font-size: 11px;
        color: #94a3b8;
        margin-left: 4px;
    }
</style>
