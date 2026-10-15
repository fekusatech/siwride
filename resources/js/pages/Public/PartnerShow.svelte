<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { partner } = $props<{
        partner: {
            id: number;
            slug: string;
            name: string;
            type: string;
            type_label: string;
            description: string | null;
            image_url: string;
            phone: string;
            created_at: string | null;
        };
    }>();

    const PLACEHOLDER_IMAGE =
        "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 300'%3E%3Crect width='400' height='300' fill='%23e9ecef'/%3E%3Cpath d='M160 120a20 20 0 1 1 0-40 20 20 0 0 1 0 40Zm-40 100 60-70 40 45 30-35 70 60H120Z' fill='%23adb5bd'/%3E%3C/svg%3E";

    const joinedLabel = $derived(
        partner.created_at
            ? new Date(partner.created_at).toLocaleDateString('id-ID', {
                  year: 'numeric',
                  month: 'long',
              })
            : null,
    );
</script>

<AppHead title="{partner.name} - {partner.type_label} | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">{partner.name}</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/partners">Partners</a></li>
                <li><span>{partner.name}</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 60px 0 100px; background: #f7f9fa; min-height: 60vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div
                        class="d-flex flex-column flex-lg-row align-items-center gap-4 p-4 p-lg-5 rounded-4"
                        style="background: #fff; box-shadow: 0 15px 40px rgba(0,0,0,0.06);"
                    >
                        <img
                            src={partner.image_url || PLACEHOLDER_IMAGE}
                            alt={partner.name}
                            class="rounded-circle"
                            style="width: 110px; height: 110px; object-fit: cover; border: 4px solid var(--travhub-base, #d11f1f);"
                        />
                        <div class="text-center text-lg-start flex-grow-1">
                            <span
                                class="badge bg-primary-subtle text-primary mb-2"
                            >
                                {partner.type_label}
                            </span>
                            <h3 class="mb-1 fw-bold">{partner.name}</h3>
                            {#if joinedLabel}
                                <p class="text-muted mb-3">
                                    <i class="ti ti-calendar me-1"></i>Listed since {joinedLabel}
                                </p>
                            {/if}
                            <p class="mb-0" style="color: #555; line-height: 1.7;">
                                {partner.description || 'This partner has not provided a description yet.'}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-4 p-4 rounded-4 text-center"
                        style="background: #fff; box-shadow: 0 15px 40px rgba(0,0,0,0.06);"
                    >
                        <h5 class="fw-bold mb-2">Interested in this partner's services?</h5>
                        <p class="text-muted mb-3">
                            Get in touch directly and mention Siwride when you reach out.
                        </p>
                        <a href="tel:{partner.phone}" class="travhub-btn">
                            <span><i class="ti ti-phone me-1"></i>{partner.phone}</span>
                        </a>
                        <p class="mt-3 mb-0">
                            <a href="/partners" class="text-decoration-none">
                                <i class="ti ti-arrow-left me-1"></i>Back to all partners
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
