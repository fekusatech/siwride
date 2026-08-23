<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { router } from '@inertiajs/svelte';

    let { items, filters } = $props<{
        items: any[];
        filters: {
            search: string;
            type: string;
            sort: string;
            min_price: number | null;
            max_price: number | null;
        };
    }>();

    let search = $state(filters.search ?? '');
    let type = $state(filters.type ?? '');
    let sort = $state(filters.sort ?? 'newest');
    let minPrice = $state(filters.min_price ?? '');
    let maxPrice = $state(filters.max_price ?? '');

    let showFilters = $state(false);

    let searchTimeout: any;
    $effect(() => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            router.get(
                '/catalog',
                {
                    search,
                    type,
                    sort,
                    min_price: minPrice || undefined,
                    max_price: maxPrice || undefined,
                },
                { preserveState: true, replace: true },
            );
        }, 350);
    });

    function applyFilters() {
        router.get(
            '/catalog',
            {
                search,
                type,
                sort,
                min_price: minPrice || undefined,
                max_price: maxPrice || undefined,
            },
            { preserveState: true, replace: true },
        );
    }

    function resetFilters() {
        search = '';
        type = '';
        sort = 'newest';
        minPrice = '';
        maxPrice = '';
        applyFilters();
    }

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    const typeBadge: Record<string, { label: string; class: string }> = {
        service: { label: 'Driver Service', class: 'bg-info-subtle text-info' },
        activity: {
            label: 'Activity',
            class: 'bg-success-subtle text-success',
        },
    };
</script>

<AppHead title="Catalog | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section
        style="padding: 60px 0 100px; background: #f7f9fa; min-height: 60vh;"
    >
        <div class="container">
            <!-- Header -->
            <div class="sec-title text-center mb-5">
                <div class="sec-title__tagline">Explore All</div>
                <h3 class="sec-title__title">Catalog</h3>
                <p
                    class="sec-title__text"
                    style="max-width: 600px; margin: 15px auto 0;"
                >
                    Browse all our driver services and activities. Search,
                    filter, and book directly.
                </p>
            </div>

            <!-- Search & Filter Bar -->
            <div
                class="card shadow-sm border-0 mb-4"
                style="border-radius: 12px;"
            >
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-5 col-md-12">
                            <div class="input-group">
                                <span
                                    class="input-group-text bg-transparent border-end-0"
                                >
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search services, activities, locations..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <select
                                class="form-select"
                                bind:value={type}
                                onchange={applyFilters}
                            >
                                <option value="">All Types</option>
                                <option value="service">Driver Services</option>
                                <option value="activity">Activities</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-4">
                            <select
                                class="form-select"
                                bind:value={sort}
                                onchange={applyFilters}
                            >
                                <option value="newest">Newest</option>
                                <option value="price_low"
                                    >Price: Low to High</option
                                >
                                <option value="price_high"
                                    >Price: High to Low</option
                                >
                                <option value="title">Name A-Z</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-4 d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-secondary btn-sm flex-shrink-0"
                                onclick={() => (showFilters = !showFilters)}
                            >
                                <i class="ti ti-filter me-1"></i>Price
                            </button>
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm flex-shrink-0"
                                onclick={resetFilters}
                            >
                                <i class="ti ti-refresh"></i>
                            </button>
                        </div>
                    </div>

                    {#if showFilters}
                        <div class="row g-2 mt-2 pt-2 border-top">
                            <div class="col-md-3">
                                <label class="form-label small mb-1"
                                    >Min Price</label
                                >
                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    bind:value={minPrice}
                                    placeholder="0"
                                    min="0"
                                />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small mb-1"
                                    >Max Price</label
                                >
                                <input
                                    type="number"
                                    class="form-control form-control-sm"
                                    bind:value={maxPrice}
                                    placeholder="∞"
                                    min="0"
                                />
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button
                                    type="button"
                                    class="btn btn-primary btn-sm w-100"
                                    onclick={applyFilters}>Apply</button
                                >
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Results count -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">
                    {items.length} item{items.length !== 1 ? 's' : ''} found
                </p>
            </div>

            <!-- Catalog Grid -->
            {#if items.length > 0}
                <div class="row gutter-y-30">
                    {#each items as item}
                        <div class="col-lg-3 col-md-6">
                            <a
                                href={item.detail_url}
                                class="d-block text-decoration-none"
                                style="color: inherit; background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; display: flex; flex-direction: column;"
                                onmouseenter={(e) =>
                                    (e.currentTarget.style.transform =
                                        'translateY(-8px)')}
                                onmouseleave={(e) =>
                                    (e.currentTarget.style.transform =
                                        'translateY(0)')}
                            >
                                <div
                                    style="height: 200px; overflow: hidden; position: relative;"
                                >
                                    <img
                                        src={item.image_url}
                                        alt={item.title}
                                        style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;"
                                        onmouseenter={(e) =>
                                            (e.currentTarget.style.transform =
                                                'scale(1.1)')}
                                        onmouseleave={(e) =>
                                            (e.currentTarget.style.transform =
                                                'scale(1)')}
                                    />
                                    <span
                                        class="badge {typeBadge[item.type]
                                            ?.class ?? ''}"
                                        style="position: absolute; top: 10px; left: 10px;"
                                    >
                                        {typeBadge[item.type]?.label ??
                                            item.type}
                                    </span>
                                </div>
                                <div
                                    style="padding: 20px; flex: 1; display: flex; flex-direction: column;"
                                >
                                    <h5
                                        style="font-size: 16px; font-weight: 700; margin-bottom: 6px; line-height: 1.3;"
                                    >
                                        {item.title}
                                    </h5>
                                    {#if item.subtitle}
                                        <p class="text-muted small mb-2">
                                            <i class="ti ti-clock me-1"
                                            ></i>{item.subtitle}
                                        </p>
                                    {/if}
                                    {#if item.driver_name}
                                        <p class="text-muted small mb-2">
                                            <a
                                                href={`/driver/${item.driver_slug}`}
                                                class="text-decoration-none text-muted"
                                                onclick={(e) =>
                                                    e.stopPropagation()}
                                            >
                                                <i
                                                    class="ti ti-steering-wheel me-1"
                                                ></i>{item.driver_name}
                                            </a>
                                        </p>
                                    {/if}
                                    <p
                                        style="color: #666; font-size: 13px; line-height: 1.5; margin-bottom: 12px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    >
                                        {item.description}
                                    </p>
                                    <div
                                        class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top"
                                    >
                                        <div
                                            class="fw-bold"
                                            style="color: var(--travhub-base, #d11f1f); font-size: 16px;"
                                        >
                                            {formatRp(item.price_per_pax)}
                                            <span
                                                class="fw-normal text-muted"
                                                style="font-size: 12px;"
                                                >/pax</span
                                            >
                                        </div>
                                        <span
                                            style="color: var(--travhub-base, #d11f1f); font-weight: 700; font-size: 13px;"
                                        >
                                            Book <i class="ti ti-arrow-right"
                                            ></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    {/each}
                </div>
            {:else}
                <div class="text-center py-5">
                    <i class="ti ti-search fs-1 text-muted"></i>
                    <h5 class="mt-3 text-muted">No results found</h5>
                    <p class="text-muted">
                        Try adjusting your search or filters.
                    </p>
                    <button
                        type="button"
                        class="btn btn-outline-primary"
                        onclick={resetFilters}>Reset Filters</button
                    >
                </div>
            {/if}
        </div>
    </section>

    <Footer />
</div>

<style>
    :global(.page-wrapper .form-control:focus) {
        border-color: var(--travhub-base, #d11f1f);
        box-shadow: 0 0 0 0.2rem rgba(209, 31, 31, 0.15);
    }
    :global(.page-wrapper .form-select:focus) {
        border-color: var(--travhub-base, #d11f1f);
        box-shadow: 0 0 0 0.2rem rgba(209, 31, 31, 0.15);
    }
</style>
