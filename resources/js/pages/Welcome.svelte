<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';

    onMount(() => {
        document.body.classList.add('custom-cursor');
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

    const auth = $derived(page.props.auth);
</script>

<AppHead title="Welcome | Travhub" />

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
                    Welcome to Travlhub
                </h5>
                <h2 class="hero-one__title title bw-split-in-down">
                    Adventure & Experience The Travel
                </h2>
                <p class="hero-one__text sub-title bw-split-in-left">
                    The leap into electronic typesetting, remaining essentially
                    unchanged. It was popularised, trust with our company
                </p>
            </div>
        </div>
        <div class="hero-one__form">
            <div
                class="banner-form wow fadeInUp"
                data-wow-duration="1500ms"
                data-wow-delay="300ms"
            >
                <form class="banner-form__wrapper" action="tour-listing-2.html">
                    <div class="banner-form row gutter-x-30 align-items-center">
                        <div
                            class="banner-form__control banner-form__col--1 banner-form__control--form"
                        >
                            <i class="icon icon-pin-2"></i>
                            <label for="location">Location</label>
                            <select
                                name="location"
                                class="selectpicker"
                                id="location"
                            >
                                <option value="select">Where to next</option>
                                <option value="Australia">Australia</option>
                                <option value="Spain">Spain</option>
                                <option value="Africa">Africa</option>
                                <option value="Europe">Europe</option>
                            </select>
                        </div>
                        <div class="banner-form__control banner-form__col--2">
                            <i class="icon icon-luggage--1"></i>
                            <label for="type2">Type</label>
                            <select name="type" class="selectpicker" id="type2">
                                <option value="select">Booking Type</option>
                                <option value="Spain">Spain</option>
                                <option value="africa">Beach</option>
                                <option value="europe">Discovery</option>
                            </select>
                        </div>
                        <div
                            class="banner-form__control banner-form__control--date banner-form__col--3"
                        >
                            <i class="icon icon-calendar-1"></i>
                            <label for="date">Date Form</label>
                            <input
                                class="travhub-multi-datepicker"
                                id="date"
                                type="text"
                                name="date"
                                placeholder="Select Date"
                            />
                        </div>
                        <div class="banner-form__control banner-form__col--4">
                            <i class="icon icon-traveler-with-a-suitcase-1"></i>
                            <label for="guests">Guests</label>
                            <input
                                id="guests"
                                type="number"
                                value="02"
                                name="guests"
                                placeholder="2"
                            />
                        </div>
                        <div
                            class="banner-form__control banner-form__button banner-form__col--5"
                        >
                            <button class="travhub-btn" type="submit"
                                ><span>
                                    Search <i class="icon-magnifying-glass-1"
                                    ></i></span
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

    <section class="destination-one">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Destinations<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Top Destinations
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    content of a page when looking at layout the point<br /> of using
                    lorem the is Ipsum less
                </p>
            </div>
            <div
                class="destination-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                data-owl-options={'{"loop": false,"items": 1,"nav": false,"navText": ["<span class=\\"icon-right-arrow\\"></span>","<span class=\\"icon-right-arrow\\"></span>"],"dots": true,"margin": 25,"responsive": {"0": {"items": 1},"575": {"items": 2},"992": {"items": 3},"1200": {"items": 4}}}'}
            >
                <div class="item">
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/destination-1-1.jpg"
                            alt="travhub"
                        />
                        <div class="destination-one__item__btn">
                            <a
                                href="destination-details.html"
                                aria-label="View Destination Details"
                                ><span><i class="flaticon-top-right"></i></span
                                ></a
                            >
                        </div>
                        <div class="destination-one__content">
                            <h4 class="destination-one__item__title">
                                <a href="destination-details.html">New York</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                12 Listing
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/destination-1-2.jpg"
                            alt="travhub"
                        />
                        <div class="destination-one__item__btn">
                            <a
                                href="destination-details.html"
                                aria-label="View Destination Details"
                                ><span><i class="flaticon-top-right"></i></span
                                ></a
                            >
                        </div>
                        <div class="destination-one__content">
                            <h4 class="destination-one__item__title">
                                <a href="destination-details.html">London</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                22 Listing
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/destination-1-3.jpg"
                            alt="travhub"
                        />
                        <div class="destination-one__item__btn">
                            <a
                                href="destination-details.html"
                                aria-label="View Destination Details"
                                ><span><i class="flaticon-top-right"></i></span
                                ></a
                            >
                        </div>
                        <div class="destination-one__content">
                            <h4 class="destination-one__item__title">
                                <a href="destination-details.html"
                                    >San Francisco</a
                                >
                            </h4>
                            <div class="destination-one__item__dec">
                                10 Listing
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="destination-one__item">
                        <img
                            src="/assets/images/destination/destination-1-4.jpg"
                            alt="travhub"
                        />
                        <div class="destination-one__item__btn">
                            <a
                                href="destination-details.html"
                                aria-label="View Destination Details"
                                ><span><i class="flaticon-top-right"></i></span
                                ></a
                            >
                        </div>
                        <div class="destination-one__content">
                            <h4 class="destination-one__item__title">
                                <a href="destination-details.html">Paris</a>
                            </h4>
                            <div class="destination-one__item__dec">
                                12 Listing
                            </div>
                        </div>
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
                                Trusted by<span>Trustpilot & rating</span>
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
                                Why we are<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="Travhub"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                Why Choose Us
                            </h3>
                            <p class="sec-title__text bw-split-in-up-fast">
                                content of a page when looking at layout the
                                point<br /> of using lorem the is Ipsum less
                            </p>
                        </div>
                        <div class="why-choose-one__box">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-suitcase"></i>
                            </div>
                            <h5 class="why-choose-one__box__title">
                                Personalized Trips
                            </h5>
                            <p class="why-choose-one__box__text">
                                Content of a page when looking at<br /> layout
                                the point of using lorem the<br /> is Ipsum less normal
                            </p>
                        </div>
                        <div class="why-choose-one__box">
                            <div class="why-choose-one__box__icon">
                                <i class="flaticon-signpost"></i>
                            </div>
                            <h5 class="why-choose-one__box__title">
                                Trusted Travel Guide
                            </h5>
                            <p class="why-choose-one__box__text">
                                Content of a page when looking at<br /> layout
                                the point of using lorem the<br /> is Ipsum less normal
                            </p>
                        </div>
                        <div class="why-choose-one__btn">
                            <a href="contact.html" class="travhub-btn">
                                <span>Start Booking</span>
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
                                18k+<span>Individual Traveller</span>
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="tours-one">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Featured Tours<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Most Favorite Tour Place
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    content of a page when looking at layout the point<br /> of using
                    lorem the is Ipsum less
                </p>
            </div>
            <div
                class="tours-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                data-owl-options={'{"loop": false,"items": 1,"nav": false,"navText": ["<span class=\\"icon-right-arrow\\"></span>","<span class=\\"icon-right-arrow\\"></span>"],"dots": true,"margin": 24,"responsive": {"0": {"items": 1},"768": {"items": 2},"992": {"items": 2},"1200": {"items": 3}}}'}
            >
                <div class="item">
                    <div class="tours-one__item">
                        <img
                            src="/assets/images/tours/tours-1-1.jpg"
                            alt="travhub"
                        />
                        <div class="tours-one__item__offer">40% off</div>
                        <div class="tours-one__item__wishlist">
                            <a
                                href="destination-details.html"
                                aria-label="Add to wishlist"
                                ><i class="icon-heart-1"></i></a
                            >
                        </div>
                        <div class="tours-one__item__content">
                            <div class="tours-one__item__feature">Featured</div>
                            <div class="tours-one__item__price">
                                $385.<span>00</span>
                            </div>
                            <h3 class="tours-one__item__title">
                                <a href="tour-details.html"
                                    >Over Turkish Waves</a
                                >
                            </h3>
                            <ul class="tours-one__item__meta list-unstyled">
                                <li><span>5.0</span>245 Rating</li>
                                <li>
                                    <i class="flaticon-pin-1"></i>US, Alaska
                                </li>
                            </ul>
                        </div>
                        <div class="tours-one__item__btn">
                            <a
                                href="#/"
                                onclick={(e) => e.preventDefault()}
                                aria-label="Open image gallery"
                                class="card__popup"
                                data-gallery-options={'{"items": [{"src": "assets/images/tours/tours-1-1.jpg"},{"src": "assets/images/tours/tours-1-2.jpg"},{"src": "assets/images/tours/tours-1-3.jpg"}],"gallery": {"enabled": true},"type": "image"}'}
                            >
                                <i class="flaticon-camera-1"></i>
                            </a>
                            <a
                                class="video-popup"
                                href="https://www.youtube.com/watch?v=G49_MdP0klg"
                                aria-label="Watch tour video"
                                ><i class="flaticon-video"></i></a
                            >
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="tours-one__item">
                        <img
                            src="/assets/images/tours/tours-1-2.jpg"
                            alt="travhub"
                        />
                        <div class="tours-one__item__offer">60% off</div>
                        <div class="tours-one__item__wishlist">
                            <a
                                href="destination-details.html"
                                aria-label="Add to wishlist"
                                ><i class="icon-heart-1"></i></a
                            >
                        </div>
                        <div class="tours-one__item__content">
                            <div class="tours-one__item__feature">Featured</div>
                            <div class="tours-one__item__price">
                                $395.<span>00</span>
                            </div>
                            <h3 class="tours-one__item__title">
                                <a href="tour-details.html">Over Rome Waves</a>
                            </h3>
                            <ul class="tours-one__item__meta list-unstyled">
                                <li><span>5.0</span>245 Rating</li>
                                <li><i class="flaticon-pin-1"></i>Rome</li>
                            </ul>
                        </div>
                        <div class="tours-one__item__btn">
                            <a
                                href="#/"
                                onclick={(e) => e.preventDefault()}
                                aria-label="Open image gallery"
                                class="card__popup"
                                data-gallery-options={'{"items": [{"src": "assets/images/tours/tours-1-2.jpg"},{"src": "assets/images/tours/tours-1-1.jpg"},{"src": "assets/images/tours/tours-1-3.jpg"}],"gallery": {"enabled": true},"type": "image"}'}
                            >
                                <i class="flaticon-camera-1"></i>
                            </a>
                            <a
                                class="video-popup"
                                href="https://www.youtube.com/watch?v=G49_MdP0klg"
                                aria-label="Watch tour video"
                                ><i class="flaticon-video"></i></a
                            >
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="tours-one__item">
                        <img
                            src="/assets/images/tours/tours-1-3.jpg"
                            alt="travhub"
                        />
                        <div class="tours-one__item__offer">20% off</div>
                        <div class="tours-one__item__wishlist">
                            <a
                                href="destination-details.html"
                                aria-label="Add to wishlist"
                                ><i class="icon-heart-1"></i></a
                            >
                        </div>
                        <div class="tours-one__item__content">
                            <div class="tours-one__item__feature">Featured</div>
                            <div class="tours-one__item__price">
                                $365.<span>00</span>
                            </div>
                            <h3 class="tours-one__item__title">
                                <a href="tour-details.html">Over United Waves</a
                                >
                            </h3>
                            <ul class="tours-one__item__meta list-unstyled">
                                <li><span>5.0</span>245 Rating</li>
                                <li>
                                    <i class="flaticon-pin-1"></i>US, Florida
                                </li>
                            </ul>
                        </div>
                        <div class="tours-one__item__btn">
                            <a
                                href="#/"
                                onclick={(e) => e.preventDefault()}
                                aria-label="Open image gallery"
                                class="card__popup"
                                data-gallery-options={'{"items": [{"src": "assets/images/tours/tours-1-3.jpg"},{"src": "assets/images/tours/tours-1-2.jpg"},{"src": "assets/images/tours/tours-1-1.jpg"}],"gallery": {"enabled": true},"type": "image"}'}
                            >
                                <i class="flaticon-camera-1"></i>
                            </a>
                            <a
                                class="video-popup"
                                href="https://www.youtube.com/watch?v=G49_MdP0klg"
                                aria-label="Watch tour video"
                                ><i class="flaticon-video"></i></a
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="gallery-one">
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Our beautiful moment<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Recent Gallery
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    content of a page when looking at layout the point<br /> of using
                    lorem the is Ipsum less
                </p>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-1.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-1.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-2.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-2.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-6.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-6.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-3.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-3.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-4.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-4.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-7.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-7.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-5.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-5.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-8.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-8.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-12.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-12.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-9.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-9.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-10.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-10.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="gallery-one__card">
                        <img
                            src="/assets/images/gallery/gallery-1-11.jpg"
                            alt="Travhub"
                        />
                        <div class="gallery-one__card__hover">
                            <a
                                href="assets/images/gallery/gallery-1-11.jpg"
                                aria-label="View large image"
                                class="img-popup"
                            >
                                <i class="flaticon-camera-2"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="counter-one">
        <div
            class="counter-one__shape travhub-splax"
            data-para-options={'{"orientation": "up","scale": 2.0,"overflow": true}'}
        >
            <img
                src="/assets/images/shapes/counter-1-shape.png"
                alt="travhub"
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
                        <div class="counter-one__image__left">
                            <div class="counter-one__image__one">
                                <img
                                    src="/assets/images/resources/count-1-1.jpg"
                                    alt="travhub"
                                />
                            </div>
                        </div>
                        <div class="counter-one__image__right">
                            <div class="counter-one__image__two">
                                <img
                                    src="/assets/images/resources/count-1-2.jpg"
                                    alt="travhub"
                                />
                            </div>
                            <div class="counter-one__image__two">
                                <img
                                    src="/assets/images/resources/count-1-3.jpg"
                                    alt="travhub"
                                />
                            </div>
                        </div>
                        <div class="counter-one__image__shape">
                            <img
                                src="/assets/images/resources/count-1-4.jpg"
                                alt="travhub"
                            />
                        </div>
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
                                    data-speed="1500"
                                ></span>
                                <span class="counter-one__funfact__count-tag"
                                    >k</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Our happy Customers around the word
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3
                                class="counter-one__funfact__count"
                                aria-label="Happy customers count"
                            >
                                <span
                                    class="count-text"
                                    data-stop="178"
                                    data-speed="1500"
                                ></span>
                            </h3>
                            <p class="counter-one__funfact__text">
                                Our happy Customers around the word
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3 class="counter-one__funfact__count">
                                <span
                                    class="count-text"
                                    data-stop="24"
                                    data-speed="1500"
                                ></span>
                                <span class="counter-one__funfact__count-tag"
                                    >M</span
                                >
                            </h3>
                            <p class="counter-one__funfact__text">
                                Our happy Customers around the word
                            </p>
                        </div>
                        <div class="counter-one__funfact__item count-box">
                            <h3
                                class="counter-one__funfact__count"
                                aria-label="Destination count"
                            >
                                <span
                                    class="count-text"
                                    data-stop="125"
                                    data-speed="1500"
                                ></span>
                            </h3>
                            <p class="counter-one__funfact__text">
                                Our happy Customers around the word
                            </p>
                        </div>
                        <div class="counter-one__funfact__item-two count-box">
                            <h3
                                class="counter-one__funfact__item-two__count"
                                aria-label="Rating count"
                            >
                                <span
                                    class="count-text"
                                    data-stop="4.8"
                                    data-speed="1500"
                                ></span>
                            </h3>
                        </div>
                        <span class="counter-one__funfact__line"></span>
                        <span class="counter-one__funfact__line-two"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="team-one">
        <div
            class="team-one__shape travhub-splax"
            data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
        >
            <img src="/assets/images/shapes/team-1-shape.png" alt="travhub" />
        </div>
        <div class="container">
            <div
                class="sec-title text-center wow fadeInUp"
                data-wow-delay="00ms"
            >
                <div class="sec-title__tagline bw-split-in-right">
                    Meet With Our Guide<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">Tour Guide</h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    content of a page when looking at layout the point<br /> of using
                    lorem the is Ipsum less
                </p>
            </div>
            <div class="row gutter-x-24 gutter-y-24">
                <div class="col-xl-3 col-md-6">
                    <div
                        class="team-one__item wow fadeInUp"
                        data-wow-delay="100ms"
                    >
                        <div class="team-one__item__image">
                            <img
                                src="/assets/images/team/team-1-1.jpg"
                                alt="team"
                            />
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">
                                <a href="team.html">Mike Hardson</a>
                            </h4>
                            <p class="team-one__item__text">Tourist Guide</p>
                            <div class="team-one__item__social">
                                <a
                                    href="https://facebook.com"
                                    aria-label="Facebook"
                                    ><i
                                        class="fab fa-facebook-f"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://twitter.com"
                                    aria-label="Twitter"
                                    ><i
                                        class="fab fa-twitter"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://pinterest.com"
                                    aria-label="LinkedIn"
                                    ><i
                                        class="fab fa-linkedin-in"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://instagram.com"
                                    aria-label="Instagram"
                                    ><i
                                        class="fab fa-instagram"
                                        aria-hidden="true"
                                    ></i></a
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div
                        class="team-one__item wow fadeInUp"
                        data-wow-delay="200ms"
                    >
                        <div class="team-one__item__image">
                            <img
                                src="/assets/images/team/team-1-2.jpg"
                                alt="team"
                            />
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">
                                <a href="team.html">Leslie Alexander</a>
                            </h4>
                            <p class="team-one__item__text">Tourist Guide</p>
                            <div class="team-one__item__social">
                                <a
                                    href="https://facebook.com"
                                    aria-label="Facebook"
                                    ><i
                                        class="fab fa-facebook-f"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://twitter.com"
                                    aria-label="Twitter"
                                    ><i
                                        class="fab fa-twitter"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://pinterest.com"
                                    aria-label="LinkedIn"
                                    ><i
                                        class="fab fa-linkedin-in"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://instagram.com"
                                    aria-label="Instagram"
                                    ><i
                                        class="fab fa-instagram"
                                        aria-hidden="true"
                                    ></i></a
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div
                        class="team-one__item wow fadeInUp"
                        data-wow-delay="300ms"
                    >
                        <div class="team-one__item__image">
                            <img
                                src="/assets/images/team/team-1-3.jpg"
                                alt="team"
                            />
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">
                                <a href="team.html">Annette Black</a>
                            </h4>
                            <p class="team-one__item__text">Tourist Guide</p>
                            <div class="team-one__item__social">
                                <a
                                    href="https://facebook.com"
                                    aria-label="Facebook"
                                    ><i
                                        class="fab fa-facebook-f"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://twitter.com"
                                    aria-label="Twitter"
                                    ><i
                                        class="fab fa-twitter"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://pinterest.com"
                                    aria-label="LinkedIn"
                                    ><i
                                        class="fab fa-linkedin-in"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://instagram.com"
                                    aria-label="Instagram"
                                    ><i
                                        class="fab fa-instagram"
                                        aria-hidden="true"
                                    ></i></a
                                >
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6">
                    <div
                        class="team-one__item wow fadeInUp"
                        data-wow-delay="400ms"
                    >
                        <div class="team-one__item__image">
                            <img
                                src="/assets/images/team/team-1-4.jpg"
                                alt="team"
                            />
                        </div>
                        <div class="team-one__item__content">
                            <h4 class="team-one__item__title">
                                <a href="team.html">Guy Hawkins</a>
                            </h4>
                            <p class="team-one__item__text">Tourist Guide</p>
                            <div class="team-one__item__social">
                                <a
                                    href="https://facebook.com"
                                    aria-label="Facebook"
                                    ><i
                                        class="fab fa-facebook-f"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://twitter.com"
                                    aria-label="Twitter"
                                    ><i
                                        class="fab fa-twitter"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://pinterest.com"
                                    aria-label="LinkedIn"
                                    ><i
                                        class="fab fa-linkedin-in"
                                        aria-hidden="true"
                                    ></i></a
                                >
                                <a
                                    href="https://instagram.com"
                                    aria-label="Instagram"
                                    ><i
                                        class="fab fa-instagram"
                                        aria-hidden="true"
                                    ></i></a
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="testimonials-one">
        <div
            class="testimonials-one__bg"
            style="background-image: url(/assets/images/shapes/page-header-bg-shape.png);"
        ></div>
        <div
            class="testimonials-one__shape travhub-splax"
            data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
        >
            <img
                src="/assets/images/shapes/testimonials-1-shape.png"
                alt="travhub"
            />
        </div>
        <div class="container">
            <div class="row">
                <div class="col-xl-6">
                    <div
                        class="testimonials-one__image wow fadeInUp"
                        data-wow-delay="100ms"
                    >
                        <div class="testimonials-one__image__one">
                            <img
                                src="/assets/images/resources/test-1-1.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div
                            class="testimonials-one__image__two wow fadeInUp"
                            data-wow-delay="200ms"
                        >
                            <img
                                src="/assets/images/resources/test-1-2.jpg"
                                alt="travhub"
                            />
                            <img
                                src="/assets/images/resources/test-1-4.jpg"
                                alt="travhub"
                            />
                            <img
                                src="/assets/images/resources/test-1-5.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div
                            class="testimonials-one__image__three wow fadeInUp"
                            data-wow-delay="300ms"
                        >
                            <img
                                src="/assets/images/resources/test-1-3.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div
                            class="testimonials-one__image__shape wow fadeInUp"
                            data-wow-delay="400ms"
                        >
                            <img
                                src="/assets/images/shapes/test-1-shape.png"
                                alt="travhub"
                            />
                        </div>
                    </div>
                </div>
                <div
                    class="col-xl-6 d-flex align-items-center wow fadeInRight"
                    data-wow-delay="400ms"
                >
                    <div
                        class="testimonials-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                        data-owl-options={'{"items": 1,"margin": 30,"loop": false,"smartSpeed": 700,"nav": false,"navText": ["<span>Prev</span>","<span>Next</span>"],"dots": false,"autoplay": false}'}
                    >
                        <div class="item">
                            <div class="testimonials-card">
                                <div class="testimonials-card__quote">
                                    <i class="flaticon-left-quote"></i>
                                </div>
                                <div class="testimonials-card__content">
                                    Travehub work helped us save a significant
                                    percentage of our tour plan we are happy
                                    with all experiences & all services.
                                </div>
                                <h3 class="testimonials-card__name">
                                    Tomas Widdin
                                </h3>
                                <span class="testimonials-card__designation"
                                    >Web Developer</span
                                >
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimonials-card">
                                <div class="testimonials-card__quote">
                                    <i class="flaticon-left-quote"></i>
                                </div>
                                <div class="testimonials-card__content">
                                    Travehub work helped us save a significant
                                    percentage of our tour plan we are happy
                                    with all experiences & all services.
                                </div>
                                <h3 class="testimonials-card__name">
                                    William Jack
                                </h3>
                                <span class="testimonials-card__designation"
                                    >Web Designer</span
                                >
                            </div>
                        </div>
                        <div class="item">
                            <div class="testimonials-card">
                                <div class="testimonials-card__quote">
                                    <i class="flaticon-left-quote"></i>
                                </div>
                                <div class="testimonials-card__content">
                                    Travehub work helped us save a significant
                                    percentage of our tour plan we are happy
                                    with all experiences & all services.
                                </div>
                                <h3 class="testimonials-card__name">
                                    Mark Smith
                                </h3>
                                <span class="testimonials-card__designation"
                                    >Web Developer</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="client-carousel-one">
        <div
            class="client-carousel-one__shape"
            style="background-image: url(/assets/images/shapes/client-bg-1.png);"
        ></div>
        <div class="container">
            <h5
                class="client-carousel-one__title wow fadeInUp"
                data-wow-delay="100ms"
            >
                Trusted by 300K+ organizations worldwide
            </h5>
            <div
                class="client-carousel-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel wow fadeInUp"
                data-wow-delay="200ms"
                data-owl-options={'{"items": 6,"margin": 20,"smartSpeed": 500,"loop":true,"autoplay": true,"nav":false,"dots":false,"responsive":{"0":{"items": 2,"margin": 24},"575":{"items": 3,"margin": 24},"992":{"items": 4,"margin": 24},"1200":{"items": 5,"margin": 24},"1400":{"items": 6,"margin": 24}}}'}
            >
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-1.png"
                            alt=""
                        />
                    </div>
                </div>
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-2.png"
                            alt=""
                        />
                    </div>
                </div>
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-3.png"
                            alt=""
                        />
                    </div>
                </div>
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-4.png"
                            alt=""
                        />
                    </div>
                </div>
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-5.png"
                            alt=""
                        />
                    </div>
                </div>
                <div class="item">
                    <div class="client-carousel-one__item">
                        <img
                            src="/assets/images/resources/client-2-6.png"
                            alt=""
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- blog-start -->
    <section class="blog-one pt-120 pb-120">
        <div
            class="blog-one__shape travhub-splax"
            data-para-options={'{"orientation": "up","scale": 2.5,"overflow": true}'}
        >
            <img src="/assets/images/shapes/blog-1-shape.png" alt="travhub" />
        </div>
        <div class="container">
            <div class="sec-title text-center">
                <div class="sec-title__tagline bw-split-in-right">
                    Blog Post<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Travhub"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Our Latest Blog Post
                </h3>
                <p class="sec-title__text bw-split-in-up-fast">
                    content of a page when looking at layout the point<br /> of using
                    lorem the is Ipsum less
                </p>
            </div>
            <div
                class="blog-one__carousel travhub-owl__carousel travhub-owl__carousel--basic-nav owl-carousel"
                data-owl-options={'{"loop": false,"items": 1,"nav": false,"navText": ["<span class=\\"icon-right-arrow\\"></span>","<span class=\\"icon-right-arrow\\"></span>"],"dots": true,"margin": 30,"responsive": {"0": {"items": 1},"768": {"items": 2},"992": {"items": 3}}}'}
            >
                <div class="item">
                    <div
                        class="blog-one__item wow fadeInUp"
                        data-wow-delay="00ms"
                    >
                        <div class="blog-one__item__thumb">
                            <img
                                src="/assets/images/blog/blog-1-1.jpg"
                                alt="travhub"
                            />
                            <img
                                src="/assets/images/blog/blog-1-1.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div class="blog-one__item__content">
                            <h3 class="blog-one__item__title">
                                <a href="blog-details-right.html"
                                    >Living sustainability: A day in the life
                                    atrealar....</a
                                >
                            </h3>
                            <ul class="blog-one__item__meta">
                                <li>
                                    <i class="icon-calendar"></i>24 dec, 2024
                                </li>
                                <li><i class="icon-comment"></i>2 comments</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div
                        class="blog-one__item wow fadeInUp"
                        data-wow-delay="100ms"
                    >
                        <div class="blog-one__item__thumb">
                            <img
                                src="/assets/images/blog/blog-1-2.jpg"
                                alt="travhub"
                            />
                            <img
                                src="/assets/images/blog/blog-1-2.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div class="blog-one__item__content">
                            <h3 class="blog-one__item__title">
                                <a href="blog-details-right.html"
                                    >Psychology is a broad field so consider
                                    narrowing</a
                                >
                            </h3>
                            <ul class="blog-one__item__meta">
                                <li>
                                    <i class="icon-calendar"></i>24 dec, 2024
                                </li>
                                <li><i class="icon-comment"></i>2 comments</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div
                        class="blog-one__item wow fadeInUp"
                        data-wow-delay="200ms"
                    >
                        <div class="blog-one__item__thumb">
                            <img
                                src="/assets/images/blog/blog-1-3.jpg"
                                alt="travhub"
                            />
                            <img
                                src="/assets/images/blog/blog-1-3.jpg"
                                alt="travhub"
                            />
                        </div>
                        <div class="blog-one__item__content">
                            <h3 class="blog-one__item__title">
                                <a href="blog-details-right.html"
                                    >Affordable Therapy Options for the People
                                    Need Help</a
                                >
                            </h3>
                            <ul class="blog-one__item__meta">
                                <li>
                                    <i class="icon-calendar"></i>24 dec, 2024
                                </li>
                                <li><i class="icon-comment"></i>2 comments</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
