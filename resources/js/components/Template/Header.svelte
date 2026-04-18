<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import { Link } from '@inertiajs/svelte';

    const { auth = null }: { auth?: any } = $props();

    let scrolled = $state(false);
    let mobileNavOpen = $state(false);
    let headerElement: HTMLElement;

    const handleScroll = () => {
        scrolled = window.scrollY > 50;
    };

    onMount(() => {
        window.addEventListener('scroll', handleScroll);
        handleScroll();
    });

    onDestroy(() => {
        window.removeEventListener('scroll', handleScroll);
        document.body.classList.remove('locked');
    });

    $effect(() => {
        if (typeof document !== 'undefined') {
            document.body.classList.toggle('locked', mobileNavOpen);
        }
    });

    /** Toggle accordion submenu in mobile drawer */
    function toggleSubMenu(event: MouseEvent) {
        const target = event.currentTarget as HTMLElement;
        const li = target.closest('li.dropdown');
        if (!li) { return; }
        event.preventDefault();
        li.classList.toggle('open');
    }
</script>

<header class="main-header sticky-header sticky-header--normal" class:scrolled bind:this={headerElement}>
    <div class="container">
        <div class="main-header__inner">
            <div class="main-header__logo">
                <Link href="/" style="display: flex; align-items: center; gap: 12px;">
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
                    <li><Link href="/">Home</Link></li>
                    <li class="dropdown">
                        <Link href="/about">About</Link>
                        <ul>
                            <li><Link href="/about-us">About Us</Link></li>
                            <li><Link href="/services">Our Services</Link></li>
                            <li><Link href="/area-coverage">Area Coverage</Link></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <Link href="/vehicles">Vehicles</Link>
                        <ul>
                            <li><Link href="/vehicles/standard-cars">Standard Cars</Link></li>
                            <li><Link href="/vehicles/premium-cars">Premium Cars</Link></li>
                            <li><Link href="/vehicles/vans-minibuses">Vans/Minibuses</Link></li>
                            <li><Link href="/vehicles/buses">Buses</Link></li>
                            <li><Link href="/vehicles/special-vehicles">Special Vehicles</Link></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <Link href="/pages">Pages</Link>
                        <ul>
                            <li><Link href="/testimonials">Testimonials</Link></li>
                            <li><Link href="/faq">FAQ</Link></li>
                            <li><Link href="/terms">Terms &amp; Conditions</Link></li>
                            <li><Link href="/privacy">Privacy Policy</Link></li>
                        </ul>
                    </li>
                    <li><Link href="/booking">Booking</Link></li>
                    <li><Link href="/contact">Contact</Link></li>
                </ul>
            </nav>

            <div class="main-header__right">
                <button
                    class="mobile-nav__btn mobile-nav__toggler"
                    aria-label="Open mobile menu"
                    aria-expanded={mobileNavOpen}
                    onclick={() => (mobileNavOpen = true)}
                >
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                {#if auth?.user}
                    <div class="main-header__btn ml-2">
                        <a href="/dashboard" class="travhub-btn"><span>Dashboard</span></a>
                    </div>
                {:else}
                    <div class="main-header__btn ml-2">
                        <a href="/login" class="travhub-btn"><span>Log in</span></a>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</header>

<!-- ==================== Mobile Nav Drawer ==================== -->
<div class="mobile-nav__wrapper" class:expanded={mobileNavOpen}>
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div class="mobile-nav__overlay" onclick={() => (mobileNavOpen = false)}></div>

    <div class="mobile-nav__content">
        <button
            class="mobile-nav__close"
            aria-label="Close mobile menu"
            onclick={() => (mobileNavOpen = false)}
        >
            <i class="fa fa-times"></i>
        </button>

        <div class="logo-box">
            <a href="/" style="display: flex; align-items: center; gap: 10px;">
                <img
                    src="/assets/images/siwride_logo.png"
                    width="48"
                    alt="Siwride"
                    style="border-radius: 50%; background: white; padding: 2px;"
                />
                <span style="font-size: 22px; font-weight: 700;">SIWRIDE</span>
            </a>
        </div>

        <div class="mobile-nav__container">
            <ul class="main-menu__list">
                <li>
                    <a href="/" onclick={() => (mobileNavOpen = false)}>Home</a>
                </li>
                <li class="dropdown">
                    <!-- svelte-ignore a11y_invalid_attribute -->
                    <a href="#" onclick={toggleSubMenu}>
                        About <i class="fa fa-angle-down"></i>
                    </a>
                    <ul>
                        <li><a href="/about-us" onclick={() => (mobileNavOpen = false)}>About Us</a></li>
                        <li><a href="/services" onclick={() => (mobileNavOpen = false)}>Our Services</a></li>
                        <li><a href="/area-coverage" onclick={() => (mobileNavOpen = false)}>Area Coverage</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <!-- svelte-ignore a11y_invalid_attribute -->
                    <a href="#" onclick={toggleSubMenu}>
                        Vehicles <i class="fa fa-angle-down"></i>
                    </a>
                    <ul>
                        <li><a href="/vehicles/standard-cars" onclick={() => (mobileNavOpen = false)}>Standard Cars</a></li>
                        <li><a href="/vehicles/premium-cars" onclick={() => (mobileNavOpen = false)}>Premium Cars</a></li>
                        <li><a href="/vehicles/vans-minibuses" onclick={() => (mobileNavOpen = false)}>Vans/Minibuses</a></li>
                        <li><a href="/vehicles/buses" onclick={() => (mobileNavOpen = false)}>Buses</a></li>
                        <li><a href="/vehicles/special-vehicles" onclick={() => (mobileNavOpen = false)}>Special Vehicles</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <!-- svelte-ignore a11y_invalid_attribute -->
                    <a href="#" onclick={toggleSubMenu}>
                        Pages <i class="fa fa-angle-down"></i>
                    </a>
                    <ul>
                        <li><a href="/testimonials" onclick={() => (mobileNavOpen = false)}>Testimonials</a></li>
                        <li><a href="/faq" onclick={() => (mobileNavOpen = false)}>FAQ</a></li>
                        <li><a href="/terms" onclick={() => (mobileNavOpen = false)}>Terms &amp; Conditions</a></li>
                        <li><a href="/privacy" onclick={() => (mobileNavOpen = false)}>Privacy Policy</a></li>
                    </ul>
                </li>
                <li>
                    <a href="/booking" onclick={() => (mobileNavOpen = false)}>Booking</a>
                </li>
                <li>
                    <a href="/contact" onclick={() => (mobileNavOpen = false)}>Contact</a>
                </li>
            </ul>
        </div>

        <!-- Login / Dashboard button -->
        <div class="mobile-nav__btn-wrap">
            {#if auth?.user}
                <a href="/dashboard" class="travhub-btn" style="width: 100%; justify-content: center; display: flex;" onclick={() => (mobileNavOpen = false)}>
                    <span>Dashboard</span>
                </a>
            {:else}
                <a href="/login" class="travhub-btn" style="width: 100%; justify-content: center; display: flex;" onclick={() => (mobileNavOpen = false)}>
                    <span>Log in</span>
                </a>
            {/if}
        </div>

        <ul class="mobile-nav__contact list-unstyled">
            <li>
                <i class="fa fa-envelope"></i>
                <a href="mailto:info@siwride.com">info@siwride.com</a>
            </li>
            <li>
                <i class="fa fa-phone-alt"></i>
                <a href="tel:+6281234567890">+62 812-3456-7890</a>
            </li>
        </ul>

        <div class="mobile-nav__social">
            <a href="https://facebook.com" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-facebook-f" aria-hidden="true"></i>
                <span class="sr-only">Facebook</span>
            </a>
            <a href="https://instagram.com" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-instagram" aria-hidden="true"></i>
                <span class="sr-only">Instagram</span>
            </a>
            <a href="https://twitter.com" target="_blank" rel="noopener noreferrer">
                <i class="fab fa-twitter" aria-hidden="true"></i>
                <span class="sr-only">Twitter</span>
            </a>
        </div>
    </div>
</div>

<style>
    /* ── Sticky header ── */
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
        border-radius: 0 0 30px 15px;
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

    :global(.main-header) { transition: all 0.3s ease !important; }
    :global(.main-header .main-header__logo img) { transition: all 0.3s ease !important; }
    :global(.main-header .main-header__logo span) { transition: all 0.3s ease !important; }
    :global(.main-header .main-menu__list > li > a) { transition: all 0.3s ease !important; }
    :global(.main-header .travhub-btn) { transition: all 0.3s ease !important; }

    /* ── Burger button reset ── */
    :global(.mobile-nav__btn.mobile-nav__toggler) {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 4px;
    }

    /* ── Close button reset ── */
    :global(.mobile-nav__close) {
        background: transparent;
        border: none;
        cursor: pointer;
        font-size: 22px;
        position: absolute;
        top: 18px;
        right: 18px;
        color: var(--travhub-black, #222);
        line-height: 1;
        z-index: 10;
    }

    /* ── Mobile nav accordion ── */
    :global(.mobile-nav__container .main-menu__list .dropdown > ul) {
        display: none;
        padding-left: 16px;
        padding-top: 4px;
    }

    :global(.mobile-nav__container .main-menu__list .dropdown.open > ul) {
        display: block;
    }

    :global(.mobile-nav__container .main-menu__list > li > a) {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.12);
        color: #ffffff;
        font-weight: 600;
        font-size: 16px;
        text-decoration: none;
        transition: color 0.2s;
    }

    :global(.mobile-nav__container .main-menu__list .dropdown > ul li a) {
        display: block;
        padding: 9px 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        color: rgba(255, 255, 255, 0.75);
        font-size: 15px;
        text-decoration: none;
        transition: color 0.2s;
    }

    :global(.mobile-nav__container .main-menu__list li a:hover) {
        color: var(--travhub-base, #e52029);
    }

    :global(.mobile-nav__container .main-menu__list .dropdown.open > a .fa-angle-down) {
        transform: rotate(180deg);
    }

    :global(.mobile-nav__container .main-menu__list .dropdown > a .fa-angle-down) {
        transition: transform 0.2s;
    }

    /* ── Mobile nav wrapper z-index (above navbar) ── */
    :global(.mobile-nav__wrapper) {
        z-index: 99999 !important;
    }

    :global(.mobile-nav__content) {
        z-index: 99999 !important;
    }

    /* ── Login button in sidebar ── */
    :global(.mobile-nav__btn-wrap) {
        margin: 20px 0 10px;
    }
</style>
