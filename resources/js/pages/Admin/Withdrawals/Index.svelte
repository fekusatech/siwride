<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router, page } from '@inertiajs/svelte';

    let { withdrawals, filters, statuses } = $props<{
        withdrawals: { data: any[]; links: any[] };
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
                router.get('/admin/withdrawals', { search, status }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
        status = filters.status ?? '';
    });

    function filterByStatus(newStatus: string) {
        status = newStatus;
        router.get('/admin/withdrawals', { search, status }, { preserveState: true, replace: true });
    }

    function approve(id: number) {
        router.patch(`/admin/withdrawals/${id}/approve`, {}, { preserveScroll: true });
    }

    function reject(id: number) {
        const reason = window.prompt('Alasan penolakan:');
        if (reason === null) return;
        router.patch(`/admin/withdrawals/${id}/reject`, { rejection_reason: reason }, { preserveScroll: true });
    }

    function markPaid(id: number) {
        if (confirm('Tandai penarikan ini sudah dibayar? Saldo akan didebit dari wallet driver.')) {
            router.patch(`/admin/withdrawals/${id}/mark-paid`, {}, { preserveScroll: true });
        }
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-info-subtle text-info',
        rejected: 'bg-danger-subtle text-danger',
        paid: 'bg-success-subtle text-success',
    };

    function formatRp(amount: number | string): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    function maskAccount(number: string): string {
        if (number.length <= 4) return number;
        return '••••' + number.slice(-4);
    }
</script>

<AppHead title="Withdrawals" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Withdrawals</h4>
                <p class="text-muted mb-0">Kelola permintaan penarikan saldo driver</p>
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
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search by driver, bank, or account..."
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
                                <th>Driver</th>
                                <th>Amount</th>
                                <th>Bank Account</th>
                                <th>Status</th>
                                <th>Requested</th>
                                <th>Rejection</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each withdrawals.data as withdrawal}
                                <tr>
                                    <td>
                                        <div class="fw-medium">
                                            {withdrawal.driver?.firstname} {withdrawal.driver?.lastname}
                                        </div>
                                        <small class="text-muted">{withdrawal.driver?.email}</small>
                                        {#if withdrawal.driver_id}
                                            <div>
                                                <a href={`/admin/driver-wallets/${withdrawal.driver_id}`} class="small text-primary">
                                                    <i class="ti ti-wallet me-1"></i>Wallet
                                                </a>
                                            </div>
                                        {/if}
                                    </td>
                                    <td class="fw-bold">{formatRp(withdrawal.amount)}</td>
                                    <td>
                                        <div>{withdrawal.bank_name}</div>
                                        <small class="text-muted">{maskAccount(withdrawal.bank_account_number)} · {withdrawal.bank_account_name}</small>
                                    </td>
                                    <td>
                                        <span class="badge {statusColors[withdrawal.status] ?? ''}">
                                            {withdrawal.status}
                                        </span>
                                    </td>
                                    <td>{new Date(withdrawal.created_at).toLocaleDateString('id-ID')}</td>
                                    <td class="text-muted small" style="max-width: 160px;">
                                        <span class="d-inline-block text-truncate" title={withdrawal.rejection_reason ?? ''}>
                                            {withdrawal.rejection_reason ?? '-'}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {#if withdrawal.status === 'pending'}
                                            <div class="d-inline-flex gap-1">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-success"
                                                    onclick={() => approve(withdrawal.id)}
                                                >
                                                    <i class="ti ti-check me-1"></i>Approve
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-danger"
                                                    onclick={() => reject(withdrawal.id)}
                                                >
                                                    <i class="ti ti-x me-1"></i>Reject
                                                </button>
                                            </div>
                                        {:else if withdrawal.status === 'approved'}
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary"
                                                onclick={() => markPaid(withdrawal.id)}
                                            >
                                                <i class="ti ti-cash me-1"></i>Mark Paid
                                            </button>
                                        {:else}
                                            <span class="text-muted small">—</span>
                                        {/if}
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">Belum ada permintaan penarikan.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={withdrawals.links} />
            </div>
        </div>
    </div>
</AdminLayout>