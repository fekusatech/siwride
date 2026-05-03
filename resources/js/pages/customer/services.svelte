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
                <h3 class="sec-title__title bw-split-in-left">What We Offer</h3>
            </div>
            <div class="row gutter-y-30">
                <!-- Service 1 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-plane" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            Airport Transfer
                        </h5>
                        <p class="why-choose-one__box__text">
                            Reliable airport pickup and drop-off services with
                            professional drivers and comfortable vehicles.
                        </p>
                    </div>
                </div>

                <!-- Service 2 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="200ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-car" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">City Tour</h5>
                        <p class="why-choose-one__box__text">
                            Explore the city with our comfortable and
                            informative city tour services.
                        </p>
                    </div>
                </div>

                <!-- Service 3 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="300ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-hotel" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            Hotel Transfer
                        </h5>
                        <p class="why-choose-one__box__text">
                            Convenient hotel transfer services for comfortable
                            travel to and from your accommodation.
                        </p>
                    </div>
                </div>

                <!-- Service 4 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="400ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-ring" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">Wedding Car</h5>
                        <p class="why-choose-one__box__text">
                            Elegant wedding transportation services with premium
                            vehicles and professional chauffeurs.
                        </p>
                    </div>
                </div>

                <!-- Service 5 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="500ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-briefcase" style="font-size: 32px;"
                            ></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            Business Trip
                        </h5>
                        <p class="why-choose-one__box__text">
                            Professional business transportation with punctual
                            and reliable service.
                        </p>
                    </div>
                </div>

                <!-- Service 6 -->
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="600ms"
                >
                    <div
                        class="service-card why-choose-one__box text-center h-100 p-4"
                        style="background-color: #fff;"
                    >
                        <div
                            class="why-choose-one__box__icon mx-auto"
                            style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                        >
                            <i class="fa fa-clock" style="font-size: 32px;"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            Hourly Charter
                        </h5>
                        <p class="why-choose-one__box__text">
                            Flexible hourly charter services for customized
                            travel needs.
                        </p>
                    </div>
                </div>
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
