<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { getVehicleInfo, isValidVehicleSlug, type VehicleSlug } from './slugs.js';
    
    $: slug = page.url.split('/').pop() as VehicleSlug;
    $: vehicleInfo = getVehicleInfo(slug);
    $: isValid = isValidVehicleSlug(slug);
    
    // Determine page title based on vehicle info
    $: pageTitle = vehicleInfo?.title || slug?.replace('-', ' ').replace(/\b\w/g, l => l.toUpperCase()) || 'Vehicles';
</script>

<AppHead title="{pageTitle} - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- Page Header -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--travhub-base) 0%, #1a1a1a 100%); padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; font-size: 48px; font-weight: 700; margin-bottom: 20px;">{vehicleInfo?.title || 'Vehicles'}</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; max-width: 600px; margin: 0 auto;">{vehicleInfo?.description || 'Find the perfect vehicle for your journey'}</p>
        </div>
    </section>

    <!-- Vehicle Category Content -->
    {#if isValid && vehicleInfo}
        <section class="about-section" style="padding: 80px 0;">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6" style="margin-bottom: 40px;">
                        <h2 style="font-size: 36px; font-weight: 700; color: var(--travhub-black); margin-bottom: 30px;">Vehicle Specifications</h2>
                        <div class="why-choose-one__box" style="background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; margin-bottom: 30px;">
                            <div class="why-choose-one__box__icon" style="width: 60px; height: 60px; background: var(--travhub-base); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px;">
                                <i class="icon-traveler-with-a-suitcase-1"></i>
                            </div>
                            <h5 style="font-size: 20px; font-weight: 700; margin-bottom: 15px;">Capacity</h5>
                            <p style="color: #666; font-size: 16px; line-height: 1.8;">{vehicleInfo.capacity}</p>
                        </div>
                        
                        <div class="why-choose-one__box" style="background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s;">
                            <div class="why-choose-one__box__icon" style="width: 60px; height: 60px; background: var(--travhub-base); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 20px;">
                                <i class="flaticon-car"></i>
                            </div>
                            <h5 style="font-size: 20px; font-weight: 700; margin-bottom: 15px;">Examples</h5>
                            <p style="color: #666; font-size: 16px; line-height: 1.8;">{vehicleInfo.examples}</p>
                        </div>
                    </div>
                    <div class="col-lg-6" style="margin-bottom: 40px;">
                        <div style="background: var(--travhub-base); border-radius: 20px; padding: 40px; text-align: center; height: 100%;">
                            <img src="/assets/images/tours/tours-1-1.jpg" alt="{vehicleInfo.title}" style="width: 100%; height: 300px; object-fit: cover; border-radius: 10px; margin-bottom: 20px;" />
                            <h3 style="color: white; font-size: 24px; font-weight: 700; margin-bottom: 15px;">{vehicleInfo.title}</h3>
                            <p style="color: rgba(255,255,255,0.9); line-height: 1.6;">Professional {vehicleInfo.title.toLowerCase()} for your comfortable journey</p>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="row" style="margin-top: 60px;">
                    <div class="col-12 text-center">
                        <a href="/booking" class="travhub-btn" style="margin-right: 15px;">
                            <span>Book This Vehicle</span>
                        </a>
                        <a href="/contact" class="travhub-btn travhub-btn--secondary">
                            <span>Ask About This Vehicle</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    {:else}
        <section class="about-section" style="padding: 80px 0;">
            <div class="container text-center">
                <div class="sec-title">
                    <h2 style="font-size: 36px; font-weight: 700; color: var(--travhub-black); margin-bottom: 30px;">Vehicle Category Not Found</h2>
                    <p style="color: #666; font-size: 18px; margin-bottom: 40px;">The vehicle category "{slug}" is not available.</p>
                </div>
                
                <div class="row">
                    <div class="col-lg-4 col-md-6" style="margin-bottom: 30px;">
                        <div class="why-choose-one__box text-center">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-car"></i>
                            </div>
                            <h5>Standard Cars</h5>
                            <a href="/vehicles/standard-cars" class="travhub-btn">View Details</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" style="margin-bottom: 30px;">
                        <div class="why-choose-one__box text-center">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-car"></i>
                            </div>
                            <h5>Premium Cars</h5>
                            <a href="/vehicles/premium-cars" class="travhub-btn">View Details</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" style="margin-bottom: 30px;">
                        <div class="why-choose-one__box text-center">
                            <div class="why-choose-one__box__icon">
                                <i class="icon-traveler-with-a-suitcase-1"></i>
                            </div>
                            <h5>Vans & Minibuses</h5>
                            <a href="/vehicles/vans-minibuses" class="travhub-btn">View Details</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" style="margin-bottom: 30px;">
                        <div class="why-choose-one__box text-center">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-bus"></i>
                            </div>
                            <h5>Buses</h5>
                            <a href="/vehicles/buses" class="travhub-btn">View Details</a>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6" style="margin-bottom: 30px;">
                        <div class="why-choose-one__box text-center">
                            <div class="why-choose-one__box__icon">
                                <i class="icon-settings"></i>
                            </div>
                            <h5>Special Vehicles</h5>
                            <a href="/vehicles/special-vehicles" class="travhub-btn">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    {/if}
    
    <Footer />
</div>

<style>
    /* Vehicle category page styles will be added here */
</style>
