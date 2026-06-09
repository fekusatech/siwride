<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { services = [] } = $props<{ services: any[] }>();
</script>

<AppHead title="Choose Your Service | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- ==================== Page Header ==================== -->
    <!-- <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Book a Service</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li>Booking</li>
            </ul>
        </div>
    </section> -->

    <!-- ==================== Service Selection ==================== -->
    <section class="service-selection" style="padding: 90px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="sec-title text-center" style="margin-bottom: 55px;">
                <div class="sec-title__tagline bw-split-in-right">
                    What Are You Looking For?<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">Choose Your Service</h3>
                <p
                    class="sec-title__text bw-split-in-up-fast"
                    style="max-width: 600px; margin: 15px auto 0;"
                >
                    Siwride offers four types of transportation services across Bali. Select the one
                    that best fits your travel needs.
                </p>
            </div>

            <div class="row gutter-y-30">
                {#each services as service, i}
                    <div
                        class="col-lg-6 wow fadeInUp"
                        data-wow-duration="1000ms"
                        data-wow-delay="{i * 150}ms"
                    >
                        <a
                            href={service.href || '#'}
                            class="service-card"
                            style="background: #fff; border-radius: 20px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.07); text-decoration: none; transition: transform 0.35s ease, box-shadow 0.35s ease; border: 1px solid #f0f0f0; height: 100%;"
                            onmouseenter={(e) => {
                                e.currentTarget.style.transform = 'translateY(-8px)';
                                e.currentTarget.style.boxShadow = '0 20px 50px rgba(0,0,0,0.12)';
                            }}
                            onmouseleave={(e) => {
                                e.currentTarget.style.transform = 'translateY(0)';
                                e.currentTarget.style.boxShadow = '0 8px 30px rgba(0,0,0,0.07)';
                            }}
                        >
                            <!-- Card Top Image Banner -->
                            <div
                                class="service-card__banner"
                                style="background-image: url({service.image_url}); background-size: cover; background-position: center; padding: 32px 35px 28px; position: relative; overflow: hidden; min-height: 200px;"
                            >
                                <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: linear-gradient(to bottom, rgba(0,0,0,0.1) 0%, rgba(0,0,0,0.85) 100%);"></div>

                                <div style="display: flex; align-items: flex-end; justify-content: space-between; position: relative; z-index: 1; height: 100%;">
                                    <div style="align-self: flex-end;">
                                        <h4
                                            style="color: #fff; font-size: 26px; font-weight: 800; margin-bottom: 4px; letter-spacing: -0.5px;"
                                        >
                                            {service.title}
                                        </h4>
                                        {#if service.subtitle}
                                        <p style="color: rgba(255,255,255,0.9); font-size: 14px; font-weight: 500; margin: 0;">
                                            {service.subtitle}
                                        </p>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div style="padding: 28px 35px 32px; display: flex; flex-direction: column; flex-grow: 1;">
                                <div style="display: flex; align-items: flex-start; margin-bottom: 15px;">
                                    <p style="color: #555; font-size: 15px; line-height: 1.6; margin: 0;">
                                        {service.description}
                                    </p>
                                </div>

                                <div style="flex-grow: 1;"></div>

                                <!-- CTA -->
                                <div
                                    style="display: flex; align-items: center; justify-content: space-between; margin-top: auto; border-top: 1px solid #f0f0f0; padding-top: 20px;"
                                >
                                    <span
                                        style="font-size: 16px; font-weight: 700; color: var(--travhub-base, #e52029); letter-spacing: 0.2px;"
                                    >
                                        Book Now →
                                    </span>
                                    <div
                                        style="width: 42px; height: 42px; border-radius: 50%; background: #fff5f5; display: flex; align-items: center; justify-content: center; transition: background 0.3s ease;"
                                    >
                                        <i
                                            class="flaticon-top-right"
                                            style="font-size: 18px; color: var(--travhub-base, #e52029);"
                                        ></i>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                {/each}
            </div>

            <!-- Help Text -->
            <div
                class="text-center"
                style="margin-top: 55px; padding: 30px; background: #fff; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);"
            >
                <p style="color: #666; font-size: 15px; margin-bottom: 8px;">
                    <i class="fas fa-question-circle" style="color: var(--travhub-base, #e52029); margin-right: 6px;"></i>
                    Not sure which service to pick?
                </p>
                <p style="color: #888; font-size: 14px; margin: 0;">
                    <a href="/contact" style="color: var(--travhub-base, #e52029); font-weight: 600;">Contact us</a> and we'll help you choose the right service.
                </p>
            </div>
        </div>
    </section>

    <Footer />
</div>
