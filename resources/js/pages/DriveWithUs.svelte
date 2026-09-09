<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const benefits = [
        {
            icon: 'fas fa-route',
            title: 'Flexible trip types',
            text: 'Airport transfers, tours, hourly service, and ride sharing — pick the bookings that fit your schedule.',
        },
        {
            icon: 'fas fa-wallet',
            title: 'Track your earnings',
            text: 'Every trip is logged to your driver wallet, with straightforward withdrawals whenever you need them.',
        },
        {
            icon: 'fas fa-id-card',
            title: 'Manage your own profile',
            text: 'Update your details and documents anytime from your self-service driver dashboard.',
        },
        {
            icon: 'fas fa-headset',
            title: 'Support from our team',
            text: 'Our admin team reviews every application and is on hand once you start taking trips.',
        },
    ];

    const steps = [
        {
            title: 'Apply online',
            text: 'Fill out the driver application with your details and vehicle information.',
        },
        {
            title: 'We review your application',
            text: 'Our team checks your submission — this usually only takes a short while.',
        },
        {
            title: 'Complete your profile',
            text: 'Once approved, log in and add your ID (KTP) and driving license (SIM) photos.',
        },
        {
            title: 'Start driving',
            text: 'Accept bookings and start earning across Bali.',
        },
    ];

    const requirements = [
        'A vehicle registered in your name, in good condition',
        'An active phone number reachable on WhatsApp',
        'A valid ID (KTP) and driving license (SIM)',
        'Willingness to provide great service to travelers',
    ];
</script>

<AppHead title="Drive with Us - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Drive with Siwride</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Drive with Us</span></li>
            </ul>
        </div>
    </section>

    <!-- Intro + CTA -->
    <section class="pt-120 pb-60">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Become a Driver Partner<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Turn your car or bike into an income
                </h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 620px; margin: 15px auto 0;"
                >
                    Join Siwride's network of local driver partners and get matched with travelers
                    looking for airport transfers, day tours, and rides across Bali.
                </p>
            </div>
            <div class="text-center mt-4">
                <a href="/driver/register" class="travhub-btn">
                    <span>Apply to Drive</span>
                </a>
            </div>
        </div>
    </section>

    <!-- Benefits Grid -->
    <section class="services-section pb-120">
        <div class="container">
            <div class="row gutter-y-30">
                {#each benefits as benefit, index}
                    <div
                        class="col-lg-3 col-md-6 wow fadeInUp"
                        data-wow-delay="{index * 100}ms"
                    >
                        <div
                            class="service-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i class={benefit.icon} style="font-size: 30px;"></i>
                            </div>
                            <h5 class="why-choose-one__box__title">{benefit.title}</h5>
                            <p class="why-choose-one__box__text">{benefit.text}</p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="pb-120" style="background: #f8f9fa; padding-top: 100px;">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Getting Started<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">How it works</h3>
            </div>
            <div class="row gutter-y-30 justify-content-center">
                {#each steps as step, index}
                    <div class="col-lg-3 col-md-6 wow fadeInUp" data-wow-delay="{index * 100}ms">
                        <div style="text-align: center; padding: 0 10px;">
                            <div
                                style="width: 56px; height: 56px; border-radius: 50%; background: var(--travhub-base, #d11f1f); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 800; margin: 0 auto 18px;"
                            >
                                {index + 1}
                            </div>
                            <h5 style="font-size: 17px; font-weight: 700; margin-bottom: 8px;">
                                {step.title}
                            </h5>
                            <p style="color: #777; font-size: 14px; line-height: 1.7; margin: 0;">
                                {step.text}
                            </p>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Requirements + CTA -->
    <section class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div
                        style="background: #fff; border: 1px solid #eef1f4; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.05); padding: 40px;"
                    >
                        <h4 style="font-weight: 700; margin-bottom: 20px;">What you'll need</h4>
                        <ul style="list-style: none; padding: 0; margin: 0 0 30px;">
                            {#each requirements as requirement}
                                <li
                                    style="display: flex; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f1f1; color: #555;"
                                >
                                    <i
                                        class="fas fa-check-circle"
                                        style="color: var(--travhub-base, #d11f1f); margin-top: 3px;"
                                    ></i>
                                    <span>{requirement}</span>
                                </li>
                            {/each}
                        </ul>
                        <div class="text-center">
                            <a href="/driver/register" class="travhub-btn">
                                <span>Apply to Drive</span>
                            </a>
                            <p style="color: #999; font-size: 13px; margin: 14px 0 0;">
                                Already applied? <a href="/driver/login">Log in to your driver account</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
