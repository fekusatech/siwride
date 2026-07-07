<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { tourShow } from '@/actions/App/Http/Controllers/CustomerOrderController';
    import { tourIndex } from '@/actions/App/Http/Controllers/CustomerOrderController';

    let {
        tours = [],
        filters = { search: '', sort: 'sort_order' },
    } = $props<{
        tours: {
            id: number;
            name: string;
            slug: string;
            description: string;
            price_per_pax: number;
            duration_hours: number;
            max_pax: number;
            min_pax: number;
            destinations: string[];
            image_url: string;
        }[];
        filters?: { search: string; sort: string };
    }>();

    let searchQuery = $state(filters.search || '');
    let sortOption = $state(filters.sort || 'sort_order');
    let isSortDropdownOpen = $state(false);
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    const auth = $derived((page.props as any).auth);

    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    function applyFilters(): void {
        router.get(
            tourIndex.url(),
            {
                search: searchQuery,
                sort: sortOption,
            },
            { preserveState: true, replace: true },
        );
    }

    function handleSearchInput(): void {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(applyFilters, 400);
    }

    function handleSortChange(): void {
        applyFilters();
    }

    function selectSortOption(val: string): void {
        sortOption = val;
        isSortDropdownOpen = false;
        handleSortChange();
    }

    function clearSearch(): void {
        searchQuery = '';
        applyFilters();
    }

    const sortOptions = [
        { value: 'sort_order', label: 'Featured' },
        { value: 'price_asc', label: 'Price: Low to High' },
        { value: 'price_desc', label: 'Price: High to Low' },
        { value: 'duration_asc', label: 'Duration: Shortest First' },
    ];
</script>

<AppHead title="Tour Package Booking | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header {auth} />

    <!-- ==================== Page Header ==================== -->
    <div
        style="padding: 60px 0 50px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); position: relative; overflow: hidden;"
    >
        <div
            style="position: absolute; top: -60px; right: -60px; width: 280px; height: 280px; background: radial-gradient(circle, rgba(245,158,11,0.12) 0%, transparent 70%); pointer-events: none;"
        ></div>
        <div class="container" style="position: relative; z-index: 1;">
            <div
                style="display: flex; align-items: center; gap: 10px; margin-bottom: 14px;"
            >
                <a
                    href="/booking"
                    style="color: rgba(255,255,255,0.5); text-decoration: none; font-size: 13px; transition: color 0.2s;"
                    onmouseenter={(e) => (e.currentTarget.style.color = '#f59e0b')}
                    onmouseleave={(e) =>
                        (e.currentTarget.style.color = 'rgba(255,255,255,0.5)')}
                >
                    Services
                </a>
                <span style="color: rgba(255,255,255,0.3);">›</span>
                <span style="color: rgba(255,255,255,0.7); font-size: 13px;"
                    >Tour Packages</span
                >
            </div>
            <h1
                style="font-size: 34px; font-weight: 800; color: #fff; margin: 0 0 10px;"
            >
                Tour Package Booking
            </h1>
            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 15px;">
                Curated Bali day tours with professional drivers — fixed price
                per person, no hidden fees.
            </p>
        </div>
    </div>

    <!-- ==================== Tour Content ==================== -->
    <section style="padding: 50px 0 100px; background: #f7f9fa;">
        <div class="container">
            <!-- Search & Sort Bar -->
            <div
                style="background: #fff; border-radius: 16px; padding: 20px 24px; margin-bottom: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); display: flex; gap: 16px; align-items: center; flex-wrap: wrap;"
            >
                <!-- Search Input -->
                <div style="flex: 1; min-width: 220px; position: relative;">
                    <i
                        class="fas fa-search"
                        style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px;"
                    ></i>
                    <input
                        type="text"
                        id="tour_search"
                        placeholder="Search tours, destinations..."
                        bind:value={searchQuery}
                        oninput={handleSearchInput}
                        style="width: 100%; padding: 11px 14px 11px 40px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #1e293b; outline: none; transition: border-color 0.2s; background: #f8fafc;"
                        onfocus={(e) =>
                            (e.currentTarget.style.borderColor = '#f59e0b')}
                        onblur={(e) =>
                            (e.currentTarget.style.borderColor = '#e2e8f0')}
                    />
                    {#if searchQuery}
                        <button
                            type="button"
                            onclick={clearSearch}
                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #94a3b8; cursor: pointer; font-size: 16px; padding: 0; line-height: 1;"
                        >
                            ×
                        </button>
                    {/if}
                </div>

                <!-- Sort Dropdown -->
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-sort-amount-down" style="color: #64748b; font-size: 14px;"></i>
                    <div class="custom-sort-dropdown" style="position: relative;">
                        <button
                            type="button"
                            onclick={() => isSortDropdownOpen = !isSortDropdownOpen}
                            style="padding: 11px 36px 11px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 14px; color: #1e293b; background: #f8fafc; outline: none; cursor: pointer; background-image: url(data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E); background-repeat: no-repeat; background-position: right 12px center; text-align: left; min-width: 150px;"
                        >
                            {sortOptions.find(opt => opt.value === sortOption)?.label || 'Sort By'}
                        </button>
                        {#if isSortDropdownOpen}
                            <div
                                style="position: absolute; top: calc(100% + 8px); right: 0; min-width: 200px; background: #fff; border: 1px solid #f1f5f9; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); overflow: hidden; z-index: 50; padding: 6px;"
                            >
                                {#each sortOptions as opt}
                                    <button
                                        type="button"
                                        onclick={() => selectSortOption(opt.value)}
                                        style="display: block; width: 100%; text-align: left; padding: 10px 14px; font-size: 14px; color: {sortOption === opt.value ? '#f59e0b' : '#475569'}; background: {sortOption === opt.value ? '#fffbeb' : 'transparent'}; border: none; border-radius: 8px; cursor: pointer; transition: all 0.2s; font-weight: {sortOption === opt.value ? '600' : '500'};"
                                        onmouseenter={(e) => {
                                            if (sortOption !== opt.value) e.currentTarget.style.background = '#f8fafc';
                                        }}
                                        onmouseleave={(e) => {
                                            if (sortOption !== opt.value) e.currentTarget.style.background = 'transparent';
                                        }}
                                    >
                                        {opt.label}
                                    </button>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>

                <!-- Result count -->
                <div style="color: #64748b; font-size: 13px; white-space: nowrap;">
                    {tours.length} package{tours.length !== 1 ? 's' : ''} found
                </div>
            </div>

            <!-- No Results -->
            {#if tours.length === 0}
                <div
                    style="text-align: center; background: #fff; border-radius: 20px; padding: 80px 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05);"
                >
                    <div
                        style="width: 100px; height: 100px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 28px;"
                    >
                        <i class="fas fa-search" style="font-size: 40px; color: #f59e0b;"></i>
                    </div>
                    <h3
                        style="font-size: 24px; font-weight: 800; margin-bottom: 12px; color: #1e293b;"
                    >
                        No Tour Packages Found
                    </h3>
                    <p
                        style="color: #64748b; font-size: 15px; max-width: 420px; margin: 0 auto 28px; line-height: 1.7;"
                    >
                        {#if searchQuery}
                            No packages match "<strong>{searchQuery}</strong>". Try a different search term or browse all packages.
                        {:else}
                            No tour packages are available at this time. Please check back soon.
                        {/if}
                    </p>
                    {#if searchQuery}
                        <button
                            type="button"
                            onclick={clearSearch}
                            class="travhub-btn"
                            style="display: inline-block;"
                        >
                            <span>Show All Packages</span>
                        </button>
                    {/if}
                </div>

            {:else}
                <!-- Tour Cards Grid -->
                <div class="row gutter-y-30">
                    {#each tours as tour, i}
                        <div
                            class="col-lg-4 col-md-6 wow fadeInUp"
                            data-wow-duration="1000ms"
                            data-wow-delay="{i * 80}ms"
                        >
                            <a
                                href={tourShow.url({ tourPackage: tour.slug })}
                                class="tour-card-link"
                                style="text-decoration: none; color: inherit; display: flex; flex-direction: column; height: 410px; background: #fff; border: 1px solid #f0f4f8; border-radius: 20px; overflow: hidden; transition: all 0.35s cubic-bezier(0.4,0,0.2,1); box-shadow: 0 6px 25px rgba(0,0,0,0.07);"
                                onmouseenter={(e) => {
                                    e.currentTarget.style.transform = 'translateY(-8px)';
                                    e.currentTarget.style.boxShadow = '0 25px 60px rgba(245,158,11,0.15)';
                                    e.currentTarget.style.borderColor = 'rgba(245,158,11,0.3)';
                                }}
                                onmouseleave={(e) => {
                                    e.currentTarget.style.transform = 'translateY(0)';
                                    e.currentTarget.style.boxShadow = '0 6px 25px rgba(0,0,0,0.07)';
                                    e.currentTarget.style.borderColor = '#f0f4f8';
                                }}
                            >
                                <!-- Image -->
                                <div style="height: 230px; overflow: hidden; position: relative;">
                                    {#if tour.image_url && !tour.image_url.includes('tour-default')}
                                        <img
                                            src={tour.image_url}
                                            alt={tour.name}
                                            style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                            onmouseenter={(e) => (e.currentTarget.style.transform = 'scale(1.08)')}
                                            onmouseleave={(e) => (e.currentTarget.style.transform = 'scale(1)')}
                                        />
                                    {:else}
                                        <div
                                            style="width: 100%; height: 100%; background: linear-gradient(135deg, #b45309 0%, #f59e0b 50%, #d97706 100%); display: flex; align-items: center; justify-content: center;"
                                        >
                                            <i class="flaticon-map" style="font-size: 70px; color: rgba(255,255,255,0.4);"></i>
                                        </div>
                                    {/if}

                                    <!-- Duration badge -->
                                    <div
                                        style="position: absolute; top: 14px; left: 14px; background: rgba(0,0,0,0.7); color: #fff; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; backdrop-filter: blur(6px); display: flex; align-items: center; gap: 5px;"
                                    >
                                        <i class="fas fa-clock" style="font-size: 10px; color: #f59e0b;"></i>
                                        {tour.duration_hours} Hours
                                    </div>
                                    
                                    <!-- Pax Badge -->
                                    <div
                                        style="position: absolute; top: 14px; right: 14px; background: rgba(0,0,0,0.7); color: #fff; font-size: 12px; font-weight: 700; padding: 5px 12px; border-radius: 6px; backdrop-filter: blur(6px); display: flex; align-items: center; gap: 5px;"
                                    >
                                        <i class="fas fa-users" style="font-size: 10px; color: #f59e0b;"></i>
                                        Max {tour.max_pax}
                                    </div>

                                    <!-- Price badge -->
                                    <div
                                        style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); padding: 30px 18px 16px; display: flex; align-items: flex-end; justify-content: space-between;"
                                    >
                                        <div>
                                            <p style="color: rgba(255,255,255,0.7); font-size: 11px; margin-bottom: 2px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px;">From</p>
                                            <p style="color: #f59e0b; font-size: 20px; font-weight: 800; margin: 0; line-height: 1;">
                                                {new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(tour.price_per_pax)}
                                            </p>
                                            <p style="color: rgba(255,255,255,0.5); font-size: 11px; margin: 2px 0 0;">/ person</p>
                                        </div>
                                        <div
                                            style="background: #e52029; color: #fff; padding: 8px 14px; border-radius: 50px; font-size: 12px; font-weight: 700; display: flex; align-items: center; gap: 5px;"
                                        >
                                            View <i class="fas fa-arrow-right" style="font-size: 10px;"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Content -->
                                <div style="padding: 20px 22px 24px; flex: 1; display: flex; flex-direction: column;">
                                    <h4
                                        style="color: #1e293b; font-size: 17px; font-weight: 800; margin-bottom: 8px; line-height: 1.3;"
                                    >{tour.name}</h4>
                                    <p
                                        style="color: #64748b; font-size: 13px; line-height: 1.6; margin-bottom: 14px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;"
                                    >
                                        {tour.description}
                                    </p>

                                    <!-- Destinations pills -->
                                    {#if tour.destinations && tour.destinations.length > 0}
                                        <div style="display: flex; flex-wrap: nowrap; gap: 6px; margin-top: 4px; overflow: hidden; align-items: center; width: 100%;">
                                            {#each tour.destinations.slice(0, 1) as dest}
                                                <span
                                                    style="background: #fee2e2; color: #dc2626; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; border: 1px solid #fca5a5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px; display: inline-block; min-width: 0;"
                                                >
                                                    📍 {dest}
                                                </span>
                                            {/each}
                                            {#if tour.destinations.length > 1}
                                                <span
                                                    style="background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 600; padding: 3px 10px; border-radius: 6px; border: 1px solid #e2e8f0; white-space: nowrap; flex-shrink: 0;"
                                                >
                                                    +{tour.destinations.length - 1} other
                                                </span>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                            </a>
                        </div>
                    {/each}
                </div>
            {/if}
        </div>
    </section>

    <Footer />
</div>
