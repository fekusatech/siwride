<script lang="ts">
    export let auth: any = null;
</script>

<header class="main-header sticky-header sticky-header--normal">
    <div class="container">
        <div class="main-header__inner">
            <div class="main-header__logo">
                <a href="/">
                    <img
                        src="assets/images/logo-1.png"
                        alt="Travhub"
                        width="152"
                    />
                </a>
            </div>
            <nav class="main-header__nav main-menu">
                <ul class="main-menu__list">
                    <li class="dropdown">
                        <a href="/">Home</a>
                        <ul>
                            <li><a href="/">Home One</a></li>
                            <li><a href="index2.html">Home Two</a></li>
                            <li><a href="index3.html">Home Three</a></li>
                        </ul>
                    </li>
                    <li>
                        <a href="about.html">About</a>
                    </li>
                    <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>Pages</a
                        >
                        <ul>
                            <li><a href="team.html">Our Team</a></li>
                            <li><a href="pricing.html">Pricing Table</a></li>
                            <li><a href="gallery.html">Gallery</a></li>
                            <li><a href="faq.html">FAQs</a></li>
                            <li><a href="login.html">Login</a></li>
                            <li><a href="404.html">404 Error</a></li>
                        </ul>
                    </li>
                    <li class="dropdown">
                        <a
                            href="#/"
                            role="button"
                            aria-haspopup="true"
                            onclick={(e) => e.preventDefault()}>Tours</a
                        >
                        <ul>
                            <li><a href="tour.html">Tour Page</a></li>
                            <li>
                                <a href="tour-sidebar.html">Tour Sidebar</a>
                            </li>
                            <li>
                                <a href="tour-carousel.html">Tour Carousel</a>
                            </li>
                            <li>
                                <a href="tour-details.html">Tour Details</a>
                            </li>
                        </ul>
                    </li>
                    <li class="dropdown">
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
                    </li>
                    <li class="dropdown">
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
