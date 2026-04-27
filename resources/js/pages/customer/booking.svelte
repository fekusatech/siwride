<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { useForm } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';

    // Receive prefill data from controller
    let { prefill } = $props<{
        prefill: {
            pickup: string;
            dropoff: string;
            date: string;
            passengers: string;
            vehicle: string;
        }
    }>();

    const form = useForm({
        customer_name: '',
        email: '',
        customer_phone: '',
        pickup_address: prefill?.pickup || '',
        dropoff_address: prefill?.dropoff || '',
        date: prefill?.date || '',
        time: '',
        vehicle_type: prefill?.vehicle || '',
        passengers: prefill?.passengers || '1',
        notes: ''
    });

    // Initialize passenger count from prefill or default to 1
    let passengerCount = $state(parseInt(prefill?.passengers || '1'));

    // Email validation state
    let emailError = $state('');

    // Calculate minimum time (if today is selected, min time is current time)
    let minTime = $derived(() => {
        const today = new Date().toISOString().split('T')[0];
        if (form.date === today) {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            return `${hours}:${minutes}`;
        }
        return '';
    });

    // Sync passenger count with form
    $effect(() => {
        form.passengers = String(passengerCount);
    });

    const handleSubmit = (e: Event) => {
        e.preventDefault();
        form.post('/orders', {
            onSuccess: () => {
                form.reset();
            },
        });
    };

    // Real-time validation functions
    const validateName = (e: Event) => {
        const target = e.target as HTMLInputElement;
        // Allow only letters, spaces, dots, hyphens, and apostrophes
        target.value = target.value.replace(/[^A-Za-z\s.'\-]/g, '');
        form.customer_name = target.value;
    };

    const validatePhone = (e: Event) => {
        const target = e.target as HTMLInputElement;
        // Allow only numbers, spaces, plus, and hyphens
        target.value = target.value.replace(/[^0-9+\s\-]/g, '');
        form.customer_phone = target.value;
    };

    const validateEmail = (e: Event) => {
        const target = e.target as HTMLInputElement;
        // Remove spaces immediately
        target.value = target.value.replace(/\s/g, '');
        form.email = target.value;

        // Validate email format if there's input
        if (target.value.length > 0) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(target.value)) {
                emailError = 'Please enter a valid email address (e.g., name@example.com)';
            } else {
                emailError = '';
            }
        } else {
            emailError = '';
        }
    };
</script>

<AppHead title="Book a Ride - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Booking</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Booking</span></li>
            </ul>
        </div>
    </section>

    <!-- Booking Form Section -->
    <section class="booking-section" style="padding: 100px 0; background: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-xl-8 mx-auto wow fadeInUp" data-wow-delay="100ms">
                    <div class="booking-form" style="background: white; border-radius: 20px; padding: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); border: 1px solid #f1f1f1;">
                        
                        <div class="text-center mb-5">
                            <h3 style="font-size: 32px; font-weight: 700; color: #222;">Trip Details</h3>
                            <p style="color: #666;">Please provide accurate details so our driver can reach you efficiently.</p>
                        </div>

                        <form onsubmit={handleSubmit}>
                            <!-- Personal Info -->
                            <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">1. Contact Information</h4>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Full Name *</label>
                                    <input
                                        type="text"
                                        value={form.customer_name}
                                        oninput={validateName}
                                        required
                                        minlength="3"
                                        maxlength="50"
                                        pattern="[A-Za-z\s.'\-]+"
                                        title="Only letters, spaces, dots, hyphens and apostrophes allowed"
                                        class="premium-input"
                                        placeholder="Enter your full name"
                                    />
                                    {#if form.errors.customer_name}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.customer_name}</small>
                                    {/if}
                                    <small class="form-text text-muted" style="font-size: 12px;">As displayed on your ID.</small>
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Email Address *</label>
                                    <input
                                        type="email"
                                        value={form.email}
                                        oninput={validateEmail}
                                        required
                                        maxlength="50"
                                        title="Please enter a valid email address (spaces not allowed)"
                                        class="premium-input"
                                        placeholder="your.email@example.com"
                                    />
                                    {#if emailError}
                                        <small class="text-danger" style="font-size: 12px;">{emailError}</small>
                                    {/if}
                                    {#if form.errors.email}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.email}</small>
                                    {/if}
                                    <small class="form-text text-muted" style="font-size: 12px;">For booking confirmation.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 35px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">WhatsApp / Phone Number</label>
                                    <input
                                        type="tel"
                                        value={form.customer_phone}
                                        oninput={validatePhone}
                                        minlength="8"
                                        maxlength="15"
                                        pattern="[0-9+\s\-]+"
                                        inputmode="numeric"
                                        title="Only numbers, spaces, + and - allowed"
                                        class="premium-input"
                                        placeholder="+62 812 3456 7890"
                                    />
                                    {#if form.errors.customer_phone}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.customer_phone}</small>
                                    {/if}
                                    <small class="form-text text-muted" style="font-size: 12px;">Active number for driver pickup.</small>
                                </div>
                                <div class="col-md-6" style="margin-bottom: 35px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Total Passengers *</label>
                                    <div class="passenger-counter" style="display: flex; align-items: center; gap: 15px; padding: 12px 20px; border: 2px solid #eef2f5; border-radius: 10px; background: #fcfcfc;">
                                        <button 
                                            type="button" 
                                            onclick={() => { if (passengerCount > 1) passengerCount--; }}
                                            style="background: transparent; border: 1px solid #ccc; width: 32px; height: 32px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; color: #666; transition: all 0.2s;"
                                            onmouseenter={(e) => e.currentTarget.style.borderColor = 'var(--travhub-base)'}
                                            onmouseleave={(e) => e.currentTarget.style.borderColor = '#ccc'}
                                        >-</button>
                                        <span style="font-size: 16px; font-weight: 600; min-width: 80px; text-align: center; color: #333;">{passengerCount} Passenger{passengerCount > 1 ? 's' : ''}</span>
                                        <input type="hidden" bind:value={form.passengers} />
                                        <button 
                                            type="button" 
                                            onclick={() => { if (passengerCount < 50) passengerCount++; }}
                                            style="background: transparent; border: 1px solid #ccc; width: 32px; height: 32px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; color: #666; transition: all 0.2s;"
                                            onmouseenter={(e) => e.currentTarget.style.borderColor = 'var(--travhub-base)'}
                                            onmouseleave={(e) => e.currentTarget.style.borderColor = '#ccc'}
                                        >+</button>
                                    </div>
                                    {#if form.errors.passengers}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.passengers}</small>
                                    {/if}
                                </div>
                            </div>

                            <!-- Ride Details -->
                            <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">2. Ride Preferences</h4>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Location *</label>
                                    <LocationSearchInput
                                        id="pickup_address"
                                        bind:value={form.pickup_address}
                                        placeholder="Hotel Name / Airport / Area"
                                        required
                                        variant="premium"
                                        onchange={(v) => (form.pickup_address = v)}
                                    />
                                    {#if form.errors.pickup_address}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.pickup_address}</small>
                                    {/if}
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Destination *</label>
                                    <LocationSearchInput
                                        id="dropoff_address"
                                        bind:value={form.dropoff_address}
                                        placeholder="Beach / Temple / Area"
                                        required
                                        variant="premium"
                                        onchange={(v) => (form.dropoff_address = v)}
                                    />
                                    {#if form.errors.dropoff_address}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.dropoff_address}</small>
                                    {/if}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Date *</label>
                                    <input
                                        type="date"
                                        bind:value={form.date}
                                        required
                                        class="premium-input"
                                        min="{new Date().toISOString().split('T')[0]}"
                                        style="cursor: pointer;"
                                        title="Click to select date from calendar"
                                        onfocus={(e) => { e.currentTarget.showPicker(); }}
                                    />
                                    {#if form.errors.date}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.date}</small>
                                    {/if}
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Time *</label>
                                    <input
                                        type="time"
                                        bind:value={form.time}
                                        required
                                        class="premium-input"
                                        style="cursor: pointer;"
                                        title="Click to select time"
                                        min={minTime()}
                                        onfocus={(e) => { e.currentTarget.showPicker(); }}
                                    />
                                    {#if form.errors.time}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.time}</small>
                                    {/if}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Select Vehicle Type *</label>
                                    <select
                                        bind:value={form.vehicle_type}
                                        required
                                        class="premium-input"
                                    >
                                        <option value="" disabled>-- Select a Vehicle --</option>
                                        <option value="economy">Economy / Standard Hatchback (Up to 4 Passengers)</option>
                                        <option value="premium">Premium SUV / Sedan (Up to 5 Passengers)</option>
                                        <option value="van">Van / Minibus (Up to 14 Passengers)</option>
                                        <option value="bus">Mid/Large Bus (Up to 30+ Passengers)</option>
                                        <option value="special">Other / Custom Request</option>
                                    </select>
                                    {#if form.errors.vehicle_type}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.vehicle_type}</small>
                                    {/if}
                                    <small class="form-text text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Luggage limits apply. If traveling heavy, consider upgrading your vehicle size.</small>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12" style="margin-bottom: 40px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Additional Notes (Optional)</label>
                                    <textarea
                                        bind:value={form.notes}
                                        rows="4"
                                        maxlength="500"
                                        class="premium-input"
                                        placeholder="Add flight number, child seat requests, or heavy luggage notes..."
                                        style="resize: vertical;"
                                    ></textarea>
                                    {#if form.errors.notes}
                                        <small class="text-danger" style="font-size: 12px;">{form.errors.notes}</small>
                                    {/if}
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 text-center">
                                    <button
                                        type="submit"
                                        class="booking-submit-btn"
                                        disabled={form.processing}
                                        style="background: {form.processing ? '#999' : 'var(--travhub-base)'}; color: white; padding: 18px 50px; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: {form.processing ? 'not-allowed' : 'pointer'}; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(229, 32, 41, 0.2);"
                                    >
                                        {form.processing ? 'Processing...' : 'Confirm Request'}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    .premium-input {
        width: 100%; 
        padding: 15px 20px; 
        border: 2px solid #eef2f5; 
        background-color: #fcfcfc;
        border-radius: 10px; 
        font-size: 15px; 
        font-weight: 500;
        color: #333;
        transition: all 0.3s ease;
    }

    .premium-input::placeholder {
        color: #bbb;
        font-weight: 400;
    }

    .premium-input:focus {
        outline: none;
        background-color: #fff;
        border-color: var(--travhub-base) !important;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }
    
    .booking-submit-btn:hover:not(:disabled) {
        background: #111 !important;
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;
    }
</style>
