<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { useForm } from '@inertiajs/svelte';

    let { activity, customer = null } = $props<{
        activity: any;
        customer: { name: string; email: string; phone: string } | null;
    }>();

    let form = useForm({
        booking_date: '',
        pax: activity.min_pax ?? 1,
        customer_name: customer?.name || '',
        customer_email: customer?.email || '',
        customer_phone: customer?.phone || '',
        notes: '',
    });

    let totalPrice = $derived(Number(activity.price_per_pax) * form.pax);

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

    <section style="padding: 80px 0 100px; background: #f7f9fa;">
        <div class="container">
            <div class="row gutter-y-40">
                <!-- Left: Activity Details -->
                <div class="col-lg-7">
                    {#if activity.image_url}
                        <img
                            src={activity.image_url}
                            alt={activity.title}
                            class="img-fluid rounded-3 mb-4 w-100"
                            style="max-height: 420px; object-fit: cover;"
                        />
                    {/if}

                    <h2 class="fw-bold mb-1">{activity.title}</h2>
                    {#if activity.subtitle}
                        <p class="text-muted fs-5 mb-3">{activity.subtitle}</p>
                    {/if}

                    <div class="d-flex flex-wrap gap-3 mb-4">
                        {#if activity.duration_label}
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                <i class="ti ti-clock me-1"></i> {activity.duration_label}
                            </span>
                        {/if}
                        {#if activity.meeting_point}
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                                <i class="ti ti-map-pin me-1"></i> {activity.meeting_point}
                            </span>
                        {/if}
                        <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                            <i class="ti ti-users me-1"></i> Min {activity.min_pax} pax
                            {#if activity.max_pax} · Max {activity.max_pax} pax{/if}
                        </span>
                    </div>

                    {#if activity.description}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">About This Activity</h5>
                            <p class="text-muted">{activity.description}</p>
                        </div>
                    {/if}

                    {#if activity.includes?.length}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">What's Included</h5>
                            <ul class="list-unstyled">
                                {#each activity.includes as item}
                                    <li class="mb-1">
                                        <i class="ti ti-circle-check text-success me-2"></i>{item}
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    {/if}

                    {#if activity.excludes?.length}
                        <div class="mb-4">
                            <h5 class="fw-bold mb-2">Not Included</h5>
                            <ul class="list-unstyled">
                                {#each activity.excludes as item}
                                    <li class="mb-1 text-muted">
                                        <i class="ti ti-circle-x text-danger me-2"></i>{item}
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    {/if}
                </div>

                <!-- Right: Booking Form -->
                <div class="col-lg-5">
                    <div class="card shadow border-0 rounded-3 sticky-top" style="top: 100px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <div>
                                    <div class="fs-4 fw-bold text-primary">{formatRp(Number(activity.price_per_pax))}</div>
                                    <small class="text-muted">per person</small>
                                </div>
                            </div>

                            <form onsubmit={submit}>
                                <div class="mb-3">
                                    <label class="form-label fw-medium" for="booking_date">Activity Date <span class="text-danger">*</span></label>
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

                                <div class="border rounded-3 p-3 mb-4 bg-light">
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">{formatRp(Number(activity.price_per_pax))} × {form.pax} pax</span>
                                        <span class="fw-bold">{formatRp(totalPrice)}</span>
                                    </div>
                                </div>

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100 py-3 fw-bold"
                                    disabled={form.processing}
                                >
                                    {#if form.processing}
                                        <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                                        Processing...
                                    {:else}
                                        Book Now — {formatRp(totalPrice)}
                                    {/if}
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
