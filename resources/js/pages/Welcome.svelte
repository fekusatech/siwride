<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import RideSharingLocationSearchInput from '@/components/RideSharingLocationSearchInput.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import TimePicker from '@/components/TimePicker.svelte';
    import { getMinPickupTime, formatEarliestTime } from '@/lib/pickupTime';

    let heroPickup = $state('');
    let heroDropoff = $state('');
    let heroDate = $state('');
    let heroTime = $state('');
    let heroTripType = $state<'one_way' | 'round_trip'>('one_way');
    let heroReturnDate = $state('');

    const heroReturnDateError = $derived.by(() => {
        if (heroTripType === 'round_trip' && heroDate && heroReturnDate && heroReturnDate < heroDate) {
            return 'Return date cannot be before departure date.';
        }
        return null;
    });

    /** Minimum pickup time for today (empty string = no restriction for future dates). */
    const heroMinTime = $derived(getMinPickupTime(heroDate));

    function handleHeroSubmit(e: Event) {
        if (heroReturnDateError) {
            e.preventDefault();
            alert(heroReturnDateError);
        }
    }

    let passengerCount = $state(1);

    // --- Service Tab ---
    let activeTab = $state<'point-to-point' | 'ride-sharing'>('point-to-point');

    // --- Ride Sharing form state ---
    let rsDate = $state('');
    let rsPickupLocationId = $state('');
    let rsDropoffLocationId = $state('');

    let rsPassengers = $state(1);

    let {
        vehicleCategories = [],
        rideSharingLocations = [],
        services = [],
        tourPackages = [],
    } = $props<{
        vehicleCategories: any[];
        rideSharingLocations: { id: number; name: string }[];
        services?: any[];
        tourPackages?: {
            id: number;
            name: string;
            slug: string;
            description: string;
            price_per_pax: number;
            duration_hours: number;
            max_pax: number;
            destinations: string[];
            image_url: string;
        }[];
    }>();

    const settings = $derived(page.props.settings as any);

    const popularDestinations = $derived(settings.popular_destinations || []);
    const customerTestimonials = $derived(settings.customer_testimonials || []);

    onMount(() => {
        document.body.classList.add('custom-cursor');

        if ((window as any).$) {
            const jQuery = (window as any).$;

            // Wait for DOM
            setTimeout(() => {
            // Initialize Tour Packages Carousel
                const tourCarousel = jQuery('.featured-tours__carousel');
                if (
                    tourCarousel.length &&
                    typeof tourCarousel.owlCarousel === 'function'
                ) {
                    tourCarousel.owlCarousel({
                        loop: true,
                        margin: 30,
                        nav: true,
                        dots: false,
                        navText: [
                            '<i class="fas fa-chevron-left"></i>',
                            '<i class="fas fa-chevron-right"></i>',
                        ],
                        smartSpeed: 700,
                        autoplay: true,
                        autoplayTimeout: 5000,
                        autoplayHoverPause: true,
                        responsive: {
                            0: { items: 1 },
                            768: { items: 2 },
                            1024: { items: 3 },
                        },
                    });
                }

                // Initialize Vehicles Carousel
                const vehicleCarousel = jQuery('.tours-one__carousel');
                if (
                    vehicleCarousel.length &&
                    typeof vehicleCarousel.owlCarousel === 'function'
                ) {
                    const options = vehicleCarousel.data('owl-options');
                    vehicleCarousel.owlCarousel(
                        typeof options === 'object'
                            ? options
                            : JSON.parse(options),
                    );
                }
                // Initialize Testimonials Carousel
                const testimonialCarousel = jQuery(
                    '.testimonials-one__carousel',
                );
                if (
                    testimonialCarousel.length &&
                    typeof testimonialCarousel.owlCarousel === 'function'
                ) {
                    const options = testimonialCarousel.data('owl-options');
                    testimonialCarousel.owlCarousel(
                        typeof options === 'object'
                            ? options
                            : JSON.parse(options),
                    );
                }
            }, 100);
        }
    });

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            document.body.classList.remove('custom-cursor');
        }
    });

    import Preloader from '@/components/Template/Preloader.svelte';
    import Topbar from '@/components/Template/Topbar.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import BookingSearch from '@/components/BookingSearch.svelte';

    const auth = $derived(page.props.auth);
</script>

<AppHead title="Welcome | Siwride" />

<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<Preloader />

<div class="page-wrapper">
    <Topbar />
    <Header {auth} />

    <section class="hero-one">
        <div class="container">
            <div class="hero-one__content">
                <h5 class="hero-one__sub-title sub-title bw-split-in-left">
                    {settings.hero_welcome_text || 'Welcome to Siwride'}
                </h5>
                <h2 class="hero-one__title title bw-split-in-down">
                    {settings.hero_title || 'Hassle-Free Bali Travels'}
                </h2>
                <p class="hero-one__text sub-title bw-split-in-left">
                    {settings.hero_subtitle ||
                        'Book your professional driver in advance.'}
                </p>
            </div>
        </div>
        <div class="hero-one__form">
            <div
                class="banner-form wow fadeInUp"
                data-wow-duration="1500ms"
                data-wow-delay="300ms"
            >
                <!-- Service Tabs -->
                <div class="hero-service-tabs">
                    <button
                        type="button"
                        class="hero-tab-btn {activeTab === 'point-to-point'
                            ? 'hero-tab-btn--active'
                            : ''}"
                        onclick={() => (activeTab = 'point-to-point')}
                    >
                        <i class="icon-pin-2"></i>
                        Airport Transfer
                    </button>
                    <button
                        type="button"
                        class="hero-tab-btn {activeTab === 'ride-sharing'
                            ? 'hero-tab-btn--active'
                            : ''}"
                        onclick={() => (activeTab = 'ride-sharing')}
                    >
                        <i class="fas fa-car"></i>
                        Ride Sharing
                    </button>
                </div>

                <!-- Point-to-Point Form -->
                {#if activeTab === 'point-to-point'}
                    <form
                        class="banner-form__wrapper airport-transfer-wrapper"
                        action="/booking/airport-transfer"
                        method="GET"
                        onsubmit={handleHeroSubmit}
                    >
                        <!-- Trip Type Toggle -->
                        <div class="hero-trip-type-toggle">
                            <button
                                type="button"
                                class="hero-trip-btn {heroTripType === 'one_way' ? 'hero-trip-btn--active' : ''}"
                                onclick={() => (heroTripType = 'one_way')}
                            >
                                <i class="fas fa-long-arrow-alt-right"></i>
                                One-Way
                            </button>
                            <button
                                type="button"
                                class="hero-trip-btn {heroTripType === 'round_trip' ? 'hero-trip-btn--active' : ''}"
                                onclick={() => (heroTripType = 'round_trip')}
                            >
                                <i class="fas fa-exchange-alt"></i>
                                Round-Trip
                            </button>
                        </div>
                        <input type="hidden" name="trip_type" value={heroTripType} />
                        <div class="banner-form hf-flex align-items-center">
                            <!-- Pickup -->
                            <div class="banner-form__control">
                                <i class="icon icon-pin-2"></i>
                                <label for="hero_pickup">Pick-up *</label>
                                <input
                                    type="hidden"
                                    name="pickup"
                                    value={heroPickup}
                                />
                                <LocationSearchInput
                                    id="hero_pickup"
                                    bind:value={heroPickup}
                                    placeholder="Hotel, airport, area..."
                                    required
                                />
                            </div>

                            <!-- Dropoff -->
                            <div class="banner-form__control">
                                <i class="icon icon-pin-2"></i>
                                <label for="hero_dropoff">Drop-off *</label>
                                <input
                                    type="hidden"
                                    name="dropoff"
                                    value={heroDropoff}
                                />
                                <LocationSearchInput
                                    id="hero_dropoff"
                                    bind:value={heroDropoff}
                                    placeholder="Beach, temple, area..."
                                    required
                                />
                            </div>

                            <!-- Departure Date -->
                            <div
                                class="banner-form__control banner-form__control--date"
                            >
                                <i class="icon icon-calendar-1"></i>
                                <label for="hero_date">Departure Date *</label>
                                <input
                                    type="hidden"
                                    name="date"
                                    value={heroDate}
                                />
                                <input
                                    type="hidden"
                                    name="time"
                                    value={heroTime}
                                />
                                <DatePicker
                                    id="hero_date"
                                    bind:value={heroDate}
                                    placeholder="Select pickup date"
                                    required
                                    hideIcon
                                    hideChevron
                                />
                            </div>

                            <!-- Return Date (round-trip only) -->
                            {#if heroTripType === 'round_trip'}
                                <div
                                    class="banner-form__control banner-form__control--date hero-return-date"
                                >
                                    <i class="icon icon-calendar-1"></i>
                                    <label for="hero_return_date">Return Date *</label>
                                    <input
                                        type="hidden"
                                        name="return_date"
                                        value={heroReturnDate}
                                    />
                                    <DatePicker
                                        id="hero_return_date"
                                        bind:value={heroReturnDate}
                                        placeholder="Select return date"
                                        required={heroTripType === 'round_trip'}
                                        hideIcon
                                        hideChevron
                                        minDate={heroDate}
                                    />
                                    {#if heroReturnDateError}
                                        <p style="color: #dc2626; font-size: 12px; margin-top: 4px; font-weight: 500;"><i class="fas fa-exclamation-circle"></i> {heroReturnDateError}</p>
                                    {/if}
                                </div>
                            {/if}

                            <!-- Passengers -->
                            <div class="banner-form__control">
                                <i class="icon icon-traveler-with-a-suitcase-1"
                                ></i>
                                <label for="passengers">Passengers</label>
                                <div
                                    class="passenger-counter"
                                    style="display: flex; align-items: center; gap: 10px; padding-top: 3px;"
                                >
                                    <button
                                        type="button"
                                        onclick={() => {
                                            if (passengerCount > 1)
                                                passengerCount--;
                                        }}
                                        style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;"
                                        >−</button
                                    >
                                    <span
                                        style="font-size: 16px; font-weight: 500; min-width: 30px; text-align: center;"
                                        >{passengerCount}</span
                                    >
                                    <input
                                        type="hidden"
                                        name="passengers"
                                        value={passengerCount}
                                        id="passengers"
                                    />
                                    <button
                                        type="button"
                                        onclick={() => passengerCount++}
                                        style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;"
                                        >+</button
                                    >
                                </div>
                            </div>

                            <!-- Submit -->
                            <div
                                class="banner-form__control banner-form__button"
                            >
                                <button class="travhub-btn" type="submit">
                                    <span>Choose Vehicle</span>
                                </button>
                            </div>
                        </div>
                    </form>
                {/if}

                <!-- Ride Sharing Form -->
                {#if activeTab === 'ride-sharing'}
                    <!-- <div class="rs-info-badge">
                        <i class="fas fa-users"></i>
                        <span>Predefined routes &amp; schedules by the operator. You are booking a <strong>seat in a shared ride</strong> — not a private trip.</span>
                    </div> -->
                    <form
                        class="banner-form__wrapper"
                        action="/ride-sharing"
                        method="GET"
                    >
                        <div class="banner-form hf-flex align-items-center">
                            <!-- Departure Date -->
                            <div
                                class="banner-form__control banner-form__control--date"
                            >
                                <i class="icon icon-calendar-1"></i>
                                <label for="rs_date">Departure Date *</label>
                                <input
                                    type="hidden"
                                    name="date"
                                    value={rsDate}
                                />
                                <DatePicker
                                    id="rs_date"
                                    bind:value={rsDate}
                                    placeholder="Select travel date"
                                    required
                                    hideIcon
                                    hideChevron
                                />
                            </div>

                            <!-- Pickup Location -->
                            <div class="banner-form__control">
                                <i class="icon icon-pin-2"></i>
                                <label for="rs_pickup">Pickup Location *</label>
                                <RideSharingLocationSearchInput
                                    id="rs_pickup"
                                    name="pickup_location_id"
                                    bind:value={rsPickupLocationId}
                                    locations={rideSharingLocations}
                                    placeholder="Search departure city..."
                                    required
                                />
                            </div>

                            <!-- Dropoff Location -->
                            <div class="banner-form__control">
                                <i class="icon icon-pin-2"></i>
                                <label for="rs_dropoff"
                                    >Dropoff Location *</label
                                >
                                <RideSharingLocationSearchInput
                                    id="rs_dropoff"
                                    name="dropoff_location_id"
                                    bind:value={rsDropoffLocationId}
                                    locations={rideSharingLocations}
                                    placeholder="Search destination city..."
                                    required
                                />
                            </div>

                            <!-- Passengers -->
                            <div class="banner-form__control">
                                <i class="icon icon-traveler-with-a-suitcase-1"
                                ></i>
                                <label for="rs_passengers">Passengers</label>
                                <div
                                    class="passenger-counter"
                                    style="display: flex; align-items: center; gap: 10px; padding-top: 3px;"
                                >
                                    <button
                                        type="button"
                                        onclick={() => {
                                            if (rsPassengers > 1)
                                                rsPassengers--;
                                        }}
                                        style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;"
                                        >−</button
                                    >
                                    <span
                                        style="font-size: 16px; font-weight: 500; min-width: 30px; text-align: center;"
                                        >{rsPassengers}</span
                                    >
                                    <input
                                        type="hidden"
                                        name="passengers"
                                        value={rsPassengers}
                                        id="rs_passengers"
                                    />
                                    <button
                                        type="button"
                                        onclick={() => rsPassengers++}
                                        style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;"
                                        >+</button
                                    >
                                </div>
                            </div>

                            <!-- Submit -->
                            <div
                                class="banner-form__control banner-form__button"
                            >
                                <button class="travhub-btn" type="submit">
                                    <span>Book Ride Sharing</span>
                                </button>
                            </div>
                        </div>
                    </form>
                {/if}
            </div>
        </div>

        <div
            class="hero-one__shape-one"
            style="background-image: url(/assets/images/shapes/group-1-1.png);"
        ></div>

        <!-- <div class="hero-one__image">
            <div
                class="hero-one__image-one travhub-splax"
                data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
            >
                <img src="/assets/images/resources/image-hero-1-1.png" alt="" />
            </div>
            <div
                class="hero-one__image-two travhub-splax"
                data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
            >
                <img src="/assets/images/resources/image-hero-1-2.png" alt="" />
            </div>
            <div
                class="hero-one__image-three travhub-splax"
                data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
            >
                <img src="/assets/images/resources/image-hero-1-3.png" alt="" />
            </div>
            <div
                class="hero-one__image-four travhub-splax"
                data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
            >
                <img src="/assets/images/resources/image-hero-1-4.png" alt="" />
            </div>
        </div> -->
        <div class="hero-one__shape-two">
            <img src="/assets/images/shapes/plane.png" alt="" />
        </div>
        <div class="hero-one__shape-four">
            <img src="/assets/images/shapes/cloude-1-2.png" alt="" />
        </div>
    </section>

    <section
        class="booking-tracker"
        style="padding: 60px 0; background: linear-gradient(135deg, #1e293b 0%, #334155 100%);"
    >
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="text-center mb-4">
                        <div
                            style="display: inline-flex; align-items: center; gap: 10px; background: rgba(255,255,255,0.1); padding: 8px 20px; border-radius: 50px; margin-bottom: 20px;"
                        >
                            <i
                                class="fas fa-search-location"
                                style="color: #fbbf24;"
                            ></i>
                            <span
                                style="color: #fff; font-size: 14px; font-weight: 500;"
                                >Track Your Booking</span
                            >
                        </div>
                        <h3
                            style="color: #fff; font-size: 28px; font-weight: 700; margin-bottom: 10px;"
                        >
                            Already Booked? Track Your Ride
                        </h3>
                        <p
                            style="color: rgba(255,255,255,0.7); font-size: 16px; margin-bottom: 30px;"
                        >
                            Enter your booking code to check status and view
                            details instantly
                        </p>
                    </div>
                    <BookingSearch variant="hero" />
                    <div
                        class="text-center mt-4"
                        style="color: rgba(255,255,255,0.5); font-size: 13px;"
                    >
                        <i class="fas fa-info-circle"></i>
                        Booking code starts with "SW" followed by 6 characters (e.g.,
                        SWABC123)
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="our-services"
        style="padding: 100px 0; background-color: #f0f0f0;"
    >
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    What We Offer<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Our Premium Services
                </h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 600px; margin: 15px auto 0;"
                >
                    From quick trips to full-day explorations, we provide a variety of reliable transportation solutions tailored for your needs.
                </p>
            </div>

            <div class="row gutter-y-30 justify-content-center">
                {#each services as service, index}
                    <div
                        class="col-lg-3 col-md-6 wow fadeInUp"
                        data-wow-duration="1500ms"
                        data-wow-delay={index * 100 + 'ms'}
                    >
                        <a
                            href={service.href || '#/'}
                            class="service-card d-block"
                            style="text-decoration: none; color: inherit; background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; display: flex; flex-direction: column;"
                            role="presentation"
                            onmouseenter={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(-10px)')}
                            onmouseleave={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(0)')}
                        >
                            <div
                                class="service-card__img"
                                style="height: 220px; overflow: hidden; position: relative;"
                            >
                                <img
                                    src={service.image_url}
                                    alt={service.title}
                                    style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;"
                                    role="presentation"
                                    onmouseenter={(e) =>
                                        (e.currentTarget.style.transform =
                                            'scale(1.1)')}
                                    onmouseleave={(e) =>
                                        (e.currentTarget.style.transform =
                                            'scale(1)')}
                                />
                            </div>
                            <div
                                class="service-card__content"
                                style="padding: 25px 20px 25px; position: relative; z-index: 1;"
                            >
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; gap: 10px;">
                                    <h4
                                        style="font-size: 18px; font-weight: 700; margin-bottom: 0; line-height: 1.3;"
                                    >
                                        <span
                                            style="color: inherit; text-decoration: none;"
                                            >{service.title}</span
                                        >
                                    </h4>
                                    <span
                                        style="display: inline-flex; align-items: center; color: var(--travhub-base, #e52029); font-weight: 700; font-size: 14px; transition: 0.3s; white-space: nowrap;"
                                        onmouseenter={(e) => (e.currentTarget.style.transform = 'translateX(3px)')}
                                        onmouseleave={(e) => (e.currentTarget.style.transform = 'translateX(0)')}
                                    >
                                        Book <i class="ti ti-arrow-right ms-1"></i>
                                    </span>
                                </div>
                                <p
                                    style="color: #666; font-size: 14px; line-height: 1.6; margin-bottom: 0;"
                                >
                                    {service.description}
                                </p>
                            </div>
                        </a>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <!-- ==================== Featured Tour Packages ==================== -->
    {#if tourPackages && tourPackages.length > 0}
        <section
            class="featured-tours"
            style="padding: 100px 0; background: linear-gradient(160deg, #0f172a 0%, #1e293b 60%, #0f172a 100%); position: relative; overflow: hidden;"
        >
            <!-- Background decorative blobs -->
            <div
                style="position: absolute; top: -80px; right: -80px; width: 350px; height: 350px; background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 70%); pointer-events: none;"
            ></div>
            <div
                style="position: absolute; bottom: -80px; left: -80px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(229,32,41,0.1) 0%, transparent 70%); pointer-events: none;"
            ></div>

            <div class="container" style="position: relative; z-index: 1;">
                <div class="sec-title text-center" style="margin-bottom: 60px;">
                    <div class="sec-title__tagline bw-split-in-right" style="color: #f59e0b;">
                        Explore Bali
                        <img
                            src="/assets/images/shapes/sec-title-shape.png"
                            alt="Siwride"
                            style="filter: brightness(0) saturate(100%) invert(72%) sepia(97%) saturate(442%) hue-rotate(352deg) brightness(103%) contrast(97%);"
                        />
                    </div>
                    <h3
                        class="sec-title__title bw-split-in-left"
                        style="color: #fff; font-size: 38px; padding-bottom:0px !important;"
                    >
                        Featured Tour Packages
                    </h3>
                    <!-- <p
                        class="sec-title__text bw-split-in-up-fast"
                        style="max-width: 600px; margin: 15px auto 0; color: rgba(255,255,255,0.65); font-size: 16px; line-height: 1.7;"
                    >
                        Curated Bali day tours with professional drivers. Fixed price per person — no hidden fees.
                    </p> -->
                </div>

                <div
                    class="featured-tours__carousel owl-carousel owl-theme"
                    data-wow-duration="1000ms"
                >
                    {#each tourPackages as tour}
                        <div class="featured-tour-card-wrapper">
                            <a
                                href="/tour-packages/{tour.slug}"
                                class="featured-tour-card"
                                style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 410px; background: #fff; border: 1px solid rgba(255,255,255,0.12); border-radius: 20px; overflow: hidden; transition: all 0.35s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 4px 20px rgba(0,0,0,0.25);"
                                onmouseenter={(e) => {
                                    e.currentTarget.style.transform = 'translateY(-8px)';
                                    e.currentTarget.style.boxShadow = '0 25px 60px rgba(245,158,11,0.35)';
                                    e.currentTarget.style.borderColor = 'rgba(245,158,11,0.5)';
                                }}
                                onmouseleave={(e) => {
                                    e.currentTarget.style.transform = 'translateY(0)';
                                    e.currentTarget.style.boxShadow = '0 4px 20px rgba(0,0,0,0.25)';
                                    e.currentTarget.style.borderColor = 'rgba(255,255,255,0.12)';
                                }}
                            >
                                <!-- Image -->
                                <div style="height: 230px; overflow: hidden; position: relative;">
                                    {#if tour.image_url && !tour.image_url.includes('tour-default')}
                                        <img
                                            src={tour.image_url}
                                            alt={tour.name}
                                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                            onmouseenter={(e) => (e.currentTarget.style.transform = 'scale(1.08)')}
                                            onmouseleave={(e) => (e.currentTarget.style.transform = 'scale(1)')}
                                        />
                                    {:else}
                                        <div
                                            style="width: 100%; height: 100%; background: linear-gradient(135deg, #b45309 0%, #f59e0b 50%, #d97706 100%); display: flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="flaticon-map" style="font-size: 70px; color: rgba(255,255,255,0.4);"></i>
                                        </div>
                                    {/if}

                                    <!-- Duration badge -->
                                    <div
                                        style="position: absolute; top: 14px; left: 14px; background: rgba(0,0,0,0.7); color: #fff; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; backdrop-filter: blur(6px); display: flex; align-items: center; gap: 5px;"
                                    >
                                        <i class="fas fa-clock" style="font-size: 10px; color: #f59e0b;"></i>
                                        {tour.duration_hours} Hours
                                    </div>

                                    <!-- Price badge -->
                                    <div
                                        style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding: 30px 18px 16px; display: flex; align-items: flex-end; justify-content: space-between;"
                                    >
                                        <div>
                                            <p style="color: rgba(255,255,255,0.7); font-size: 11px; margin-bottom: 2px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">From</p>
                                            <p style="color: #f59e0b; font-size: 20px; font-weight: 800; margin: 0; line-height: 1;">
                                                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(tour.price_per_pax)}
                                            </p>
                                            <p style="color: rgba(255,255,255,0.5); font-size: 11px; margin: 2px 0 0;">/ person</p>
                                        </div>
                                        <div
                                            style="background: #e52029; color: #fff; padding: 8px 14px; border-radius: 50px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 5px;"
                                        >
                                            View <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div style="padding: 20px 22px 24px; flex: 1; display: flex; flex-direction: column;">
                                    <h4
                                        style="color: #1e293b; font-size: 17px; font-weight: 800; margin-bottom: 8px; line-height: 1.3;"
                                    >{tour.name}</h4>
                                    <p
                                        style="color: #64748b; font-size: 13px; line-height: 1.6; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;"
                                    >
                                        {tour.description}
                                    </p>

                                    <!-- Destinations pills -->
                                    {#if tour.destinations && tour.destinations.length > 0}
                                        <div style="display: flex; flex-wrap: nowrap; gap: 6px; margin-top: 4px; overflow: hidden; align-items: center; width: 100%;">
                                            {#each tour.destinations.slice(0, 1) as dest}
                                                <span
                                                    style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; display: inline-block; min-width: 0;"
                                                >
                                                    📍 {dest}
                                                </span>
                                            {/each}
                                            {#if tour.destinations.length > 1}
                                                <span
                                                    style="background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; border: 1px solid #e2e8f0; white-space: nowrap; flex-shrink: 0;"
                                                >
                                                    +{tour.destinations.length - 1} other
                                                </span>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            </a>
                        </div>
                    {/each}
                </div>

                <!-- CTA -->
                <div class="text-center" style="margin-top: 50px;">
                    <a href="/booking/tour" class="travhub-btn">
                        <span>View All Tour Packages</span>
                    </a>
                </div>
            </div>
        </section>
    {/if}

    <section
        class="how-it-works"
        style="padding: 100px 0; background-color: #f7f9fa;"
    >
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Booking Process<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    3 Simple Steps to Start Your Journey
                </h3>
            </div>

            <div class="row gutter-y-30">
                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-duration="1500ms"
                    data-wow-delay="00ms"
                >
                    <div
                        role="presentation"
                        class="how-it-works__card text-center"
                        style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;"
                        onmouseenter={(e) =>
                            (e.currentTarget.style.transform =
                                'translateY(-10px)')}
                        onmouseleave={(e) =>
                            (e.currentTarget.style.transform = 'translateY(0)')}
                    >
                        <div
                            class="icon"
                            style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;"
                        >
                            <i class="icon-pin-2"></i>
                            <div
                                style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);"
                            >
                                1
                            </div>
                        </div>
                        <h4
                            style="font-weight: 700; margin-bottom: 20px; font-size: 22px;"
                        >
                            Set Your Route & Schedule
                        </h4>
                        <p
                            style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;"
                        >
                            Enter your pick-up point, destination, and travel
                            date. We will instantly match you with the best
                            available drivers nearby.
                        </p>
                    </div>
                </div>

                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-duration="1500ms"
                    data-wow-delay="200ms"
                >
                    <div
                        role="presentation"
                        class="how-it-works__card text-center"
                        style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;"
                        onmouseenter={(e) =>
                            (e.currentTarget.style.transform =
                                'translateY(-10px)')}
                        onmouseleave={(e) =>
                            (e.currentTarget.style.transform = 'translateY(0)')}
                    >
                        <div
                            class="icon"
                            style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;"
                        >
                            <i class="icon-traveler-with-a-suitcase-1"></i>
                            <div
                                style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);"
                            >
                                2
                            </div>
                        </div>
                        <h4
                            style="font-weight: 700; margin-bottom: 20px; font-size: 22px;"
                        >
                            Choose Your Perfect Vehicle
                        </h4>
                        <p
                            style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;"
                        >
                            Pick the transport that suits your comfort and group
                            size. Enjoy peace of mind with our transparent
                            upfront details.
                        </p>
                    </div>
                </div>

                <div
                    class="col-lg-4 col-md-6 wow fadeInUp"
                    data-wow-duration="1500ms"
                    data-wow-delay="400ms"
                >
                    <div
                        role="presentation"
                        class="how-it-works__card text-center"
                        style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;"
                        onmouseenter={(e) =>
                            (e.currentTarget.style.transform =
                                'translateY(-10px)')}
                        onmouseleave={(e) =>
                            (e.currentTarget.style.transform = 'translateY(0)')}
                    >
                        <div
                            class="icon"
                            style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;"
                        >
                            <i class="flaticon-check"></i>
                            <div
                                style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);"
                            >
                                3
                            </div>
                        </div>
                        <h4
                            style="font-weight: 700; margin-bottom: 20px; font-size: 22px;"
                        >
                            Secure Payment & Ride!
                        </h4>
                        <p
                            style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;"
                        >
                            Complete your booking safely through our trusted
                            payment gateways. No hidden fees, just sit back and
                            enjoy your comfortable trip.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="why-choose-one">
        <div
            class="why-choose-one__bg"
            style="background-image: url(/assets/images/shapes/why-choose-one-bg.png);"
        ></div>
        <div class="container">
            <div class="row d-flex align-items-center">
                <div class="col-xl-6 wow fadeInUp" data-wow-delay="300ms">
                    <div class="why-choose-one__image">
                        <img
                            style="width:438px; height:544px;"
                            src="/assets/images/resources/why-choose.jpg"
                            alt="travhub"
                        />
                        <!-- <div
                            class="why-choose-one__image__shape"
                            style="background-image: url(/assets/images/shapes/why-choose.jpg);"
                        ></div> -->
                        <!-- <div class="why-choose-one__image__two">
                            <img
                                src="/assets/images/resources/why-choose-one-2.jpg"
                                alt="travhub"
                            />
                        </div> -->
                        <div class="why-choose-one__check">
                            <div class="why-choose-one__check__icon">
                                <i class="flaticon-check"></i>
                            </div>
                            <div class="why-choose-one__check__title">
                                Trusted by<span>Thousands in Bali</span>
                            </div>
                        </div>
                        <div class="why-choose-one__rm">
                            <a
                                href="/about"
                                aria-label="Read more about our services"
                                ><i class="flaticon-top-right"></i></a
                            >
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 wow fadeInRight" data-wow-delay="200ms">
                    <div class="why-choose-one__content">
                        <div class="sec-title">
                            <div class="sec-title__tagline bw-split-in-right">
                                {settings.why_choose_us_title ||
                                    'Why Siwride?'}<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="Siwride"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                {settings.why_choose_us_subtitle ||
                                    'Your Premium Ride Partner'}
                            </h3>
                            <p class="sec-title__text bw-split-in-up-fast">
                                {settings.why_choose_us_text ||
                                    'Experience hassle-free, comfortable, and safe transportation across Bali with our highly-rated drivers and diverse fleet. We prioritize your comfort above all else.'}
                            </p>
                        </div>
                        {#each settings.why_choose_us_features || [] as feature}
                            <div class="why-choose-one__box">
                                <div class="why-choose-one__box__icon">
                                    <i class={feature.icon || 'flaticon-check'}
                                    ></i>
                                </div>
                                <h5 class="why-choose-one__box__title">
                                    {feature.title}
                                </h5>
                                <p class="why-choose-one__box__text">
                                    {feature.text}
                                </p>
                            </div>
                        {/each}
                        <div class="why-choose-one__btn">
                            <a href="/booking" class="travhub-btn">
                                <span>Book a Ride</span>
                            </a>
                            <div class="why-choose-one__author">
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-1.png"
                                        alt="Travhub"
                                    />
                                </div>
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-2.png"
                                        alt="Travhub"
                                    />
                                </div>
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-3.png"
                                        alt="Travhub"
                                    />
                                </div>
                                <div
                                    class="why-choose-one__author__item why-choose-one__author__item--plus"
                                >
                                    <i class="flaticon-add"></i>
                                </div>
                            </div>
                            <h5 class="why-choose-one__count ms-1">
                                {settings.why_choose_us_passenger_count ||
                                    '10k+'}<span>Happy Passengers</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="vehicle-category"
        style="padding: 100px 0; background-color: #ffffff;"
    >
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div class="sec-title mb-0" style="flex: 1;">
                    <div class="sec-title__tagline bw-split-in-right">
                        Our Fleet<img
                            src="/assets/images/shapes/sec-title-shape.png"
                            alt="Siwride"
                        />
                    </div>
                    <h3 class="sec-title__title bw-split-in-left">
                        Vehicle Category Highlight
                    </h3>
                    <p
                        class="sec-title__text bw-split-in-up-fast"
                        style="max-width: 650px; margin-top: 15px;"
                    >
                        Discover the wide variety of vehicles available for you,
                        the fleet fits to every situation you may need, to the
                        passengers, trip and budget.
                    </p>
                </div>
                <div
                    class="see-all-btn d-none d-md-block"
                    style="padding-bottom: 20px;"
                >
                    <a href="/vehicles" class="travhub-btn travhub-btn--base"
                        ><span>See All</span></a
                    >
                </div>
            </div>

            <div
                class="tours-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                data-owl-options={'{"loop": true,"autoplay": true,"autoplayTimeout": 3000,"autoplayHoverPause": true,"items": 1,"nav": false,"navText": ["<span class=\\"icon-right-arrow\\"></span>","<span class=\\"icon-right-arrow\\"></span>"],"dots": true,"margin": 8,"responsive": {"0": {"items": 1,"margin": 5},"575": {"items": 2,"margin": 10},"992": {"items": 3,"margin": 12},"1200": {"items": 4,"margin": 15}}}'}
            >
                {#each vehicleCategories as vehicle}
                    <div class="item">
                        <div
                            role="presentation"
                            class="vehicle-card"
                            style="border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 8px 15px rgba(0,0,0,0.06); transition: transform 0.3s ease; height: 100%; border: 1px solid #f1f1f1;"
                            onmouseenter={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(-8px)')}
                            onmouseleave={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(0)')}
                        >
                            <div
                                class="vehicle-card__img"
                                style="height: 220px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; overflow: hidden; padding: 20px;"
                            >
                                <img
                                    src={vehicle.image_url}
                                    alt={vehicle.title}
                                    style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; transition: transform 0.5s ease;"
                                    role="presentation"
                                    onmouseenter={(e) =>
                                        (e.currentTarget.style.transform =
                                            'scale(1.08)')}
                                    onmouseleave={(e) =>
                                        (e.currentTarget.style.transform =
                                            'scale(1)')}
                                />
                            </div>
                            <div
                                class="vehicle-card__content"
                                style="padding: 25px;"
                            >
                                <h4
                                    style="font-size: 20px; font-weight: 700; margin-bottom: 15px;"
                                >
                                    <a
                                        href={`/vehicles/${vehicle.slug}`}
                                        style="color: inherit; text-decoration: none;"
                                        >{vehicle.title}</a
                                    >
                                </h4>
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between;"
                                >
                                    <div
                                        style="display: flex; align-items: center; color: #666; font-size: 15px; font-weight: 500;"
                                    >
                                        <i
                                            class="icon-traveler-with-a-suitcase-1"
                                            style="margin-right: 8px; font-size: 18px; color: var(--travhub-base, #e52029);"
                                        ></i>
                                        {vehicle.capacity}
                                    </div>
                                    <a
                                        href={`/vehicles/${vehicle.slug}`}
                                        style="width: 35px; height: 35px; border-radius: 50%; background: #f7f9fa; display: flex; align-items: center; justify-content: center; color: var(--travhub-base, #e52029); transition: 0.3s; text-decoration: none;"
                                        onmouseenter={(e) => {
                                            e.currentTarget.style.background =
                                                'var(--travhub-base, #e52029)';
                                            e.currentTarget.style.color =
                                                '#fff';
                                        }}
                                        onmouseleave={(e) => {
                                            e.currentTarget.style.background =
                                                '#f7f9fa';
                                            e.currentTarget.style.color =
                                                'var(--travhub-base, #e52029)';
                                        }}
                                    >
                                        <i class="flaticon-top-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    

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
                                <p
                                    style="color: #888; font-size: 15px; margin: 0;"
                                >
                                    (This area is reserved for a static map
                                    image of Bali)
                                </p>
                            </div>
                        {/if}
                        <img
                            src={settings.coverage_area_image ||
                                'https://placehold.co/1200x500/e9ecef/e9ecef'}
                            alt="Bali Map Placeholder"
                            style="width: 100%; height: 100%; object-fit: cover; position: relative; z-index: 0;"
                        />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section
        class="popular-destinations"
        style="padding: 100px 0; background-color: #f4f6f8;"
    >
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Explore Bali With Us<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    {settings.destinations_title || 'Popular Destinations'}
                </h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 600px; margin: 15px auto 0;"
                >
                    {settings.destinations_subtitle ||
                        'Discover the most frequently visited and breathtaking locations in Bali. Book a ride with us and travel there in comfort.'}
                </p>
            </div>

            <div class="row gutter-y-30">
                {#each popularDestinations as dest, index}
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-duration="1500ms"
                        data-wow-delay={index * 100 + 'ms'}
                    >
                        <div
                            class="destination-card"
                            style="border-radius: 12px; overflow: hidden; position: relative; height: 350px; background: #000; box-shadow: 0 10px 30px rgba(0,0,0,0.1); cursor: pointer;"
                            role="presentation"
                            onmouseenter={(e) => {
                                const el = e.currentTarget as HTMLElement;
                                const img = el.querySelector(
                                    'img',
                                ) as HTMLElement | null;
                                if (img) img.style.transform = 'scale(1.1)';
                                const overlay = el.querySelector(
                                    '.destination-overlay',
                                ) as HTMLElement | null;
                                if (overlay) overlay.style.opacity = '1';
                                const btn = el.querySelector(
                                    '.destination-btn',
                                ) as HTMLElement | null;
                                if (btn) {
                                    btn.style.transform = 'translateY(0)';
                                    btn.style.opacity = '1';
                                }
                            }}
                            onmouseleave={(e) => {
                                const el = e.currentTarget as HTMLElement;
                                const img = el.querySelector(
                                    'img',
                                ) as HTMLElement | null;
                                if (img) img.style.transform = 'scale(1)';
                                const overlay = el.querySelector(
                                    '.destination-overlay',
                                ) as HTMLElement | null;
                                if (overlay) overlay.style.opacity = '0.7';
                                const btn = el.querySelector(
                                    '.destination-btn',
                                ) as HTMLElement | null;
                                if (btn) {
                                    btn.style.transform = 'translateY(10px)';
                                    btn.style.opacity = '0';
                                }
                            }}
                        >
                            <img
                                src={dest.img}
                                alt={dest.name}
                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; opacity: 0.9;"
                            />
                            <div
                                class="destination-overlay"
                                style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent); transition: 0.3s; opacity: 0.8; z-index: 1;"
                            ></div>
                            <div
                                style="position: absolute; bottom: 30px; left: 30px; right: 30px; z-index: 2; display: flex; flex-direction: column;"
                            >
                                <h4
                                    style="color: #fff; font-size: 24px; font-weight: 700; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);"
                                >
                                    {dest.name}
                                </h4>
                                <div
                                    style="color: #eee; font-size: 15px; display: flex; align-items: center; justify-content: space-between; font-weight: 500;"
                                >
                                    <div>
                                        <i
                                            class="icon-pin-2"
                                            style="color: var(--travhub-base, #e52029); margin-right: 8px; font-size: 16px;"
                                        ></i>
                                        {dest.location}
                                    </div>
                                    <a
                                        href="/booking"
                                        class="destination-btn"
                                        style="background: var(--travhub-base, #e52029); color: #fff; padding: 6px 15px; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.3s; transform: translateY(10px); opacity: 0;"
                                        >Book Now</a
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <section
        class="testimonials-one"
        style="padding: 120px 0; background-color: #f8fafd; position: relative; overflow: visible; z-index: 5;"
    >
        <div
            class="testimonials-one__bg"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(circle at 20% 30%, rgba(229, 32, 41, 0.03) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(229, 32, 41, 0.03) 0%, transparent 40%);"
        ></div>

        <div class="container" style="position: relative; z-index: 2;">
            <div class="sec-title text-center" style="margin-bottom: 60px;">
                <div
                    class="sec-title__tagline bw-split-in-right"
                    style="justify-content: center;"
                >
                    Trusted by Travelers<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3
                    class="sec-title__title bw-split-in-left"
                    style="font-size: 42px;"
                >
                    Real Stories from Our Clients
                </h3>
            </div>

            <div
                class="testimonials-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                data-owl-options={'{"items": 1, "margin": 30, "loop": true, "smartSpeed": 700, "nav": false, "dots": true, "autoplay": true, "responsive": {"768": {"items": 2}, "1200": {"items": 3}}}'}
            >
                {#each customerTestimonials as testimonial}
                    <div class="item">
                        <div
                            class="testimonials-card"
                            style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 8px 10px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: column; transition: 0.3s; margin: 15px 0;"
                            role="presentation"
                            onmouseenter={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(-10px)')}
                            onmouseleave={(e) =>
                                (e.currentTarget.style.transform =
                                    'translateY(0)')}
                        >
                            <div
                                style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;"
                            >
                                <div
                                    style="display: flex; gap: 4px; color: #ffab00; font-size: 14px;"
                                >
                                    {#each Array(testimonial.rating) as _}
                                        <i class="fa fa-star"></i>
                                    {/each}
                                </div>
                                <i
                                    class="flaticon-left-quote"
                                    style="color: rgba(229, 32, 41, 0.1); font-size: 40px; line-height: 1;"
                                ></i>
                            </div>

                            <div
                                class="testimonials-card__content"
                                style="font-size: 17px; color: #555; line-height: 1.7; flex-grow: 1; margin-bottom: 30px;"
                            >
                                "{testimonial.comment}"
                            </div>

                            <div
                                style="display: flex; align-items: center; gap: 15px; padding-top: 25px; border-top: 1px solid #f1f1f1;"
                            >
                                <div
                                    style="width: 55px; height: 55px; border-radius: 50%; overflow: hidden; border: 2px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1);"
                                >
                                    <img
                                        src={testimonial.img}
                                        alt={testimonial.name}
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                    />
                                </div>
                                <div>
                                    <h3
                                        style="font-size: 17px; font-weight: 700; margin: 0; color: #222;"
                                    >
                                        {testimonial.name}
                                    </h3>
                                    <span
                                        style="color: #999; font-size: 13px; font-weight: 500;"
                                        >{testimonial.country}</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>
                {/each}
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    :global(.featured-tours__carousel .owl-nav) {
        position: absolute;
        top: 50%;
        width: calc(100% + 140px) !important;
        left: -70px !important;
        transform: translateY(-50%);
        display: flex !important;
        justify-content: space-between;
        pointer-events: none;
        margin: 0 !important;
    }
    :global(.featured-tours__carousel .owl-nav button) {
        pointer-events: auto;
        width: 50px;
        height: 50px;
        background: #fff !important;
        color: #1e293b !important;
        border-radius: 50% !important;
        box-shadow: 0 4px 15px rgba(0,0,0,0.15) !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        font-size: 16px !important;
        transition: all 0.3s ease !important;
    }
    :global(.featured-tours__carousel .owl-nav button:hover) {
        background: #f59e0b !important;
        color: #fff !important;
        transform: scale(1.1);
    }
    :global(.featured-tours__carousel) {
        padding: 0 10px; /* Give room for shadows */
    }

    /* Fix shadow clipping while keeping carousel width constrained */
    :global(.testimonials-one .owl-carousel, .vehicle-category .owl-carousel) {
        position: relative;
    }
    /* Vehicle carousel styling */
    :global(.vehicle-category .owl-stage-outer) {
        overflow: hidden !important;
        padding: 20px 10px 50px 10px !important;
        margin: -20px -10px -50px -10px !important;
    }
    :global(.vehicle-category .item) {
        display: flex !important;
        flex: 1;
        width: 100%;
        padding: 0 5px 10px 5px !important;
    }
    :global(.vehicle-category .vehicle-card) {
        width: 100% !important;
    }

    /* Testimonials styling */
    :global(.testimonials-one .owl-stage-outer) {
        overflow: hidden !important;
        padding: 20px 10px 50px 10px !important;
        margin: -20px -10px -50px -10px !important;
    }
    :global(.testimonials-one .item) {
        display: flex !important;
        flex: 1;
        width: 100%;
        padding: 0 5px 10px 5px !important;
    }

    :global(.testimonials-one .owl-stage, .vehicle-category .owl-stage) {
        display: flex !important;
    }

    /* Hide topbar when header is scrolled */
    :global(.main-header.scrolled) ~ * .topbar-one,
    :global(.main-header.scrolled) + .topbar-one {
        display: none !important;
    }

    /* Alternative selector for topbar hiding */
    :global(.page-wrapper .main-header.scrolled) ~ .topbar-one,
    :global(.page-wrapper .main-header.scrolled) + .topbar-one {
        display: none !important;
    }

    /* Hide hero images on screens under 1600px */
    :global(.hero-one__image) {
        visibility: visible;
    }

    /* Ensure search dropdowns are not clipped */
    :global(.banner-form, .banner-form__wrapper, .banner-form__control) {
        overflow: visible !important;
    }

    /* Fix stacking context so active input's dropdown goes over subsequent columns */
    :global(.banner-form__control:focus-within) {
        z-index: 999 !important;
    }

    @media (max-width: 1599px) {
        :global(.hero-one__image-two),
        :global(.hero-one__image-four) {
            visibility: hidden !important;
            display: none !important;
        }
    }
    /* ── Hero form: time picker row ── */
    :global(.hero-time-row) {
        position: relative;
        display: flex;
        align-items: center;
        margin-top: 8px;
    }
    :global(.hero-time-icon) {
        position: absolute;
        left: 0;
        color: var(--travhub-base, #e52029);
        font-size: 15px;
        pointer-events: none;
        z-index: 2;
        /* Visually align with the date icon above */
        top: 50%;
        transform: translateY(-50%);
        display: none; /* The TimePicker has its own badge icon */
    }
    :global(.hero-time-notice) {
        margin: 6px 0 0;
        font-size: 12px;
        color: #92400e;
        background: #fffbeb;
        border: 1px solid #fde68a;
        border-radius: 6px;
        padding: 5px 10px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }
    :global(.hero-time-notice i) {
        color: #d97706;
        font-size: 11px;
    }
    :global(.hero-time-notice strong) {
        font-weight: 800;
        color: #78350f;
    }

    /* ────────────────────────────────────────────────────────────
       Hero Form (hf-flex) — responsive layout
       Berjajar ke samping, jika width tidak cukup maka ke bawah.
    ──────────────────────────────────────────────────────────── */
    :global(.hero-one__form .banner-form.hf-flex) {
        display: grid;
        /* Default: grid columns adjust automatically based on available width */
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        align-items: center;
        gap: 15px 25px;
    }

    :global(.hero-one__form .banner-form.hf-flex .banner-form__control) {
        position: relative;
        padding-left: 0 !important;
        margin-bottom: 0 !important;
        width: 100% !important; /* Ensure it fills its grid cell */
    }

    /* Dashed border for gaps between inputs (except the submit button) */
    @media (min-width: 576px) {
        :global(
            .hero-one__form
                .banner-form.hf-flex
                .banner-form__control:not(.banner-form__button)::after
        ) {
            content: '';
            position: absolute;
            right: -12.5px; /* Half of the 25px gap */
            top: 15%;
            height: 70%;
            border-right: 1px dashed rgba(0, 0, 0, 0.15); /* Thin line */
        }
        /* On tablet size, when grid wraps, hide the right border on the 3rd element which is at edge */
        /* Since auto-fit dynamically places them, we hide borders if they overflow visually */
    }

    /* Stretch button to ignore parent padding vertically and touch edges */
    @media (min-width: 1070px) {
        :global(.hero-one__form .banner-form.hf-flex .banner-form__button) {
            align-self: stretch;
            margin-top: -20px !important;
            margin-bottom: -20px !important;
        }
        :global(
            .hero-one__form .banner-form.hf-flex .banner-form__button button
        ) {
            height: 100%;
            width: 100%;
            align-items: center;
            justify-content: center;
            margin: 0;
        }
    }

    @media (max-width: 575px) {
        :global(.hero-one__form .banner-form.hf-flex) {
            grid-template-columns: 1fr; /* Stack vertically on mobile */
            gap: 15px;
        }
    }

    /* ── Ride Sharing select dropdowns (Original Theme Styles) ── */
    :global(.rs-select) {
        width: 100%;
        background: transparent;
        border: none;
        outline: none;
        color: inherit;
        font-size: 15px;
        font-family: inherit;
        padding: 3px 0;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }
    :global(.rs-select option) {
        color: #333;
        background: #fff;
    }
    :global(.rs-select option:disabled) {
        color: #aaa;
    }

    /* ── Hero service tabs ── */

    /* Force the outer banner-form container to be a column so tabs
       always sit ABOVE the white form card, never beside it. */
    :global(.hero-one__form .banner-form) {
        display: flex;
        flex-direction: column;
        align-items: stretch;
    }

    :global(.hero-service-tabs) {
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 6px;
        margin-bottom: 0;
        padding: 0;
        /* Ensure the tab row always stacks ABOVE the form card,
           never beside it — achieved by forcing full width */
        width: 100%;
        flex-shrink: 0;
    }
    :global(.hero-tab-btn) {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 24px;
        font-size: 14px;
        font-weight: 700;
        /* Dark semi-opaque pill — visible on ANY hero background */
        background: rgba(223, 221, 221, 0.55);
        color: rgba(95, 95, 95, 0.85);
        border: 1.5px solid rgba(255, 255, 255, 0.22);
        border-bottom: none;
        border-radius: 10px 10px 0 0;
        cursor: pointer;
        transition:
            background 0.22s,
            color 0.22s,
            border-color 0.22s,
            box-shadow 0.22s;
        letter-spacing: 0.4px;
        white-space: nowrap;
        min-width: 150px;
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }
    :global(.hero-tab-btn:hover) {
        background: rgba(15, 15, 20, 0.75);
        color: #ffffff;
        border-color: rgba(255, 255, 255, 0.4);
    }
    :global(.hero-tab-btn--active) {
        background: #ffffff !important;
        color: var(--travhub-base, #e52029) !important;
        border-color: #ffffff !important;
        border-bottom: none !important;
        box-shadow: 0 -3px 14px rgba(0, 0, 0, 0.18);
    }
    :global(.hero-tab-btn--active:hover) {
        background: #fff !important;
        color: var(--travhub-base, #e52029) !important;
    }
    :global(.hero-tab-btn i) {
        font-size: 16px;
    }

    /* Mobile: tabs go full-width and pill-shaped for better touch targets */
    @media (max-width: 575px) {
        :global(.hero-service-tabs) {
            gap: 8px;
        }
        :global(.hero-tab-btn) {
            flex: 1;
            min-width: 0;
            border-radius: 10px 10px 0 0;
            padding: 10px 14px;
            font-size: 13px;
        }
    }

    /* ── Ride Sharing info badge ── */
    :global(.rs-info-badge) {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 16px;
        background: rgba(251, 191, 36, 0.12);
        border: 1px solid rgba(251, 191, 36, 0.35);
        border-radius: 8px;
        margin: 12px 0 0;
        font-size: 13px;
        color: #fff;
        line-height: 1.5;
    }
    :global(.rs-info-badge i) {
        margin-top: 2px;
        color: #fbbf24;
        font-size: 15px;
        flex-shrink: 0;
    }
    :global(.rs-info-badge strong) {
        color: #fbbf24;
    }

    /* ── Ride Sharing select dropdowns ── */
    :global(.rs-select) {
        width: 100%;
        background: transparent;
        border: none;
        outline: none;
        color: inherit;
        font-size: 15px;
        font-family: inherit;
        padding: 3px 0;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
    }
    :global(.rs-select option) {
        color: #333;
        background: #fff;
    }
    :global(.rs-select option:disabled) {
        color: #aaa;
    }

    /* "Book Ride Sharing" button accent */
    :global(.travhub-btn--rs) {
        background: linear-gradient(
            135deg,
            #1e40af 0%,
            #2563eb 100%
        ) !important;
    }
    :global(.travhub-btn--rs:hover) {
        background: linear-gradient(
            135deg,
            #1e3a8a 0%,
            #1e40af 100%
        ) !important;
    }

    /* ── Form Labels Spacing & Alignment ── */
    :global(.banner-form__control label) {
        display: inline-block;
        vertical-align: middle;
        margin-bottom: 8px !important;
        line-height: 1;
    }

    :global(.banner-form__control i.icon) {
        margin-top: -8px !important;
    }

    /* ── Trip Type Toggle (One-Way / Round-Trip) ── */
    :global(.airport-transfer-wrapper) {
        position: relative;
        padding-top: 65px !important; /* space for absolute toggle */
    }
    :global(.hero-trip-type-toggle) {
        position: absolute;
        top: 15px;
        left: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
        z-index: 10;
    }
    :global(.hero-trip-btn) {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 16px;
        border-radius: 50px;
        border: 1.5px solid #cbd5e1;
        background: transparent;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        letter-spacing: 0.3px;
    }
    :global(.hero-trip-btn:hover) {
        border-color: var(--travhub-base, #e52029);
        color: var(--travhub-base, #e52029);
        background: rgba(229, 32, 41, 0.05);
    }
    :global(.hero-trip-btn--active) {
        background: var(--travhub-base, #e52029) !important;
        border-color: var(--travhub-base, #e52029) !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(229, 32, 41, 0.25);
    }
    
    @media (min-width: 1070px) {
        :global(.hero-one__form .airport-transfer-wrapper .banner-form.hf-flex .banner-form__button) {
            align-self: stretch;
            margin-top: -65px !important; /* override the default -20px to reach the higher top padding */
            margin-bottom: -20px !important;
        }
    }
    @media (max-width: 575px) {
        :global(.airport-transfer-wrapper) {
            padding-top: 80px !important;
        }
    }
    :global(.hero-return-date) {
        animation: fadeSlideIn 0.25s ease;
    }
    @keyframes fadeSlideIn {
        from {
            opacity: 0;
            transform: translateY(-6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
