<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import { useForm } from '@inertiajs/svelte';

    let { activity, relatedActivities = [], payment = { dp_percent: 30 }, packTiers = [], customer = null } = $props<{
        activity: any;
        relatedActivities?: any[];
        payment: { dp_percent: number };
        packTiers?: any[];
        customer: { name: string; email: string; phone: string } | null;
    }>();

    let form = useForm({
        booking_date: '',
        pax: activity.min_pax ?? 1,
        customer_name: customer?.name || '',
        customer_email: customer?.email || '',
        customer_phone: customer?.phone || '',
        notes: '',
        voucher_code: '',
    });

    let voucherState: 'idle' | 'loading' | 'valid' | 'invalid' = $state('idle');
    let voucherDiscount = $state(0);
    let voucherMessage = $state('');

    let basePricePerPax = $derived(Number(activity.price_per_pax));

    let applicableTier = $derived.by(() => {
        const pax = Number(form.pax) || 1;
        return packTiers.find((t) => pax >= t.min_pax && (t.max_pax === null || pax <= t.max_pax)) ?? null;
    });

    let effectivePricePerPax = $derived.by(() => {
        if (!applicableTier) return basePricePerPax;
        if (applicableTier.discount_type === 'percent') {
            return Math.round(basePricePerPax * (1 - applicableTier.discount_value / 100) * 100) / 100;
        }
        return Math.max(0, Math.round((basePricePerPax - applicableTier.discount_value) * 100) / 100);
    });

    let subtotal = $derived(Math.round(effectivePricePerPax * form.pax * 100) / 100);
    let totalAmount = $derived(Math.max(0, Math.round((subtotal - voucherDiscount) * 100) / 100));
    let dpAmount = $derived(Math.round(totalAmount * (payment.dp_percent / 100) * 100) / 100);
    let remainingCash = $derived(Math.round((totalAmount - dpAmount) * 100) / 100);

    async function applyVoucher() {
        const code = (form.voucher_code ?? '').trim().toUpperCase();
        if (!code) return;

        voucherState = 'loading';
        voucherMessage = '';

        try {
            const response = await fetch(`/activities/${activity.slug}/validate-voucher`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ code, pax: form.pax, email: form.customer_email }),
            });

            const data = await response.json();

            if (response.ok && data.valid) {
                voucherState = 'valid';
                voucherDiscount = Number(data.discount_amount);
                form.voucher_code = data.code;
                voucherMessage = `-${formatRp(voucherDiscount)} (${data.code})`;
            } else {
                voucherState = 'invalid';
                voucherDiscount = 0;
                voucherMessage = data.message ?? 'Kode promo tidak valid.';
            }
        } catch {
            voucherState = 'invalid';
            voucherDiscount = 0;
            voucherMessage = 'Gagal memvalidasi kode promo.';
        }
    }

    function removeVoucher() {
        voucherState = 'idle';
        voucherDiscount = 0;
        voucherMessage = '';
        form.voucher_code = '';
    }

    let allImages = (activity.gallery_urls?.length
        ? [activity.image_url, ...activity.gallery_urls]
        : [activity.image_url]
    ).filter(Boolean);
    let mainImage = $state(allImages[0]);
    let extraThumbs = allImages.slice(1, 5);
    let remainingPhotoCount = Math.max(0, allImages.length - 5);

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }

    function submit(e: Event) {
        e.preventDefault();
        form.post(`/activities/${activity.slug}/book`);
    }

    let today = new Date().toISOString().split('T')[0];
</script>

<AppHead title="{activity.title} | Siwride" />

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
                        alt={activity.title}
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
                                            alt={activity.title}
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
                <!-- Left: Activity Details -->
                <div class="col-lg-7">
                    <h2 class="fw-bold mb-1">{activity.title}</h2>
                    {#if activity.subtitle}
                        <p class="text-muted fs-5 mb-3">{activity.subtitle}</p>
                    {/if}

                    <!-- Quick specs -->
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        {#if activity.duration_label}
                            <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                                <i class="ti ti-clock fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                                <div>
                                    <div class="small text-muted lh-1">Duration</div>
                                    <div class="fw-medium">{activity.duration_label}</div>
                                </div>
                            </div>
                        {/if}
                        <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                            <i class="ti ti-users fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                            <div>
                                <div class="small text-muted lh-1">Participants</div>
                                <div class="fw-medium">Min {activity.min_pax}{#if activity.max_pax} · Max {activity.max_pax}{/if}</div>
                            </div>
                        </div>
                        {#if activity.meeting_point}
                            <div class="d-flex align-items-center gap-2 border rounded-3 px-3 py-2 bg-white">
                                <i class="ti ti-map-pin fs-4" style="color: var(--travhub-base, #d11f1f);"></i>
                                <div>
                                    <div class="small text-muted lh-1">Meeting Point</div>
                                    <div class="fw-medium">{activity.meeting_point}</div>
                                </div>
                            </div>
                        {/if}
                    </div>

                    {#if activity.highlights?.length}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3">Why You'll Love This Tour</h5>
                            <div class="row g-2">
                                {#each activity.highlights as item}
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

                    {#if activity.description}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">About This Activity</h5>
                            <p class="text-muted">{activity.description}</p>
                        </div>
                    {/if}

                    {#if activity.includes?.length || activity.excludes?.length}
                        <div class="row mb-4">
                            {#if activity.includes?.length}
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <h5 class="fw-bold mb-2">What's Included</h5>
                                    <ul class="list-unstyled">
                                        {#each activity.includes as item}
                                            <li class="mb-2">
                                                <i class="ti ti-circle-check text-success me-2"></i>{item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            {/if}
                            {#if activity.excludes?.length}
                                <div class="col-md-6">
                                    <h5 class="fw-bold mb-2">Not Included</h5>
                                    <ul class="list-unstyled">
                                        {#each activity.excludes as item}
                                            <li class="mb-2 text-muted">
                                                <i class="ti ti-circle-x text-danger me-2"></i>{item}
                                            </li>
                                        {/each}
                                    </ul>
                                </div>
                            {/if}
                        </div>
                    {/if}

                    {#if activity.meeting_point}
                        <div class="mb-4 pt-2 border-top">
                            <h5 class="fw-bold mb-2 mt-3">Meeting Point</h5>
                            <p class="text-muted mb-0">
                                <i class="ti ti-map-pin me-2"></i>{activity.meeting_point}
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
                                    <div class="fs-4 fw-bold" style="color: var(--travhub-base, #d11f1f);">
                                        {formatRp(effectivePricePerPax)}
                                        {#if applicableTier}
                                            <span class="fs-6 text-muted text-decoration-line-through fw-normal ms-1">{formatRp(basePricePerPax)}</span>
                                        {/if}
                                    </div>
                                    <small class="text-muted">per person{#if applicableTier} · {applicableTier.label}{/if}</small>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-3 small text-muted mb-4 pb-3 border-bottom">
                                {#if activity.duration_label}
                                    <span><i class="ti ti-clock me-1"></i>{activity.duration_label}</span>
                                {/if}
                                <span><i class="ti ti-users me-1"></i>Min {activity.min_pax}{#if activity.max_pax} · Max {activity.max_pax}{/if} pax</span>
                            </div>

                            <form onsubmit={submit}>
                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="booking_date">Activity Date <span class="text-danger">*</span></label>
                                    <DatePicker
                                        bind:value={form.booking_date}
                                        placeholder="Select activity date"
                                        id="booking_date"
                                        minDate={today}
                                        required={true}
                                    />
                                    {#if form.errors.booking_date}
                                        <div class="invalid-feedback" style="display: block;">{form.errors.booking_date}</div>
                                    {/if}
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="pax">Number of Participants <span class="text-danger">*</span></label>
                                    <input
                                        type="number"
                                        class="form-control {form.errors.pax ? 'is-invalid' : ''}"
                                        id="pax"
                                        bind:value={form.pax}
                                        min={activity.min_pax ?? 1}
                                        max={activity.max_pax || undefined}
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

                                <div class="mb-4">
                                    <label class="form-label fw-medium" for="voucher_code">Promo Code</label>
                                    <div class="input-group">
                                        <input
                                            type="text"
                                            class="form-control text-uppercase {voucherState === 'valid' ? 'is-valid' : voucherState === 'invalid' ? 'is-invalid' : ''}"
                                            id="voucher_code"
                                            bind:value={form.voucher_code}
                                            placeholder="Masukkan kode promo"
                                            maxlength="50"
                                            disabled={voucherState === 'loading' || voucherState === 'valid'}
                                        />
                                        {#if voucherState === 'valid'}
                                            <button
                                                type="button"
                                                class="btn btn-outline-secondary"
                                                onclick={removeVoucher}
                                                title="Hapus kode promo"
                                            >
                                                <i class="ti ti-x"></i>
                                            </button>
                                        {:else}
                                            <button
                                                type="button"
                                                class="btn {voucherState === 'invalid' ? 'btn-outline-danger' : 'btn-outline-secondary'}"
                                                onclick={applyVoucher}
                                                disabled={voucherState === 'loading' || !(form.voucher_code ?? '').trim()}
                                            >
                                                {#if voucherState === 'loading'}
                                                    <span class="spinner-border spinner-border-sm" role="status"></span>
                                                {:else}
                                                    Apply
                                                {/if}
                                            </button>
                                        {/if}
                                    </div>
                                    {#if voucherState === 'valid'}
                                        <div class="text-success small mt-1 fw-medium">
                                            <i class="ti ti-circle-check me-1"></i>{voucherMessage}
                                        </div>
                                    {:else if voucherState === 'invalid'}
                                        <div class="text-danger small mt-1">
                                            <i class="ti ti-alert-triangle me-1"></i>{voucherMessage}
                                        </div>
                                    {/if}
                                </div>

                                {#if packTiers.length > 0}
                                    <div class="mb-3 p-2 rounded-3 border border-dashed">
                                        <div class="small text-muted mb-1"><i class="ti ti-stack-2 me-1"></i>Group Discount</div>
                                        {#each packTiers as tier}
                                            <div class="d-flex justify-content-between small {applicableTier?.id === tier.id ? 'fw-bold text-success' : 'text-muted'}">
                                                <span>{tier.min_pax}{tier.max_pax ? '-' + tier.max_pax : '+'} pax</span>
                                                <span>{tier.discount_label}</span>
                                            </div>
                                        {/each}
                                    </div>
                                {/if}

                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">
                                            {formatRp(effectivePricePerPax)} × {form.pax} pax
                                            {#if applicableTier}<span class="text-success small ms-1">({applicableTier.label})</span>{/if}
                                        </span>
                                        <span class="fw-bold">{formatRp(subtotal)}</span>
                                    </div>
                                    {#if voucherDiscount > 0}
                                        <div class="d-flex justify-content-between mt-2 text-success">
                                            <span>Diskon promo ({form.voucher_code})</span>
                                            <span class="fw-bold">-{formatRp(voucherDiscount)}</span>
                                        </div>
                                    {/if}
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-muted">Total</span>
                                        <span class="fw-bold">{formatRp(totalAmount)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 text-success">
                                        <span>Bayar sekarang (DP {payment.dp_percent}%)</span>
                                        <span class="fw-bold">{formatRp(dpAmount)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2">
                                        <span class="text-muted">Sisa tunai</span>
                                        <span class="fw-bold">{formatRp(remainingCash)}</span>
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
                                            Book Now — DP {formatRp(dpAmount)}
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

    {#if relatedActivities?.length}
        <section style="padding: 20px 0 100px; background: #fff;">
            <div class="container">
                <h3 class="fw-bold mb-4">You Might Also Like</h3>
                <div class="row gutter-y-30">
                    {#each relatedActivities as item}
                        <div class="col-lg-4 col-md-6">
                            <a href={item.link_url} class="d-block text-decoration-none" style="color: inherit;">
                                <div class="card border-0 shadow-sm h-100" style="border-radius: 12px; overflow: hidden;">
                                    <img src={item.image_url} alt={item.title} style="height: 180px; object-fit: cover;" />
                                    <div class="card-body">
                                        <h6 class="fw-bold mb-1">{item.title}</h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2 small text-muted">
                                            {#if item.duration_label}
                                                <span><i class="ti ti-clock me-1"></i>{item.duration_label}</span>
                                            {/if}
                                            <span><i class="ti ti-users me-1"></i>Min {item.min_pax} pax</span>
                                        </div>
                                        <div class="fw-bold" style="color: var(--travhub-base, #d11f1f);">
                                            {formatRp(Number(item.price_per_pax))}
                                            <span class="fw-normal text-muted small">/ person</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    {/each}
                </div>
            </div>
        </section>
    {/if}

    <Footer />
</div>

<style>
    :global(.page-wrapper .form-control:focus) {
        border-color: var(--travhub-base, #d11f1f);
        box-shadow: 0 0 0 0.2rem rgba(var(--travhub-base-rgb, 209, 31, 31), 0.15);
    }

    /* Gallery: main photo + thumbnail grid stack on mobile, so they can't
       share one fixed height like the side-by-side desktop layout does. */
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
