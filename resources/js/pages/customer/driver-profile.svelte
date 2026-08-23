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

    const pricedServices = $derived(
        services.filter((service) => service.price_per_pax !== null),
    );
    const lowestPrice = $derived(
        pricedServices.length
            ? Math.min(
                  ...pricedServices.map((service) => service.price_per_pax!),
              )
            : null,
    );
    const durationOptions = $derived(
        new Set(
            services
                .map((service) => service.duration_label)
                .filter((duration): duration is string => Boolean(duration)),
        ).size,
    );
</script>

<AppHead title={`${driver.name} - Siwride Driver`} />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- Page Header -->
    <section class="page-header driver-page-header">
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
    <section class="driver-overview">
        <div class="container">
            <div
                class="driver-profile-card d-flex flex-column flex-lg-row align-items-center gap-4 p-4 p-lg-5 rounded-4"
            >
                <img
                    src={driver.image || PLACEHOLDER_IMAGE}
                    alt={driver.name}
                    class="rounded-circle"
                    style="width: 110px; height: 110px; object-fit: cover; border: 4px solid var(--travhub-base, #e52029);"
                />
                <div class="text-center text-lg-start flex-grow-1">
                    <span class="driver-profile-card__eyebrow">Driver Siwride</span>
                    <h3 class="mb-1 fw-bold">{driver.name}</h3>
                    <p class="text-muted mb-3">
                        {#if driver.joined_at}
                            <i class="ti ti-calendar me-1"></i>Bergabung sejak {driver.joined_at}
                        {/if}
                    </p>
                    <p class="driver-profile-card__intro mb-0">
                        Pilih layanan perjalanan yang sesuai dari driver ini dan lihat detail paketnya.
                    </p>
                </div>
                <a href="#driver-services" class="travhub-btn driver-profile-card__cta">
                    <span>Lihat layanan</span>
                </a>
                <div class="driver-stats w-100">
                    <div class="driver-stat">
                        <i class="ti ti-list-check"></i>
                        <div><strong>{driver.total_services}</strong><span>Layanan aktif</span></div>
                    </div>
                    <div class="driver-stat">
                        <i class="ti ti-circle-check"></i>
                        <div><strong>{driver.completed_bookings}</strong><span>Trip selesai</span></div>
                    </div>
                    <div class="driver-stat">
                        <i class="ti ti-clock"></i>
                        <div><strong>{durationOptions || '-'}</strong><span>Pilihan durasi</span></div>
                    </div>
                    {#if lowestPrice !== null}
                        <div class="driver-stat">
                            <i class="ti ti-wallet"></i>
                            <div><strong>{formatPrice(lowestPrice)}</strong><span>Harga mulai / orang</span></div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="services-section driver-services" id="driver-services">
        <div class="container">
            <div class="driver-services__heading d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3 mb-4">
                <div class="sec-title mb-0">
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
                <p class="driver-services__count mb-1">{services.length} paket tersedia</p>
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
                                        style="height: 175px; width: 100%; object-fit: cover;"
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
                                    {#if service.min_pax || service.max_pax}
                                        <div class="small text-muted mb-2">
                                            <i class="ti ti-users me-1"></i>
                                            {service.min_pax ?? 1}{service.max_pax ? `-${service.max_pax}` : ''} orang
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
    .driver-page-header {
        padding-top: 72px;
        padding-bottom: 78px;
    }

    .driver-overview {
        padding: 34px 0 0;
    }

    .driver-profile-card {
        position: relative;
        background: #fff;
        box-shadow: 0 12px 35px rgba(28, 34, 43, 0.08);
    }

    .driver-profile-card > img {
        flex: 0 0 auto;
    }

    .driver-profile-card__eyebrow {
        display: inline-block;
        margin-bottom: 8px;
        color: var(--travhub-base, #e52029);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .driver-profile-card__intro {
        max-width: 520px;
        color: #69727d;
        line-height: 1.6;
    }

    .driver-profile-card__cta {
        flex: 0 0 auto;
        white-space: nowrap;
    }

    .driver-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        padding-top: 22px;
        border-top: 1px solid #edf0f2;
    }

    .driver-stat {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .driver-stat > i {
        flex: 0 0 auto;
        color: var(--travhub-base, #e52029);
        font-size: 22px;
    }

    .driver-stat div {
        display: flex;
        min-width: 0;
        flex-direction: column;
    }

    .driver-stat strong {
        overflow: hidden;
        color: #252b35;
        font-size: 15px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .driver-stat span {
        color: #87909b;
        font-size: 12px;
    }

    .driver-services {
        padding: 56px 0 110px;
    }

    .driver-services__count {
        color: #87909b;
        font-size: 14px;
    }

    .card:hover img {
        transform: scale(1.05);
    }

    .card img {
        transition: transform 0.5s ease;
    }

    @media (max-width: 991px) {
        .driver-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 575px) {
        .driver-page-header {
            padding-top: 58px;
            padding-bottom: 62px;
        }

        .driver-overview {
            padding-top: 24px;
        }

        .driver-profile-card {
            padding: 24px 20px !important;
        }

        .driver-profile-card__cta {
            width: 100%;
            text-align: center;
        }

        .driver-stats {
            gap: 16px 10px;
        }
    }
</style>
