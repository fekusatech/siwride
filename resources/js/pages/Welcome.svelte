<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';

    let passengerCount = $state(1);

    const vehicleCategories = [
        { name: 'Sedan', passengers: 'Up to 3 passengers', img: '/assets/images/tours/tours-1-1.jpg' },
        { name: 'Electrical Vehicle', passengers: 'Up to 4 passengers', img: '/assets/images/tours/tours-1-2.jpg' },
        { name: 'Business', passengers: 'Up to 3 passengers', img: '/assets/images/tours/tours-1-3.jpg' },
        { name: 'First Class', passengers: 'Up to 3 passengers', img: '/assets/images/tours/tours-1-1.jpg' },
        { name: 'Super VIP', passengers: 'Up to 2 passengers', img: '/assets/images/tours/tours-1-2.jpg' },
        { name: 'Limousine', passengers: 'Up to 16 passengers', img: '/assets/images/tours/tours-1-3.jpg' },
        { name: 'SUV', passengers: 'Up to 6 passengers', img: '/assets/images/tours/tours-1-1.jpg' },
        { name: 'Minivan', passengers: 'Up to 7 passengers', img: '/assets/images/tours/tours-1-2.jpg' },
        { name: 'Minibus', passengers: 'Up to 15 passengers', img: '/assets/images/tours/tours-1-3.jpg' },
        { name: 'Motorbike', passengers: 'Up to 1 passenger', img: '/assets/images/tours/tours-1-1.jpg' }
    ];

    const ourServices = [
        { title: 'Airport Transfer', description: 'Punctual pick-up and drop-off to and from Ngurah Rai Airport. Start or end your journey stress-free.', icon: 'flaticon-signpost', img: '/assets/images/resources/why-choose-one-1.jpg' },
        { title: 'Point-to-Point Ride', description: 'Quick and reliable everyday rides to take you from your hotel to the beach, restaurant, or anywhere in between.', icon: 'icon-pin-2', img: '/assets/images/resources/why-choose-one-2.jpg' },
        { title: 'Hourly & Daily Rental', description: 'Explore Bali at your own pace with our flexible hourly and full-day vehicle rental complete with a professional driver.', icon: 'flaticon-camera-1', img: '/assets/images/gallery/gallery-1-3.jpg' },
        { title: 'Intercity Travel', description: 'Comfortable long-distance travel across Bali for those planning to move between distant regions like Ubud to Uluwatu.', icon: 'flaticon-pin-1', img: '/assets/images/gallery/gallery-1-4.jpg' },
        { title: 'Corporate Travel', description: 'Premium transportation solutions for business delegates, meetings, and corporate events with top-tier services.', icon: 'icon-traveler-with-a-suitcase-1', img: '/assets/images/gallery/gallery-1-5.jpg' },
        { title: 'Tour Packages', description: 'Discover the hidden gems of the island with our curated half-day or full-day tour itineraries.', icon: 'flaticon-camera-2', img: '/assets/images/gallery/gallery-1-6.jpg' }
    ];

    const popularDestinations = [
        { name: 'Tanah Lot Temple', location: 'Tabanan, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Tanah+Lot' },
        { name: 'Uluwatu Temple', location: 'South Kuta, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Uluwatu' },
        { name: 'Tegallalang Rice Terrace', location: 'Ubud, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Tegallalang' },
        { name: 'Lempuyang Temple', location: 'Karangasem, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Lempuyang' },
        { name: 'Seminyak Beach', location: 'Kuta, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Seminyak' },
        { name: 'Mount Batur', location: 'Kintamani, Bali', img: 'https://placehold.co/800x600/e9ecef/6c757d?text=Mount+Batur' }
    ];

    const customerTestimonials = [
        { name: 'Sarah Miller', country: 'Australia', comment: 'Booking my airport transfer via Siwride was the best decision. The driver was already there with a sign, and the car was incredibly clean and fresh!', rating: 5, img: '/assets/images/resources/test-1-1.jpg' },
        { name: 'James Chen', country: 'Hong Kong', comment: 'We used the SUV for a full day trip to Ubud. Our driver knew exactly where to go and was very patient with our children. Highly recommended!', rating: 5, img: '/assets/images/resources/test-1-2.jpg' },
        { name: 'Linda Wagner', country: 'Germany', comment: 'Safe driving and easy booking. I felt very secure as a solo traveler. The transparent pricing is a huge plus compared to other local options.', rating: 5, img: '/assets/images/resources/test-1-3.jpg' },
        { name: 'David Smith', country: 'USA', comment: 'Excellent service! The app was easy to use and the pickup was on time. The driver was professional and the car was very comfortable.', rating: 5, img: '/assets/images/resources/test-1-4.jpg' },
        { name: 'Emilia Clarke', country: 'UK', comment: 'Siwride is my go-to for Bali travel. Reliable, transparent, and superior fleet quality compared to others. Worth every penny!', rating: 5, img: '/assets/images/resources/test-1-5.jpg' }
    ];

    onMount(() => {
        document.body.classList.add('custom-cursor');

        if ((window as any).$) {
            const jQuery = (window as any).$;
            
            // Wait for DOM
            setTimeout(() => {
                // Initialize DatePicker
                let dateEl = jQuery('#pickup_date');
                if (dateEl.length && dateEl.daterangepicker) {
                    dateEl.daterangepicker({
                        autoUpdateInput: false,
                        singleDatePicker: true,
                        timePicker: false,
                        locale: {
                            format: 'D MMM YYYY'
                        }
                    });
                    dateEl.on("apply.daterangepicker", function (this: HTMLElement, ev: any, picker: any) {
                        jQuery(this).val(picker.startDate.format("D MMM YYYY"));
                    });
                }

                // Initialize Vehicles Carousel
                const vehicleCarousel = jQuery('.tours-one__carousel');
                if (vehicleCarousel.length && typeof vehicleCarousel.owlCarousel === 'function') {
                    const options = vehicleCarousel.data('owl-options');
                    vehicleCarousel.owlCarousel(
                        typeof options === "object" ? options : JSON.parse(options)
                    );
                }
                // Initialize Testimonials Carousel
                const testimonialCarousel = jQuery('.testimonials-one__carousel');
                if (testimonialCarousel.length && typeof testimonialCarousel.owlCarousel === 'function') {
                    const options = testimonialCarousel.data('owl-options');
                    testimonialCarousel.owlCarousel(
                        typeof options === "object" ? options : JSON.parse(options)
                    );
                }
            }, 100);
        }
    });

    onDestroy(() => {
        if (typeof window !== 'undefined') {
            document.body.classList.remove('custom-cursor');

            if ((window as any).$) {
                let dateEl = (window as any).$('#pickup_date');
                if (dateEl.length && dateEl.data('daterangepicker')) {
                    dateEl.data('daterangepicker').remove();
                }
            }
        }
    });

    import Preloader from '@/components/Template/Preloader.svelte';
    import Topbar from '@/components/Template/Topbar.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';

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
                    Welcome to Siwride
                </h5>
                <h2 class="hero-one__title title bw-split-in-down">
                    Hassle-Free Bali Travels with Siwride
                </h2>
                <p class="hero-one__text sub-title bw-split-in-left">
                    Book your professional driver in advance and enjoy a comfortable, 
                    safe, and reliable journey across the beautiful island of Bali.
                </p>
            </div>
        </div>
        <div class="hero-one__form">
            <div
                class="banner-form wow fadeInUp"
                data-wow-duration="1500ms"
                data-wow-delay="300ms"
            >
                <form class="banner-form__wrapper" action="/booking" method="GET">
                    <div class="banner-form row gutter-x-30 align-items-center">
                        <div
                            class="banner-form__control banner-form__col--1"
                        >
                            <i class="icon icon-pin-2"></i>
                            <label for="pickup">Pick-up *</label>
                            <input
                                id="pickup"
                                type="text"
                                name="pickup"
                                placeholder="Airport, hotel, address..."
                                required
                            />
                        </div>
                        <div class="banner-form__control banner-form__col--2">
                            <i class="icon icon-pin-2"></i>
                            <label for="dropoff">Drop-off *</label>
                            <input
                                id="dropoff"
                                type="text"
                                name="dropoff"
                                placeholder="Airport, hotel, address..."
                                required
                            />
                        </div>
                        <div
                            class="banner-form__control banner-form__control--date banner-form__col--3"
                        >
                            <i class="icon icon-calendar-1"></i>
                            <label for="pickup_date">Pick-up Date *</label>
                            <input
                                class="travhub-multi-datepicker"
                                id="pickup_date"
                                type="text"
                                name="pickup_date"
                                placeholder="Select Date"
                                autocomplete="off"
                                readonly
                                style="cursor: pointer;"
                                required
                            />
                        </div>
                        <div class="banner-form__control banner-form__col--4">
                            <i class="icon icon-traveler-with-a-suitcase-1"></i>
                            <label for="passengers">Passengers</label>
                            <div class="passenger-counter" style="display: flex; align-items: center; width: 40%;justify-content: space-between; padding-top: 3px;">
                                <button type="button" onclick={() => { if (passengerCount > 1) passengerCount--; }} style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;">-</button>
                                <span style="font-size: 16px; font-weight: 500; min-width: 30px; text-align: center;">{passengerCount}</span>
                                <input type="hidden" name="passengers" value={passengerCount} id="passengers" />
                                <button type="button" onclick={() => passengerCount++} style="background: transparent; border: 1px solid currentColor; opacity: 0.6; color: inherit; width: 24px; height: 24px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 18px; padding-bottom: 2px;">+</button>
                            </div>
                        </div>
                        <div
                            class="banner-form__control banner-form__button banner-form__col--5"
                        >
                            <button class="travhub-btn" type="submit"
                                ><span>
                                    Choose Vehicle</span
                                ></button
                            >
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div
            class="hero-one__shape-one"
            style="background-image: url(/assets/images/shapes/group-1-1.png);"
        ></div>
        <div class="hero-one__image">
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
        </div>
        <div class="hero-one__shape-two">
            <img src="/assets/images/shapes/plane.png" alt="" />
        </div>
        <div class="hero-one__shape-four">
            <img src="/assets/images/shapes/cloude-1-2.png" alt="" />
        </div>
    </section>

    <section class="how-it-works" style="padding: 100px 0; background-color: #f7f9fa;">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Booking Process<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    3 Simple Steps to Start Your Journey
                </h3>
            </div>
            
            <div class="row gutter-y-30">
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1500ms" data-wow-delay="00ms">
                    <div role="presentation" class="how-it-works__card text-center" style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-10px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div class="icon" style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;">
                            <i class="icon-pin-2"></i>
                            <div style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);">1</div>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 20px; font-size: 22px;">Set Your Route & Schedule</h4>
                        <p style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;">Enter your pick-up point, destination, and travel date. We will instantly match you with the best available drivers nearby.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1500ms" data-wow-delay="200ms">
                    <div role="presentation" class="how-it-works__card text-center" style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-10px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div class="icon" style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;">
                            <i class="icon-traveler-with-a-suitcase-1"></i>
                            <div style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);">2</div>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 20px; font-size: 22px;">Choose Your Perfect Vehicle</h4>
                        <p style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;">Pick the transport that suits your comfort and group size. Enjoy peace of mind with our transparent upfront details.</p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1500ms" data-wow-delay="400ms">
                    <div role="presentation" class="how-it-works__card text-center" style="padding: 50px 30px; border-radius: 12px; background: #fff; box-shadow: 0px 10px 40px 0px rgba(0, 0, 0, 0.05); height: 100%; transition: transform 0.3s ease;" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-10px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div class="icon" style="width: 90px; height: 90px; background: var(--travhub-base, #e52029); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 30px; position: relative;">
                            <i class="flaticon-check"></i>
                            <div style="position: absolute; right: -10px; top: -5px; width: 35px; height: 35px; background: #fff; color: var(--travhub-base, #e52029); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; box-shadow: 0px 5px 15px rgba(0,0,0,0.1);">3</div>
                        </div>
                        <h4 style="font-weight: 700; margin-bottom: 20px; font-size: 22px;">Secure Payment & Ride!</h4>
                        <p style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 0;">Complete your booking safely through our trusted payment gateways. No hidden fees, just sit back and enjoy your comfortable trip.</p>
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
                            src="/assets/images/resources/why-choose-one-1.jpg"
                            alt="travhub"
                        />
                        <div
                            class="why-choose-one__image__shape"
                            style="background-image: url(/assets/images/shapes/why-choose-one-shape.png);"
                        ></div>
                        <div class="why-choose-one__image__two">
                            <img
                                src="/assets/images/resources/why-choose-one-2.jpg"
                                alt="travhub"
                            />
                        </div>
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
                                href="about.html"
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
                                Why Siwride?<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="Siwride"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                Your Premium Ride Partner
                            </h3>
                            <p class="sec-title__text bw-split-in-up-fast">
                                Experience hassle-free, comfortable, and safe transportation across Bali with our highly-rated drivers and diverse fleet. We prioritize your comfort above all else.
                            </p>
                        </div>
                        <div class="why-choose-one__box">
                            <div class="why-choose-one__box__icon">
                                <i class="icon-traveler-with-a-suitcase-1"></i>
                            </div>
                            <h5 class="why-choose-one__box__title">
                                Professional Drivers
                            </h5>
                            <p class="why-choose-one__box__text">
                                All our drivers are highly trained, vetted,<br /> and dedicated to ensuring your smooth<br /> and safe journey everywhere you go.
                            </p>
                        </div>
                        <div class="why-choose-one__box">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-check"></i>
                            </div>
                            <h5 class="why-choose-one__box__title">
                                Transparent Pricing
                            </h5>
                            <p class="why-choose-one__box__text">
                                No hidden fees or unexpected surges!<br /> Enjoy high-quality service at fair,<br /> fixed rates every time you ride.
                            </p>
                        </div>
                        <div class="why-choose-one__btn">
                            <a href="#/" class="travhub-btn">
                                <span>Book a Ride</span>
                            </a>
                            <div class="why-choose-one__author">
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-1.jpg"
                                        alt="Travhub"
                                    />
                                </div>
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-2.jpg"
                                        alt="Travhub"
                                    />
                                </div>
                                <div class="why-choose-one__author__item">
                                    <img
                                        src="/assets/images/resources/why-choose-one-author-3.jpg"
                                        alt="Travhub"
                                    />
                                </div>
                                <div
                                    class="why-choose-one__author__item why-choose-one__author__item--plus"
                                >
                                    <i class="flaticon-add"></i>
                                </div>
                            </div>
                            <h5 class="why-choose-one__count">
                                10k+<span>Happy Passengers</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="vehicle-category" style="padding: 100px 0; background-color: #ffffff;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-5">
                <div class="sec-title mb-0" style="flex: 1;">
                    <div class="sec-title__tagline bw-split-in-right">
                        Our Fleet<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                    </div>
                    <h3 class="sec-title__title bw-split-in-left">
                        Vehicle Category Highlight
                    </h3>
                    <p class="sec-title__text bw-split-in-up-fast" style="max-width: 650px; margin-top: 15px;">
                        Discover the wide variety of vehicles available for you, the fleet fits to every situation you may need, to the passengers, trip and budget.
                    </p>
                </div>
                <div class="see-all-btn d-none d-md-block" style="padding-bottom: 20px;">
                    <a href="#/" class="travhub-btn travhub-btn--base"><span>See All</span></a>
                </div>
            </div>
            
            <div class="tours-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel" data-owl-options={'{"loop": true,"autoplay": true,"autoplayTimeout": 3000,"autoplayHoverPause": true,"items": 1,"nav": false,"navText": ["<span class=\\"icon-right-arrow\\"></span>","<span class=\\"icon-right-arrow\\"></span>"],"dots": true,"margin": 8,"responsive": {"0": {"items": 1,"margin": 8},"575": {"items": 2,"margin": 10},"992": {"items": 3,"margin": 12},"1200": {"items": 4,"margin": 15}}}'}>
                {#each vehicleCategories as vehicle}
                <div class="item">
                    <div role="presentation" class="vehicle-card" style="border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 8px 15px rgba(0,0,0,0.06); transition: transform 0.3s ease; height: 100%; border: 1px solid #f1f1f1;" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-8px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div class="vehicle-card__img" style="height: 220px; display: flex; align-items: center; justify-content: center; background: #f9f9f9; overflow: hidden; padding: 20px;">
                            <img src={vehicle.img} alt={vehicle.name} style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px; transition: transform 0.5s ease;" role="presentation" onmouseenter={(e) => e.currentTarget.style.transform = 'scale(1.08)'} onmouseleave={(e) => e.currentTarget.style.transform = 'scale(1)'}/>
                        </div>
                        <div class="vehicle-card__content" style="padding: 25px;">
                            <h4 style="font-size: 20px; font-weight: 700; margin-bottom: 15px;"><a href="#/" style="color: inherit; text-decoration: none;">{vehicle.name}</a></h4>
                            <div style="display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; color: #666; font-size: 15px; font-weight: 500;">
                                    <i class="icon-traveler-with-a-suitcase-1" style="margin-right: 8px; font-size: 18px; color: var(--travhub-base, #e52029);"></i> {vehicle.passengers}
                                </div>
                                <a href="#/" style="width: 35px; height: 35px; border-radius: 50%; background: #f7f9fa; display: flex; align-items: center; justify-content: center; color: var(--travhub-base, #e52029); transition: 0.3s; text-decoration: none;" onmouseenter={(e) => { e.currentTarget.style.background = 'var(--travhub-base, #e52029)'; e.currentTarget.style.color = '#fff'; }} onmouseleave={(e) => { e.currentTarget.style.background = '#f7f9fa'; e.currentTarget.style.color = 'var(--travhub-base, #e52029)'; }}>
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

    <section class="our-services" style="padding: 100px 0; background-color: #f4f6f8;">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    What We Offer<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Our Premium Services
                </h3>
                <p class="sec-title__text bw-split-in-up-fast" style="max-width: 600px; margin: 15px auto 0;">
                    From quick trips to full-day explorations, we provide a variety of reliable transportation solutions tailored for your needs.
                </p>
            </div>
            
            <div class="row gutter-y-30">
                {#each ourServices as service, index}
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1500ms" data-wow-delay={index * 100 + "ms"}>
                    <div class="service-card" style="background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s;" role="presentation" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-10px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div class="service-card__img" style="height: 220px; overflow: hidden; position: relative;">
                            <img src={service.img} alt={service.title} style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;" role="presentation" onmouseenter={(e) => e.currentTarget.style.transform = 'scale(1.1)'} onmouseleave={(e) => e.currentTarget.style.transform = 'scale(1)'}/>
                        </div>
                        <div class="service-card__content" style="padding: 35px 30px 30px; position: relative; z-index: 1;">
                            <div style="position: absolute; top: -30px; right: 30px; width: 60px; height: 60px; background: var(--travhub-base, #e52029); border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; border: 4px solid #fff; z-index: 2; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <i class={service.icon}></i>
                            </div>
                            <h4 style="font-size: 22px; font-weight: 700; margin-bottom: 15px;">
                                <a href="#/" style="color: inherit; text-decoration: none;">{service.title}</a>
                            </h4>
                            <p style="color: #666; font-size: 16px; line-height: 1.8; margin-bottom: 20px;">
                                {service.description}
                            </p>
                            <a href="#/" style="display: inline-flex; align-items: center; color: var(--travhub-base, #e52029); font-weight: 600; text-decoration: none; font-size: 15px; transition: 0.3s;" role="presentation" onmouseenter={(e) => e.currentTarget.style.letterSpacing = '1px'} onmouseleave={(e) => e.currentTarget.style.letterSpacing = '0'}>
                                Read More <i class="icon-right-arrow" style="margin-left: 8px; font-size: 12px;"></i>
                            </a>
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
                    Where We Operate<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Our Coverage in Bali
                </h3>
                <p class="sec-title__text bw-split-in-up-fast" style="max-width: 600px; margin: 15px auto 0;">
                    Currently, Siwride operates across all major tourist and business hubs in Bali. Zoom in on the map to see exactly where our drivers are ready to pick you up.
                </p>
            </div>
            
            <div class="row" style="margin-top: 40px;">
                <div class="col-lg-12 wow fadeInUp" data-wow-duration="1500ms">
                    <div style="border-radius: 15px; overflow: hidden; box-shadow: 0 15px 40px rgba(0,0,0,0.1); height: 500px; position: relative; border: 1px solid #f1f1f1; background-color: #eef2f5;">
                        <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 1;">
                            <i class="flaticon-pin-1" style="font-size: 60px; color: var(--travhub-base, #e52029); margin-bottom: 20px; opacity: 0.8;"></i>
                            <h4 style="color: #444; font-weight: 700; font-size: 24px; margin-bottom: 10px;">Illustration Map of Bali</h4>
                            <p style="color: #888; font-size: 15px; margin: 0;">(This area is reserved for a static map image of Bali)</p>
                        </div>
                        <img src="https://placehold.co/1200x500/e9ecef/e9ecef" alt="Bali Map Placeholder" style="width: 100%; height: 100%; object-fit: cover; position: relative; z-index: 0;" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="popular-destinations" style="padding: 100px 0; background-color: #f4f6f8;">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Explore Bali With Us<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Popular Destinations
                </h3>
                <p class="sec-title__text bw-split-in-up-fast" style="max-width: 600px; margin: 15px auto 0;">
                    Discover the most frequently visited and breathtaking locations in Bali. Book a ride with us and travel there in comfort.
                </p>
            </div>
            
            <div class="row gutter-y-30">
                {#each popularDestinations as dest, index}
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="1500ms" data-wow-delay={index * 100 + "ms"}>
                    <div class="destination-card" style="border-radius: 12px; overflow: hidden; position: relative; height: 350px; background: #000; box-shadow: 0 10px 30px rgba(0,0,0,0.1); cursor: pointer;" role="presentation" onmouseenter={(e) => { const el = e.currentTarget as HTMLElement; const img = el.querySelector('img') as HTMLElement | null; if (img) img.style.transform = 'scale(1.1)'; const overlay = el.querySelector('.destination-overlay') as HTMLElement | null; if (overlay) overlay.style.opacity = '1'; const btn = el.querySelector('.destination-btn') as HTMLElement | null; if (btn) { btn.style.transform = 'translateY(0)'; btn.style.opacity = '1'; } }} onmouseleave={(e) => { const el = e.currentTarget as HTMLElement; const img = el.querySelector('img') as HTMLElement | null; if (img) img.style.transform = 'scale(1)'; const overlay = el.querySelector('.destination-overlay') as HTMLElement | null; if (overlay) overlay.style.opacity = '0.7'; const btn = el.querySelector('.destination-btn') as HTMLElement | null; if (btn) { btn.style.transform = 'translateY(10px)'; btn.style.opacity = '0'; } }}>
                        <img src={dest.img} alt={dest.name} style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; opacity: 0.9;" />
                        <div class="destination-overlay" style="position: absolute; bottom: 0; left: 0; width: 100%; height: 60%; background: linear-gradient(to top, rgba(0,0,0,0.95), transparent); transition: 0.3s; opacity: 0.8; z-index: 1;"></div>
                        <div style="position: absolute; bottom: 30px; left: 30px; right: 30px; z-index: 2; display: flex; flex-direction: column;">
                            <h4 style="color: #fff; font-size: 24px; font-weight: 700; margin-bottom: 5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">{dest.name}</h4>
                            <div style="color: #eee; font-size: 15px; display: flex; align-items: center; justify-content: space-between; font-weight: 500;">
                                <div><i class="icon-pin-2" style="color: var(--travhub-base, #e52029); margin-right: 8px; font-size: 16px;"></i> {dest.location}</div>
                                <a href="#/" class="destination-btn" style="background: var(--travhub-base, #e52029); color: #fff; padding: 6px 15px; border-radius: 4px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.3s; transform: translateY(10px); opacity: 0;">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                {/each}
            </div>
        </div>
    </section>

    <section class="testimonials-one" style="padding: 120px 0; background-color: #f8fafd; position: relative; overflow: visible; z-index: 5;">
        <div class="testimonials-one__bg" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(circle at 20% 30%, rgba(229, 32, 41, 0.03) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(229, 32, 41, 0.03) 0%, transparent 40%);"></div>
        
        <div class="container" style="position: relative; z-index: 2;">
            <div class="sec-title text-center" style="margin-bottom: 60px;">
                <div class="sec-title__tagline bw-split-in-right" style="justify-content: center;">
                    Trusted by Travelers<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                </div>
                <h3 class="sec-title__title bw-split-in-left" style="font-size: 42px;">
                    Real Stories from Our Clients
                </h3>
            </div>
            
            <div class="testimonials-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel" 
                data-owl-options={'{"items": 1, "margin": 30, "loop": true, "smartSpeed": 700, "nav": false, "dots": true, "autoplay": true, "responsive": {"768": {"items": 2}, "1200": {"items": 3}}}'}>
                {#each customerTestimonials as testimonial}
                <div class="item">
                    <div class="testimonials-card" style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 8px 10px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: column; transition: 0.3s; margin: 15px 0;" role="presentation" onmouseenter={(e) => e.currentTarget.style.transform = 'translateY(-10px)'} onmouseleave={(e) => e.currentTarget.style.transform = 'translateY(0)'}>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 25px;">
                            <div style="display: flex; gap: 4px; color: #ffab00; font-size: 14px;">
                                {#each Array(testimonial.rating) as _}
                                    <i class="fa fa-star"></i>
                                {/each}
                            </div>
                            <i class="flaticon-left-quote" style="color: rgba(229, 32, 41, 0.1); font-size: 40px; line-height: 1;"></i>
                        </div>
                        
                        <div class="testimonials-card__content" style="font-size: 17px; color: #555; line-height: 1.7; flex-grow: 1; margin-bottom: 30px;">
                            "{testimonial.comment}"
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 15px; padding-top: 25px; border-top: 1px solid #f1f1f1;">
                            <div style="width: 55px; height: 55px; border-radius: 50%; overflow: hidden; border: 2px solid #fff; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
                                <img src={testimonial.img} alt={testimonial.name} style="width: 100%; height: 100%; object-fit: cover;" />
                            </div>
                            <div>
                                <h3 style="font-size: 17px; font-weight: 700; margin: 0; color: #222;">{testimonial.name}</h3>
                                <span style="color: #999; font-size: 13px; font-weight: 500;">{testimonial.country}</span>
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
    /* Fix shadow clipping while keeping carousel width constrained */
    :global(.testimonials-one .owl-carousel, .vehicle-category .owl-carousel) {
        position: relative;
    }
    /* Vehicle carousel styling */
    :global(.vehicle-category .owl-stage-outer) {
        overflow: hidden !important; 
        padding-left: 0 !important;
        padding-right: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        padding-top: 10px !important;
        padding-bottom: 40px !important;
        margin-top: -10px !important;
        margin-bottom: -40px !important;
    }
    :global(.vehicle-category .owl-item) {
        display: flex !important;
        flex: 1;
        width: 100%;
        padding: 0 0 10px 0 !important; /* Only bottom padding, let owl margin handle horizontal gap */
        visibility: visible !important;
        opacity: 1 !important;
    }
    :global(.vehicle-category .owl-item .vehicle-card) {
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
        min-width: 300px !important;
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
    
    @media (max-width: 1599px) {
        :global(.hero-one__image-two),
        :global(.hero-one__image-four) {
            visibility: hidden !important;
            display: none !important;
        }
    }

    /* Vehicle cards responsive for mobile */
    @media (max-width: 767px) {
        :global(.vehicle-category .owl-item) {
            width: 100% !important;
            min-width: 350px !important;
            max-width: 400px !important;
            flex: 0 0 auto !important;
        }
        
        :global(.vehicle-category .owl-item .vehicle-card) {
            width: 100% !important;
            min-width: 350px !important;
            max-width: 400px !important;
            margin: 0 auto !important;
        }
    }
</style>
