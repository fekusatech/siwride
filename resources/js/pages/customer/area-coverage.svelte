<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
    const popularDestinations = $derived(settings.popular_destinations || []);
</script>

<AppHead title="Area Coverage - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Area Coverage</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Area Coverage</span></li>
            </ul>
        </div>
    </section>
    <!-- Map Section -->
    <section class="service-area" style="padding: 100px 0; background: #fff;">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Where We Operate<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    {settings.coverage_area_title || 'Our Coverage in Bali'}
                </h3>
            </div>

            <div class="row" style="margin-top: 40px;">
                <div class="col-lg-12 wow fadeInUp" data-wow-duration="1500ms">
                    <div
                        style="border-radius: 15px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.1); height: 500px; position: relative; border: 1px solid #f1f1f1; background-color: #eef2f5;"
                    >
                        {#if !settings.coverage_area_image}
                            <div
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1;"
                            >
                                <i
                                    class="flaticon-pin-1"
                                    style="font-size: 60px; color: var(--travhub-base, #e52029); margin-bottom: 20px; opacity: 0.8;"
                                ></i>
                                <h4
                                    style="color: #444; font-weight: 700; font-size: 24px; margin-bottom: 10px;"
                                >
                                    Illustration Map of Bali
                                </h4>
                                <p style="color: #888; font-size: 15px; margin: 0;">
                                    (This area is reserved for a static map image of
                                    Bali)
                                </p>
                            </div>
                        {/if}
                        <img
                            src={settings.coverage_area_image || 'https://placehold.co/1200x500/e9ecef/e9ecef'}
                            alt="Bali Map Placeholder"
                            style="width: 100%; height: 100%; object-fit: cover; position: relative; z-index: 0;"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Area Coverage Grid -->
    <section class="destination-one pt-120 pb-120">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Where We Serve<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    {settings.destinations_title || 'Explore Our Covered Areas in Bali'}
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    {settings.destinations_subtitle || 'From stunning beaches to cultural hubs, our professional drivers are ready to take you anywhere comfortably.'}
                </p>
            </div>
            <div class="row gutter-y-25">
                {#each popularDestinations as dest, index}
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="{index * 100}ms"
                >
                    <div class="destination-one__item">
                        <img
                            src={dest.img}
                            alt={dest.name}
                        />
                        <div class="destination-one__item__btn">
                            <a
                                href="/booking"
                                aria-label="Book for this destination"
                                ><span><i class="flaticon-top-right"></i></span
                                ></a
                            >
                        </div>
                        <div class="destination-one__content">
                            <h4 class="destination-one__item__title">
                                <a href="/booking">{dest.name}</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                {dest.location}
                            </div>
                        </div>
                    </div>
                </div>
                {/each}
            </div>

            <div class="trusted-area text-center mt-5">
                <p class="mb-4" style="color: #666; font-size: 18px;">
                    Don't see your destination here? No worries, Siwride covers
                    all areas in Bali. <br />
                    You can simply enter any specific pick-up or drop-off point when
                    booking your ride!
                </p>
                <div class="destination-one__btn">
                    <a href="/booking" class="travhub-btn">
                        <span>Book Your Ride Now</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* Force uniform card height and cover-fit images */
    :global(.destination-one__item) {
        height: 350px;
    }

    :global(.destination-one__item img) {
        height: 100%;
        width: 100%;
        object-fit: cover;
    }
</style>
