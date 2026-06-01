<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
    const faqs = $derived(settings.faq_items || []);

    let activeIndex = $state(0);
</script>

<AppHead title="FAQ - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">
                Frequently Asked Questions
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>FAQ</span></li>
            </ul>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section pt-120 pb-120">
        <div class="container">
            <div class="faq-section__wrapper">
                <div class="row justify-content-center">
                    <div
                        class="col-lg-10 col-xl-8 wow fadeInUp"
                        data-wow-delay="00ms"
                    >
                        <div class="sec-title text-center">
                            <div
                                class="sec-title__tagline bw-split-in-right"
                                style="justify-content: center;"
                            >
                                FAQ<img
                                    src="/assets/images/shapes/sec-title-shape.png"
                                    alt="Travhub"
                                />
                            </div>
                            <h3 class="sec-title__title bw-split-in-left">
                                Some Questions <br />About Us
                            </h3>
                            <p
                                class="mt-3 text-muted"
                                style="max-width: 600px; margin: 0 auto;"
                            >
                                Got questions? We've got answers! Browse through
                                our most frequently asked questions to learn
                                everything you need to know about Siwride's
                                services.
                            </p>
                        </div>
                        <div
                            class="destination-details__accordion travhub-accrodion mt-5"
                        >
                            {#each faqs as faq, i}
                                <div
                                    class="accrodion {activeIndex === i
                                        ? 'active'
                                        : ''}"
                                >
                                    <!-- svelte-ignore a11y_click_events_have_key_events -->
                                    <div
                                        class="accrodion-title"
                                        role="button"
                                        tabindex="0"
                                        onclick={() =>
                                            (activeIndex =
                                                activeIndex === i ? -1 : i)}
                                    >
                                        <h4>
                                            {faq.question}
                                            <span class="accrodion-title__icon"
                                            ></span>
                                        </h4>
                                    </div>
                                    {#if activeIndex === i}
                                        <div
                                            class="accrodion-content"
                                            style="display: block;"
                                        >
                                            <div class="inner">
                                                <p>{faq.answer}</p>
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            {/each}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* FAQ Specific Tweaks */
    .accrodion-title {
        cursor: pointer;
    }
</style>
