<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    
    let bookingData = {
        name: '',
        email: '',
        phone: '',
        pickup: '',
        destination: '',
        date: '',
        time: '',
        vehicle: '',
        passengers: '1',
        message: ''
    };

    const passengerOptions = Array.from({ length: 15 }, (_, i) => i + 1);

    let isSubmitting = false;

    const handleSubmit = async (e: Event) => {
        e.preventDefault();
        isSubmitting = true;
        // Simulate network request
        setTimeout(() => {
            alert('Booking requested successfully! We will contact you shortly.');
            isSubmitting = false;
        }, 1500);
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

                        <form on:submit={handleSubmit}>
                            <!-- Personal Info -->
                            <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">1. Contact Information</h4>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Full Name *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.name}
                                        required
                                        minlength="3"
                                        maxlength="60"
                                        class="premium-input"
                                        placeholder="Enter your full name"
                                    />
                                    <small class="form-text text-muted" style="font-size: 12px;">As displayed on your ID.</small>
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Email Address *</label>
                                    <input 
                                        type="email" 
                                        bind:value={bookingData.email}
                                        required
                                        maxlength="80"
                                        class="premium-input"
                                        placeholder="your.email@example.com"
                                    />
                                    <small class="form-text text-muted" style="font-size: 12px;">For booking confirmation.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 35px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">WhatsApp / Phone Number *</label>
                                    <input 
                                        type="tel" 
                                        bind:value={bookingData.phone}
                                        required
                                        minlength="8"
                                        maxlength="16"
                                        pattern="[+]*[0-9]{8,16}"
                                        class="premium-input"
                                        placeholder="+62 812 3456 7890"
                                    />
                                    <small class="form-text text-muted" style="font-size: 12px;">Active number for driver pickup.</small>
                                </div>
                                <div class="col-md-6" style="margin-bottom: 35px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Total Passengers *</label>
                                    <select 
                                        bind:value={bookingData.passengers}
                                        required
                                        class="premium-input"
                                    >
                                        {#each passengerOptions as num}
                                            <option value="{num}">{num} Passenger{num > 1 ? 's' : ''}</option>
                                        {/each}
                                        <option value="16+">16+ Passengers</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Ride Details -->
                            <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #eee;">2. Ride Preferences</h4>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Location *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.pickup}
                                        required
                                        minlength="5"
                                        maxlength="120"
                                        class="premium-input"
                                        placeholder="Hotel Name / Airport / Area"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Destination *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.destination}
                                        required
                                        minlength="5"
                                        maxlength="120"
                                        class="premium-input"
                                        placeholder="Beach / Temple / Area"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Date *</label>
                                    <input 
                                        type="date" 
                                        bind:value={bookingData.date}
                                        required
                                        class="premium-input"
                                        min="{new Date().toISOString().split('T')[0]}"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Pickup Time *</label>
                                    <input 
                                        type="time" 
                                        bind:value={bookingData.time}
                                        required
                                        class="premium-input"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Select Vehicle Type *</label>
                                    <select 
                                        bind:value={bookingData.vehicle}
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
                                    <small class="form-text text-muted" style="font-size: 12px; margin-top: 5px; display: block;">Luggage limits apply. If traveling heavy, consider upgrading your vehicle size.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 40px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #444;">Additional Notes (Optional)</label>
                                    <textarea 
                                        bind:value={bookingData.message}
                                        rows="4"
                                        maxlength="500"
                                        class="premium-input"
                                        placeholder="Add flight number, child seat requests, or heavy luggage notes..."
                                        style="resize: vertical;"
                                    ></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button 
                                        type="submit"
                                        class="booking-submit-btn"
                                        disabled={isSubmitting}
                                        style="background: {isSubmitting ? '#999' : 'var(--travhub-base)'}; color: white; padding: 18px 50px; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: {isSubmitting ? 'not-allowed' : 'pointer'}; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(229, 32, 41, 0.2);"
                                    >
                                        {isSubmitting ? 'Processing...' : 'Confirm Request'}
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
