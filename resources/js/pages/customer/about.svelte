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

    <!-- ==================== Page Header ==================== -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">About Us</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li>About Us</li>
            </ul>
        </div>
    </section>

    <!-- ==================== Story / Why Choose Us ==================== -->
    <section class="why-choose-one why-choose-one--about">
        <div class="container">
            <div class="row d-flex align-items-center justify-content-center">
                <!-- Content Side -->
                <div class="col-lg-10 wow fadeInRight" data-wow-delay="200ms">
                    <div class="why-choose-one__content">
                        <div class="sec-title text-center">
                            <div class="sec-title__tagline bw-split-in-right">
                                Our Story<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="Travhub"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                Why Choose Siwride
                            </h3>
                            <p class="sec-title__text bw-split-in-up-fast">
                                Siwride was founded with a simple mission: to
                                provide reliable, comfortable, and affordable
                                transportation services across Bali and
                                surrounding islands. Based in Tabanan, we are
                                your trusted local experts.
                            </p>
                        </div>
                        <div class="row mt-5">
                            <div class="col-md-6 mb-4">
                                <div class="text-center h-100 p-4">
                                    <div
                                        class="why-choose-one__box__icon mx-auto"
                                        style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                                    >
                                        <i class="flaticon-suitcase"></i>
                                    </div>
                                    <h5
                                        style="font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);"
                                    >
                                        Professional Drivers
                                    </h5>
                                    <p style="color: #666; margin-bottom: 0;">
                                        Enjoy a safe and comfortable trip with
                                        our highly trained, experienced, and
                                        licensed local drivers.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="text-center h-100 p-4">
                                    <div
                                        class="why-choose-one__box__icon mx-auto"
                                        style="position: relative; left: auto; top: auto; display: inline-flex; justify-content: center; align-items: center; margin-bottom: 20px;"
                                    >
                                        <i class="flaticon-signpost"></i>
                                    </div>
                                    <h5
                                        style="font-weight: 700; margin-bottom: 15px; color: var(--travhub-black);"
                                    >
                                        Tailored Destinations
                                    </h5>
                                    <p style="color: #666; margin-bottom: 0;">
                                        Whether it's a popular tourist spot or a
                                        hidden gem in Bali, we will take you
                                        there with ease.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="why-choose-one__btn justify-content-center text-center mt-4"
                        >
                            <a
                                href="/booking"
                                class="travhub-btn"
                                style="width: 100%; max-width: 500px; padding: 15px 0;"
                            >
                                <span>Start Booking</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== Statistics / Counters ==================== -->
    <section class="counter-one">
        <!-- Parallax Background Shape -->
        <div
            class="counter-one__shape travhub-splax"
            data-para-options={JSON.stringify({
                orientation: 'up',
                scale: 2.0,
                overflow: true,
            })}
        >
            <img
                src="/assets/images/shapes/counter-1-shape.png"
                alt="Siwride"
            />
        </div>
        <div class="container">
            <div class="row align-items-end gutter-y-30">
                <div class="col-lg-6">
                    <div
                        class="counter-one__image wow fadeInLeft"
                        data-wow-duration="1500ms"
                        data-wow-delay="100ms"
                    >
                        <img
                            src="/assets/images/resources/why-choose-2.png"
                            alt="Siwride Overview"
                            style="width: 100%; border-radius: 20px; object-fit: cover; aspect-ratio: 4/3; box-shadow: 0 10px 40px rgba(0,0,0,0.1);"
                        />
                    </div>
                </div>
                <div class="col-lg-6">
                    <div
                        class="counter-one__funfact wow fadeInRight"
                        data-wow-duration="1500ms"
                        data-wow-delay="100ms"
                    >
                        <div class="counter-one__funfact__item count-box">
                            <h3 class="counter-one__funfact__count">
                                <span
                                    class="count-text"
                                    data-stop="10"
                                    data-speed="1500">10</span
                                >
                                <span class="counter-one__funfact__count-tag"
                                    >k+</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Satisfied Customers
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3 class="counter-one__funfact__count">
                                <span
                                    class="count-text"
                                    data-stop="150"
                                    data-speed="1500">150</span
                                >
                                <span class="counter-one__funfact__count-tag"
                                    >+</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Professional Drivers
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3 class="counter-one__funfact__count">
                                <span
                                    class="count-text"
                                    data-stop="5"
                                    data-speed="1500">5</span
                                >
                                <span class="counter-one__funfact__count-tag"
                                    >+</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Years of Experience
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3 class="counter-one__funfact__count">
                                <span
                                    class="count-text"
                                    data-stop="100"
                                    data-speed="1500">100</span
                                >
                                <span class="counter-one__funfact__count-tag"
                                    >%</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Secure Trips
                            </p>
                        </div>
                        <div class="counter-one__funfact__item-two count-box">
                            <h3 class="counter-one__funfact__item-two__count">
                                <span
                                    class="count-text"
                                    data-stop="4.8"
                                    data-speed="1500">4.9</span
                                >
                            </h3>
                        </div>
                        <span class="counter-one__funfact__line"></span>
                        <span class="counter-one__funfact__line-two"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ==================== Professional Drivers / Team ====================
    <section class="team-one">
        <div class="team-one__shape travhub-splax" data-para-options={JSON.stringify({ orientation: "up", scale: 2.5, overflow: true })}>
            <img src="/assets/images/shapes/team-1-shape.png" alt="travhub"/>
        </div>
        <div class="container">
            <div class="sec-title text-center wow fadeInUp" data-wow-delay="00ms">
                <div class="sec-title__tagline bw-split-in-right">Friendly Experts<img src="/assets/images/shapes/sec-title-shape.png" alt="Travhub"></div>
                <h3 class="sec-title__title bw-split-in-left">Meet Our Drivers</h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    Our team consists of dedicated local experts who know the island inside out, ensuring your journey is safe and memorable.
                </p>
            </div>
            <div class="row gutter-x-24 gutter-y-24">
                <div class="col-xl-3 col-md-6">
                    <div class="team-one__item wow fadeInUp" data-wow-delay="100ms">
                        <div class="team-one__item__image">
                            <img src="/assets/images/team/team-1-1.jpg" alt="Driver Made">
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">Bli Made</h4>
                            <p class="team-one__item__text">Senior Driver</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="team-one__item wow fadeInUp" data-wow-delay="200ms">
                        <div class="team-one__item__image">
                            <img src="/assets/images/team/team-1-2.jpg" alt="Driver Wayan">
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">Bli Wayan</h4>
                            <p class="team-one__item__text">Tour & Transport Specialist</p>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div class="team-one__item wow fadeInUp" data-wow-delay="300ms">
                        <div class="team-one__item__image">
                            <img src="/assets/images/team/team-1-3.jpg" alt="Driver Ketut">
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">Bli Ketut</h4>
                            <p class="team-one__item__text">VIP Driver</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6">
                    <div class="team-one__item wow fadeInUp" data-wow-delay="400ms">
                        <div class="team-one__item__image">
                            <img src="/assets/images/team/team-1-4.jpg" alt="Driver Putu">
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">Bli Putu</h4>
                            <p class="team-one__item__text">Minibus Expert</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section> -->

    <section
        class="why-choose-one how-we-work"
        style="background-color: #f7f9fa;"
    >
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    How We Work<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    A Reliable Ride, Every Time
                </h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 700px; margin: 15px auto 0;"
                >
                    We focus on punctuality, safety, and clear communication so
                    your trip stays smooth from pick-up to drop-off.
                </p>
            </div>

            <div class="row gutter-y-30">
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div
                        class="why-choose-one__box text-center"
                        style="height: 100%;"
                    >
                        <div
                            class="why-choose-one__box__icon"
                            style="position: relative; left: auto; top: auto; margin: 0 auto 18px; display: inline-flex; justify-content: center; align-items: center;"
                        >
                            <i class="flaticon-time"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            On-Time Pickups
                        </h5>
                        <p class="why-choose-one__box__text">
                            We prioritize punctual schedules, especially for
                            airport and hotel transfers.
                        </p>
                    </div>
                </div>

                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="200ms"
                >
                    <div
                        class="why-choose-one__box text-center"
                        style="height: 100%;"
                    >
                        <div
                            class="why-choose-one__box__icon"
                            style="position: relative; left: auto; top: auto; margin: 0 auto 18px; display: inline-flex; justify-content: center; align-items: center;"
                        >
                            <i class="flaticon-shield"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">Safety First</h5>
                        <p class="why-choose-one__box__text">
                            Professional drivers, well-maintained vehicles, and
                            a calm driving style.
                        </p>
                    </div>
                </div>

                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="300ms"
                >
                    <div
                        class="why-choose-one__box text-center"
                        style="height: 100%;"
                    >
                        <div
                            class="why-choose-one__box__icon"
                            style="position: relative; left: auto; top: auto; margin: 0 auto 18px; display: inline-flex; justify-content: center; align-items: center;"
                        >
                            <i class="flaticon-mail"></i>
                        </div>
                        <h5 class="why-choose-one__box__title">
                            Clear Communication
                        </h5>
                        <p class="why-choose-one__box__text">
                            We confirm details and keep you updated so there are
                            no surprises.
                        </p>
                    </div>
                </div>
            </div>

            <div class="text-center" style="margin-top: 35px;">
                <a
                    href="/booking"
                    class="travhub-btn"
                    style="max-width: 420px; width: 100%;"
                >
                    <span>Book Your Ride</span>
                </a>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    :global(.how-we-work .why-choose-one__box) {
        padding: 0;
    }

    :global(.how-we-work .why-choose-one__box__icon) {
        position: relative;
        left: auto;
        top: auto;
    }
</style>
