<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { router } from '@inertiajs/svelte';

    let { partners, filters } = $props<{
        partners: any[];
        filters: { search: string; type: string };
    }>();

    let search = $state(filters.search ?? '');
    let type = $state(filters.type ?? '');

    let searchTimeout: any;
    $effect(() => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            router.get(
                '/partners',
                { search, type },
                { preserveState: true, replace: true },
            );
        }, 350);
    });

    function applyFilters() {
        router.get('/partners', { search, type }, { preserveState: true, replace: true });
    }

    function resetFilters() {
        search = '';
        type = '';
        applyFilters();
    }

    const typeBadge: Record<string, { label: string; class: string }> = {
        travel: { label: 'Travel Partner', class: 'bg-primary-subtle text-primary' },
        guide: { label: 'Tour Guide', class: 'bg-success-subtle text-success' },
    };
</script>

<AppHead title="Our Partners | Siwride" />

<Preloader />

<div class="page-wrapper">
    <Header />

    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Our Partners</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Partners</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 60px 0 100px; background: #f7f9fa; min-height: 60vh;">
        <div class="container">
            <div class="sec-title text-center mb-5">
                <div class="sec-title__tagline">Trusted Network</div>
                <h3 class="sec-title__title">Travel Partners &amp; Guides</h3>
                <p class="sec-title__text" style="max-width: 600px; margin: 15px auto 0;">
                    Browse our vetted local partners. Every listing is reviewed and approved by our team.
                </p>
            </div>

            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-lg-6 col-md-12">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search partners by name or service..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <select class="form-select" bind:value={type} onchange={applyFilters}>
                                <option value="">All Types</option>
                                <option value="travel">Travel Partners</option>
                                <option value="guide">Tour Guides</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-6 d-flex gap-2">
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm flex-shrink-0"
                                onclick={resetFilters}
                            >
                                <i class="ti ti-refresh"></i>
                            </button>
                            <a href="/become-partner" class="btn btn-primary btn-sm flex-grow-1">
                                Become a Partner
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">
                    {partners.length} partner{partners.length !== 1 ? 's' : ''} found
                </p>
            </div>

            {#if partners.length > 0}
                <div class="row gutter-y-30">
                    {#each partners as partner}
                        <div class="col-lg-3 col-md-6">
                            <a
                                href={`/partners/${partner.slug}`}
                                class="d-block text-decoration-none"
                                style="color: inherit; background: #fff; border-radius: 12px; overflow: hidden; height: 100%; box-shadow: 0 10px 30px rgba(0,0,0,0.05); transition: 0.3s; display: flex; flex-direction: column;"
                                onmouseenter={(e) => (e.currentTarget.style.transform = 'translateY(-8px)')}
                                onmouseleave={(e) => (e.currentTarget.style.transform = 'translateY(0)')}
                            >
                                <div style="height: 200px; overflow: hidden; position: relative;">
                                    <img
                                        src={partner.image_url}
                                        alt={partner.name}
                                        style="width: 100%; height: 100%; object-fit: cover; transition: 0.5s;"
                                        onmouseenter={(e) => (e.currentTarget.style.transform = 'scale(1.1)')}
                                        onmouseleave={(e) => (e.currentTarget.style.transform = 'scale(1)')}
                                    />
                                    <span
                                        class="badge {typeBadge[partner.type]?.class ?? ''}"
                                        style="position: absolute; top: 10px; left: 10px;"
                                    >
                                        {typeBadge[partner.type]?.label ?? partner.type}
                                    </span>
                                </div>
                                <div style="padding: 20px; flex: 1; display: flex; flex-direction: column;">
                                    <h5 style="font-size: 16px; font-weight: 700; margin-bottom: 6px; line-height: 1.3;">
                                        {partner.name}
                                    </h5>
                                    <p
                                        style="color: #666; font-size: 13px; line-height: 1.5; margin-bottom: 12px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"
                                    >
                                        {partner.description || 'No description provided.'}
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                        <span class="text-muted small">
                                            <i class="ti ti-phone me-1"></i>{partner.phone}
                                        </span>
                                        <span style="color: var(--travhub-base, #d11f1f); font-weight: 700; font-size: 13px;">
                                            View <i class="ti ti-arrow-right"></i>
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
                    <h5 class="mt-3 text-muted">No partners found</h5>
                    <p class="text-muted">Try adjusting your search or filters.</p>
                    <button type="button" class="btn btn-outline-primary" onclick={resetFilters}>
                        Reset Filters
                    </button>
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
