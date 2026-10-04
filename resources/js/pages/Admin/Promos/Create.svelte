<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, Link } from '@inertiajs/svelte';

    let { voucher = null } = $props();

    const form = useForm({
        _method: voucher ? 'put' : 'post',
        code: voucher?.code ?? '',
        type: voucher?.type ?? 'percent',
        value: voucher?.value ?? '',
        min_spend: voucher?.min_spend ?? '',
        max_discount: voucher?.max_discount ?? '',
        usage_limit: voucher?.usage_limit ?? '',
        usage_limit_per_user: voucher?.usage_limit_per_user ?? '',
        valid_from: voucher?.valid_from ? new Date(voucher.valid_from).toISOString().slice(0, 10) : '',
        valid_until: voucher?.valid_until ? new Date(voucher.valid_until).toISOString().slice(0, 10) : '',
        is_active: voucher?.is_active ?? true,
    });

    function submit(e: Event) {
        e.preventDefault();
        if (voucher) {
            form.put(`/admin/promos/${voucher.id}`);
        } else {
            form.post('/admin/promos');
        }
    }
</script>

<AppHead title={voucher ? 'Edit Voucher' : 'Add Voucher'} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/promos"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to Promos
            </Link>
            <h4 class="mb-0">{voucher ? 'Edit Voucher' : 'Add Voucher'}</h4>
        </div>

        <div class="card">
            <div class="card-body">
                <form onsubmit={submit}>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label text-uppercase fs-12 fw-bold text-muted">Kode Voucher</label>
                                <input
                                    type="text"
                                    id="code"
                                    class="form-control text-uppercase"
                                    bind:value={form.code}
                                    required
                                    disabled={form.processing}
                                    maxlength="20"
                                    placeholder="e.g. HEMAT50"
                                />
                                {#if form.errors.code}<div class="text-danger small mt-1">{form.errors.code}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label text-uppercase fs-12 fw-bold text-muted">Tipe Diskon</label>
                                <select
                                    id="type"
                                    class="form-select"
                                    bind:value={form.type}
                                    required
                                    disabled={form.processing}
                                >
                                    <option value="percent">Persen (%)</option>
                                    <option value="fixed">Nominal (Rp)</option>
                                </select>
                                {#if form.errors.type}<div class="text-danger small mt-1">{form.errors.type}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="value" class="form-label text-uppercase fs-12 fw-bold text-muted">
                                    Nilai ({form.type === 'percent' ? '%' : 'Rp'})
                                </label>
                                <input
                                    type="number"
                                    id="value"
                                    class="form-control"
                                    bind:value={form.value}
                                    required
                                    disabled={form.processing}
                                    min="0.01"
                                    max={form.type === 'percent' ? 100 : undefined}
                                    step={form.type === 'percent' ? 'any' : '0.01'}
                                    placeholder={form.type === 'percent' ? 'e.g. 10' : 'e.g. 50000'}
                                />
                                {#if form.errors.value}<div class="text-danger small mt-1">{form.errors.value}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="min_spend" class="form-label text-uppercase fs-12 fw-bold text-muted">Min. Spend (Rp)</label>
                                <input
                                    type="number"
                                    id="min_spend"
                                    class="form-control"
                                    bind:value={form.min_spend}
                                    disabled={form.processing}
                                    min="0"
                                    step="0.01"
                                    placeholder="e.g. 100000"
                                />
                                {#if form.errors.min_spend}<div class="text-danger small mt-1">{form.errors.min_spend}</div>{/if}
                            </div>
                        </div>

                        {#if form.type === 'percent'}
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="max_discount" class="form-label text-uppercase fs-12 fw-bold text-muted">Max. Diskon (Rp)</label>
                                    <input
                                        type="number"
                                        id="max_discount"
                                        class="form-control"
                                        bind:value={form.max_discount}
                                        disabled={form.processing}
                                        min="0"
                                        step="0.01"
                                        placeholder="e.g. 50000"
                                    />
                                    {#if form.errors.max_discount}<div class="text-danger small mt-1">{form.errors.max_discount}</div>{/if}
                                </div>
                            </div>
                        {/if}

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="usage_limit" class="form-label text-uppercase fs-12 fw-bold text-muted">Batas Pemakaian Total</label>
                                <input
                                    type="number"
                                    id="usage_limit"
                                    class="form-control"
                                    bind:value={form.usage_limit}
                                    disabled={form.processing}
                                    min="1"
                                    placeholder="Kosongkan = tanpa batas"
                                />
                                {#if form.errors.usage_limit}<div class="text-danger small mt-1">{form.errors.usage_limit}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="usage_limit_per_user" class="form-label text-uppercase fs-12 fw-bold text-muted">Batas per Email</label>
                                <input
                                    type="number"
                                    id="usage_limit_per_user"
                                    class="form-control"
                                    bind:value={form.usage_limit_per_user}
                                    disabled={form.processing}
                                    min="1"
                                    placeholder="Kosongkan = tanpa batas"
                                />
                                {#if form.errors.usage_limit_per_user}<div class="text-danger small mt-1">{form.errors.usage_limit_per_user}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label text-uppercase fs-12 fw-bold text-muted">Berlaku Dari</label>
                                <input
                                    type="date"
                                    id="valid_from"
                                    class="form-control"
                                    bind:value={form.valid_from}
                                    disabled={form.processing}
                                />
                                {#if form.errors.valid_from}<div class="text-danger small mt-1">{form.errors.valid_from}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="valid_until" class="form-label text-uppercase fs-12 fw-bold text-muted">Berlaku Sampai</label>
                                <input
                                    type="date"
                                    id="valid_until"
                                    class="form-control"
                                    bind:value={form.valid_until}
                                    disabled={form.processing}
                                />
                                {#if form.errors.valid_until}<div class="text-danger small mt-1">{form.errors.valid_until}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <div class="form-check form-switch mt-4">
                                    <input
                                        type="checkbox"
                                        id="is_active"
                                        class="form-check-input"
                                        bind:checked={form.is_active}
                                        disabled={form.processing}
                                    />
                                    <label for="is_active" class="form-check-label">Aktif</label>
                                </div>
                                {#if form.errors.is_active}<div class="text-danger small mt-1">{form.errors.is_active}</div>{/if}
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 border-top pt-3 text-end">
                        <button
                            type="submit"
                            class="btn btn-primary d-inline-flex align-items-center gap-1 px-4"
                            disabled={form.processing}
                        >
                            <i class="ti ti-device-floppy fs-18"></i>
                            {voucher ? 'Update Voucher' : 'Save Voucher'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>