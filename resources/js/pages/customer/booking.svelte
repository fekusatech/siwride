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
</script>

<AppHead title="Booking - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- Booking Section -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--travhub-base) 0%, #1a1a1a 100%); padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; font-size: 48px; font-weight: 700; margin-bottom: 20px;">Book Your Ride</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; max-width: 600px; margin: 0 auto;">Simple and secure booking process for your transportation needs</p>
        </div>
    </section>

    <section class="booking-section" style="padding: 80px 0; background: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="booking-form" style="background: white; border-radius: 20px; padding: 50px; box-shadow: 0 15px 35px rgba(0,0,0,0.1);">
                        <form>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Full Name *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.name}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="Enter your full name"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Email Address *</label>
                                    <input 
                                        type="email" 
                                        bind:value={bookingData.email}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="your.email@example.com"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Phone Number *</label>
                                    <input 
                                        type="tel" 
                                        bind:value={bookingData.phone}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="+62 812-3456-789"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Number of Passengers</label>
                                    <select 
                                        bind:value={bookingData.passengers}
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                    >
                                        <option value="1">1 Passenger</option>
                                        <option value="2">2 Passengers</option>
                                        <option value="3">3 Passengers</option>
                                        <option value="4">4 Passengers</option>
                                        <option value="5+">5+ Passengers</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Pickup Location *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.pickup}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="Enter pickup address"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Destination *</label>
                                    <input 
                                        type="text" 
                                        bind:value={bookingData.destination}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="Enter destination"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Pickup Date *</label>
                                    <input 
                                        type="date" 
                                        bind:value={bookingData.date}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Pickup Time *</label>
                                    <input 
                                        type="time" 
                                        bind:value={bookingData.time}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Vehicle Type</label>
                                    <select 
                                        bind:value={bookingData.vehicle}
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                    >
                                        <option value="">Select Vehicle Type</option>
                                        <option value="standard">Standard Car</option>
                                        <option value="premium">Premium Car</option>
                                        <option value="van">Van/Minibus</option>
                                        <option value="bus">Bus</option>
                                        <option value="special">Special Vehicle</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 30px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Additional Message</label>
                                    <textarea 
                                        bind:value={bookingData.message}
                                        rows="4"
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease; resize: vertical;"
                                        placeholder="Any special requirements or notes..."
                                    ></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button 
                                        type="submit"
                                        class="booking-submit-btn"
                                        style="background: var(--travhub-base); color: white; padding: 18px 50px; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(0,0,0,0.2);"
                                        on:mouseover={(e) => {
                                            e.currentTarget.style.transform = 'translateY(-3px)';
                                            e.currentTarget.style.boxShadow = '0 15px 35px rgba(0,0,0,0.3)';
                                        }}
                                        on:mouseout={(e) => {
                                            e.currentTarget.style.transform = 'translateY(0)';
                                            e.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
                                        }}
                                    >
                                        Submit Booking Request
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
    input:focus, select:focus, textarea:focus {
        outline: none;
        border-color: var(--travhub-base) !important;
    }
    
    button:hover {
        background: var(--travhub-black) !important;
    }
</style>
