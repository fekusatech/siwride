<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router, page } from '@inertiajs/svelte';

    let { reassignments, filters, statuses } = $props<{
        reassignments: { data: any[]; links: any[] };
        filters: { search: string; status: string };
        statuses: string[];
    }>();

    let search = $state(filters.search ?? '');
    let status = $state(filters.status ?? '');

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/service-reassignments', { search, status }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
        status = filters.status ?? '';
    });

    function filterByStatus(newStatus: string) {
        status = newStatus;
        router.get('/admin/service-reassignments', { search, status }, { preserveState: true, replace: true });
    }

    function approve(id: number) {
        if (confirm('Approve reassignment ini? DP akan ditransfer dari driver lama ke driver baru.')) {
            router.patch(`/admin/service-reassignments/${id}/approve`, {}, { preserveScroll: true });
        }
    }

    function reject(id: number) {
        const reason = window.prompt('Alasan penolakan (opsional):') ?? '';
        router.patch(`/admin/service-reassignments/${id}/reject`, { rejection_reason: reason }, { preserveScroll: true });
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    function driverName(d: any): string {
        if (!d) return '—';
        return `${d.firstname ?? ''} ${d.lastname ?? ''}`.trim() || d.email;
    }
</script>

<AppHead title="Service Reassignments" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Service Reassignments</h4>
                <p class="text-muted mb-0">Kelola permintaan reassignment booking driver</p>
            </div>
        </div>

        {#if (page.props as any).flash?.success}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i>
                {(page.props as any).flash.success}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}

        {#if (page.props as any).flash?.error}
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>
                {(page.props as any).flash.error}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search booking code, driver, or reason..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex gap-2 flex-wrap">
                                {#each ['', ...statuses] as s}
                                    <button
                                        onclick={() => filterByStatus(s)}
                                        class="btn btn-sm {status === s ? 'btn-primary' : 'btn-outline-secondary'}"
                                    >
                                        {s === '' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1)}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Booking</th>
                                <th>Service</th>
                                <th>From Driver</th>
                                <th>To Driver</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each reassignments.data as r}
                                <tr>
                                    <td>
                                        <a href={`/admin/service-reassignments/${r.id}`} class="fw-medium">
                                            {r.booking?.booking_code ?? '—'}
                                        </a>
                                        <div class="small text-muted">{r.booking?.customer_name ?? ''}</div>
                                    </td>
                                    <td class="small">{r.booking?.driver_service?.title ?? '—'}</td>
                                    <td>
                                        <div class="fw-medium">{driverName(r.from_driver)}</div>
                                        <small class="text-muted">{r.from_driver?.email}</small>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{driverName(r.to_driver)}</div>
                                        <small class="text-muted">{r.to_driver?.email}</small>
                                    </td>
                                    <td class="text-muted small" style="max-width: 180px;">
                                        <span class="d-inline-block text-truncate" title={r.reason ?? ''}>
                                            {r.reason ?? '—'}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {statusColors[r.status] ?? ''}">
                                            {r.status}
                                        </span>
                                    </td>
                                    <td class="small">{new Date(r.created_at).toLocaleDateString('id-ID')}</td>
                                    <td class="text-center">
                                        {#if r.status === 'pending'}
                                            <div class="d-inline-flex gap-1">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success"
                                                    onclick={() => approve(r.id)}
                                                >
                                                    <i class="ti ti-check me-1"></i>Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick={() => reject(r.id)}
                                                >
                                                    <i class="ti ti-x me-1"></i>Reject
                                                </button>
                                            </div>
                                        {:else}
                                            <span class="text-muted small">
                                                {#if r.rejection_reason}
                                                    <span title={r.rejection_reason}>Rejected</span>
                                                {:else}
                                                    —
                                                {/if}
                                            </span>
                                        {/if}
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">Belum ada permintaan reassignment.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={reassignments.links} />
            </div>
        </div>
    </div>
</AdminLayout>
