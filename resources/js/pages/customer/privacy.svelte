<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);
</script>

<AppHead title="Privacy Policy - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Privacy Policy</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Privacy</span></li>
            </ul>
        </div>
    </section>

    <!-- Legal Content Section -->
    <section class="legal-section pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div
                    class="col-lg-10 col-xl-8 wow fadeInUp"
                    data-wow-delay="100ms"
                >
                    <div
                        class="legal-content"
                        style="background: #fff; padding: 50px; border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.05); border: 1px solid #f1f1f1;"
                    >
                        <div
                            class="text-center mb-5 pb-3"
                            style="border-bottom: 1px solid #eee;"
                        >
                            <h3
                                style="font-size: 32px; font-weight: 700; margin-bottom: 15px;"
                            >
                                Siwride Privacy Policy
                            </h3>
                            <!-- <p style="color: #666; font-size: 15px;">
                                Last Updated: October 2024
                            </p> -->
                        </div>

                        <div class="legal-text-block">
                            {@html settings.privacy_content}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .legal-text-block h4 {
        font-size: 22px;
        font-weight: 700;
        color: #222;
    }
    .legal-text-block p {
        color: #666;
        line-height: 1.8;
        font-size: 16px;
    }
</style>
