<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
</script>

<AppHead title="Services - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- Services Section -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--travhub-base) 0%, #1a1a1a 100%); padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; font-size: 48px; font-weight: 700; margin-bottom: 20px;">Our Services</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; max-width: 600px; margin: 0 auto;">Professional transportation services tailored to your needs</p>
        </div>
    </section>

    <section class="services-section" style="padding: 80px 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-plane" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">Airport Transfer</h3>
                        <p style="color: #666; line-height: 1.6;">Reliable airport pickup and drop-off services with professional drivers and comfortable vehicles.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-car" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">City Tour</h3>
                        <p style="color: #666; line-height: 1.6;">Explore the city with our comfortable and informative city tour services.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-hotel" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">Hotel Transfer</h3>
                        <p style="color: #666; line-height: 1.6;">Convenient hotel transfer services for comfortable travel to and from your accommodation.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-ring" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">Wedding Car</h3>
                        <p style="color: #666; line-height: 1.6;">Elegant wedding transportation services with premium vehicles and professional chauffeurs.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-briefcase" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">Business Trip</h3>
                        <p style="color: #666; line-height: 1.6;">Professional business transportation with punctual and reliable service.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="service-card" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; height: 100%;">
                        <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 30px;">
                            <i class="fa fa-clock" style="color: white; font-size: 36px;"></i>
                        </div>
                        <h3 style="font-size: 24px; font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);">Hourly Charter</h3>
                        <p style="color: #666; line-height: 1.6;">Flexible hourly charter services for customized travel needs.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    .service-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
</style>
