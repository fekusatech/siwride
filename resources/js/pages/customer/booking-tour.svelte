<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import { getMinPickupTime } from '@/lib/pickupTime';

    let { tours = [] } = $props<{
        tours: {
            id: number;
            name: string;
            description: string;
            price_per_pax: number;
            duration_hours: number;
            max_pax: number;
            image_url: string | null;
            destinations: string[];
        }[];
    }>();

    let selectedDate = $state('');
    let selectedPax = $state(1);
    let selectedTour = $state<number | null>(null);
    const minTime = $derived(getMinPickupTime(selectedDate));

    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }
</script>

<AppHead title="Tour Package | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- ==================== Simple Service Header ==================== -->
    <div class="service-simple-header" style="padding: 40px 0 10px; background: #f7f9fa; border-bottom: 1px solid #eaeef2;">
        <div class="container">
            <h2 style="font-size: 26px; font-weight: 800; color: #1e293b; margin: 0;">Tour Package Booking</h2>
            <p style="color: #64748b; margin: 6px 0 0; font-size: 15px;">Browse and book our curated Bali tour packages.</p>
        </div>
    </div>

    <!-- ==================== Tour Content ==================== -->
    <section style="padding: 40px 0 100px; background: #f7f9fa;">
        <div class="container">
            <!-- Info Banner -->
            <div
                class="wow fadeInUp"
                data-wow-duration="800ms"
                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 20px; padding: 35px 40px; margin-bottom: 50px; display: flex; align-items: center; gap: 25px; flex-wrap: wrap;"
            >
                <div
                    style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                >
                    <i class="flaticon-map" style="font-size: 32px; color: #fff;"></i>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <h3 style="color: #fff; font-size: 24px; font-weight: 800; margin-bottom: 6px;">
                        Curated Bali Tour Packages
                    </h3>
                    <p style="color: rgba(255,255,255,0.85); font-size: 15px; margin: 0; line-height: 1.6;">
                        Choose from our pre-planned itineraries to visit the most beautiful spots in
                        Bali. Fixed package price per person, includes driver and fuel.
                    </p>
                </div>
                <a
                    href="/contact"
                    style="background: rgba(255,255,255,0.2); color: #fff; border: 2px solid rgba(255,255,255,0.5); padding: 12px 28px; border-radius: 50px; font-weight: 700; text-decoration: none; white-space: nowrap; font-size: 15px; transition: background 0.3s ease;"
                    onmouseenter={(e) => (e.currentTarget.style.background = 'rgba(255,255,255,0.35)')}
                    onmouseleave={(e) => (e.currentTarget.style.background = 'rgba(255,255,255,0.2)')}
                >
                    Ask via WhatsApp
                </a>
            </div>

            <!-- Coming Soon / Placeholder if no tours -->
            {#if tours.length === 0}
                <div
                    style="text-align: center; background: #fff; border-radius: 20px; padding: 80px 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);"
                >
                    <div
                        style="width: 100px; height: 100px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 28px;"
                    >
                        <i class="flaticon-map" style="font-size: 45px; color: #f59e0b;"></i>
                    </div>
                    <h3 style="font-size: 26px; font-weight: 800; margin-bottom: 14px; color: #1e293b;">
                        Tour Packages Coming Soon
                    </h3>
                    <p style="color: #64748b; font-size: 16px; max-width: 500px; margin: 0 auto 30px; line-height: 1.7;">
                        We are currently preparing our curated Bali tour packages with exciting
                        itineraries. Contact us to get a custom tour package tailored to your
                        preferences.
                    </p>
                    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                        <a href="/contact" class="travhub-btn">
                            <span>Request a Custom Tour</span>
                        </a>
                        <a
                            href="/booking"
                            style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 30px; border: 2px solid #e2e8f0; border-radius: 50px; font-weight: 600; color: #475569; text-decoration: none; font-size: 15px; transition: all 0.3s ease;"
                            onmouseenter={(e) => {
                                e.currentTarget.style.borderColor = 'var(--travhub-base, #e52029)';
                                e.currentTarget.style.color = 'var(--travhub-base, #e52029)';
                            }}
                            onmouseleave={(e) => {
                                e.currentTarget.style.borderColor = '#e2e8f0';
                                e.currentTarget.style.color = '#475569';
                            }}
                        >
                            ← Other Services
                        </a>
                    </div>
                </div>
            {:else}
                <!-- Tour Cards Grid -->
                <div class="row gutter-y-30">
                    {#each tours as tour, i}
                        <div
                            class="col-lg-6 wow fadeInUp"
                            data-wow-duration="1000ms"
                            data-wow-delay="{i * 100}ms"
                        >
                            <div
                                class="tour-card"
                                style="background: #fff; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 25px rgba(0,0,0,0.07); transition: transform 0.3s ease; border: 1px solid #f0f0f0; height: 100%;"
                                onmouseenter={(e) => (e.currentTarget.style.transform = 'translateY(-6px)')}
                                onmouseleave={(e) => (e.currentTarget.style.transform = 'translateY(0)')}
                                role="presentation"
                            >
                                <!-- Tour Image -->
                                <div
                                    style="height: 220px; background: {tour.image_url ? 'none' : 'linear-gradient(135deg, #f59e0b, #d97706)'}; overflow: hidden; position: relative;"
                                >
                                    {#if tour.image_url}
                                        <img
                                            src={tour.image_url}
                                            alt={tour.name}
                                            style="width: 100%; height: 100%; object-fit: cover;"
                                        />
                                    {:else}
                                        <div
                                            style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="flaticon-map" style="font-size: 60px; color: rgba(255,255,255,0.5);"></i>
                                        </div>
                                    {/if}
                                    <!-- Duration Badge -->
                                    <div
                                        style="position: absolute; top: 16px; right: 16px; background: rgba(0,0,0,0.65); color: #fff; padding: 5px 14px; border-radius: 50px; font-size: 13px; font-weight: 600; backdrop-filter: blur(4px);"
                                    >
                                        <i class="flaticon-time" style="margin-right: 5px; font-size: 12px;"></i>
                                        {tour.duration_hours}h
                                    </div>
                                </div>

                                <!-- Tour Content -->
                                <div style="padding: 28px;">
                                    <h4 style="font-size: 20px; font-weight: 800; margin-bottom: 10px; color: #1e293b;">
                                        {tour.name}
                                    </h4>
                                    <p style="color: #64748b; font-size: 14px; line-height: 1.65; margin-bottom: 18px;">
                                        {tour.description}
                                    </p>

                                    <!-- Destinations -->
                                    {#if tour.destinations && tour.destinations.length > 0}
                                        <div style="margin-bottom: 20px;">
                                            <p style="font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #94a3b8; margin-bottom: 10px;">
                                                Destinations
                                            </p>
                                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                                {#each tour.destinations as dest}
                                                    <span
                                                        style="background: #fffbeb; color: #d97706; font-size: 12px; font-weight: 600; padding: 4px 12px; border-radius: 50px; border: 1px solid #fde68a;"
                                                    >
                                                        📍 {dest}
                                                    </span>
                                                {/each}
                                            </div>
                                        </div>
                                    {/if}

                                    <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 18px; border-top: 1px solid #f1f5f9;">
                                        <div>
                                            <p style="font-size: 12px; color: #94a3b8; margin-bottom: 2px;">
                                                Price per person
                                            </p>
                                            <p style="font-size: 22px; font-weight: 800; color: #f59e0b; margin: 0;">
                                                {formatCurrency(tour.price_per_pax)}
                                            </p>
                                        </div>
                                        <button class="travhub-btn" type="button">
                                            <span>Book Now</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </section>

    <Footer />
</div>
