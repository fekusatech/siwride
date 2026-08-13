<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { services = [], filters = { search: '' } } = $props<{
        services: any[];
        filters?: { search: string };
    }>();

    let searchQuery = $state(filters.search || '');
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    function handleSearchInput(): void {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            router.get('/driver-services', { search: searchQuery }, { preserveState: true, replace: true });
        }, 400);
    }

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }
</script>

<AppHead title="Driver Services | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <div style="padding: 60px 0 50px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="container">
            <h1 style="font-size: 34px; font-weight: 800; color: #fff; margin: 0 0 10px;">
                Services from Our Drivers
            </h1>
            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 15px;">
                Local tours and experiences hosted directly by the drivers who know Bali best.
            </p>
        </div>
    </div>

    <section style="padding: 50px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="mb-4" style="max-width: 400px;">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Search services..."
                    bind:value={searchQuery}
                    oninput={handleSearchInput}
                />
            </div>

            {#if services.length === 0}
                <div class="text-center py-5">
                    <p class="text-muted">No services published yet. Check back soon.</p>
                </div>
            {:else}
                <div class="row gutter-y-30">
                    {#each services as service}
                        <div class="col-lg-4 col-md-6">
                            <a href={`/services/${service.slug}`} class="d-block text-decoration-none" style="color: inherit;">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                    <img src={service.image_url} alt={service.title} style="height: 200px; object-fit: cover;" />
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-1">{service.title}</h5>
                                        <div class="small text-muted mb-2">
                                            <i class="ti ti-steering-wheel me-1"></i>{service.driver?.name ?? 'Siwride Driver'}
                                        </div>
                                        {#if service.price_per_pax}
                                            <div class="fw-bold" style="color: var(--travhub-base, #d11f1f);">
                                                {formatRp(Number(service.price_per_pax))}
                                                <span class="fw-normal text-muted small">/ person</span>
                                            </div>
                                        {/if}
                                    </div>
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
