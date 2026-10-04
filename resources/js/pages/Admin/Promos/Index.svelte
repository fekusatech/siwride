<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { vouchers, filters } = $props();
    let search = $state(filters.search ?? '');
    let filter = $state(filters.filter ?? '');

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/promos', { search, filter }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
        filter = filters.filter ?? '';
    });

    function filterBy(newFilter: string) {
        filter = newFilter;
        router.get('/admin/promos', { search, filter }, { preserveState: true, replace: true });
    }

    let voucherList = $derived(vouchers.data);

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
</script>

<AppHead title="Promos" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Promos</h4>
                <p class="text-muted mb-0">Kelola voucher diskon untuk layanan</p>
            </div>
            <Link
                href="/admin/promos/create"
                class="btn btn-primary d-inline-flex align-items-center gap-1"
            >
                <i class="ti ti-plus"></i> Add Voucher
            </Link>
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
                                    placeholder="Search by code..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex gap-2">
                                {#each ['', 'active', 'expired'] as f}
                                    <button
                                        onclick={() => filterBy(f)}
                                        class="btn btn-sm {filter === f ? 'btn-primary' : 'btn-outline-secondary'}"
                                    >
                                        {f === '' ? 'All' : f.charAt(0).toUpperCase() + f.slice(1)}
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
                                <th>Code</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Min. Spend</th>
                                <th>Used</th>
                                <th>Limit</th>
                                <th>Valid Until</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each voucherList as voucher}
                                <tr>
                                    <td>
                                        <Link
                                            href={`/admin/promos/${voucher.id}`}
                                            class="fw-medium text-primary"
                                        >
                                            {voucher.code}
                                        </Link>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {voucher.type === 'percent' ? '% Diskon' : 'Rp Fixed'}
                                        </span>
                                    </td>
                                    <td>
                                        {voucher.type === 'percent' ? Number(voucher.value) + '%' : formatRp(Number(voucher.value))}
                                    </td>
                                    <td>{Number(voucher.min_spend) > 0 ? formatRp(Number(voucher.min_spend)) : '-'}</td>
                                    <td>
                                        <span class="badge {voucher.usage_limit !== null && Number(voucher.used_count) >= Number(voucher.usage_limit) ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'}">
                                            {voucher.used_count}
                                        </span>
                                    </td>
                                    <td>{voucher.usage_limit ?? '∞'}</td>
                                    <td>
                                        {voucher.valid_until
                                            ? new Date(voucher.valid_until).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                                            : '∞'}
                                    </td>
                                    <td>
                                        <span class="badge {voucher.is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">
                                            {voucher.is_active ? 'Aktif' : 'Nonaktif'}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <Link
                                                href={`/admin/promos/${voucher.id}`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-eye"></i>
                                            </Link>
                                            <Link
                                                href={`/admin/promos/${voucher.id}/edit`}
                                                class="btn btn-sm btn-icon btn-outline-secondary"
                                            >
                                                <i class="ti ti-pencil"></i>
                                            </Link>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-icon btn-outline-{voucher.is_active ? 'danger' : 'success'}"
                                                onclick={() =>
                                                    router.patch(
                                                        `/admin/promos/${voucher.id}/toggle`,
                                                        {},
                                                        { preserveScroll: true },
                                                    )}
                                            >
                                                <i
                                                    class="ti {voucher.is_active ? 'ti-toggle-off' : 'ti-toggle-on'}"
                                                ></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">Belum ada voucher.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={vouchers.links} />
            </div>
        </div>
    </div>
</AdminLayout>