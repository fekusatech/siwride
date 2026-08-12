<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { service } = $props();

    const statuses = ['pending', 'approved', 'rejected'];

    let rejectionReason = $state(service.rejection_reason ?? '');

    function updateStatus(newStatus: string) {
        router.patch(
            `/admin/driver-services/${service.id}/status`,
            {
                status: newStatus,
                rejection_reason: newStatus === 'rejected' ? rejectionReason : null,
            },
            { preserveScroll: true },
        );
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
</script>

<AppHead title="Driver Service Detail" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Service Detail</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><Link href="/admin/driver-services">Driver Services</Link></li>
                    <li class="breadcrumb-item active">{service.title}</li>
                </ol>
            </div>
            <Link href="/admin/driver-services" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        {#if service.image_url}
                            <img src={service.image_url} alt={service.title} class="rounded-3 w-100 mb-3" style="max-height: 320px; object-fit: cover;">
                        {/if}
                        <h4 class="mb-1">{service.title}</h4>
                        {#if service.price_per_pax}
                            <p class="fw-bold mb-2">{formatRp(Number(service.price_per_pax))} / pax</p>
                        {/if}
                        {#if service.description}
                            <p class="text-muted" style="white-space: pre-wrap;">{service.description}</p>
                        {/if}

                        <div class="row g-2 my-2 small text-muted">
                            <div class="col-md-4"><strong>Min pax:</strong> {service.min_pax ?? '-'}</div>
                            <div class="col-md-4"><strong>Max pax:</strong> {service.max_pax ?? 'Unlimited'}</div>
                            <div class="col-md-4"><strong>Duration:</strong> {service.duration_label ?? '-'}</div>
                        </div>
                        {#if service.meeting_point}
                            <p class="small text-muted mb-0"><strong>Meeting point:</strong> {service.meeting_point}</p>
                        {/if}

                        {#if service.includes?.length}
                            <div class="mt-3">
                                <h6 class="fw-bold">Includes</h6>
                                <ul class="small text-muted">
                                    {#each service.includes as item}<li>{item}</li>{/each}
                                </ul>
                            </div>
                        {/if}
                        {#if service.excludes?.length}
                            <div class="mt-2">
                                <h6 class="fw-bold">Excludes</h6>
                                <ul class="small text-muted">
                                    {#each service.excludes as item}<li>{item}</li>{/each}
                                </ul>
                            </div>
                        {/if}

                        {#if service.gallery_urls?.length}
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                {#each service.gallery_urls as url}
                                    <img src={url} alt={service.title} class="rounded-2" style="width: 100px; height: 100px; object-fit: cover;">
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Service Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Current Status</small>
                            <span class="badge fs-6 {statusColors[service.status] ?? ''}">
                                {service.status}
                            </span>
                        </div>

                        {#if service.status === 'rejected' && service.rejection_reason}
                            <div class="alert alert-danger small mb-3">{service.rejection_reason}</div>
                        {/if}

                        <div class="mb-3">
                            <label class="form-label small" for="rejection_reason">Rejection reason (optional)</label>
                            <textarea id="rejection_reason" class="form-control form-control-sm" rows="2" bind:value={rejectionReason}></textarea>
                        </div>

                        <hr>
                        <p class="small text-muted mb-2">Update status:</p>
                        <div class="d-flex flex-column gap-2">
                            {#each statuses as s}
                                <button
                                    onclick={() => updateStatus(s)}
                                    class="btn btn-sm {service.status === s ? 'btn-secondary disabled' : 'btn-outline-secondary'}"
                                    disabled={service.status === s}
                                >
                                    Mark as {s.charAt(0).toUpperCase() + s.slice(1)}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Driver</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Name</small>
                            <span>{service.driver?.name ?? '-'}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <span>{service.driver?.email ?? '-'}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
