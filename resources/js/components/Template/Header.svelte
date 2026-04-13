<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    
    export let auth: any = null;
    
    let scrolled = false;
    let headerElement: HTMLElement;
    
    const handleScroll = () => {
        scrolled = window.scrollY > 50;
    };
    
    onMount(() => {
        window.addEventListener('scroll', handleScroll);
        handleScroll(); // Check initial position
    });
    
    onDestroy(() => {
        window.removeEventListener('scroll', handleScroll);
    });
</script>

<header class="main-header sticky-header sticky-header--normal" class:scrolled={scrolled} bind:this={headerElement}>
    <div class="container">
        <div class="main-header__inner">
            <div class="main-header__logo">
                <a href="/" style="display: flex; align-items: center; gap: 12px;">
                    <img
                        src="assets/images/siwride_logo.png"
                        alt="Siwride"
                        width="70"
                        style="border: 2px solid white; border-radius: 50%; padding: 2px; background-color: white;"
                    />
                    <span style="color: white; font-size: 30px; font-weight: 700; letter-spacing: 1px;">SIWRIDE</span>
                </a>
            </div>
            <nav class="main-header__nav main-menu" style="margin-right: 50px;">
                <ul class="main-menu__list">
                    <li class="dropdown">
                        <a href="/">Home</a>
                    </li>
                    <li>
                        <a href="about.html">About</a>
                        <ul>
                            <li><a href="index2.html">About Us</a></li>
                            <li><a href="/">Our Services</a></li>
                            <li><a href="index3.html">Area Coverage</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>Vehicles</a
                        >
                        <ul>
                            <li><a href="team.html">Standard Cars?</a></li>
                            <li><a href="pricing.html">Premium Cars</a></li>
                            <li><a href="gallery.html">Vans/Minibuses</a></li>
                            <li><a href="faq.html">Buses</a></li>
                            <li><a href="/">Login</a></li>
                            <li><a href="/">Special Vehicles</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>Pages</a
                        >
                        <ul>
                            <li><a href="tour.html">Testimonials</a></li>
                            <li>
                                <a href="tour-sidebar.html">FAQ</a>
                            </li>
                            <li>
                                <a href="tour-carousel.html">Terms & Coditions</a>
                            </li>
                            <li>
                                <a href="tour-details.html">Privacy Policy</a>
                            </li>
                        </ul>
                    </li>
                    <!-- <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>Destination</a
                        >
                        <ul class="sub-menu">
                            <li><a href="destination.html">Destination</a></li>
                            <li>
                                <a href="destination-carousel.html"
                                    >Destination Carousel</a
                                >
                            </li>
                            <li>
                                <a href="destination-details.html"
                                    >Destination Details</a
                                >
                            </li>
                        </ul>
                    </li> -->
                    <!-- <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>News</a
                        >
                        <ul class="sub-menu">
                            <li class="dropdown">
                                <a
                                    href="#/"
                                    role="button"
                                    aria-haspopup="true"
                                    onclick={(e) => e.preventDefault()}
                                    >News grid</a
                                >
                                <ul class="sub-menu">
                                    <li>
                                        <a href="blog-grid.html">No sidebar</a>
                                    </li>
                                    <li>
                                        <a href="blog-grid-left.html"
                                            >Left sidebar</a
                                        >
                                    </li>
                                    <li>
                                        <a href="blog-grid-right.html"
                                            >Right sidebar</a
                                        >
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a
                                    href="#/"
                                    role="button"
                                    aria-haspopup="true"
                                    onclick={(e) => e.preventDefault()}
                                    >News list</a
                                >
                                <ul class="sub-menu">
                                    <li>
                                        <a href="blog-list.html">No sidebar</a>
                                    </li>
                                    <li>
                                        <a href="blog-list-left.html"
                                            >Left sidebar</a
                                        >
                                    </li>
                                    <li>
                                        <a href="blog-list-right.html"
                                            >Right sidebar</a
                                        >
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <a href="blog-carousel.html">News carousel</a>
                            </li>
                            <li class="dropdown">
                                <a
                                    href="#/"
                                    role="button"
                                    aria-haspopup="true"
                                    onclick={(e) => e.preventDefault()}
                                    >News details</a
                                >
                                <ul class="sub-menu">
                                    <li>
                                        <a href="blog-details.html"
                                            >No sidebar</a
                                        >
                                    </li>
                                    <li>
                                        <a href="blog-details-left.html"
                                            >Left sidebar</a
                                        >
                                    </li>
                                    <li>
                                        <a href="blog-details-right.html"
                                            >Right sidebar</a
                                        >
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </li> -->
                    <li>
                        <a href="contact.html">Booking</a>
                    </li>
                    <li>
                        <a href="contact.html">Contact</a>
                    </li>
                </ul>
            </nav>
            <div class="main-header__right">
                <div class="mobile-nav__btn mobile-nav__toggler">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <!-- 
                <a href="#" class="search-toggler main-header__search">
                    <i class="flaticon-search" aria-hidden="true"></i>
                    <span class="sr-only">Search</span>
                </a>
                -->
                {#if auth?.user}
                    <div class="main-header__btn ml-2">
                        <a href="/dashboard" class="travhub-btn">
                            <span>Dashboard</span>
                        </a>
                    </div>
                {:else}
                    <div class="main-header__btn ml-2">
                        <a href="/login" class="travhub-btn">
                            <span>Log in</span>
                        </a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</header>

<style>
    :global(.main-header.scrolled) {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 9999 !important;
        background: transparent !important;
        box-shadow: none !important;
        transition: all 0.3s ease !important;
        padding: 0 !important;
        border-radius: 0 0 15px 15px !important;
    }
    
    :global(.main-header.scrolled .main-header__inner) {
        padding: 0 50px 0 0 !important;
        transition: all 0.3s ease !important;
        height: 70px !important;
        display: flex !important;
        align-items: center !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        border-radius: 0 0 14px 15px !important;
    }
    
    :global(.main-header.scrolled .main-header__logo) {
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        border-radius: 0px 0px 30px 15px;
    }
    
    :global(.main-header.scrolled .main-header__logo img) {
        width: 50px !important;
        height: 50px !important;
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header.scrolled .main-header__logo span) {
        font-size: 24px !important;
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header.scrolled .main-menu__list > li > a) {
        padding: 8px 15px !important;
        font-size: 15px !important;
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header.scrolled .travhub-btn) {
        padding: 8px 20px !important;
        font-size: 14px !important;
        transition: all 0.3s ease !important;
    }
    
    /* Hide topbar when scrolled - this will be handled in Welcome.svelte */
    
    /* Add smooth transitions for normal state */
    :global(.main-header) {
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header .main-header__logo img) {
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header .main-header__logo span) {
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header .main-menu__list > li > a) {
        transition: all 0.3s ease !important;
    }
    
    :global(.main-header .travhub-btn) {
        transition: all 0.3s ease !important;
    }
</style>
