<script lang="ts">
    import { page } from '@inertiajs/svelte';
    const settings = $derived(page.props.settings as any);
</script>

<footer class="main-footer">
    <div
        class="main-footer__shape-one"
        style="background-image: url(/assets/images/shapes/footer-shape-1.png);"
    ></div>
    <div class="container" style="padding-top: 50px;">
        <!-- <div class="main-footer__top wow fadeInUp" data-wow-delay="100ms">
            <div class="main-footer__top__dot"></div>
            <div
                style="display: flex; justify-content: space-between; align-items: center; width: 100%;"
            >
                <div style="flex: 1;">
                    <h3 class="main-footer__top__title">
                        Get it on Google Play
                        <img
                            src="/assets/images/shapes/sec-title-shape.png"
                            alt="Siwride"
                        />
                    </h3>
                </div>
                <div
                    style="flex: 1; display: flex; justify-content: flex-end; align-items: center;"
                >
                    <form
                        onsubmit={(e) => e.preventDefault()}
                        data-url="MAILCHIMP_FORM_URL"
                        class="main-footer__newsletter mc-form"
                    >
                        <a
                            href="#"
                            class="travhub-btn"
                            style="background: #4285f4; border-color: #4285f4; padding: 12px 30px; font-size: 16px; display: inline-flex; align-items: center; text-decoration: none;"
                        >
                            <i
                                class="fab fa-google-play"
                                style="margin-right: 10px; font-size: 20px;"
                            ></i>
                            <span>Google Play</span>
                        </a>
                    </form>
                </div>
            </div>
            <div class="mc-form__response"></div>
        </div> -->
        <div class="row">
            <div class="col-md-6 col-xl-4 wow fadeInUp" data-wow-delay="00ms">
                <div class="footer-widget footer-widget--about">
                    <a
                        href="/"
                        class="footer-widget__logo"
                        style="display: flex; align-items: center; gap: 12px;"
                    >
                        <img
                            src={settings?.logo || "/assets/images/siwride_logo.png"}
                            width="50"
                            alt={settings?.business_name || "Siwride"}
                            style="border-radius: 50%; background: white; padding: 2px;"
                        />
                        <span
                            style="color: white; font-size: 24px; font-weight: 700;"
                            >{settings?.business_name || "SIWRIDE"}</span
                        >
                    </a>
                    <p class="footer-widget__text">
                        {settings.company_description || 'Your premium ride partner in Bali. Safe, reliable, and comfortable transportation solutions.'}
                    </p>
                    <div class="main-footer__social">
                        {#if settings.company_facebook}
                        <a href={settings.company_facebook}>
                            <i class="fab fa-facebook-f" aria-hidden="true"></i>
                            <span class="sr-only">Facebook</span>
                        </a>
                        {/if}
                        {#if settings.company_twitter}
                        <a href={settings.company_twitter}>
                            <i class="fab fa-twitter" aria-hidden="true"></i>
                            <span class="sr-only">Twitter</span>
                        </a>
                        {/if}
                        {#if settings.company_linkedin}
                        <a href={settings.company_linkedin}>
                            <i class="fab fa-linkedin-in" aria-hidden="true"
                            ></i>
                            <span class="sr-only">Linkedin</span>
                        </a>
                        {/if}
                        {#if settings.company_instagram}
                        <a href={settings.company_instagram}>
                            <i class="fab fa-instagram" aria-hidden="true"></i>
                            <span class="sr-only">Instagram</span>
                        </a>
                        {/if}
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 wow fadeInUp" data-wow-delay="100ms">
                <div class="footer-widget footer-widget--links">
                    <h2 class="footer-widget__title">Useful Links</h2>
                    <ul class="list-unstyled footer-widget__links">
                        <li><a href="/">Home</a></li>
                        <li><a href="/about">About us</a></li>
                        <li><a href="/vehicles">Vehicles</a></li>
                        <li><a href="/booking">Book Ride</a></li>
                        <li><a href="/contact">Contact Us</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-xl-2 wow fadeInUp" data-wow-delay="200ms">
                <div class="footer-widget footer-widget--links-two">
                    <h2 class="footer-widget__title">Legal</h2>
                    <ul class="list-unstyled footer-widget__links">
                        <li><a href="/terms">Terms of use</a></li>
                        <li><a href="/privacy">Privacy policy</a></li>
                        <li><a href="/cookie">Cookie policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="col-md-6 col-xl-4 wow fadeInUp" data-wow-delay="300ms">
                <div class="footer-widget footer-widget--contact">
                    <h2 class="footer-widget__title">Contact Info</h2>
                    <ul class="list-unstyled footer-widget__info">
                        <li
                            style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;"
                        >
                            <div
                                class="footer-widget__info__icon"
                                style="margin-bottom: 0; color: RGBA(var(--travhub-white-rgb), 0.5);"
                            >
                                <i class="fa fa-phone-alt"></i>
                            </div>
                            <div
                                class="footer-widget__info__content"
                                style="margin-bottom: 0;"
                            >
                                <span
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >Hotline:</span
                                >
                                <a
                                    href="tel:{settings.company_phone || '+62812345678'}"
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >{settings.company_phone || '+62 812-3456-78'}</a
                                >
                            </div>
                        </li>
                        <li
                            style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;"
                        >
                            <div
                                class="footer-widget__info__icon"
                                style="margin-bottom: 0; color: RGBA(var(--travhub-white-rgb), 0.5);"
                            >
                                <i class="fa fa-envelope"></i>
                            </div>
                            <div
                                class="footer-widget__info__content"
                                style="margin-bottom: 0;"
                            >
                                <span
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >Email:</span
                                >
                                <a
                                    href="mailto:{settings.company_email || 'info@siwride.com'}"
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >{settings.company_email || 'info@siwride.com'}</a
                                >
                            </div>
                        </li>
                        <li
                            style="display: flex; align-items: center; gap: 12px; margin-bottom: 0;"
                        >
                            <div
                                class="footer-widget__info__icon"
                                style="margin-bottom: 0; color: RGBA(var(--travhub-white-rgb), 0.5);"
                            >
                                <i class="fa fa-map-marker-alt"></i>
                            </div>
                            <div
                                class="footer-widget__info__content"
                                style="margin-bottom: 0;"
                            >
                                <span
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >Address:</span
                                >
                                <span
                                    style="color: RGBA(var(--travhub-white-rgb), 0.5);"
                                    >{settings.company_address || 'Bali, Indonesia'}</span
                                >
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="main-footer__bottom wow fadeInUp" data-wow-delay="00ms">
        <div class="container">
            <div class="main-footer__bottom__inner">
                <p class="main-footer__copyright__text">
                    &copy; Copyright <span class="dynamic-year"></span>
                    <a href="/">{settings?.business_name || "Siwride"}</a>.
                </p>
            </div>
        </div>
    </div>
</footer>

<div class="mobile-nav__wrapper">
    <div class="mobile-nav__overlay mobile-nav__toggler"></div>
    <div class="mobile-nav__content">
        <span class="mobile-nav__close mobile-nav__toggler"
            ><i class="fa fa-times"></i></span
        >
        <div class="logo-box">
            <a href="/" aria-label="logo image"
                ><img
                    src="/assets/images/siwride_logo.png"
                    width="155"
                    alt="Siwride"
                /></a
            >
        </div>
        <div class="mobile-nav__container"></div>

        <ul class="mobile-nav__contact list-unstyled">
            <li>
                <i class="fa fa-envelope"></i>
                <a href="mailto:{settings.company_email || 'info@siwride.com'}">{settings.company_email || 'info@siwride.com'}</a>
            </li>
            <li>
                <i class="fa fa-phone-alt"></i>
                <a href="tel:{settings.company_phone || '+62812345678'}">{settings.company_phone || '+62 812-3456-78'}</a>
            </li>
        </ul>
        <div class="mobile-nav__social">
            {#if settings.company_facebook}
            <a href={settings.company_facebook}>
                <i class="fab fa-facebook-f" aria-hidden="true"></i>
                <span class="sr-only">Facebook</span>
            </a>
            {/if}
            {#if settings.company_twitter}
            <a href={settings.company_twitter}>
                <i class="fab fa-twitter" aria-hidden="true"></i>
                <span class="sr-only">Twitter</span>
            </a>
            {/if}
            {#if settings.company_linkedin}
            <a href={settings.company_linkedin}>
                <i class="fab fa-linkedin-in" aria-hidden="true"></i>
                <span class="sr-only">Linkedin</span>
            </a>
            {/if}
            {#if settings.company_instagram}
            <a href={settings.company_instagram}>
                <i class="fab fa-instagram" aria-hidden="true"></i>
                <span class="sr-only">Instagram</span>
            </a>
            {/if}
        </div>
    </div>
</div>

<div class="search-popup">
    <div class="search-popup__overlay search-toggler"></div>
    <div class="search-popup__content">
        <form role="search" method="get" class="search-popup__form" action="#">
            <input type="text" id="search" placeholder="Search Here..." />
            <button type="submit" aria-label="search submit">
                <span><i class="flaticon-search"></i></span>
            </button>
        </form>
    </div>
</div>

<a href="#top" class="scroll-to-top" aria-label="Scroll to top">
    <svg
        class="scroll-to-top__circle"
        width="100%"
        height="100%"
        viewBox="-1 -1 102 102"
    >
        <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" />
    </svg>
</a>
