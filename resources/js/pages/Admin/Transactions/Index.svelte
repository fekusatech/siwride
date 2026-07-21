<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';

    let { transactions, filters } = $props();
    // svelte-ignore state_referenced_locally
    let search = $state(filters.search ?? '');

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';

        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/transactions',
                    { search },
                    { preserveState: true, replace: true },
                );
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let transactionList = $derived(transactions.data);

    function statusBadgeClass(status: string) {
        return (
            {
                paid: 'bg-success-subtle text-success',
                pending: 'bg-warning-subtle text-warning',
                failed: 'bg-danger-subtle text-danger',
                expired: 'bg-secondary-subtle text-secondary',
            }[status] ?? 'bg-secondary-subtle text-secondary'
        );
    }

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }
</script>

<AppHead title="Transactions" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Transactions</h4>
                <p class="text-muted mb-0">Manual transactions with QRIS payment via Xendit</p>
            </div>
            <Link
                href="/admin/transactions/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                New Transaction
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search transactions..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each transactionList as transaction (transaction.id)}
                                <tr>
                                    <td class="fw-bold text-dark">{transaction.code}</td>
                                    <td class="text-wrap" style="max-width: 300px;">
                                        {transaction.description}
                                    </td>
                                    <td>{formatCurrency(transaction.amount)}</td>
                                    <td>
                                        <span
                                            class="badge text-capitalize {statusBadgeClass(
                                                transaction.status,
                                            )}"
                                        >
                                            {transaction.status}
                                        </span>
                                    </td>
                                    <td>{new Date(transaction.created_at).toLocaleString('id-ID')}</td>
                                    <td class="text-center">
                                        <Link
                                            href={`/admin/transactions/${transaction.id}`}
                                            class="btn btn-sm btn-icon btn-primary"
                                            aria-label="View transaction"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </Link>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            {#if search}
                                                No transactions match "{search}"
                                            {:else}
                                                No transactions found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={transactions.links} />
            </div>
        </div>
    </div>
</AdminLayout>
