<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { router, page } from '@inertiajs/svelte';

    let { driver, wallet, transactions } = $props<{
        driver: { id: number; name: string; email: string; phone: string };
        wallet: { balance: string; is_negative: boolean };
        transactions: any[];
    }>();

    let amount = $state('');
    let reason = $state('');
    let submitting = $state(false);

    function formatRp(value: number | string): string {
        return 'Rp ' + Number(value).toLocaleString('id-ID');
    }

    function formatAmount(value: number | string): string {
        const n = Number(value);
        return (n > 0 ? '+ ' : '') + formatRp(n);
    }

    function applyAdjustment() {
        if (submitting) return;
        const parsed = Number(amount);
        if (!Number.isFinite(parsed) || parsed === 0) {
            alert('Masukkan nominal selain 0.');
            return;
        }
        if (!reason.trim()) {
            alert('Alasan wajib diisi.');
            return;
        }
        if (!confirm(`Terapkan penyesuaian ${formatRp(parsed)} ke wallet driver?`)) return;
        submitting = true;
        router.post(
            `/admin/driver-wallets/${driver.id}/adjustment`,
            { amount: parsed, reason: reason.trim() },
            {
                preserveScroll: true,
                onSuccess: () => {
                    amount = '';
                    reason = '';
                    submitting = false;
                },
                onError: () => {
                    submitting = false;
                },
            },
        );
    }
</script>

<AppHead title="Driver Wallet" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Driver Wallet</h4>
                <p class="text-muted mb-0">Saldo dan riwayat transaksi {driver.name}</p>
            </div>
            <a href="/admin/driver-service-bookings" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-arrow-left me-1"></i>Kembali
            </a>
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

        <div class="row g-3">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-circle bg-primary-subtle text-primary me-3">
                                {driver.name.charAt(0)}
                            </div>
                            <div>
                                <h6 class="mb-0">{driver.name}</h6>
                                <small class="text-muted">{driver.email}</small>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted">Saldo</span>
                                <span class="fw-bold fs-5 {wallet.is_negative ? 'text-danger' : ''}">
                                    {formatRp(wallet.balance)}
                                </span>
                            </div>
                            {#if wallet.is_negative}
                                <div class="mt-2">
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="ti ti-alert-triangle me-1"></i>Negative balance
                                    </span>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="ti ti-adjustments me-1"></i>Manual Adjustment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Nominal</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input
                                    type="number"
                                    step="0.01"
                                    class="form-control"
                                    placeholder="Positif = tambah, negatif = kurangi"
                                    bind:value={amount}
                                />
                            </div>
                            <small class="text-muted">Gunakan angka negatif untuk mengurangi saldo.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Alasan <span class="text-danger">*</span></label>
                            <textarea
                                class="form-control"
                                rows="3"
                                maxlength="500"
                                placeholder="Wajib diisi, misal: koreksi komisi booking SVC-XXX"
                                bind:value={reason}
                            ></textarea>
                        </div>
                        <button
                            type="button"
                            class="btn btn-primary w-100"
                            onclick={applyAdjustment}
                            disabled={submitting}
                        >
                            <i class="ti ti-device-floppy me-1"></i>Apply Adjustment
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white">
                        <h6 class="mb-0"><i class="ti ti-history me-1"></i>Riwayat Transaksi</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap">
                            <thead class="bg-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Tipe</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Nominal</th>
                                    <th class="text-end">Saldo</th>
                                    <th>Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each transactions as txn}
                                    <tr>
                                        <td class="text-muted small">
                                            {new Date(txn.created_at).toLocaleString('id-ID')}
                                        </td>
                                        <td>
                                            <span class="badge {txn.type === 'admin_adjustment' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary'}">
                                                {txn.type}
                                            </span>
                                        </td>
                                        <td class="small">
                                            <span class="d-inline-block text-truncate" style="max-width: 260px;" title={txn.description}>
                                                {txn.description}
                                            </span>
                                            {#if txn.booking_code}
                                                <div><code class="small">{txn.booking_code}</code></div>
                                            {/if}
                                        </td>
                                        <td class="text-end fw-bold {Number(txn.amount) < 0 ? 'text-danger' : 'text-success'}">
                                            {formatAmount(txn.amount)}
                                        </td>
                                        <td class="text-end">{formatRp(txn.balance_after)}</td>
                                        <td class="text-muted small">{txn.created_by_name ?? 'System'}</td>
                                    </tr>
                                {:else}
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            Belum ada transaksi.
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .avatar-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        text-transform: uppercase;
    }
</style>