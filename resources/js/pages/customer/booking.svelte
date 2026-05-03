<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { useForm, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import BookingSearch from '@/components/BookingSearch.svelte';

    import flatpickr from 'flatpickr';
    import 'flatpickr/dist/flatpickr.min.css';

    // Receive prefill data from controller
    let { prefill } = $props<{
        prefill: {
            pickup: string;
            dropoff: string;
            date: string;
            passengers: string;
            vehicle: string;
        };
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
        notes: '',
        create_account: false,
        password: '',
        password_confirmation: '',
        payment_method: 'cash',
    });

    let currentStep = $state(1);
    let stepError = $state('');

    let useProfileData = $state(false);

    $effect(() => {
        if (useProfileData && page.props.auth?.customer) {
            form.customer_name = page.props.auth.customer.name;
            form.email = page.props.auth.customer.email;
            if (page.props.auth.customer.phone) {
                form.customer_phone = page.props.auth.customer.phone;
            }
        } else if (!useProfileData && page.props.auth?.customer) {
            // Optional: reset to empty if unchecked, but usually we just let them edit it manually
        }
    });

    // Initialize passenger count from prefill or default to 1
    let passengerCount = $state(parseInt(prefill?.passengers || '1'));

    // Email validation state
    let emailError = $state('');

    // Step validation loading state
    let isValidatingStep = $state(false);

    // Password visibility state
    let showPassword = $state(false);
    let showConfirmPassword = $state(false);

    // Calculate minimum time (if today is selected, min time is current time)
    let minTime = $derived(() => {
        const now = new Date();
        const localDate = new Date(
            now.getTime() - now.getTimezoneOffset() * 60000,
        )
            .toISOString()
            .split('T')[0];

        if (form.date === localDate) {
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

    const nextStep = async () => {
        stepError = '';

        if (currentStep === 1) {
            // Validate Step 1 - Basic validation
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
                stepError =
                    'Please enter a password with at least 8 characters.';
                return;
            }
            if (
                form.create_account &&
                form.password !== form.password_confirmation
            ) {
                stepError =
                    'Passwords do not match. Please confirm your password.';
                return;
            }

            // Server-side email validation (only for guests creating account)
            if (!page.props.auth?.customer && form.create_account) {
                isValidatingStep = true;
                try {
                    const response = await fetch('/booking/validate-email', {
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

                    const data = await response.json();

                    if (!data.valid) {
                        stepError = data.message || 'Email validation failed.';
                        isValidatingStep = false;
                        return;
                    }
                } catch (error) {
                    stepError = 'Unable to validate email. Please try again.';
                    isValidatingStep = false;
                    return;
                }
                isValidatingStep = false;
            }

            currentStep = 2;
        } else if (currentStep === 2) {
            // Validate Step 2
            if (!form.pickup_address) {
                stepError = 'Please provide a pickup location.';
                return;
            }
            if (!form.dropoff_address) {
                stepError = 'Please provide a destination.';
                return;
            }
            if (!form.date || !form.time) {
                stepError = 'Please select pickup date and time.';
                return;
            }

            // Validate that the selected time is not in the past if the date is today
            const now = new Date();
            const localDate = new Date(
                now.getTime() - now.getTimezoneOffset() * 60000,
            )
                .toISOString()
                .split('T')[0];
            if (form.date === localDate) {
                const currentHour = now.getHours();
                const currentMinute = now.getMinutes();
                const [selectedHour, selectedMinute] = form.time
                    .split(':')
                    .map(Number);

                if (
                    selectedHour < currentHour ||
                    (selectedHour === currentHour &&
                        selectedMinute < currentMinute)
                ) {
                    stepError =
                        'The selected time has already passed. Please choose a future time.';
                    return;
                }
            }

            if (!form.vehicle_type) {
                stepError = 'Please select a vehicle type.';
                return;
            }
            currentStep = 3;
        }
    };

    const prevStep = () => {
        stepError = '';
        if (currentStep > 1) {
            currentStep--;
        }
    };

    const handleSubmit = (e: Event) => {
        e.preventDefault();

        if (currentStep !== 3) return;

        form.post('/orders', {
            onSuccess: () => {
                form.reset();
                currentStep = 1;
            },
        });
    };

    // Real-time validation functions
    const validateName = (e: Event) => {
        const target = e.target as HTMLInputElement;
        target.value = target.value.replace(/[^A-Za-z\s.'\-]/g, '');
        form.customer_name = target.value;
    };

    const validatePhone = (e: Event) => {
        const target = e.target as HTMLInputElement;
        target.value = target.value.replace(/[^0-9+\s\-]/g, '');
        form.customer_phone = target.value;
    };

    const validateEmail = (e: Event) => {
        const target = e.target as HTMLInputElement;
        target.value = target.value.replace(/\s/g, '');
        form.email = target.value;

        if (target.value.length > 0) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(target.value)) {
                emailError = 'Please enter a valid email address';
            } else {
                emailError = '';
            }
        } else {
            emailError = '';
        }
    };

    // Flatpickr actions
    function datePicker(node: HTMLInputElement) {
        const fp = flatpickr(node, {
            minDate: 'today',
            defaultDate: form.date || null,
            disableMobile: 'true', // Forces custom flatpickr UI on mobile
            monthSelectorType: 'static', // Uses a clean text span instead of a native select dropdown
            onChange: (selectedDates, dateStr) => {
                form.date = dateStr;
            },
        });
        return {
            destroy() {
                fp.destroy();
            },
        };
    }

    function timePicker(node: HTMLInputElement, currentMinTime: string) {
        const fp = flatpickr(node, {
            enableTime: true,
            noCalendar: true,
            dateFormat: 'H:i',
            time_24hr: true,
            minTime: currentMinTime || null,
            defaultDate: form.time || null,
            disableMobile: 'true',
            onChange: (selectedDates, dateStr) => {
                form.time = dateStr;
            },
        });
        return {
            update(newMinTime: string) {
                fp.set('minTime', newMinTime || null);
            },
            destroy() {
                fp.destroy();
            },
        };
    }

    // Custom Select State
    let isVehicleDropdownOpen = $state(false);
    const vehiclesList = [
        {
            value: 'economy',
            label: 'Economy / Standard Hatchback (Up to 4 Passengers)',
            icon: 'fa-car',
        },
        {
            value: 'premium',
            label: 'Premium SUV / Sedan (Up to 5 Passengers)',
            icon: 'fa-car-side',
        },
        {
            value: 'van',
            label: 'Van / Minibus (Up to 14 Passengers)',
            icon: 'fa-shuttle-van',
        },
        {
            value: 'bus',
            label: 'Mid/Large Bus (Up to 30+ Passengers)',
            icon: 'fa-bus',
        },
        { value: 'special', label: 'Other / Custom Request', icon: 'fa-star' },
    ];

    let selectedVehicle = $derived(
        vehiclesList.find((v) => v.value === form.vehicle_type),
    );

    const toggleVehicleDropdown = (e: Event) => {
        e.stopPropagation();
        isVehicleDropdownOpen = !isVehicleDropdownOpen;
    };

    const selectVehicle = (value: string) => {
        form.vehicle_type = value;
        isVehicleDropdownOpen = false;
    };

    const closeDropdowns = (e: Event) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.custom-select-wrapper')) {
            isVehicleDropdownOpen = false;
        }
    };
</script>

<svelte:window onclick={closeDropdowns} />

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

    <!-- Booking Tracker Section -->
    <section
        class="booking-tracker-section"
        style="padding: 40px 0; background: linear-gradient(135deg, var(--travhub-base, #e52029) 0%, #c41820 100%);"
    >
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 mb-3 mb-lg-0">
                    <div style="color: #fff;">
                        <h4
                            style="font-size: 22px; font-weight: 700; margin-bottom: 5px;"
                        >
                            <i
                                class="fas fa-search-location"
                                style="margin-right: 10px;"
                            ></i>
                            Track Your Booking
                        </h4>
                        <p style="font-size: 14px; opacity: 0.9; margin: 0;">
                            Already have a booking? Enter your code to check
                            status
                        </p>
                    </div>
                </div>
                <div class="col-lg-7">
                    <BookingSearch variant="compact" />
                </div>
            </div>
        </div>
    </section>

    <!-- Booking Form Section -->
    <section
        class="booking-section"
        style="padding: 80px 0 100px; background: #f4f7f9;"
    >
        <div class="container">
            <div class="row">
                <div
                    class="col-lg-10 col-xl-8 mx-auto wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div class="text-center mb-5">
                        <h3
                            style="font-size: 36px; font-weight: 800; color: #111; letter-spacing: -1px;"
                        >
                            Complete Your Booking
                        </h3>
                        <p
                            style="color: #666; font-size: 16px; margin-top: 10px;"
                        >
                            Follow the simple steps below to confirm your ride.
                        </p>
                    </div>

                    <div class="wizard-container">
                        <!-- Horizontal Stepper Header -->
                        <div class="wizard-stepper">
                            <div
                                class="step {currentStep >= 1
                                    ? 'active'
                                    : ''} {currentStep > 1 ? 'completed' : ''}"
                            >
                                <div class="step-icon">
                                    {#if currentStep > 1}<i class="fas fa-check"
                                        ></i>{:else}1{/if}
                                </div>
                                <div class="step-text">Personal Info</div>
                            </div>
                            <div
                                class="step-line {currentStep >= 2
                                    ? 'active'
                                    : ''}"
                            ></div>

                            <div
                                class="step {currentStep >= 2
                                    ? 'active'
                                    : ''} {currentStep > 2 ? 'completed' : ''}"
                            >
                                <div class="step-icon">
                                    {#if currentStep > 2}<i class="fas fa-check"
                                        ></i>{:else}2{/if}
                                </div>
                                <div class="step-text">Ride Details</div>
                            </div>
                            <div
                                class="step-line {currentStep >= 3
                                    ? 'active'
                                    : ''}"
                            ></div>

                            <div
                                class="step {currentStep >= 3 ? 'active' : ''}"
                            >
                                <div class="step-icon">3</div>
                                <div class="step-text">Payment</div>
                            </div>
                        </div>

                        {#if stepError}
                            <div
                                class="alert alert-danger"
                                style="border-radius: 10px; margin-bottom: 25px; font-size: 14px;"
                            >
                                <i class="fas fa-exclamation-circle mr-2"></i>
                                {stepError}
                            </div>
                        {/if}

                        <form onsubmit={handleSubmit} class="wizard-form">
                            <!-- Step 1: Personal Information -->
                            {#if currentStep === 1}
                                <div
                                    class="step-content"
                                    style="animation: fadeInRight 0.4s ease;"
                                >
                                    <div class="step-header">
                                        <h4>Personal Information</h4>
                                        <p>How can our driver reach you?</p>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 20px;"
                                        >
                                            <label class="form-label"
                                                >Full Name *</label
                                            >
                                            <input
                                                type="text"
                                                value={form.customer_name}
                                                oninput={validateName}
                                                class="premium-input"
                                                minlength="2"
                                                maxlength="50"
                                                placeholder="Enter your full name"
                                            />
                                        </div>
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 20px;"
                                        >
                                            <label class="form-label"
                                                >Email Address *</label
                                            >
                                            <input
                                                type="email"
                                                value={form.email}
                                                oninput={validateEmail}
                                                class="premium-input {emailError
                                                    ? 'has-error'
                                                    : ''}"
                                                placeholder="your.email@example.com"
                                                maxlength="25"
                                            />
                                            {#if emailError}<small
                                                    class="text-danger error-text"
                                                    >{emailError}</small
                                                >{/if}
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 20px;"
                                        >
                                            <label class="form-label"
                                                >WhatsApp / Phone Number</label
                                            >
                                            <input
                                                type="tel"
                                                maxlength="15"
                                                value={form.customer_phone}
                                                oninput={validatePhone}
                                                class="premium-input"
                                                placeholder="+62 812 3456 7890"
                                            />
                                        </div>
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 20px;"
                                        >
                                            <label class="form-label"
                                                >Total Passengers *</label
                                            >
                                            <div class="passenger-counter">
                                                <button
                                                    type="button"
                                                    onclick={() => {
                                                        if (passengerCount > 1)
                                                            passengerCount--;
                                                    }}>-</button
                                                >
                                                <span
                                                    >{passengerCount} Passenger{passengerCount >
                                                    1
                                                        ? 's'
                                                        : ''}</span
                                                >
                                                <button
                                                    type="button"
                                                    onclick={() => {
                                                        if (passengerCount < 50)
                                                            passengerCount++;
                                                    }}>+</button
                                                >
                                            </div>
                                        </div>
                                    </div>

                                    {#if !page.props.auth?.customer}
                                        <div class="row">
                                            <div
                                                class="col-12"
                                                style="margin-top: 10px;"
                                            >
                                                <div
                                                    class="account-creation-box"
                                                >
                                                    <label
                                                        class="checkbox-label"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            bind:checked={
                                                                form.create_account
                                                            }
                                                        />
                                                        Create an account for faster
                                                        booking next time
                                                    </label>
                                                    <span class="login-prompt">
                                                        Already have an account? <a
                                                            href="/customer/login"
                                                            >Login here</a
                                                        >
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        {#if form.create_account}
                                            <div
                                                class="row"
                                                style="animation: fadeIn 0.3s ease; margin-top: 20px;"
                                            >
                                                <div class="col-md-6">
                                                    <label class="form-label"
                                                        >Password *</label
                                                    >
                                                    <div
                                                        class="password-wrapper"
                                                    >
                                                        <input
                                                            type={showPassword
                                                                ? 'text'
                                                                : 'password'}
                                                            bind:value={
                                                                form.password
                                                            }
                                                            class="premium-input"
                                                            class:is-invalid={form
                                                                .password
                                                                .length > 0 &&
                                                                form.password
                                                                    .length < 8}
                                                            placeholder="Create a secure password"
                                                        />
                                                        <button
                                                            type="button"
                                                            class="password-toggle"
                                                            onclick={() =>
                                                                (showPassword =
                                                                    !showPassword)}
                                                        >
                                                            <i
                                                                class="fas {showPassword
                                                                    ? 'fa-eye-slash'
                                                                    : 'fa-eye'}"
                                                            ></i>
                                                        </button>
                                                    </div>
                                                    {#if form.password.length > 0 && form.password.length < 8}
                                                        <small
                                                            class="text-danger"
                                                            style="font-size: 12px; margin-top: 5px; display: block;"
                                                            ><i
                                                                class="fas fa-exclamation-circle"
                                                            ></i> Minimum 8 characters
                                                            required.</small
                                                        >
                                                    {:else}
                                                        <small
                                                            class="form-text text-muted"
                                                            style="font-size: 12px; margin-top: 5px; display: block;"
                                                            >Minimum 8
                                                            characters.</small
                                                        >
                                                    {/if}
                                                </div>
                                                <div
                                                    class="col-md-6"
                                                    style="margin-top: 0;"
                                                >
                                                    <label class="form-label"
                                                        >Confirm Password *</label
                                                    >
                                                    <div
                                                        class="password-wrapper"
                                                    >
                                                        <input
                                                            type={showConfirmPassword
                                                                ? 'text'
                                                                : 'password'}
                                                            bind:value={
                                                                form.password_confirmation
                                                            }
                                                            class="premium-input"
                                                            class:is-invalid={form
                                                                .password_confirmation
                                                                .length > 0 &&
                                                                form.password !==
                                                                    form.password_confirmation}
                                                            class:is-valid={form
                                                                .password_confirmation
                                                                .length > 0 &&
                                                                form.password ===
                                                                    form.password_confirmation}
                                                            placeholder="Repeat your password"
                                                        />
                                                        <button
                                                            type="button"
                                                            class="password-toggle"
                                                            onclick={() =>
                                                                (showConfirmPassword =
                                                                    !showConfirmPassword)}
                                                        >
                                                            <i
                                                                class="fas {showConfirmPassword
                                                                    ? 'fa-eye-slash'
                                                                    : 'fa-eye'}"
                                                            ></i>
                                                        </button>
                                                    </div>
                                                    {#if form.password_confirmation.length > 0 && form.password !== form.password_confirmation}
                                                        <small
                                                            class="text-danger"
                                                            style="font-size: 12px; margin-top: 5px; display: block;"
                                                            ><i
                                                                class="fas fa-times-circle"
                                                            ></i> Passwords do not
                                                            match.</small
                                                        >
                                                    {:else if form.password_confirmation.length > 0 && form.password === form.password_confirmation}
                                                        <small
                                                            class="text-success"
                                                            style="font-size: 12px; margin-top: 5px; display: block;"
                                                            ><i
                                                                class="fas fa-check-circle"
                                                            ></i> Passwords match.</small
                                                        >
                                                    {:else}
                                                        <small
                                                            class="form-text text-muted"
                                                            style="font-size: 12px; margin-top: 5px; display: block;"
                                                            >Must match the
                                                            password above.</small
                                                        >
                                                    {/if}
                                                </div>
                                            </div>
                                        {/if}
                                    {:else}
                                        <div class="row">
                                            <div
                                                class="col-12"
                                                style="margin-top: 10px;"
                                            >
                                                <div
                                                    class="account-creation-box"
                                                    style="background: #e6f4ea; border-color: #81c784;"
                                                >
                                                    <label
                                                        class="checkbox-label"
                                                        style="color: #2e7d32;"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            bind:checked={
                                                                useProfileData
                                                            }
                                                        />
                                                        Pesan menggunakan data profil
                                                        saya
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            {/if}

                            <!-- Step 2: Ride Preferences -->
                            {#if currentStep === 2}
                                <div
                                    class="step-content"
                                    style="animation: fadeInRight 0.4s ease;"
                                >
                                    <div class="step-header">
                                        <h4>Ride Details</h4>
                                        <p>
                                            Where and when do you need the ride?
                                        </p>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 25px;"
                                        >
                                            <label class="form-label"
                                                >Pickup Location *</label
                                            >
                                            <LocationSearchInput
                                                id="pickup_address"
                                                bind:value={form.pickup_address}
                                                placeholder="Hotel Name / Airport / Area"
                                                variant="premium"
                                            />
                                        </div>
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 25px;"
                                        >
                                            <label class="form-label"
                                                >Destination *</label
                                            >
                                            <LocationSearchInput
                                                id="dropoff_address"
                                                bind:value={
                                                    form.dropoff_address
                                                }
                                                placeholder="Beach / Temple / Area"
                                                variant="premium"
                                            />
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 25px;"
                                        >
                                            <label class="form-label"
                                                >Pickup Date *</label
                                            >
                                            <div class="custom-input-wrapper">
                                                <input
                                                    type="text"
                                                    use:datePicker
                                                    class="premium-input date-input"
                                                    placeholder="Select Date"
                                                />
                                                <i
                                                    class="fas fa-calendar-alt input-icon"
                                                ></i>
                                            </div>
                                        </div>
                                        <div
                                            class="col-md-6"
                                            style="margin-bottom: 25px;"
                                        >
                                            <label class="form-label"
                                                >Pickup Time *</label
                                            >
                                            <div class="custom-input-wrapper">
                                                <input
                                                    type="text"
                                                    use:timePicker={minTime()}
                                                    class="premium-input date-input"
                                                    placeholder="Select Time"
                                                />
                                                <i
                                                    class="fas fa-clock input-icon"
                                                ></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col-12"
                                            style="margin-bottom: 25px;"
                                        >
                                            <label class="form-label"
                                                >Select Vehicle Type *</label
                                            >
                                            <div class="custom-select-wrapper">
                                                <div
                                                    class="custom-select-trigger {isVehicleDropdownOpen
                                                        ? 'open'
                                                        : ''}"
                                                    onclick={toggleVehicleDropdown}
                                                >
                                                    {#if selectedVehicle}
                                                        <div
                                                            class="selected-value"
                                                        >
                                                            <i
                                                                class="fas {selectedVehicle.icon}"
                                                            ></i>
                                                            <span
                                                                >{selectedVehicle.label}</span
                                                            >
                                                        </div>
                                                    {:else}
                                                        <div
                                                            class="select-placeholder"
                                                            style="color: #64748b; font-weight: 500;"
                                                        >
                                                            <i
                                                                class="fas fa-car"
                                                                style="margin-right: 10px; opacity: 0.5;"
                                                            ></i>
                                                            <span
                                                                >Choose your
                                                                vehicle...</span
                                                            >
                                                        </div>
                                                    {/if}
                                                    <i
                                                        class="fas fa-chevron-down arrow"
                                                    ></i>
                                                </div>

                                                {#if isVehicleDropdownOpen}
                                                    <div
                                                        class="custom-select-dropdown"
                                                        style="animation: dropdownFade 0.2s ease;"
                                                    >
                                                        {#each vehiclesList as vehicle}
                                                            <div
                                                                class="custom-option {form.vehicle_type ===
                                                                vehicle.value
                                                                    ? 'selected'
                                                                    : ''}"
                                                                onclick={() =>
                                                                    selectVehicle(
                                                                        vehicle.value,
                                                                    )}
                                                            >
                                                                <i
                                                                    class="fas {vehicle.icon}"
                                                                ></i>
                                                                <span
                                                                    >{vehicle.label}</span
                                                                >
                                                                {#if form.vehicle_type === vehicle.value}
                                                                    <i
                                                                        class="fas fa-check check-icon"
                                                                    ></i>
                                                                {/if}
                                                            </div>
                                                        {/each}
                                                    </div>
                                                {/if}
                                            </div>
                                            <small
                                                class="form-text text-muted"
                                                style="font-size: 12px; margin-top: 8px; display: block;"
                                                >Luggage limits apply. Consider
                                                upgrading if traveling heavy.</small
                                            >
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12">
                                            <label class="form-label"
                                                >Additional Notes (Optional)</label
                                            >
                                            <textarea
                                                bind:value={form.notes}
                                                rows="3"
                                                maxlength="500"
                                                class="premium-input"
                                                placeholder="Add flight number, child seat requests, etc."
                                                style="resize: vertical;"
                                            ></textarea>
                                        </div>
                                    </div>
                                </div>
                            {/if}

                            <!-- Step 3: Payment Method -->
                            {#if currentStep === 3}
                                <div
                                    class="step-content"
                                    style="animation: fadeInRight 0.4s ease;"
                                >
                                    <div class="step-header">
                                        <h4>Payment Method</h4>
                                        <p>
                                            Choose how you want to pay for this
                                            trip.
                                        </p>
                                    </div>

                                    <div class="payment-options-container">
                                        <label
                                            class="payment-card {form.payment_method ===
                                            'cash'
                                                ? 'active'
                                                : ''}"
                                        >
                                            <input
                                                type="radio"
                                                bind:group={form.payment_method}
                                                value="cash"
                                                class="d-none"
                                                style="display: none;"
                                            />
                                            <div class="pay-icon">
                                                <i
                                                    class="fas fa-money-bill-wave"
                                                ></i>
                                            </div>
                                            <div class="pay-details">
                                                <h5>Cash to Driver</h5>
                                                <p>
                                                    Pay directly when you arrive
                                                </p>
                                            </div>
                                            <div class="pay-check">
                                                <div class="circle"></div>
                                            </div>
                                        </label>

                                        <label
                                            class="payment-card {form.payment_method ===
                                            'transfer'
                                                ? 'active'
                                                : ''}"
                                        >
                                            <input
                                                type="radio"
                                                bind:group={form.payment_method}
                                                value="transfer"
                                                class="d-none"
                                                style="display: none;"
                                            />
                                            <div class="pay-icon">
                                                <i class="fas fa-university"
                                                ></i>
                                            </div>
                                            <div class="pay-details">
                                                <h5>Bank Transfer</h5>
                                                <p>
                                                    Transfer via ATM / Mobile
                                                    Banking
                                                </p>
                                            </div>
                                            <div class="pay-check">
                                                <div class="circle"></div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            {/if}

                            <!-- Wizard Footer Actions -->
                            <div class="wizard-footer mt-5">
                                {#if currentStep > 1}
                                    <button
                                        type="button"
                                        class="btn-back"
                                        onclick={prevStep}
                                    >
                                        <i
                                            class="fas fa-arrow-left"
                                            style="margin-right: 8px;"
                                        ></i> Back
                                    </button>
                                {:else}
                                    <div></div>
                                    <!-- Spacer for flexbox -->
                                {/if}

                                {#if currentStep < 3}
                                    <button
                                        type="button"
                                        class="btn-next"
                                        onclick={nextStep}
                                        disabled={isValidatingStep}
                                    >
                                        {#if isValidatingStep}
                                            Validating... <i
                                                class="fas fa-spinner fa-spin"
                                                style="margin-left: 8px;"
                                            ></i>
                                        {:else}
                                            Next Step <i
                                                class="fas fa-arrow-right"
                                                style="margin-left: 8px;"
                                            ></i>
                                        {/if}
                                    </button>
                                {:else}
                                    <button
                                        type="submit"
                                        class="btn-submit"
                                        disabled={form.processing}
                                    >
                                        {form.processing
                                            ? 'Processing...'
                                            : 'Confirm Booking'}
                                        {#if !form.processing}<i
                                                class="fas fa-check-circle"
                                                style="margin-left: 8px;"
                                            ></i>{/if}
                                    </button>
                                {/if}
                            </div>
                            {#if currentStep === 3}
                                <p
                                    class="text-center mt-3"
                                    style="color: #888; font-size: 13px;"
                                >
                                    By confirming, you agree to our Terms of
                                    Service & Privacy Policy.
                                </p>
                            {/if}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* Wizard Container */
    .wizard-container {
        background: white;
        border: 1px solid #eaeef2;
        border-radius: 20px;
        padding: 40px;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
    }

    @media (max-width: 768px) {
        .wizard-container {
            padding: 25px 20px;
        }
    }

    /* Horizontal Stepper */
    .wizard-stepper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        z-index: 2;
        flex: 1;
        position: relative;
    }

    .step-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: 700;
        border: 3px solid white;
        transition: all 0.3s ease;
        margin-bottom: 10px;
        box-shadow: 0 0 0 4px #f8fafc;
    }

    .step.active .step-icon {
        background: var(--travhub-base);
        color: white;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.15);
    }

    .step.completed .step-icon {
        background: #10b981; /* Green color for completed steps */
        color: white;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
    }

    .step-text {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        transition: all 0.3s ease;
    }

    .step.active .step-text {
        color: var(--travhub-base);
    }

    .step.completed .step-text {
        color: #10b981;
    }

    @media (max-width: 576px) {
        .step-text {
            font-size: 11px;
            text-align: center;
        }
        .step-icon {
            width: 35px;
            height: 35px;
            font-size: 14px;
        }
    }

    .step-line {
        flex: 1;
        height: 4px;
        background: #f1f5f9;
        position: relative;
        top: -15px;
        border-radius: 2px;
        margin: 0 5px;
        transition: all 0.3s ease;
    }

    .step-line.active {
        background: #10b981; /* Green line connecting completed to active */
    }

    /* Content Area */
    .step-content {
        min-height: 300px;
    }

    .step-header {
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 1px solid #f0f3f6;
    }

    .step-header h4 {
        font-size: 24px;
        font-weight: 800;
        color: #1a202c;
        margin: 0;
    }

    .step-header p {
        color: #64748b;
        margin: 5px 0 0 0;
        font-size: 15px;
        font-weight: 500;
    }

    /* Form Elements */
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }

    .premium-input {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        background-color: #f8fafc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s ease;
    }

    .premium-input.has-error {
        border-color: #ef4444;
        background-color: #fef2f2;
    }

    .premium-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .premium-input:focus {
        outline: none;
        background-color: #fff;
        border-color: var(--travhub-base) !important;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }

    .date-input {
        cursor: pointer;
    }

    .error-text {
        font-size: 12px;
        margin-top: 4px;
        display: block;
        font-weight: 500;
    }

    /* Passenger Counter */
    .passenger-counter {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 10px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        background: #f8fafc;
    }

    .passenger-counter button {
        background: transparent;
        border: 1px solid #cbd5e1;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        font-size: 18px;
        color: #64748b;
        transition: all 0.2s;
    }

    .passenger-counter button:hover {
        border-color: var(--travhub-base);
        color: var(--travhub-base);
        background: rgba(229, 32, 41, 0.05);
    }

    .passenger-counter span {
        font-size: 15px;
        font-weight: 600;
        min-width: 90px;
        text-align: center;
        color: #1e293b;
    }

    /* Account Box */
    .account-creation-box {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 15px 20px;
        background: #f8fafc;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        flex-wrap: wrap;
        gap: 10px;
        transition: all 0.3s ease;
    }

    .account-creation-box:hover {
        border-color: #94a3b8;
        background: #f1f5f9;
    }

    .checkbox-label {
        display: flex;
        align-items: center;
        cursor: pointer;
        margin: 0;
        font-weight: 600;
        color: #334155;
        user-select: none;
        font-size: 14px;
    }

    .checkbox-label input {
        width: 18px;
        height: 18px;
        margin-right: 12px;
        cursor: pointer;
        accent-color: var(--travhub-base);
    }

    .login-prompt {
        font-size: 13px;
        color: #64748b;
        font-weight: 500;
    }

    .login-prompt a {
        color: var(--travhub-base);
        font-weight: 700;
        text-decoration: underline;
        margin-left: 3px;
    }

    /* Payment Cards */
    .payment-options-container {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
    }

    @media (min-width: 640px) {
        .payment-options-container {
            grid-template-columns: 1fr 1fr;
        }
    }

    .payment-card {
        display: flex;
        align-items: center;
        padding: 20px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #fff;
    }

    .payment-card:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    .payment-card.active {
        border-color: var(--travhub-base);
        background: rgba(229, 32, 41, 0.03);
    }

    .pay-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: #64748b;
        margin-right: 15px;
        transition: all 0.2s ease;
    }

    .payment-card.active .pay-icon {
        background: var(--travhub-base);
        color: #fff;
    }

    .pay-details {
        flex-grow: 1;
    }

    .pay-details h5 {
        margin: 0 0 3px 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .pay-details p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
        line-height: 1.3;
    }

    .pay-check {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-left: 10px;
        transition: all 0.2s ease;
    }

    .pay-check .circle {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: transparent;
        transition: all 0.2s ease;
    }

    .payment-card.active .pay-check {
        border-color: var(--travhub-base);
    }

    .payment-card.active .pay-check .circle {
        background: var(--travhub-base);
    }

    /* Buttons */
    .wizard-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 30px;
        border-top: 1px solid #f0f3f6;
    }

    .btn-next,
    .btn-submit {
        background: var(--travhub-base);
        color: white;
        padding: 14px 35px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 20px rgba(229, 32, 41, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .btn-next:hover,
    .btn-submit:hover:not(:disabled) {
        background: #111;
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
    }

    .btn-submit:disabled {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
        transform: none;
    }

    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 14px 25px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
    }

    .btn-back:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Animations */
    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Custom Input and Select Styles */
    .custom-input-wrapper {
        position: relative;
        width: 100%;
    }
    .input-icon {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--travhub-base);
        pointer-events: none;
        font-size: 16px;
    }

    .password-wrapper {
        position: relative;
        width: 100%;
    }
    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 5px;
        transition: color 0.2s;
    }
    .password-toggle:hover {
        color: var(--travhub-base);
    }

    /* Flatpickr Custom Theme */
    :global(.flatpickr-calendar) {
        border-radius: 12px !important;
        box-shadow: 0 10px 35px rgba(0, 0, 0, 0.1) !important;
        border: 1px solid #e2e8f0 !important;
        padding: 10px !important;
    }
    :global(.flatpickr-day.selected),
    :global(.flatpickr-day.startRange),
    :global(.flatpickr-day.endRange),
    :global(.flatpickr-day.selected.inRange),
    :global(.flatpickr-day.startRange.inRange),
    :global(.flatpickr-day.endRange.inRange),
    :global(.flatpickr-day.selected:focus),
    :global(.flatpickr-day.startRange:focus),
    :global(.flatpickr-day.endRange:focus),
    :global(.flatpickr-day.selected:hover),
    :global(.flatpickr-day.startRange:hover),
    :global(.flatpickr-day.endRange:hover),
    :global(.flatpickr-day.selected.prevMonthDay),
    :global(.flatpickr-day.startRange.prevMonthDay),
    :global(.flatpickr-day.endRange.prevMonthDay),
    :global(.flatpickr-day.selected.nextMonthDay),
    :global(.flatpickr-day.startRange.nextMonthDay),
    :global(.flatpickr-day.endRange.nextMonthDay) {
        background: var(--travhub-base) !important;
        border-color: var(--travhub-base) !important;
    }
    :global(.flatpickr-time input:hover),
    :global(.flatpickr-time input:focus) {
        background: rgba(229, 32, 41, 0.05) !important;
    }
    :global(.flatpickr-months .flatpickr-month) {
        color: #1e293b !important;
        fill: #1e293b !important;
    }
    :global(.flatpickr-current-month span.cur-month) {
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        margin-right: 5px !important;
    }
    :global(.flatpickr-current-month input.cur-year) {
        background: transparent !important;
        border: none !important;
        color: #1e293b !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        pointer-events: none !important;
        -moz-appearance: textfield !important;
        appearance: textfield !important;
    }
    :global(.flatpickr-current-month .numInputWrapper span.arrowUp),
    :global(.flatpickr-current-month .numInputWrapper span.arrowDown) {
        display: none !important;
    }
    :global(.flatpickr-current-month input.cur-year:hover) {
        background: transparent !important;
    }
    :global(.flatpickr-months .flatpickr-prev-month:hover svg),
    :global(.flatpickr-months .flatpickr-next-month:hover svg) {
        fill: var(--travhub-base) !important;
    }

    .custom-select-wrapper {
        position: relative;
        width: 100%;
        user-select: none;
    }
    .custom-select-trigger {
        width: 100%;
        padding: 14px 18px;
        border: 2px solid #e2e8f0;
        background-color: #f8fafc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.2s ease;
    }
    .custom-select-trigger.open,
    .custom-select-trigger:focus {
        background-color: #fff;
        border-color: var(--travhub-base);
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }
    .custom-select-trigger .select-placeholder {
        color: #94a3b8;
        font-weight: 400;
    }
    .custom-select-trigger .selected-value {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .custom-select-trigger .selected-value i {
        color: var(--travhub-base);
    }
    .custom-select-trigger .arrow {
        color: #94a3b8;
        transition: transform 0.2s ease;
    }
    .custom-select-trigger.open .arrow {
        transform: rotate(180deg);
    }

    .custom-select-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        width: 100%;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        z-index: 100;
        max-height: 250px;
        overflow-y: auto;
        padding: 8px;
    }

    .custom-option {
        padding: 12px 15px;
        display: flex;
        align-items: center;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.2s ease;
        color: #334155;
        font-weight: 500;
    }
    .custom-option i:not(.check-icon) {
        width: 25px;
        color: #64748b;
        margin-right: 10px;
        text-align: center;
    }
    .custom-option:hover {
        background: #f8fafc;
    }
    .custom-option.selected {
        background: rgba(229, 32, 41, 0.05);
        color: var(--travhub-base);
    }
    .custom-option.selected i:not(.check-icon) {
        color: var(--travhub-base);
    }
    .custom-option .check-icon {
        margin-left: auto;
        color: var(--travhub-base);
    }

    @keyframes dropdownFade {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
