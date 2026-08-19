<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router, page } from '@inertiajs/svelte';

    let { tiers } = $props<{
        tiers: { data: any[]; links: any[] };
    }>();

    let showForm = $state(false);
    let editingId = $state<number | null>(null);

    let form = $state({
        label: '',
        min_pax: 4,
        max_pax: null as number | null,
        discount_type: 'percent',
        discount_value: 10,
        sort_order: 0,
        is_active: true,
    });

    function resetForm() {
        form = {
            label: '',
            min_pax: 4,
            max_pax: null,
            discount_type: 'percent',
            discount_value: 10,
            sort_order: 0,
            is_active: true,
        };
        editingId = null;
    }

    function openCreate() {
        resetForm();
        showForm = true;
    }

    function openEdit(tier: any) {
        form = {
            label: tier.label,
            min_pax: tier.min_pax,
            max_pax: tier.max_pax,
            discount_type: tier.discount_type,
            discount_value: tier.discount_value,
            sort_order: tier.sort_order,
            is_active: tier.is_active,
        };
        editingId = tier.id;
        showForm = true;
    }

    function submit(e: Event) {
        e.preventDefault();
        const data = { ...form, max_pax: form.max_pax || undefined };
        if (editingId) {
            router.patch(`/admin/pack-tiers/${editingId}`, data, { preserveScroll: true });
        } else {
            router.post('/admin/pack-tiers', data, { preserveScroll: true });
        }
        showForm = false;
        resetForm();
    }

    function destroy(id: number) {
        if (confirm('Hapus pack tier ini?')) {
            router.delete(`/admin/pack-tiers/${id}`, { preserveScroll: true });
        }
    }
</script>

<AppHead title="Pack Tiers" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Pack Tiers</h4>
                <p class="text-muted mb-0">Atur diskon group berdasarkan jumlah pax (berlaku untuk semua activity)</p>
            </div>
            <button type="button" class="btn btn-primary" onclick={openCreate}>
                <i class="ti ti-plus me-1"></i>Add Tier
            </button>
        </div>

        {#if (page.props as any).flash?.success}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i>
                {(page.props as any).flash.success}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}

        {#if showForm}
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">{editingId ? 'Edit Tier' : 'New Tier'}</h6>
                    <button type="button" class="btn btn-sm btn-link p-0" onclick={() => (showForm = false)}>
                        <i class="ti ti-x fs-5"></i>
                    </button>
                </div>
                <div class="card-body">
                    <form onsubmit={submit}>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Label</label>
                                <input type="text" class="form-control" bind:value={form.label} placeholder="Group 4-5 pax" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Min Pax</label>
                                <input type="number" class="form-control" bind:value={form.min_pax} min="1" required />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Max Pax</label>
                                <input type="number" class="form-control" bind:value={form.max_pax} min={form.min_pax} placeholder="∞" />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Discount Type</label>
                                <select class="form-select" bind:value={form.discount_type}>
                                    <option value="percent">Percent (%)</option>
                                    <option value="flat">Flat (Rp off / pax)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">
                                    Discount Value {form.discount_type === 'percent' ? '(%)' : '(Rp)'}
                                </label>
                                <input type="number" class="form-control" bind:value={form.discount_value} min="0" step="0.01" required />
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Sort</label>
                                <input type="number" class="form-control" bind:value={form.sort_order} min="0" />
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" bind:checked={form.is_active} />
                                    <label class="form-check-label" for="is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-check me-1"></i>{editingId ? 'Update' : 'Create'}
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick={() => (showForm = false)}>Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        {/if}

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Label</th>
                                <th>Min Pax</th>
                                <th>Max Pax</th>
                                <th>Discount</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each tiers.data as tier}
                                <tr>
                                    <td class="fw-medium">{tier.label}</td>
                                    <td>{tier.min_pax}</td>
                                    <td>{tier.max_pax ?? '∞'}</td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success">{tier.discount_label}</span>
                                    </td>
                                    <td>
                                        {#if tier.is_active}
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        {:else}
                                            <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick={() => openEdit(tier)}>
                                            <i class="ti ti-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick={() => destroy(tier.id)}>
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">Belum ada pack tier. Klik "Add Tier" untuk membuat.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
                <Pagination links={tiers.links} />
            </div>
        </div>
    </div>
</AdminLayout>
