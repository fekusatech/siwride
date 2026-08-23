<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { driver, services } = $props<{
        driver: {
            id: number;
            name: string;
            image: string | null;
            joined_at: string | null;
            total_services: number;
            completed_bookings: number;
        };
        services: {
            slug: string;
            title: string;
            description: string;
            image_url: string;
            price_per_pax: number | null;
            duration_label: string | null;
            min_pax: number | null;
            max_pax: number | null;
            is_featured: boolean;
            url: string;
        }[];
    }>();

    const settings = $derived(page.props.settings as any);

    const PLACEHOLDER_IMAGE =
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23e9ecef'/%3E%3Cpath d='M160 120a20 20 0 1 1 0-40 20 20 0 0 1 0 40Zm-40 100 60-70 40 45 30-35 70 60H120Z' fill='%23adb5bd'/%3E%3C/svg%3E";

    function formatPrice(price: number): string {
        return `Rp ${price.toLocaleString('id-ID')}`;
    }
</script>

<AppHead title={`${driver.name} - Siwride Driver`} />

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
            <h2 class="page-header__title bw-split-in-right">{driver.name}</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Drivers</span></li>
                <li><span>{driver.name}</span></li>
            </ul>
        </div>
    </section>

    <!-- Driver Info -->
    <section class="pt-60 pb-0">
        <div class="container">
            <div
                class="d-flex flex-column flex-md-row align-items-center gap-4 p-4 rounded"
                style="background: #fff; box-shadow: 0 10px 30px rgba(0,0,0,0.05);"
            >
                <img
                    src={driver.image || PLACEHOLDER_IMAGE}
                    alt={driver.name}
                    class="rounded-circle"
                    style="width: 110px; height: 110px; object-fit: cover; border: 4px solid var(--travhub-base, #e52029);"
                />
                <div class="text-center text-md-start flex-grow-1">
                    <h3 class="mb-1 fw-bold">{driver.name}</h3>
                    <p class="text-muted mb-2">
                        <i class="ti ti-steering-wheel me-1"></i>Driver Siwride
                        {#if driver.joined_at}
                            <span class="ms-3"
                                ><i class="ti ti-calendar me-1"></i>Bergabung {driver.joined_at}</span
                            >
                        {/if}
                    </p>
                    <div
                        class="d-flex gap-3 justify-content-center justify-content-md-start flex-wrap"
                    >
                        <span
                            class="badge bg-light text-dark border"
                            style="font-size: 13px; padding: 8px 14px;"
                        >
                            <i class="ti ti-list-check me-1"
                            ></i>{driver.total_services} service aktif
                        </span>
                        <span
                            class="badge bg-light text-dark border"
                            style="font-size: 13px; padding: 8px 14px;"
                        >
                            <i class="ti ti-circle-check me-1"
                            ></i>{driver.completed_bookings} trip selesai
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="services-section pt-60 pb-120">
        <div class="container">
            <div class="sec-title text-center mb-5">
                <div class="sec-title__tagline bw-split-in-right">
                    Layanan<img
                        src="/assets/images/shapes/sec-title-shape.png"
                        alt="Siwride"
                    />
                </div>
                <h3 class="sec-title__title bw-split-in-left">
                    Services by {driver.name}
                </h3>
            </div>

            <div class="row gutter-y-30">
                {#each services as service}
                    <div class="col-lg-4 col-md-6">
                        <a
                            href={service.url}
                            class="d-block text-decoration-none h-100"
                            style="color: inherit;"
                        >
                            <div
                                class="card border-0 shadow-sm h-100"
                                style="border-radius: 12px; overflow: hidden; transition: transform 0.3s;"
                                onmouseover={(e) =>
                                    (e.currentTarget.style.transform =
                                        'translateY(-6px)')}
                                onmouseout={(e) =>
                                    (e.currentTarget.style.transform =
                                        'translateY(0)')}
                            >
                                <div style="position: relative;">
                                    <img
                                        src={service.image_url ||
                                            PLACEHOLDER_IMAGE}
                                        alt={service.title}
                                        style="height: 200px; width: 100%; object-fit: cover;"
                                    />
                                    {#if service.is_featured}
                                        <span
                                            class="badge position-absolute top-0 end-0 m-2"
                                            style="background: var(--travhub-base, #e52029);"
                                            >Featured</span
                                        >
                                    {/if}
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="fw-bold mb-1">
                                        {service.title}
                                    </h5>
                                    {#if service.duration_label}
                                        <div class="small text-muted mb-2">
                                            <i class="ti ti-clock me-1"
                                            ></i>{service.duration_label}
                                        </div>
                                    {/if}
                                    <p
                                        class="small text-muted mb-3"
                                        style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    >
                                        {service.description}
                                    </p>
                                    <div
                                        class="mt-auto d-flex justify-content-between align-items-center"
                                    >
                                        {#if service.price_per_pax}
                                            <div
                                                class="fw-bold"
                                                style="color: var(--travhub-base, #d11f1f);"
                                            >
                                                {formatPrice(
                                                    service.price_per_pax,
                                                )}
                                                <span
                                                    class="fw-normal text-muted small"
                                                    >/ person</span
                                                >
                                            </div>
                                        {:else}
                                            <span></span>
                                        {/if}
                                        <span
                                            class="small fw-bold"
                                            style="color: var(--travhub-base, #e52029);"
                                            >Detail <i
                                                class="ti ti-arrow-right ms-1"
                                            ></i></span
                                        >
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                {:else}
                    <div class="col-12 text-center py-5 text-muted">
                        Belum ada service yang dipublikasikan oleh driver ini.
                    </div>
                {/each}
            </div>

            <div class="text-center mt-5">
                <a href="/catalog" class="travhub-btn"
                    ><span>Jelajahi Semua Service</span></a
                >
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .card:hover img {
        transform: scale(1.05);
    }

    .card img {
        transition: transform 0.5s ease;
    }
</style>
