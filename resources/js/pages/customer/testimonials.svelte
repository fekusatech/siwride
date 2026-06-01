<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
    const testimonials = $derived(settings.customer_testimonials || []);
</script>

<AppHead title="Testimonials - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Testimonials</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Testimonials</span></li>
            </ul>
        </div>
    </section>

    <!-- Testimonials Grid -->
    <section
        class="testimonials-page pt-120 pb-120"
        style="background-color: #f8fafd; position: relative;"
    >
        <!-- Background accents -->
        <div
            class="testimonials-one__bg"
            style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(circle at 20% 30%, rgba(229, 32, 41, 0.03) 0%, transparent 40%), radial-gradient(circle at 80% 70%, rgba(229, 32, 41, 0.03) 0%, transparent 40%); pointer-events: none;"
        ></div>

        <div class="container" style="position: relative; z-index: 2;">
            <div class="sec-title text-center mb-5 pb-3">
                <div
                    class="sec-title__tagline bw-split-in-right"
                    style="justify-content: center;"
                >
                    Trusted by Travelers<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Real Stories from Our Clients
                </h3>
            </div>

            <div class="row gutter-y-30">
                {#each testimonials as testimonial, i}
                    <div
                        class="col-lg-4 col-md-6 wow fadeInUp"
                        data-wow-delay="{i * 100}ms"
                    >
                        <div
                            class="testimonials-card"
                            style="background: #fff; padding: 40px; border-radius: 20px; box-shadow: 0 8px 10px rgba(0,0,0,0.06); border: 1px solid rgba(0,0,0,0.03); height: 100%; display: flex; flex-direction: column; transition: 0.3s;"
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

            <div
                class="text-center mt-5 pt-4 wow fadeInUp"
                data-wow-delay="700ms"
            >
                <a href="/booking" class="travhub-btn">
                    <span>Experience It Yourself</span>
                </a>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    /* Intentionally empty - styles managed via inline/global CSS */
</style>
