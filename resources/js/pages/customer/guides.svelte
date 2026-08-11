<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { guides = [], filters = { search: '' } } = $props<{
        guides: any[];
        filters?: { search: string };
    }>();

    let searchQuery = $state(filters.search || '');
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    function handleSearchInput(): void {
        if (debounceTimer) clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            router.get('/guides', { search: searchQuery }, { preserveState: true, replace: true });
        }, 400);
    }
</script>

<AppHead title="Bali Tour Guides | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <div style="padding: 60px 0 50px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
        <div class="container">
            <h1 style="font-size: 34px; font-weight: 800; color: #fff; margin: 0 0 10px;">
                Tour Guides from Our Drivers
            </h1>
            <p style="color: rgba(255,255,255,0.6); margin: 0; font-size: 15px;">
                Local tips, itineraries and stories written by the drivers who know Bali best.
            </p>
        </div>
    </div>

    <section style="padding: 50px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="mb-4" style="max-width: 400px;">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Search guides..."
                    bind:value={searchQuery}
                    oninput={handleSearchInput}
                />
            </div>

            {#if guides.length === 0}
                <div class="text-center py-5">
                    <p class="text-muted">No guides published yet. Check back soon.</p>
                </div>
            {:else}
                <div class="row gutter-y-30">
                    {#each guides as guide}
                        <div class="col-lg-4 col-md-6">
                            <a href={`/guides/${guide.slug}`} class="d-block text-decoration-none" style="color: inherit;">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                    <img src={guide.image_url} alt={guide.title} style="height: 200px; object-fit: cover;" />
                                    <div class="card-body">
                                        <h5 class="fw-bold mb-1">{guide.title}</h5>
                                        {#if guide.excerpt}
                                            <p class="text-muted small mb-2">{guide.excerpt}</p>
                                        {/if}
                                        <div class="small text-muted">
                                            <i class="ti ti-steering-wheel me-1"></i>{guide.driver?.name ?? 'Siwride Driver'}
                                        </div>
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
