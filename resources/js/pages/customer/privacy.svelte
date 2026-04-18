<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
</script>

<AppHead title="Privacy Policy - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Privacy Policy</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Privacy</span></li>
            </ul>
        </div>
    </section>

    <!-- Legal Content Section -->
    <section class="legal-section pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 col-xl-8 wow fadeInUp" data-wow-delay="100ms">
                    <div class="legal-content" style="background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f1f1f1;">
                        
                        <div class="text-center mb-5 pb-3" style="border-bottom: 1px solid #eee;">
                            <h3 style="font-size: 32px; font-weight: 700; margin-bottom: 15px;">Siwride Privacy Policy</h3>
                            <p style="color: #666; font-size: 15px;">Last Updated: October 2024</p>
                        </div>
                        
                        <div class="legal-text-block">
                            <h4 class="mb-3">1. Information We Collect</h4>
                            <p class="mb-3">When you use the Siwride platform to book trips around Bali, we may collect various types of information, including, but not limited to:</p>
                            <ul class="mb-5" style="list-style-type: none; padding-left: 0;">
                                <li style="margin-bottom: 10px; position: relative; padding-left: 20px;"><i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Personal Information:</strong> Your name, phone number, and email address used for booking and communication.</li>
                                <li style="margin-bottom: 10px; position: relative; padding-left: 20px;"><i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Trip Details:</strong> Pickup locations, drop-off locations, date, schedule, and requested amenities.</li>
                                <li style="margin-bottom: 10px; position: relative; padding-left: 20px;"><i class="flaticon-check" style="position: absolute; left: 0; top: 4px; color: var(--travhub-base); font-size: 12px;"></i> <strong>Technical Data:</strong> IP address, device types, browser information, and interactions with our website to help us improve the platform.</li>
                            </ul>

                            <h4 class="mb-3">2. How We Use Your Information</h4>
                            <p class="mb-5">Your data primarily enables us to successfully match you with our drivers and confirm your requested bookings. We also utilize this information to send booking confirmations, communicate important updates regarding your ride, process payments, and respond to your customer support requests.</p>

                            <h4 class="mb-3">3. Data Sharing and Disclosure</h4>
                            <p class="mb-5">Siwride respects your privacy and will never sell your personal data. However, we do share specific necessary information (like your pickup details and phone number) exclusively with your assigned driver so they can safely fulfill their service. We may also share data with third-party service providers (like payment gateways) under strict confidentiality agreements.</p>

                            <h4 class="mb-3">4. Cookies and Local Storage</h4>
                            <p class="mb-5">Our website utilizes "cookies" and local browser storage to enhance your browsing experience, remember your preferences, and maintain seamless functionality across sessions. By browsing our website, you consent to our use of these simple tracking technologies. You can always disable cookies through your browser settings, though doing so may limit your ability to book a ride smoothly.</p>

                            <h4 class="mb-3">5. Data Deletion & Your Rights</h4>
                            <p>You have the full right to access, amend, or request the deletion of any personal data Siwride holds about you at any time. Simply contact our support team at our official email, and we will honor your request promptly inline with applicable data protection laws.</p>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    .legal-text-block h4 {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }
    .legal-text-block p {
        color: #666;
        line-height: 1.8;
        font-size: 16px;
    }
</style>
