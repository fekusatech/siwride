<script lang="ts">
    import SearchableSelect from '@/components/SearchableSelect.svelte';
    import Flatpickr from '@/components/Flatpickr.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    declare const google: any;

    let {
        drivers,
        google_maps_api_key,
        initialDate = '',
        order = null,
        onSuccess = () => {},
        footer, // This will be the snippet
    } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        booking_code: order?.booking_code ?? '',
        order_number: order?.order_number ?? '',
        date: order ? order.date.split('T')[0] : initialDate,
        time: order?.time ?? '',
        customer_name: order?.customer_name ?? order?.customer?.name ?? '',
        customer_phone: order?.customer_phone ?? order?.customer?.phone ?? '',
        customer_email: order?.customer_email ?? order?.customer?.email ?? '',
        flight_number: order?.flight_number ?? '',
        driver_id: order?.driver_id?.toString() ?? '',
        vehicle_id: order?.vehicle_id?.toString() ?? '',
        pickup_address: order?.pickup_address ?? '',
        pickup_latitude: order?.pickup_latitude ?? null,
        pickup_longitude: order?.pickup_longitude ?? null,
        dropoff_address: order?.dropoff_address ?? '',
        dropoff_latitude: order?.dropoff_latitude ?? null,
        dropoff_longitude: order?.dropoff_longitude ?? null,
        passengers: order?.passengers ?? 1,
        price: order?.price ?? 0,
        parking_gas_fee: order?.parking_gas_fee ?? 0,
        status: order?.status ?? 'pending',
    });

    let driverOptions = $derived(
        drivers.map((d) => ({ value: d.id, label: d.name })),
    );
    let selectedDriver = $derived(
        drivers.find((d) => d.id === Number(form.driver_id)),
    );
    let availableVehicles = $derived(selectedDriver?.vehicles || []);

    let pickupInZone = $state(true);
    let dropoffInZone = $state(true);
    let validatingPickup = $state(false);
    let validatingDropoff = $state(false);

    async function checkZone(
        lat: number,
        lng: number,
        type: 'pickup' | 'dropoff',
    ) {
        if (type === 'pickup') validatingPickup = true;
        else validatingDropoff = true;

        try {
            const response = await fetch('/admin/zones/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN':
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute('content') || '',
                },
                body: JSON.stringify({ lat, lng }),
            });
            const data = await response.json();
            if (type === 'pickup') pickupInZone = data.inside;
            else dropoffInZone = data.inside;
        } catch (error) {
            console.error('Zone validation error:', error);
        } finally {
            if (type === 'pickup') validatingPickup = false;
            else validatingDropoff = false;
        }
    }

    $effect(() => {
        if (form.driver_id) {
            const driverId = Number(form.driver_id);
            // Auto-select if there's only one vehicle
            if (availableVehicles.length === 1 && !form.vehicle_id) {
                form.vehicle_id = availableVehicles[0].id.toString();
            } else if (form.vehicle_id) {
                // Clear if current vehicle doesn't belong to selected driver
                const isValid = availableVehicles.some(
                    (v) => v.id === Number(form.vehicle_id),
                );
                if (!isValid) form.vehicle_id = '';
            }
        } else {
            form.vehicle_id = '';
        }
    });

    let pickupInput: HTMLInputElement;
    let dropoffInput: HTMLInputElement;

    function initGoogleMaps() {
        if (!google_maps_api_key) return;
        const scriptExists = document.querySelector(
            'script[src*="maps.googleapis.com"]',
        );
        if (!scriptExists) {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${google_maps_api_key}&libraries=places`;
            script.async = true;
            script.defer = true;
            script.onload = () => setupAutocomplete();
            document.head.appendChild(script);
        } else {
            if (
                typeof google !== 'undefined' &&
                google.maps &&
                google.maps.places
            ) {
                setupAutocomplete();
            }
        }
    }

    function setupAutocomplete() {
        if (!pickupInput || !dropoffInput) return;

        // Define Bali bounds to restrict autocomplete suggestions
        const baliBounds = new google.maps.LatLngBounds(
            new google.maps.LatLng(-8.9472, 114.4173), // South West
            new google.maps.LatLng(-8.0583, 115.7118), // North East
        );

        const autocompleteOptions = {
            types: ['geocode', 'establishment'],
            bounds: baliBounds,
            componentRestrictions: { country: 'id' }, // Optional: restrict to Indonesia
            strictBounds: true, // Set to true if you want to ONLY show results inside Bali
        };

        const pickupAutocomplete = new google.maps.places.Autocomplete(
            pickupInput,
            autocompleteOptions,
        );
        pickupAutocomplete.addListener('place_changed', () => {
            const place = pickupAutocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                form.pickup_address = place.formatted_address || '';
                form.pickup_latitude = place.geometry.location.lat();
                form.pickup_longitude = place.geometry.location.lng();
                checkZone(
                    form.pickup_latitude,
                    form.pickup_longitude,
                    'pickup',
                );
            }
        });

        const dropoffAutocomplete = new google.maps.places.Autocomplete(
            dropoffInput,
            autocompleteOptions,
        );
        dropoffAutocomplete.addListener('place_changed', () => {
            const place = dropoffAutocomplete.getPlace();
            if (place.geometry && place.geometry.location) {
                form.dropoff_address = place.formatted_address || '';
                form.dropoff_latitude = place.geometry.location.lat();
                form.dropoff_longitude = place.geometry.location.lng();
                checkZone(
                    form.dropoff_latitude,
                    form.dropoff_longitude,
                    'dropoff',
                );
            }
        });
    }

    onMount(() => {
        initGoogleMaps();
    });

    function handleSubmit(e: Event) {
        e.preventDefault();

        if (!pickupInZone || !dropoffInZone) {
            alert(
                'Cannot save: One of the addresses is outside the service zones.',
            );
            return;
        }

        if (order) {
            form.put(`/admin/orders/${order.id}`, {
                onSuccess: () => onSuccess(),
            });
        } else {
            form.post('/admin/orders', {
                onSuccess: () => {
                    form.reset();
                    onSuccess();
                },
            });
        }
    }
</script>

<form onsubmit={handleSubmit}>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="booking_code" class="form-label">Booking Code</label>
            <input
                type="text"
                class="form-control"
                id="booking_code"
                bind:value={form.booking_code}
                placeholder="SW-12345"
                required
            />
            {#if form.errors.booking_code}<div class="text-danger small mt-1">
                    {form.errors.booking_code}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="order_number" class="form-label">Order Number</label>
            <input
                type="text"
                class="form-control"
                id="order_number"
                bind:value={form.order_number}
                placeholder="001"
                required
            />
        </div>
        <div class="col-md-6">
            <label for="date_form" class="form-label">Date</label>
            <Flatpickr
                id="date_form"
                bind:value={form.date}
                placeholder="Select Date"
                options={{
                    altInput: true,
                    altFormat: 'j F Y',
                    dateFormat: 'Y-m-d',
                }}
            />
            {#if form.errors.date}<div class="text-danger small mt-1">
                    {form.errors.date}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="time_form" class="form-label">Time</label>
            <Flatpickr
                id="time_form"
                bind:value={form.time}
                placeholder="Select Time"
                options={{
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                }}
            />
            {#if form.errors.time}<div class="text-danger small mt-1">
                    {form.errors.time}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="customer_name" class="form-label">Customer Name</label>
            <input
                type="text"
                class="form-control {form.errors.customer_name
                    ? 'is-invalid'
                    : ''}"
                id="customer_name"
                bind:value={form.customer_name}
                required
            />
            {#if form.errors.customer_name}<div class="invalid-feedback">
                    {form.errors.customer_name}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="customer_phone" class="form-label">Phone</label>
            <input
                type="tel"
                class="form-control {form.errors.customer_phone
                    ? 'is-invalid'
                    : ''}"
                id="customer_phone"
                bind:value={form.customer_phone}
                required
            />
            {#if form.errors.customer_phone}<div class="invalid-feedback">
                    {form.errors.customer_phone}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="customer_email" class="form-label">Email</label>
            <input
                type="email"
                class="form-control {form.errors.customer_email
                    ? 'is-invalid'
                    : ''}"
                id="customer_email"
                bind:value={form.customer_email}
                required
            />
            {#if form.errors.customer_email}<div class="invalid-feedback">
                    {form.errors.customer_email}
                </div>{/if}
        </div>
        <div class="col-md-6">
            <label for="flight_number" class="form-label">Flight Number</label>
            <input
                type="text"
                class="form-control"
                id="flight_number"
                bind:value={form.flight_number}
                placeholder="GA123"
            />
        </div>
        <div class="col-md-6">
            <SearchableSelect
                id="driver_id_form"
                label="Assign Driver"
                options={driverOptions}
                bind:value={form.driver_id}
                placeholder="Search Driver..."
            />
        </div>
        <div class="col-md-6">
            <label for="status_form" class="form-label">Status</label>
            <select
                class="form-select"
                id="status_form"
                bind:value={form.status}
            >
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>
        <div class="col-md-12 text-nowrap">
            <label for="vehicle_id_form" class="form-label">Vehicle</label>
            <select
                class="form-select"
                id="vehicle_id_form"
                bind:value={form.vehicle_id}
                disabled={!form.driver_id}
            >
                <option value="">Select Vehicle</option>
                {#each availableVehicles as vehicle}
                    <option value={vehicle.id}
                        >{vehicle.registration_number} ({vehicle.brand}
                        {vehicle.model})</option
                    >
                {/each}
            </select>
        </div>
        <div class="col-md-6">
            <label for="pickup_address_form" class="form-label">Pick Up</label>
            <input
                type="text"
                class="form-control {!pickupInZone ? 'is-invalid' : ''}"
                id="pickup_address_form"
                bind:this={pickupInput}
                bind:value={form.pickup_address}
                placeholder="Search address..."
                required
            />
            {#if validatingPickup}
                <div class="text-muted small mt-1">Checking zone...</div>
            {:else if !pickupInZone}
                <div class="invalid-feedback">
                    Address is outside the service zones.
                </div>
            {/if}
        </div>
        <div class="col-md-6">
            <label for="dropoff_address_form" class="form-label">Drop Off</label
            >
            <input
                type="text"
                class="form-control {!dropoffInZone ? 'is-invalid' : ''}"
                id="dropoff_address_form"
                bind:this={dropoffInput}
                bind:value={form.dropoff_address}
                placeholder="Search address..."
                required
            />
            {#if validatingDropoff}
                <div class="text-muted small mt-1">Checking zone...</div>
            {:else if !dropoffInZone}
                <div class="invalid-feedback">
                    Address is outside the service zones.
                </div>
            {/if}
        </div>
        <div class="col-md-4">
            <label for="passengers_form" class="form-label">Pass</label>
            <input
                type="number"
                class="form-control"
                id="passengers_form"
                bind:value={form.passengers}
                min="1"
                required
            />
        </div>
        <div class="col-md-4">
            <label for="price_form" class="form-label">Price (IDR)</label>
            <input
                type="number"
                class="form-control"
                id="price_form"
                bind:value={form.price}
                min="0"
                required
            />
        </div>
        <div class="col-md-4">
            <label for="parking_form" class="form-label">P/B (IDR)</label>
            <input
                type="number"
                class="form-control"
                id="parking_form"
                bind:value={form.parking_gas_fee}
                min="0"
                required
            />
        </div>
    </div>
    <div class="mt-4 d-flex justify-content-end gap-2">
        {#if footer}
            {@render footer({
                processing:
                    form.processing || validatingPickup || validatingDropoff,
            })}
        {:else}
            <button
                type="submit"
                class="btn btn-primary"
                disabled={form.processing ||
                    validatingPickup ||
                    validatingDropoff ||
                    !pickupInZone ||
                    !dropoffInZone}
            >
                {form.processing ? 'Saving...' : 'Save Order'}
            </button>
        {/if}
    </div>
</form>
