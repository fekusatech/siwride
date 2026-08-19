<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link } from '@inertiajs/svelte';
    import { router, page } from '@inertiajs/svelte';

    let { reassignment } = $props<{ reassignment: any }>();

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    function driverName(d: any): string {
        if (!d) return '—';
        return `${d.firstname ?? ''} ${d.lastname ?? ''}`.trim() || d.email;
    }

    function approve() {
        if (confirm('Approve reassignment ini? DP akan ditransfer dari driver lama ke driver baru.')) {
            router.patch(`/admin/service-reassignments/${reassignment.id}/approve`, {}, { preserveScroll: true });
        }
    }

    function reject() {
        const reason = window.prompt('Alasan penolakan (opsional):') ?? '';
        router.patch(`/admin/service-reassignments/${reassignment.id}/reject`, { rejection_reason: reason }, { preserveScroll: true });
    }
</script>

<AppHead title={`Reassignment ${reassignment.booking?.booking_code ?? ''}`} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Reassignment Detail</h4>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><Link href="/admin/service-reassignments">Reassignments</Link></li>
                        <li class="breadcrumb-item active">#{reassignment.id}</li>
                    </ol>
                </nav>
            </div>
            {#if reassignment.status === 'pending'}
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success" onclick={approve}>
                        <i class="ti ti-check me-1"></i>Approve
                    </button>
                    <button type="button" class="btn btn-danger" onclick={reject}>
                        <i class="ti ti-x me-1"></i>Reject
                    </button>
                </div>
            {/if}
        </div>

        {#if (page.props as any).flash?.success}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i>
                {(page.props as any).flash.success}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="ti ti-info-circle me-2"></i>Booking</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Booking Code</dt>
                            <dd class="col-sm-8">{reassignment.booking?.booking_code ?? '—'}</dd>
                            <dt class="col-sm-4">Service</dt>
                            <dd class="col-sm-8">{reassignment.booking?.driver_service?.title ?? '—'}</dd>
                            <dt class="col-sm-4">Customer</dt>
                            <dd class="col-sm-8">{reassignment.booking?.customer_name ?? '—'}</dd>
                            <dt class="col-sm-4">Date</dt>
                            <dd class="col-sm-8">{reassignment.booking?.booking_date ?? '—'}</dd>
                        </dl>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="ti ti-users me-2"></i>Drivers</h6>
                    </div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">From (Old)</dt>
                            <dd class="col-sm-8">
                                <div class="fw-medium">{driverName(reassignment.from_driver)}</div>
                                <small class="text-muted">{reassignment.from_driver?.email}</small>
                            </dd>
                            <dt class="col-sm-4">To (New)</dt>
                            <dd class="col-sm-8">
                                <div class="fw-medium">{driverName(reassignment.to_driver)}</div>
                                <small class="text-muted">{reassignment.to_driver?.email}</small>
                            </dd>
                            <dt class="col-sm-4">Assigned Now</dt>
                            <dd class="col-sm-8">{driverName(reassignment.booking?.assigned_driver)}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="ti ti-clipboard-list me-2"></i>Request & Decision</h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-2">Status</dt>
                    <dd class="col-sm-10">
                        <span class="badge {statusColors[reassignment.status] ?? ''}">{reassignment.status}</span>
                    </dd>
                    <dt class="col-sm-2">Reason</dt>
                    <dd class="col-sm-10">{reassignment.reason ?? '—'}</dd>
                    <dt class="col-sm-2">Requested</dt>
                    <dd class="col-sm-10">{new Date(reassignment.created_at).toLocaleString('id-ID')}</dd>
                    {#if reassignment.decided_at}
                        <dt class="col-sm-2">Decided At</dt>
                        <dd class="col-sm-10">{new Date(reassignment.decided_at).toLocaleString('id-ID')}</dd>
                        <dt class="col-sm-2">Decided By</dt>
                        <dd class="col-sm-10">{driverName(reassignment.decider)}</dd>
                    {/if}
                    {#if reassignment.rejection_reason}
                        <dt class="col-sm-2">Rejection Reason</dt>
                        <dd class="col-sm-10 text-danger">{reassignment.rejection_reason}</dd>
                    {/if}
                </dl>
            </div>
        </div>
    </div>
</AdminLayout>
