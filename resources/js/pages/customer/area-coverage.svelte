<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
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
                    Our Coverage in Bali
                </h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 600px; margin: 15px auto 0;"
                >
                    Currently, Siwride operates across all major tourist and
                    business hubs in Bali. Zoom in on the map to see exactly
                    where our drivers are ready to pick you up.
                </p>
            </div>

            <div class="row" style="margin-top: 40px;">
                <div class="col-lg-12 wow fadeInUp" data-wow-duration="1500ms">
                    <div
                        style="border-radius: 15px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.1); height: 500px; position: relative; border: 1px solid #f1f1f1; background-color: #eef2f5;"
                    >
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
                        <img
                            src="https://placehold.co/1200x500/e9ecef/e9ecef"
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
                    Explore Our Covered Areas in Bali
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    From stunning beaches to cultural hubs, our professional
                    drivers <br /> are ready to take you anywhere comfortably.
                </p>
            </div>
            <div class="row gutter-y-25">
                <!-- Area 1 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/tanahlot-temple.jpg"
                            alt="Tanah Lot Temple"
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
                                <a href="/booking">Tanah Lot Temple</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                Tabanan, Bali
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area 2 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="200ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/uluwatu-temple.webp"
                            alt="Uluwatu Temple"
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
                                <a href="/booking">Uluwatu Temple</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                South Kuta, Bali
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area 3 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="300ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/tegallalang.webp"
                            alt="Tegallalang Rice Terrace"
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
                                <a href="/booking">Tegallalang Rice Terrace</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                Ubud, Bali
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area 4 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="400ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/lempuyang.jpg"
                            alt="Lempuyang Temple"
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
                                <a href="/booking">Lempuyang Temple</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                Karangasem, Bali
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area 5 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="500ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/seminyak.webp"
                            alt="Seminyak Beach"
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
                                <a href="/booking">Seminyak Beach</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                Kuta, Bali
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Area 6 -->
                <div
                    class="col-xl-4 col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-delay="600ms"
                >
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/mount-batur.jpg"
                            alt="Mount Batur"
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
                                <a href="/booking">Mount Batur</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                Kintamani, Bali
                            </div>
                        </div>
                    </div>
                </div>
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
