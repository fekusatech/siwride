<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { guide } = $props<{ guide: any }>();

    let allImages = [
        guide.image_url,
        ...(guide.gallery_urls?.slice(1) ?? []),
    ].filter(Boolean);
    let mainImage = $state(allImages[0]);
    let extraThumbs = allImages.slice(1, 5);
</script>

<AppHead title="{guide.title} | Siwride Guides" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section style="padding: 40px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="row g-2 mb-4 gallery-row">
                <div class="col-lg-8 gallery-main">
                    <img
                        src={mainImage}
                        alt={guide.title}
                        class="rounded-3 w-100 h-100"
                        style="object-fit: cover;"
                    />
                </div>
                {#if extraThumbs.length}
                    <div class="col-lg-4 gallery-thumbs">
                        <div class="row g-2 h-100">
                            {#each extraThumbs as url}
                                <div class="col-6" style="height: 50%;">
                                    <button
                                        type="button"
                                        class="p-0 border-0 w-100 h-100 overflow-hidden rounded-3"
                                        style="cursor: pointer;"
                                        onclick={() => (mainImage = url)}
                                    >
                                        <img
                                            src={url}
                                            alt={guide.title}
                                            class="w-100 h-100"
                                            style="object-fit: cover; outline: {mainImage === url ? '3px solid var(--travhub-base, #d11f1f)' : 'none'}; outline-offset: -3px;"
                                        />
                                    </button>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>

            <div class="row gutter-y-40">
                <div class="col-lg-8">
                    <h2 class="fw-bold mb-2">{guide.title}</h2>
                    <div class="small text-muted mb-4">
                        <i class="ti ti-steering-wheel me-1"></i>Written by {guide.driver?.name ?? 'a Siwride driver'}
                    </div>

                    <div style="white-space: pre-wrap;">{guide.content}</div>
                </div>

                <div class="col-lg-4">
                    {#if guide.activity}
                        <div class="card shadow border-0 rounded-3 sticky-top" style="top: 100px;">
                            <div class="card-body p-4 text-center">
                                <h5 class="fw-bold mb-2">{guide.activity.title}</h5>
                                {#if guide.activity.price_per_pax}
                                    <p class="text-muted mb-3">
                                        From <strong>Rp {Number(guide.activity.price_per_pax).toLocaleString('id-ID')}</strong> / person
                                    </p>
                                {/if}
                                <a href={guide.activity.link_url} class="travhub-btn w-100" style="padding: 14px 25px;">
                                    <span>Book This Tour</span>
                                </a>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .gallery-row {
        height: auto;
    }
    .gallery-main {
        height: 260px;
    }
    .gallery-thumbs {
        height: 140px;
        margin-top: 8px;
    }
    @media (min-width: 992px) {
        .gallery-row {
            height: 420px;
        }
        .gallery-main,
        .gallery-thumbs {
            height: 100%;
            margin-top: 0;
        }
    }
</style>
