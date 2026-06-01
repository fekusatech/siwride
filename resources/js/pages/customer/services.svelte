<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
    const ourServices = $derived(settings.our_services || []);
</script>

<AppHead title="Services - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Our Services</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Services</span></li>
            </ul>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="services-section pt-120 pb-120">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Siwride Services<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">{settings.services_title || 'What We Offer'}</h3>
                {#if settings.services_subtitle}
                <p class="sec-title__text bw-split-in-up-fast" style="max-width: 600px; margin: 15px auto 0;">
                    {settings.services_subtitle}
                </p>
                {/if}
            </div>
            <div class="row gutter-y-30">
                {#each ourServices as service, index}
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
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
                            <i class={service.icon} style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            {service.title}
                        </h5>
                        <p class="why-choose-one__box__text">
                            {service.description}
                        </p>
                    </div>
                </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section
        class="cta-one pt-120 pb-120 text-center"
        style="background-color: #f9f9f9;"
    >
        <div class="container">
            <h2 class="sec-title__title bw-split-in-down mb-4">
                Ready for a comfortable ride?
            </h2>
            <p class="mb-5" style="font-size: 18px; color: #666;">
                Book your trip with Siwride today and enjoy the best
                transportation services in Bali.
            </p>
            <a href="/booking" class="travhub-btn">
                <span>Start Booking Now</span>
            </a>
        </div>
    </section>

    <Footer />
</div>

<style>
    .service-card {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .service-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border-color: var(--travhub-base);
    }
</style>
