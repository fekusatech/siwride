<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { useForm } from '@inertiajs/svelte';

    let { service, customer = null } = $props<{
        service: any;
        customer: { name: string; email: string; phone: string } | null;
    }>();

    let form = useForm({
        booking_date: '',
        pax: service.min_pax ?? 1,
        customer_name: customer?.name || '',
        customer_email: customer?.email || '',
        customer_phone: customer?.phone || '',
        notes: '',
    });

    let totalPrice = $derived(Number(service.price_per_pax) * form.pax);

    let allImages = (service.gallery_urls?.length
        ? [service.image_url, ...service.gallery_urls]
        : [service.image_url]
    ).filter(Boolean);
    let mainImage = $state(allImages[0]);
    let extraThumbs = allImages.slice(1, 5);
    let remainingPhotoCount = Math.max(0, allImages.length - 5);

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function submit(e: Event) {
        e.preventDefault();
        form.post(`/services/${service.slug}/book`);
    }

    let today = new Date().toISOString().split('T')[0];
</script>

<AppHead title="{service.title} | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section style="padding: 40px 0 100px; background: #f7f9fa;">
        <div class="container">
            <!-- Gallery -->
            <div class="row g-2 mb-4 gallery-row">
                <div class="col-lg-8 gallery-main">
                    <img
                        src={mainImage}
                        alt={service.title}
                        class="rounded-3 w-100 h-100"
                        style="object-fit: cover;"
                    />
                </div>
                {#if extraThumbs.length}
                    <div class="col-lg-4 gallery-thumbs">
                        <div class="row g-2 h-100">
                            {#each extraThumbs as url, i}
                                <div class="col-6" style="height: 50%;">
                                    <button
                                        type="button"
                                        class="p-0 border-0 w-100 h-100 position-relative overflow-hidden rounded-3"
                                        style="cursor: pointer;"
                                        onclick={() => (mainImage = url)}
                                    >
                                        <img
                                            src={url}
                                            alt={service.title}
                                            class="w-100 h-100"
                                            style="object-fit: cover; outline: {mainImage === url ? '3px solid var(--travhub-base, #d11f1f)' : 'none'}; outline-offset: -3px;"
                                        />
                                        {#if i === 3 && remainingPhotoCount > 0}
                                            <div
                                                class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold"
                                                style="background: rgba(0,0,0,0.5);"
                                            >
                                                +{remainingPhotoCount} Photos
                                            </div>
                                        {/if}
                                    </button>
                                </div>
                            {/each}
                        </div>
                    </div>
                {/if}
            </div>

            <div class="row gutter-y-40">
                <!-- Left: Service Details -->
                <div class="col-lg-7">
                    <h2 class="fw-bold mb-1">{service.title}</h2>
                    {#if service.driver}
                        <p class="text-muted fs-5 mb-3">Hosted by {service.driver.name}</p>
                    {/if}

                    <!-- Quick specs -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        {#if service.duration_label}
                            <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                                <i class="ti ti-clock fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                                <div>
                                    <div class="small text-muted lh-1">Duration</div>
                                    <div class="fw-medium">{service.duration_label}</div>
                                </div>
                            </div>
                        {/if}
                        <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                            <i class="ti ti-users fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                            <div>
                                <div class="small text-muted lh-1">Participants</div>
                                <div class="fw-medium">Min {service.min_pax}{#if service.max_pax} · Max {service.max_pax}{/if}</div>
                            </div>
                        </div>
                        {#if service.meeting_point}
                            <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                                <i class="ti ti-map-pin fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                                <div>
                                    <div class="small text-muted lh-1">Meeting Point</div>
                                    <div class="fw-medium">{service.meeting_point}</div>
                                </div>
                            </div>
                        {/if}
                    </div>

                    {#if service.highlights?.length}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Why You'll Love This</h5>
                            <div class="row g-2">
                                {#each service.highlights as item}
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="ti ti-sparkles mt-1" style="color: var(--travhub-base, #d11f1f);"></i>
                                            <span>{item}</span>
                                        </div>
                                    </div>
                                {/each}
                            </div>
                        </div>
                    {/if}

                    {#if service.description}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">About This Service</h5>
                            <p class="text-muted">{service.description}</p>
                        </div>
                    {/if}

                    {#if service.includes?.length || service.excludes?.length}
                        <div class="row mb-4">
                            {#if service.includes?.length}
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h5 class="fw-bold mb-2">What's Included</h5>
                                    <ul class="list-unstyled">
                                        {#each service.includes as item}
                                            <li class="mb-2">
                                                <i class="ti ti-circle-check text-success me-2"></i>{item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            {/if}
                            {#if service.excludes?.length}
                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-2">Not Included</h5>
                                    <ul class="list-unstyled">
                                        {#each service.excludes as item}
                                            <li class="mb-2 text-muted">
                                                <i class="ti ti-circle-x text-danger me-2"></i>{item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    {#if service.meeting_point}
                        <div class="mb-4 pt-2 border-top">
                            <h5 class="fw-bold mb-2 mt-3">Meeting Point</h5>
                            <p class="text-muted mb-0">
                                <i class="ti ti-map-pin me-2"></i>{service.meeting_point}
                            </p>
                        </div>
                    {/if}
                </div>

                <!-- Right: Booking Form -->
                <div class="col-lg-5">
                    <div class="card shadow border-0 rounded-3 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <div>
                                    <div class="fs-4 fw-bold" style="color: var(--travhub-base, #d11f1f);">{formatRp(Number(service.price_per_pax))}</div>
                                    <small class="text-muted">per person</small>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small text-muted mb-4 pb-3 border-bottom">
                                {#if service.duration_label}
                                    <span><i class="ti ti-clock me-1"></i>{service.duration_label}</span>
                                {/if}
                                <span><i class="ti ti-users me-1"></i>Min {service.min_pax}{#if service.max_pax} · Max {service.max_pax}{/if} pax</span>
                            </div>

                            <form onsubmit={submit}>
                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="booking_date">Date <span class="text-danger">*</span></label>
                                    <input
                                        type="date"
                                        class="form-control {form.errors.booking_date ? 'is-invalid' : ''}"
                                        id="booking_date"
                                        bind:value={form.booking_date}
                                        min={today}
                                        required
                                    />
                                    {#if form.errors.booking_date}
                                        <div class="invalid-feedback">{form.errors.booking_date}</div>
                                    {/if}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="pax">Number of Participants <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        class="form-control {form.errors.pax ? 'is-invalid' : ''}"
                                        id="pax"
                                        bind:value={form.pax}
                                        min={service.min_pax ?? 1}
                                        max={service.max_pax || undefined}
                                        required
                                    />
                                    {#if form.errors.pax}
                                        <div class="invalid-feedback">{form.errors.pax}</div>
                                    {/if}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="customer_name">Your Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control {form.errors.customer_name ? 'is-invalid' : ''}"
                                        id="customer_name"
                                        bind:value={form.customer_name}
                                        required
                                    />
                                    {#if form.errors.customer_name}
                                        <div class="invalid-feedback">{form.errors.customer_name}</div>
                                    {/if}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="customer_email">Email <span class="text-danger">*</span></label>
                                    <input
                                        type="email"
                                        class="form-control {form.errors.customer_email ? 'is-invalid' : ''}"
                                        id="customer_email"
                                        bind:value={form.customer_email}
                                        required
                                    />
                                    {#if form.errors.customer_email}
                                        <div class="invalid-feedback">{form.errors.customer_email}</div>
                                    {/if}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="customer_phone">Phone / WhatsApp</label>
                                    <input
                                        type="tel"
                                        class="form-control"
                                        id="customer_phone"
                                        bind:value={form.customer_phone}
                                        placeholder="+62..."
                                    />
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-medium" for="notes">Special Requests</label>
                                    <textarea class="form-control" id="notes" rows="2" bind:value={form.notes} placeholder="Any allergies, special needs..."></textarea>
                                </div>

                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{formatRp(Number(service.price_per_pax))} × {form.pax} pax</span>
                                        <span class="fw-bold">{formatRp(totalPrice)}</span>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="travhub-btn w-100 fw-bold"
                                    style="padding: 16px 25px; opacity: {form.processing ? 0.7 : 1};"
                                    disabled={form.processing}
                                >
                                    <span>
                                        {#if form.processing}
                                            <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                            Processing...
                                        {:else}
                                            Book Now — {formatRp(totalPrice)}
                                        {/if}
                                    </span>
                                </button>

                                <p class="text-muted text-center small mt-3 mb-0">
                                    You will be redirected to Xendit to complete payment.
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    :global(.page-wrapper .form-control:focus) {
        border-color: var(--travhub-base, #d11f1f);
        box-shadow: 0 0 0 0.2rem rgba(var(--travhub-base-rgb, 209, 31, 31), 0.15);
    }

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
