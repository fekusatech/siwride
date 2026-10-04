<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router, page } from '@inertiajs/svelte';

    let { voucher, stats, redemptions } = $props();

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    function toggleActive() {
        router.patch(`/admin/promos/${voucher.id}/toggle`, {}, { preserveScroll: true });
    }

    function destroy() {
        if (Number(voucher.redemptions_count) > 0) {
            alert('Voucher dengan redeem tidak bisa dihapus. Nonaktifkan saja.');
            return;
        }
        if (confirm(`Hapus voucher ${voucher.code}?`)) {
            router.delete(`/admin/promos/${voucher.id}`, { preserveScroll: true });
        }
    }
</script>

<AppHead title={`Voucher ${voucher.code}`} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/promos"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to Promos
            </Link>
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <h4 class="mb-0">{voucher.code}</h4>
                    <span class="badge {voucher.is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'}">
                        {voucher.is_active ? 'Aktif' : 'Nonaktif'}
                    </span>
                </div>
                <div class="d-flex gap-2">
                    <Link href={`/admin/promos/${voucher.id}/edit`} class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                        <i class="ti ti-pencil"></i> Edit
                    </Link>
                    <button
                        type="button"
                        class="btn btn-outline-{voucher.is_active ? 'danger' : 'success'} d-inline-flex align-items-center gap-1"
                        onclick={toggleActive}
                    >
                        <i class="ti {voucher.is_active ? 'ti-toggle-off' : 'ti-toggle-on'}"></i>
                        {voucher.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-danger d-inline-flex align-items-center gap-1"
                        onclick={destroy}
                    >
                        <i class="ti ti-trash"></i> Hapus
                    </button>
                </div>
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

        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">Detail Voucher</div>
                    <div class="card-body">
                        <dl class="row mb-0">
                            <dt class="col-sm-4 text-muted">Kode</dt>
                            <dd class="col-sm-8"><code>{voucher.code}</code></dd>

                            <dt class="col-sm-4 text-muted">Tipe</dt>
                            <dd class="col-sm-8">{voucher.type === 'percent' ? 'Diskon Persen (%)' : 'Diskon Nominal (Rp)'}</dd>

                            <dt class="col-sm-4 text-muted">Nilai</dt>
                            <dd class="col-sm-8">
                                {voucher.type === 'percent' ? Number(voucher.value) + '%' : formatRp(Number(voucher.value))}
                            </dd>

                            <dt class="col-sm-4 text-muted">Min. Spend</dt>
                            <dd class="col-sm-8">{Number(voucher.min_spend) > 0 ? formatRp(Number(voucher.min_spend)) : '-'}</dd>

                            {#if voucher.max_discount}
                                <dt class="col-sm-4 text-muted">Max. Diskon</dt>
                                <dd class="col-sm-8">{formatRp(Number(voucher.max_discount))}</dd>
                            {/if}

                            <dt class="col-sm-4 text-muted">Batas Pemakaian</dt>
                            <dd class="col-sm-8">{voucher.usage_limit ?? 'Tanpa batas'}</dd>

                            <dt class="col-sm-4 text-muted">Batas per Email</dt>
                            <dd class="col-sm-8">{voucher.usage_limit_per_user ?? 'Tanpa batas'}</dd>

                            <dt class="col-sm-4 text-muted">Periode</dt>
                            <dd class="col-sm-8">
                                {voucher.valid_from ? new Date(voucher.valid_from).toLocaleDateString('id-ID') : '∞'}
                                &nbsp;-&nbsp;
                                {voucher.valid_until ? new Date(voucher.valid_until).toLocaleDateString('id-ID') : '∞'}
                            </dd>

                            <dt class="col-sm-4 text-muted">Dibuat</dt>
                            <dd class="col-sm-8">{new Date(voucher.created_at).toLocaleString('id-ID')}</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center">
                                <h2 class="mb-0 text-primary">{stats.total_redemptions}</h2>
                                <div class="text-muted small">Total Redeem</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center">
                                <h2 class="mb-0 text-success">{formatRp(stats.total_discount)}</h2>
                                <div class="text-muted small">Total Nilai Diskon</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold">20 Redeem Terakhir</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Booking</th>
                                        <th>Customer</th>
                                        <th>Tanggal Booking</th>
                                        <th>Diskon</th>
                                        <th>Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each redemptions as redemption}
                                        <tr>
                                            <td>
                                                <Link href={`/admin/driver-service-bookings/${redemption.booking_id}`} class="text-primary">
                                                    {redemption.booking?.booking_code ?? '-'}
                                                </Link>
                                            </td>
                                            <td>{redemption.booking?.customer_name ?? redemption.email}</td>
                                            <td>
                                                {redemption.booking?.booking_date
                                                    ? new Date(redemption.booking.booking_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
                                                    : '-'}
                                            </td>
                                            <td class="text-success">{formatRp(Number(redemption.discount_amount))}</td>
                                            <td>{new Date(redemption.created_at).toLocaleString('id-ID')}</td>
                                        </tr>
                                    {:else}
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Belum ada redeem.</td>
                                        </tr>
                                    {/each}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>