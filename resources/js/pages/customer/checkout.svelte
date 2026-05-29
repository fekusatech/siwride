<script lang="ts">
    import { page, useForm, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import flatpickr from 'flatpickr';
    import 'flatpickr/dist/flatpickr.min.css';

    interface VehicleCategory {
        id: number;
        title: string;
        description: string;
        passenger_capacity: number | null;
        luggage_capacity: number | null;
        advantages: string[] | null;
        base_price: string;
        image_url: string;
        vehicle_type: string;
    }

    let { transfer, vehicleCategory } = $props<{
        transfer: { pickup: string; dropoff: string; date: string; time: string; passengers: string };
        vehicleCategory: VehicleCategory | null;
    }>();

    // Steps: 1=Transfer Details, 2=Passenger Info, 3=Extras, 4=Summary
    let currentStep = $state(1);
    let stepError = $state('');
    let isValidatingEmail = $state(false);
    let emailError = $state('');
    let showPassword = $state(false);
    let showConfirmPassword = $state(false);

    const EXTRAS = [
        { id: 'english_driver', label: 'English-speaking driver', description: 'A driver with basic English communication ability.', price: 3 },
        { id: 'water', label: 'Drinking water', description: 'A bottle of still water (0.5L).', price: 2 },
        { id: 'pets', label: 'I am travelling with pets', description: 'Pets must be kept in a carrier. Additional charges may apply.', price: 0 },
    ];

    let selectedExtras = $state<string[]>([]);

    const form = useForm({
        customer_name: '',
        email: '',
        customer_phone: '',
        pickup_address: transfer?.pickup || '',
        dropoff_address: transfer?.dropoff || '',
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

    // Computed totals
    let extrasTotal = $derived(
        selectedExtras.reduce((sum, id) => {
            const extra = EXTRAS.find((e) => e.id === id);
            return sum + (extra?.price ?? 0);
        }, 0)
    );
    let basePrice = $derived(vehicleCategory ? parseFloat(vehicleCategory.base_price) : 0);
    let totalPrice = $derived(basePrice + extrasTotal);

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

    function datePicker(node: HTMLInputElement) {
        const fp = flatpickr(node, {
            minDate: 'today',
            defaultDate: form.date || null,
            disableMobile: 'true',
            monthSelectorType: 'static',
            onChange: (_: any, dateStr: string) => { form.date = dateStr; },
        });
        return { destroy() { fp.destroy(); } };
    }

    function timePicker(node: HTMLInputElement) {
        const fp = flatpickr(node, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            defaultDate: form.time || null,
            disableMobile: 'true',
            onChange: (_: any, dateStr: string) => { form.time = dateStr; },
        });
        return { destroy() { fp.destroy(); } };
    }

    async function nextStep() {
        stepError = '';
        if (currentStep === 1) {
            if (!form.pickup_address) { stepError = 'Please provide a pickup location.'; return; }
            if (!form.dropoff_address) { stepError = 'Please provide a destination.'; return; }
            if (!form.date) { stepError = 'Please select a transfer date.'; return; }
            if (!form.time) { stepError = 'Please select a pickup time.'; return; }
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
        }
    }

    function prevStep() {
        stepError = '';
        if (currentStep > 1) currentStep--;
    }

    function handleSubmit(e: Event) {
        e.preventDefault();
        if (currentStep !== 4) return;
        form.post('/orders', {
            onSuccess: () => { form.reset(); },
        });
    }

    const stepLabels = ['Transfer Details', 'Passenger Info', 'Extras', 'Summary'];
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
                                        <div class="input-icon-wrap">
                                            <input type="text" use:datePicker class="premium-input" placeholder="Select date" />
                                            <i class="fas fa-calendar-alt input-icon-r"></i>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Pickup Time *</label>
                                        <div class="input-icon-wrap">
                                            <input type="text" use:timePicker class="premium-input" placeholder="Select time" />
                                            <i class="fas fa-clock input-icon-r"></i>
                                        </div>
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
                                        <label class="form-label">Notes / Comments</label>
                                        <input type="text" bind:value={form.notes} class="premium-input" placeholder="Flight number, special requests..." maxlength="500" />
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
                                                        <span class="extra-price">+${extra.price}</span>
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
                                <p>Review your booking before continuing to payment</p>
                            </div>
                            <div class="step-card__body">
                                <div class="summary-section">
                                    <h6 class="summary-section-title">Transfer Route</h6>
                                    <div class="summary-row">
                                        <span class="summary-label"><i class="fas fa-map-marker-alt text-danger"></i> Pickup</span>
                                        <span class="summary-value">{form.pickup_address}</span>
                                    </div>
                                    <div class="summary-row">
                                        <span class="summary-label"><i class="fas fa-flag-checkered text-success"></i> Drop-off</span>
                                        <span class="summary-value">{form.dropoff_address}</span>
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
                                        <span class="summary-vehicle-price">${basePrice.toFixed(0)}</span>
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
                                            <span class="summary-value">{extra.price > 0 ? '+$' + extra.price : 'Free'}</span>
                                        </div>
                                        {/if}
                                    {/each}
                                </div>
                                {/if}
                                <div class="summary-total">
                                    <span>Total</span>
                                    <span class="total-amount">${totalPrice.toFixed(0)}</span>
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
                                <a href="/booking?pickup={encodeURIComponent(transfer?.pickup || '')}&dropoff={encodeURIComponent(transfer?.dropoff || '')}&date={transfer?.date || ''}&passengers={transfer?.passengers || '1'}" class="btn-back">
                                    <i class="fas fa-arrow-left"></i> Back to Vehicles
                                </a>
                            {/if}

                            {#if currentStep < 4}
                                <button type="button" class="btn-next" onclick={nextStep} disabled={isValidatingEmail}>
                                    {#if isValidatingEmail}
                                        Validating... <i class="fas fa-spinner fa-spin"></i>
                                    {:else}
                                        Continue <i class="fas fa-arrow-right"></i>
                                    {/if}
                                </button>
                            {:else}
                                <button type="submit" class="btn-submit" disabled={form.processing}>
                                    {form.processing ? 'Processing...' : 'Continue to Payment'}
                                    {#if !form.processing}<i class="fas fa-credit-card"></i>{/if}
                                </button>
                            {/if}
                        </div>
                    </form>
                </div>

                <!-- Right: Sticky Order Summary -->
                <div class="checkout-sidebar">
                    <div class="sidebar-card">
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
                        <div class="sidebar-row">
                            <span>Vehicle</span>
                            <span>${basePrice.toFixed(0)}</span>
                        </div>
                        {#each selectedExtras as id}
                            {@const extra = EXTRAS.find(e => e.id === id)}
                            {#if extra && extra.price > 0}
                            <div class="sidebar-row">
                                <span>{extra.label}</span>
                                <span>+${extra.price}</span>
                            </div>
                            {/if}
                        {/each}
                        <div class="sidebar-divider"></div>
                        <div class="sidebar-total">
                            <span>Total</span>
                            <span>${totalPrice.toFixed(0)}</span>
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
    .step-card__header { padding: 24px 28px; border-bottom: 1px solid #f0f4f8; }
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
    .sidebar-vehicle { display: flex; align-items: center; gap: 14px; margin-bottom: 16px; }
    .sidebar-vehicle img { width: 80px; height: 60px; object-fit: contain; background: #f8fafc; border-radius: 8px; padding: 4px; }
    .sidebar-vehicle-info h5 { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
    .sidebar-vehicle-info span { font-size: 13px; color: #64748b; }
    .sidebar-divider { height: 1px; background: #f0f4f8; margin: 14px 0; }
    .sidebar-row { display: flex; justify-content: space-between; font-size: 14px; color: #475569; padding: 5px 0; }
    .sidebar-total { display: flex; justify-content: space-between; align-items: center; font-size: 18px; font-weight: 800; color: #1e293b; }
    .sidebar-note { margin-top: 16px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 8px; }
    .sidebar-note i { color: #10b981; }

    /* Flatpickr */
    :global(.flatpickr-calendar) { border-radius: 12px !important; box-shadow: 0 10px 35px rgba(0,0,0,0.1) !important; }
    :global(.flatpickr-day.selected), :global(.flatpickr-day.startRange), :global(.flatpickr-day.endRange) {
        background: var(--travhub-base) !important; border-color: var(--travhub-base) !important;
    }
</style>
