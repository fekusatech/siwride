<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { tourPackage } = $props<{
        tourPackage: {
            id: number;
            name: string;
            slug: string;
            description: string;
            highlights: string | null;
            price_per_pax: number;
            duration_hours: number;
            max_pax: number;
            min_pax: number;
            destinations: string[];
            itinerary: { time: string; activity: string; location: string }[];
            includes: string[];
            excludes: string[];
            image_url: string;
            gallery: string[] | null;
            is_active: boolean;
        };
    }>();

    const auth = $derived((page.props as any).auth);

    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }
</script>

<AppHead
    title="{tourPackage.name} | Tour Packages | Siwride"
/>

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header {auth} />

    <!-- ==================== Hero Image ==================== -->
    <div
        style="position: relative; height: 420px; overflow: hidden; background: linear-gradient(135deg, #0f172a, #1e293b);"
    >
        {#if tourPackage.image_url && !tourPackage.image_url.includes('tour-default')}
            <img
                src={tourPackage.image_url}
                alt={tourPackage.name}
                style="width: 100%; height: 100%; object-fit: cover; opacity: 0.55;"
            />
        {:else}
            <div
                style="width: 100%; height: 100%; background: linear-gradient(135deg, #78350f 0%, #f59e0b 50%, #b45309 100%); opacity: 0.4;"
            ></div>
        {/if}

        <!-- Overlay gradient -->
        <div
            style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.3) 60%, transparent 100%);"
        ></div>

        <!-- Hero content -->
        <div
            style="position: absolute; bottom: 0; left: 0; right: 0; padding: 40px 0;"
        >
            <div class="container">
                <!-- Breadcrumb -->
                <div
                    style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;"
                >
                    <a
                        href="/booking"
                        style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px; transition: color 0.2s;"
                        onmouseenter={(e) => (e.currentTarget.style.color = '#f59e0b')}
                        onmouseleave={(e) =>
                            (e.currentTarget.style.color =
                                'rgba(255,255,255,0.5)')}
                    >
                        Services
                    </a>
                    <span style="color: rgba(255,255,255,0.3);">›</span>
                    <a
                        href="/booking/tour"
                        style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px; transition: color 0.2s;"
                        onmouseenter={(e) => (e.currentTarget.style.color = '#f59e0b')}
                        onmouseleave={(e) =>
                            (e.currentTarget.style.color =
                                'rgba(255,255,255,0.5)')}
                    >
                        Tour Packages
                    </a>
                    <span style="color: rgba(255,255,255,0.3);">›</span>
                    <span style="color: rgba(255,255,255,0.7); font-size: 13px;"
                        >{tourPackage.name}</span
                    >
                </div>

                <h1
                    style="font-size: 36px; font-weight: 900; color: #fff; margin-bottom: 18px; line-height: 1.2; text-shadow: 0 2px 10px rgba(0,0,0,0.3);"
                >
                    {tourPackage.name}
                </h1>

                <!-- Quick stats -->
                <div
                    style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center;"
                >
                    <div
                        style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); backdrop-filter: blur(6px); padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.15);"
                    >
                        <i class="fas fa-clock" style="color: #f59e0b; font-size: 13px;"></i>
                        <span style="color: #fff; font-size: 13px; font-weight: 600;"
                            >{tourPackage.duration_hours} Hours</span
                        >
                    </div>
                    <div
                        style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); backdrop-filter: blur(6px); padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.15);"
                    >
                        <i class="fas fa-users" style="color: #f59e0b; font-size: 13px;"></i>
                        <span style="color: #fff; font-size: 13px; font-weight: 600;"
                            >Up to {tourPackage.max_pax} pax</span
                        >
                    </div>
                    <div
                        style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); backdrop-filter: blur(6px); padding: 8px 16px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.15);"
                    >
                        <i class="flaticon-map" style="color: #f59e0b; font-size: 13px;"></i>
                        <span style="color: #fff; font-size: 13px; font-weight: 600;"
                            >{tourPackage.destinations?.length || 0} destinations</span
                        >
                    </div>
                    <div
                        style="background: #f59e0b; padding: 8px 20px; border-radius: 50px; display: flex; align-items: center; gap: 6px;"
                    >
                        <span
                            style="color: #fff; font-size: 16px; font-weight: 800;"
                        >
                            {formatCurrency(tourPackage.price_per_pax)}
                        </span>
                        <span style="color: rgba(255,255,255,0.8); font-size: 12px;">/ pax</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== Main Content ==================== -->
    <section style="padding: 60px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="row gutter-y-30">
                <!-- Left — main info -->
                <div class="col-lg-8">

                    <!-- Description -->
                    <div
                        style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px;"
                    >
                        <h2
                            style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 16px; display: flex; align-items: center; gap: 10px;"
                        >
                            <i class="fas fa-info-circle" style="color: #f59e0b;"></i>
                            About This Tour
                        </h2>
                        <p
                            style="color: #475569; font-size: 15px; line-height: 1.8; margin: 0;"
                        >
                            {tourPackage.description}
                        </p>

                        {#if tourPackage.highlights}
                            <div
                                style="margin-top: 24px; padding: 20px; background: #fffbeb; border-radius: 12px; border-left: 4px solid #f59e0b;"
                            >
                                <p
                                    style="color: #92400e; font-size: 14px; line-height: 1.7; margin: 0;"
                                >
                                    <strong>✨ Highlights:</strong>
                                    {tourPackage.highlights}
                                </p>
                            </div>
                        {/if}
                    </div>

                    <!-- Destinations -->
                    {#if tourPackage.destinations && tourPackage.destinations.length > 0}
                        <div
                            style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px;"
                        >
                            <h2
                                style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;"
                            >
                                <i class="fas fa-map-marker-alt" style="color: #f59e0b;"></i>
                                Destinations
                            </h2>
                            <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                                {#each tourPackage.destinations as dest, i}
                                    <div
                                        style="display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #fffbeb, #fef3c7); border: 1px solid #fde68a; padding: 10px 18px; border-radius: 12px;"
                                    >
                                        <span
                                            style="width: 22px; height: 22px; background: #f59e0b; color: #fff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; flex-shrink: 0;"
                                        >
                                            {i + 1}
                                        </span>
                                        <span
                                            style="color: #92400e; font-size: 14px; font-weight: 700;"
                                        >
                                            {dest}
                                        </span>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}

                    <!-- Itinerary -->
                    {#if tourPackage.itinerary && tourPackage.itinerary.length > 0}
                        <div
                            style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px;"
                        >
                            <h2
                                style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 28px; display: flex; align-items: center; gap: 10px;"
                            >
                                <i class="fas fa-route" style="color: #f59e0b;"></i>
                                Tour Itinerary
                            </h2>

                            <div style="position: relative;">
                                <!-- Vertical line -->
                                <div
                                    style="position: absolute; left: 28px; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, #f59e0b, #fde68a, transparent);"
                                ></div>

                                {#each tourPackage.itinerary as item, idx}
                                    <div
                                        style="display: flex; gap: 20px; margin-bottom: {idx < tourPackage.itinerary.length - 1 ? '28px' : '0'}; position: relative;"
                                    >
                                        <!-- Timeline dot -->
                                        <div
                                            style="width: 56px; height: 56px; background: {idx === 0 || idx === tourPackage.itinerary.length - 1 ? '#f59e0b' : '#fff'}; border: 2px solid #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; z-index: 1; box-shadow: 0 4px 12px rgba(245,158,11,0.25);"
                                        >
                                            <span
                                                style="font-size: 12px; font-weight: 800; color: {idx === 0 || idx === tourPackage.itinerary.length - 1 ? '#fff' : '#f59e0b'}; white-space: nowrap;"
                                            >{item.time}</span>
                                        </div>

                                        <!-- Content -->
                                        <div
                                            style="flex: 1; background: #f8fafc; border-radius: 14px; padding: 16px 20px; border: 1px solid #e2e8f0;"
                                        >
                                            <p
                                                style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;"
                                            >
                                                {item.activity}
                                            </p>
                                            <p
                                                style="font-size: 12px; color: #94a3b8; margin: 0; display: flex; align-items: center; gap: 5px;"
                                            >
                                                <i class="fas fa-map-pin" style="font-size: 10px; color: #f59e0b;"></i>
                                                {item.location}
                                            </p>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}

                    <!-- Includes / Excludes -->
                    <div class="row gutter-y-20">
                        {#if tourPackage.includes && tourPackage.includes.length > 0}
                            <div class="col-md-6">
                                <div
                                    style="background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: 100%;"
                                >
                                    <h3
                                        style="font-size: 17px; font-weight: 800; color: #1e293b; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;"
                                    >
                                        <span
                                            style="width: 28px; height: 28px; background: #dcfce7; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="fas fa-check" style="font-size: 11px; color: #16a34a;"></i>
                                        </span>
                                        What's Included
                                    </h3>
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        {#each tourPackage.includes as item}
                                            <li
                                                style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #374151;"
                                            >
                                                <i class="fas fa-check-circle" style="color: #16a34a; font-size: 14px; flex-shrink: 0;"></i>
                                                {item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            </div>
                        {/if}

                        {#if tourPackage.excludes && tourPackage.excludes.length > 0}
                            <div class="col-md-6">
                                <div
                                    style="background: #fff; border-radius: 20px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); height: 100%;"
                                >
                                    <h3
                                        style="font-size: 17px; font-weight: 800; color: #1e293b; margin-bottom: 18px; display: flex; align-items: center; gap: 8px;"
                                    >
                                        <span
                                            style="width: 28px; height: 28px; background: #fee2e2; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="fas fa-times" style="font-size: 11px; color: #dc2626;"></i>
                                        </span>
                                        Not Included
                                    </h3>
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        {#each tourPackage.excludes as item}
                                            <li
                                                style="display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #374151;"
                                            >
                                                <i class="fas fa-times-circle" style="color: #dc2626; font-size: 14px; flex-shrink: 0;"></i>
                                                {item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Right — booking sidebar -->
                <div class="col-lg-4">
                    <div
                        style="position: sticky; top: 30px;"
                    >
                        <!-- Price Card -->
                        <div
                            style="background: linear-gradient(135deg, #0f172a, #1e293b); border-radius: 24px; padding: 36px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.08);"
                        >
                            <p
                                style="color: rgba(255,255,255,0.5); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; font-weight: 600;"
                            >
                                Price per person
                            </p>
                            <p
                                style="color: #f59e0b; font-size: 36px; font-weight: 900; margin-bottom: 4px; line-height: 1;"
                            >
                                {formatCurrency(tourPackage.price_per_pax)}
                            </p>
                            <p
                                style="color: rgba(255,255,255,0.4); font-size: 12px; margin-bottom: 28px;"
                            >
                                * Min {tourPackage.min_pax} pax, Max {tourPackage.max_pax} pax
                            </p>

                            <!-- Stats -->
                            <div
                                style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 28px;"
                            >
                                <div
                                    style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; text-align: center;"
                                >
                                    <i class="fas fa-clock" style="color: #f59e0b; font-size: 20px; margin-bottom: 6px;"></i>
                                    <p
                                        style="color: #fff; font-size: 16px; font-weight: 800; margin: 0;"
                                    >
                                        {tourPackage.duration_hours}h
                                    </p>
                                    <p
                                        style="color: rgba(255,255,255,0.4); font-size: 11px; margin: 0;"
                                    >
                                        Duration
                                    </p>
                                </div>
                                <div
                                    style="background: rgba(255,255,255,0.06); border-radius: 12px; padding: 14px; text-align: center;"
                                >
                                    <i class="fas fa-users" style="color: #f59e0b; font-size: 20px; margin-bottom: 6px;"></i>
                                    <p
                                        style="color: #fff; font-size: 16px; font-weight: 800; margin: 0;"
                                    >
                                        {tourPackage.max_pax}
                                    </p>
                                    <p
                                        style="color: rgba(255,255,255,0.4); font-size: 11px; margin: 0;"
                                    >
                                        Max Pax
                                    </p>
                                </div>
                            </div>

                            <!-- Book CTA -->
                            <a
                                href="/contact"
                                class="travhub-btn"
                                style="display: block; text-align: center; width: 100%; margin-bottom: 12px;"
                            >
                                <span>Book This Tour</span>
                            </a>
                            <p
                                style="color: rgba(255,255,255,0.35); font-size: 11px; text-align: center; margin: 0;"
                            >
                                Contact us via WhatsApp to confirm your booking
                            </p>
                        </div>

                        <!-- Info Card -->
                        <div
                            style="background: #fff; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);"
                        >
                            <h4
                                style="font-size: 15px; font-weight: 800; color: #1e293b; margin-bottom: 16px;"
                            >
                                ℹ️ Good to Know
                            </h4>
                            <ul
                                style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px;"
                            >
                                <li
                                    style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #475569;"
                                >
                                    <i class="fas fa-car" style="color: #f59e0b; margin-top: 2px; flex-shrink: 0;"></i>
                                    Air-conditioned private vehicle
                                </li>
                                <li
                                    style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #475569;"
                                >
                                    <i class="fas fa-id-card" style="color: #f59e0b; margin-top: 2px; flex-shrink: 0;"></i>
                                    Professional licensed driver
                                </li>
                                <li
                                    style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #475569;"
                                >
                                    <i class="fas fa-hotel" style="color: #f59e0b; margin-top: 2px; flex-shrink: 0;"></i>
                                    Hotel pickup & drop-off included
                                </li>
                                <li
                                    style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: #475569;"
                                >
                                    <i class="fas fa-calendar-alt" style="color: #f59e0b; margin-top: 2px; flex-shrink: 0;"></i>
                                    Available every day
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
