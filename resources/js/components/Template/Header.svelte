<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { Link } from '@inertiajs/svelte';
    
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
                <Link href="/" data-inertia="true" style="display: flex; align-items: center; gap: 12px;">
                    <img
                        src="/assets/images/siwride_logo.png"
                        alt="Siwride"
                        width="70"
                        style="border: 2px solid white; border-radius: 50%; padding: 2px; background-color: white;"
                    />
                    <span style="color: white; font-size: 30px; font-weight: 700; letter-spacing: 1px;">SIWRIDE</span>
                </Link>
            </div>
            <nav class="main-header__nav main-menu" style="margin-right: 50px;">
                <ul class="main-menu__list">
                    <li class="dropdown">
                        <Link href="/" data-inertia="true">Home</Link>
                    </li>
                    <li class="dropdown">
                        <Link href="/about" data-inertia="true">About</Link>
                        <ul>
                            <li><Link href="/about-us" data-inertia="true">About Us</Link></li>
                            <li><Link href="/services" data-inertia="true">Our Services</Link></li>
                            <li><Link href="/area-coverage" data-inertia="true">Area Coverage</Link></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <Link href="/vehicles" data-inertia="true">Vehicles</Link>
                        <ul>
                            <li><Link href="/vehicles/standard-cars" data-inertia="true">Standard Cars</Link></li>
                            <li><Link href="/vehicles/premium-cars" data-inertia="true">Premium Cars</Link></li>
                            <li><Link href="/vehicles/vans-minibuses" data-inertia="true">Vans/Minibuses</Link></li>
                            <li><Link href="/vehicles/buses" data-inertia="true">Buses</Link></li>
                            <li><Link href="/vehicles/special-vehicles" data-inertia="true">Special Vehicles</Link></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <Link href="/pages" data-inertia="true">Pages</Link>
                        <ul>
                            <li><Link href="/testimonials" data-inertia="true">Testimonials</Link></li>
                            <li><Link href="/faq" data-inertia="true">FAQ</Link></li>
                            <li><Link href="/terms" data-inertia="true">Terms & Conditions</Link></li>
                            <li><Link href="/privacy" data-inertia="true">Privacy Policy</Link></li>
                        </ul>
                    </li>

                    <li>
                        <Link href="/booking" data-inertia="true">Booking</Link>
                    </li>
                    <li>
                        <Link href="/contact" data-inertia="true">Contact</Link>
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
