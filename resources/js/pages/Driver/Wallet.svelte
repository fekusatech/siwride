<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import { router, useForm, page } from '@inertiajs/svelte';
    import driverRoutes from '@/routes/driver';
    const walletRoute = driverRoutes.wallet;
    const withdrawalsRoute = driverRoutes.withdrawals;

    let { wallet, transactions, filters, transactionTypes, withdrawals } = $props<{
        wallet: { balance: string | number; is_negative: boolean; available_balance: string | number; min_withdrawal_amount: string | number };
        transactions: { data: any[]; links: any[]; from: number | null; to: number | null; total: number };
        filters: { type: string };
        transactionTypes: string[];
        withdrawals: any[];
    }>();

    const withdrawalForm = useForm({
        amount: '',
        bank_name: '',
        bank_account_number: '',
        bank_account_name: '',
    });

    function submitWithdrawal(e: Event) {
        e.preventDefault();
        withdrawalForm.post(withdrawalsRoute.store.url(), {
            preserveScroll: true,
            onSuccess: () => withdrawalForm.reset(),
        });
    }

    function formatRp(amount: number | string): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    function formatType(type: string): string {
        return type
            .split('_')
            .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
            .join(' ');
    }

    function formatStatus(status: string): string {
        return status.charAt(0).toUpperCase() + status.slice(1);
    }

    function filterTransactions(event: Event): void {
        const type = (event.currentTarget as HTMLSelectElement).value;

        router.get(walletRoute.url(type ? { query: { type } } : {}), {
            preserveState: true,
            preserveScroll: true,
        });
    }
</script>

<AppHead title="Driver Wallet" />

<DriverLayout>
    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap mb-4">
        <div>
            <h4 class="fw-bold mb-1">Wallet</h4>
            <p class="text-muted mb-0">Track your service earnings and wallet activity.</p>
        </div>
        <div class="text-muted small">{transactions.total} transactions</div>
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

    <div class="card border-0 shadow-sm mb-4 text-white" style="background: linear-gradient(135deg, #1e293b, #334155);">
        <div class="card-body p-4">
            <div class="d-flex align-items-start justify-content-between gap-3">
                <div>
                    <div class="text-white-50 small text-uppercase fw-bold mb-2">Available balance</div>
                    <div class="display-6 fw-bold">{formatRp(wallet.available_balance)}</div>
                    <div class="text-white-50 small mt-1">Total saldo: {formatRp(wallet.balance)}{#if withdrawals.length} · {withdrawals.length} penarikan dalam proses{/if}</div>
                    {#if wallet.is_negative}
                        <div class="badge bg-danger mt-2">Negative balance</div>
                    {/if}
                </div>
                <i class="ti ti-wallet fs-1 text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-1">Withdraw</h5>
                    <p class="text-muted small mb-3">Minimal penarikan {formatRp(wallet.min_withdrawal_amount)}</p>

                    <form onsubmit={submitWithdrawal}>
                        <div class="mb-3">
                            <label class="form-label fw-medium small text-uppercase text-muted" for="wd_amount">Amount (Rp)</label>
                            <input
                                type="number"
                                class="form-control {withdrawalForm.errors.amount ? 'is-invalid' : ''}"
                                id="wd_amount"
                                bind:value={withdrawalForm.amount}
                                min="0.01"
                                step="0.01"
                                required
                                disabled={withdrawalForm.processing}
                                placeholder="e.g. 250000"
                            />
                            {#if withdrawalForm.errors.amount}
                                <div class="invalid-feedback">{withdrawalForm.errors.amount}</div>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small text-uppercase text-muted" for="wd_bank">Bank Name</label>
                            <input
                                type="text"
                                class="form-control {withdrawalForm.errors.bank_name ? 'is-invalid' : ''}"
                                id="wd_bank"
                                bind:value={withdrawalForm.bank_name}
                                maxlength="100"
                                required
                                disabled={withdrawalForm.processing}
                                placeholder="e.g. BCA"
                            />
                            {#if withdrawalForm.errors.bank_name}
                                <div class="invalid-feedback">{withdrawalForm.errors.bank_name}</div>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small text-uppercase text-muted" for="wd_account_number">Account Number</label>
                            <input
                                type="text"
                                class="form-control {withdrawalForm.errors.bank_account_number ? 'is-invalid' : ''}"
                                id="wd_account_number"
                                bind:value={withdrawalForm.bank_account_number}
                                maxlength="50"
                                required
                                disabled={withdrawalForm.processing}
                                placeholder="e.g. 1234567890"
                            />
                            {#if withdrawalForm.errors.bank_account_number}
                                <div class="invalid-feedback">{withdrawalForm.errors.bank_account_number}</div>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium small text-uppercase text-muted" for="wd_account_name">Account Name</label>
                            <input
                                type="text"
                                class="form-control {withdrawalForm.errors.bank_account_name ? 'is-invalid' : ''}"
                                id="wd_account_name"
                                bind:value={withdrawalForm.bank_account_name}
                                maxlength="100"
                                required
                                disabled={withdrawalForm.processing}
                                placeholder="e.g. BUDI SANTOSO"
                            />
                            {#if withdrawalForm.errors.bank_account_name}
                                <div class="invalid-feedback">{withdrawalForm.errors.bank_account_name}</div>
                            {/if}
                        </div>

                        <button
                            type="submit"
                            class="btn btn-dark w-100 d-inline-flex align-items-center justify-content-center gap-2"
                            disabled={withdrawalForm.processing || Number(wallet.available_balance) <= 0}
                        >
                            {#if withdrawalForm.processing}
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                            {:else}
                                <i class="ti ti-cash"></i>
                            {/if}
                            Request Withdrawal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Withdrawal Requests</h5>
                    {#if withdrawals.length}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="px-3">Date</th>
                                        <th>Amount</th>
                                        <th>Bank</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each withdrawals as withdrawal (withdrawal.id)}
                                        <tr>
                                            <td class="px-3 text-muted small">{new Date(withdrawal.created_at).toLocaleDateString('id-ID')}</td>
                                            <td class="fw-bold">{formatRp(withdrawal.amount)}</td>
                                            <td class="small">{withdrawal.bank_name} ••••{withdrawal.bank_account_last_four ?? withdrawal.bank_account_number.slice(-4)}</td>
                                            <td>
                                                <span class="badge {withdrawal.status === 'pending' ? 'bg-warning-subtle text-warning' : 'bg-info-subtle text-info'}">
                                                    {formatStatus(withdrawal.status)}
                                                </span>
                                            </td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    {:else}
                        <div class="text-center text-muted py-5">
                            <i class="ti ti-cash-off fs-1 d-block mb-2"></i>
                            Belum ada permintaan penarikan.
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap p-4 border-bottom">
                <h5 class="fw-bold mb-0">Transaction history</h5>
                <select class="form-select" style="max-width: 240px;" value={filters.type} onchange={filterTransactions} aria-label="Filter transaction type">
                    <option value="">All transaction types</option>
                    {#each transactionTypes as type}
                        <option value={type}>{formatType(type)}</option>
                    {/each}
                </select>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4">Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end px-4">Balance after</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each transactions.data as transaction (transaction.id)}
                            <tr>
                                <td class="px-4 text-muted small">{transaction.created_at}</td>
                                <td><span class="badge bg-light text-dark">{formatType(transaction.type)}</span></td>
                                <td>{transaction.description}</td>
                                <td class="text-end fw-bold {Number(transaction.amount) >= 0 ? 'text-success' : 'text-danger'}">
                                    {Number(transaction.amount) >= 0 ? '+' : ''}{formatRp(transaction.amount)}
                                </td>
                                <td class="text-end px-4">{formatRp(transaction.balance_after)}</td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">No transactions found.</td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>

            <Pagination links={transactions.links} />
        </div>
    </div>
</DriverLayout>
