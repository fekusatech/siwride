<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import {
        getVehicleInfo,
        isValidVehicleSlug,
        type VehicleSlug,
    } from './slugs.js';

    $: slug = page.url.split('/').pop() as VehicleSlug;
    $: vehicleInfo = getVehicleInfo(slug);
    $: isValid = isValidVehicleSlug(slug);

    // Determine page title based on vehicle info
    $: pageTitle =
        vehicleInfo?.title ||
        slug?.replace('-', ' ').replace(/\b\w/g, (l) => l.toUpperCase()) ||
        'Vehicles';
</script>

<AppHead title="{pageTitle} - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">
                {vehicleInfo?.title || 'Vehicles'}
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/vehicles">Vehicles</a></li>
                <li><span>{vehicleInfo?.title || 'Details'}</span></li>
            </ul>
        </div>
    </section>

    <!-- Vehicle Category Content -->
    {#if isValid && vehicleInfo}
        <section class="about-section pt-120 pb-120">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-5 wow fadeInLeft" data-wow-delay="100ms">
                        <div class="sec-title mb-4">
                            <div class="sec-title__tagline bw-split-in-right">
                                Vehicle Specifications<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="travhub"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                {vehicleInfo.title} Details
                            </h3>
                        </div>
                        <p class="mb-5" style="color: #666; font-size: 18px;">
                            {vehicleInfo.description ||
                                'Find the perfect vehicle for your journey'}
                        </p>

                        <div class="row gutter-y-20">
                            <!-- Capacity -->
                            <div class="col-md-6">
                                <div
                                    class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                                    style="background-color: #fff;"
                                >
                                    <div
                                        class="why-choose-one__box__icon mx-auto"
                                        style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                                    >
                                        <i
                                            class="icon-traveler-with-a-suitcase-1"
                                            style="font-size: 32px;"
                                        ></i>
                                    </div>
                                    <h5 class="why-choose-one__box__title">
                                        Capacity
                                    </h5>
                                    <p class="why-choose-one__box__text">
                                        {vehicleInfo.capacity}
                                    </p>
                                </div>
                            </div>

                            <!-- Examples -->
                            <div class="col-md-6">
                                <div
                                    class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                                    style="background-color: #fff;"
                                >
                                    <div
                                        class="why-choose-one__box__icon mx-auto"
                                        style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                                    >
                                        <i
                                            class="fa fa-car"
                                            style="font-size: 32px;"
                                        ></i>
                                    </div>
                                    <h5 class="why-choose-one__box__title">
                                        Examples
                                    </h5>
                                    <p class="why-choose-one__box__text">
                                        {vehicleInfo.examples}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="col-lg-7 wow fadeInRight"
                        data-wow-delay="200ms"
                    >
                        <div
                            class="about-three__image mt-5 mt-lg-0"
                            style="position: relative; border-radius: 20px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.1);"
                        >
                            <img
                                src={vehicleInfo.img}
                                alt={vehicleInfo.title}
                                style="width: 100%; height: 500px; object-fit: cover;"
                            />
                            <div
                                style="position: absolute; bottom: 0; left: 0; width: 100%; background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, rgba(0,0,0,0) 100%); padding: 40px 30px 30px;"
                            >
                                <h3
                                    style="color: white; font-size: 28px; font-weight: 700; margin-bottom: 5px;"
                                >
                                    {vehicleInfo.title}
                                </h3>
                                <p
                                    style="color: rgba(255,255,255,0.9); margin: 0;"
                                >
                                    Professional {vehicleInfo.title.toLowerCase()}
                                    for your comfortable journey
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-5">
                    <div
                        class="col-12 text-center wow fadeInUp"
                        data-wow-delay="300ms"
                    >
                        <a
                            href="/booking?vehicle={vehicleInfo.vehicleType}"
                            class="travhub-btn mt-2"
                            style="margin-right: 15px;"
                        >
                            <span>Book This Vehicle</span>
                        </a>
                        <!-- Link to contact page with a stylized custom dark button -->
                        <a
                            href="/contact"
                            class="travhub-btn mt-2"
                            style="background-color: #222;"
                        >
                            <span>Ask About This Vehicle</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    {:else}
        <section class="about-section pt-120 pb-120">
            <div class="container text-center">
                <div class="sec-title mb-5">
                    <div class="sec-title__tagline bw-split-in-right">
                        Oops!<img
                            src="/assets/images/shapes/sec-title-shape.png"
                            alt="travhub"
                        />
                    </div>
                    <h2 class="sec-title__title bw-split-in-left">
                        Vehicle Category Not Found
                    </h2>
                    <p class="sec-title__text bw-split-in-up-fast">
                        The vehicle category "{slug}" is not available.
                    </p>
                </div>

                <div class="row gutter-y-30">
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="100ms"
                    >
                        <div
                            class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i class="fa fa-car" style="font-size: 32px;"
                                ></i>
                            </div>
                            <h5 class="why-choose-one__box__title mb-3">
                                Standard Cars
                            </h5>
                            <a
                                href="/vehicles/standard-cars"
                                class="travhub-btn"
                                style="font-size: 14px; padding: 12px 25px;"
                                ><span>View Details</span></a
                            >
                        </div>
                    </div>
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="200ms"
                    >
                        <div
                            class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i class="fa fa-car" style="font-size: 32px;"
                                ></i>
                            </div>
                            <h5 class="why-choose-one__box__title mb-3">
                                Premium Cars
                            </h5>
                            <a
                                href="/vehicles/premium-cars"
                                class="travhub-btn"
                                style="font-size: 14px; padding: 12px 25px;"
                                ><span>View Details</span></a
                            >
                        </div>
                    </div>
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="300ms"
                    >
                        <div
                            class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i
                                    class="icon-traveler-with-a-suitcase-1"
                                    style="font-size: 32px;"
                                ></i>
                            </div>
                            <h5 class="why-choose-one__box__title mb-3">
                                Vans & Minibuses
                            </h5>
                            <a
                                href="/vehicles/vans-minibuses"
                                class="travhub-btn"
                                style="font-size: 14px; padding: 12px 25px;"
                                ><span>View Details</span></a
                            >
                        </div>
                    </div>
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="400ms"
                    >
                        <div
                            class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i class="flaticon-bus" style="font-size: 32px;"
                                ></i>
                            </div>
                            <h5 class="why-choose-one__box__title mb-3">
                                Buses
                            </h5>
                            <a
                                href="/vehicles/buses"
                                class="travhub-btn"
                                style="font-size: 14px; padding: 12px 25px;"
                                ><span>View Details</span></a
                            >
                        </div>
                    </div>
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="500ms"
                    >
                        <div
                            class="vehicle-feature-card why-choose-one__box text-center h-100 p-4"
                            style="background-color: #fff;"
                        >
                            <div
                                class="why-choose-one__box__icon mx-auto"
                                style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                            >
                                <i
                                    class="icon-settings"
                                    style="font-size: 32px;"
                                ></i>
                            </div>
                            <h5 class="why-choose-one__box__title mb-3">
                                Special Vehicles
                            </h5>
                            <a
                                href="/vehicles/special-vehicles"
                                class="travhub-btn"
                                style="font-size: 14px; padding: 12px 25px;"
                                ><span>View Details</span></a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </section>
    {/if}

    <Footer />
</div>

<style>
    .vehicle-feature-card {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        transition:
            transform 0.3s ease,
            box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }

    .vehicle-feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        border-color: var(--travhub-base);
    }

    :global(.about-three__image) {
        margin-right: 0;
    }
</style>
