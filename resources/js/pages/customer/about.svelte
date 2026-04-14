<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
</script>

<AppHead title="About Us - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- About Section -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--travhub-base) 0%, #1a1a1a 100%); padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; font-size: 48px; font-weight: 700; margin-bottom: 20px;">About Siwride</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; max-width: 600px; margin: 0 auto;">Your trusted partner for professional transportation services in Indonesia</p>
        </div>
    </section>

    <section class="about-section" style="padding: 80px 0;">
        <div class="container">
            <div class="row">
                <div class="col-lg-6" style="margin-bottom: 40px;">
                    <h2 style="font-size: 36px; font-weight: 700; color: var(--travhub-black); margin-bottom: 30px;">Our Story</h2>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px; font-size: 16px;">
                        Siwride was founded with a simple mission: to provide reliable, comfortable, and affordable transportation services across Indonesia. What started as a small fleet of vehicles has grown into a comprehensive transportation solution serving thousands of satisfied customers.
                    </p>
                    <p style="color: #666; line-height: 1.8; margin-bottom: 20px; font-size: 16px;">
                        We understand that every journey is important, whether it's a business trip, family vacation, or special occasion. That's why we've built our service around three core principles: safety, comfort, and reliability.
                    </p>
                </div>
                <div class="col-lg-6" style="margin-bottom: 40px;">
                    <div style="background: var(--travhub-base); border-radius: 20px; padding: 40px; text-align: center; height: 100%;">
                        <img src="assets/images/siwride_logo.png" alt="Siwride" style="width: 120px; margin-bottom: 20px; border: 3px solid white; border-radius: 50%; padding: 5px; background: white;" />
                        <h3 style="color: white; font-size: 24px; font-weight: 700; margin-bottom: 15px;">Professional Transportation</h3>
                        <p style="color: rgba(255,255,255,0.9); line-height: 1.6;">Serving Indonesia with pride and excellence since 2020</p>
                    </div>
                </div>
            </div>
            
            <div class="row" style="margin-top: 60px;">
                <div class="col-12">
                    <h2 style="font-size: 36px; font-weight: 700; color: var(--travhub-black); margin-bottom: 40px; text-align: center;">Why Choose Siwride?</h2>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="feature-card" style="text-align: center; padding: 30px;">
                        <div style="width: 70px; height: 70px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fa fa-shield-alt" style="color: white; font-size: 30px;"></i>
                        </div>
                        <h4 style="font-size: 20px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">Safety First</h4>
                        <p style="color: #666; line-height: 1.6;">All our vehicles are regularly maintained and drivers are professionally trained and licensed.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="feature-card" style="text-align: center; padding: 30px;">
                        <div style="width: 70px; height: 70px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fa fa-clock" style="color: white; font-size: 30px;"></i>
                        </div>
                        <h4 style="font-size: 20px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">24/7 Service</h4>
                        <p style="color: #666; line-height: 1.6;">Available round the clock to serve your transportation needs anytime, anywhere.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6" style="margin-bottom: 40px;">
                    <div class="feature-card" style="text-align: center; padding: 30px;">
                        <div style="width: 70px; height: 70px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                            <i class="fa fa-dollar-sign" style="color: white; font-size: 30px;"></i>
                        </div>
                        <h4 style="font-size: 20px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">Competitive Pricing</h4>
                        <p style="color: #666; line-height: 1.6;">Transparent pricing with no hidden fees. Quality service at affordable rates.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    .feature-card {
        transition: transform 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-5px);
    }
</style>
