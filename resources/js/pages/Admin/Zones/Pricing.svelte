<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { router, useForm } from '@inertiajs/svelte';

    type Zone = { id: number; name: string; is_active: boolean };
    type PricingRule = {
        id: number;
        pickup_zone_id: number;
        dropoff_zone_id: number;
        base_price: string | number;
        price_per_km: string | number;
        minimum_price: string | number;
        is_active: boolean;
        pickup_zone?: Zone;
        dropoff_zone?: Zone;
    };

    let { zones = [], pricing_rules = [] } = $props<{
        zones: Zone[];
        pricing_rules: PricingRule[];
    }>();

    const form = useForm({
        id: null as number | null,
        pickup_zone_id: '',
        dropoff_zone_id: '',
        base_price: 0,
        price_per_km: 0,
        minimum_price: 0,
        is_active: true,
    });

    let isEditing = $state(false);

    function formatCurrency(amount: string | number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(Number(amount));
    }

    function submit(e: Event) {
        e.preventDefault();

        if (isEditing && form.id) {
            form.put(`/admin/zones/pricing/${form.id}`, {
                onSuccess: resetForm,
            });
            return;
        }

        form.post('/admin/zones/pricing', { onSuccess: resetForm });
    }

    function editRule(rule: PricingRule) {
        isEditing = true;
        form.id = rule.id;
        form.pickup_zone_id = rule.pickup_zone_id.toString();
        form.dropoff_zone_id = rule.dropoff_zone_id.toString();
        form.base_price = Number(rule.base_price);
        form.price_per_km = Number(rule.price_per_km);
        form.minimum_price = Number(rule.minimum_price);
        form.is_active = rule.is_active;
    }

    function resetForm() {
        isEditing = false;
        form.reset();
        form.id = null;
        form.is_active = true;
    }

    function deleteRule(rule: PricingRule) {
        if (
            !confirm(
                `Delete pricing rule ${rule.pickup_zone?.name} to ${rule.dropoff_zone?.name}?`,
            )
        ) {
            return;
        }

        router.delete(`/admin/zones/pricing/${rule.id}`);
    }
</script>

<AppHead title="Zone Pricing" />

<AdminLayout>
    <div class="py-3">
        <div
            class="mb-4 d-flex justify-content-between align-items-start gap-3 flex-wrap"
        >
            <div>
                <h4 class="mb-0">Zone Pricing</h4>
                <p class="text-muted mb-0">
                    Set prices for pickup and dropoff zone combinations.
                </p>
            </div>
            <a href="/admin/zones" class="btn btn-outline-primary">
                <i class="ti ti-map-pin me-1"></i>
                Manage Zones
            </a>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">
                            {isEditing ? 'Edit Rule' : 'Create Rule'}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form onsubmit={submit}>
                            <div class="mb-3">
                                <label for="pickup_zone_id" class="form-label"
                                    >Pickup Zone</label
                                >
                                <select
                                    id="pickup_zone_id"
                                    class="form-select"
                                    bind:value={form.pickup_zone_id}
                                    required
                                >
                                    <option value="">Select pickup zone</option>
                                    {#each zones as zone}
                                        <option value={zone.id}
                                            >{zone.name}</option
                                        >
                                    {/each}
                                </select>
                                {#if form.errors.pickup_zone_id}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.pickup_zone_id}
                                    </div>{/if}
                            </div>

                            <div class="mb-3">
                                <label for="dropoff_zone_id" class="form-label"
                                    >Dropoff Zone</label
                                >
                                <select
                                    id="dropoff_zone_id"
                                    class="form-select"
                                    bind:value={form.dropoff_zone_id}
                                    required
                                >
                                    <option value="">Select dropoff zone</option
                                    >
                                    {#each zones as zone}
                                        <option value={zone.id}
                                            >{zone.name}</option
                                        >
                                    {/each}
                                </select>
                                {#if form.errors.dropoff_zone_id}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.dropoff_zone_id}
                                    </div>{/if}
                            </div>

                            <div class="mb-3">
                                <label for="base_price" class="form-label"
                                    >Base Price</label
                                >
                                <input
                                    id="base_price"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    class="form-control"
                                    bind:value={form.base_price}
                                    required
                                />
                                {#if form.errors.base_price}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.base_price}
                                    </div>{/if}
                            </div>

                            <div class="mb-3">
                                <label for="price_per_km" class="form-label"
                                    >Price Per KM</label
                                >
                                <input
                                    id="price_per_km"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    class="form-control"
                                    bind:value={form.price_per_km}
                                    required
                                />
                                {#if form.errors.price_per_km}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.price_per_km}
                                    </div>{/if}
                            </div>

                            <div class="mb-3">
                                <label for="minimum_price" class="form-label"
                                    >Minimum Price</label
                                >
                                <input
                                    id="minimum_price"
                                    type="number"
                                    min="0"
                                    step="1000"
                                    class="form-control"
                                    bind:value={form.minimum_price}
                                    required
                                />
                                {#if form.errors.minimum_price}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.minimum_price}
                                    </div>{/if}
                            </div>

                            <div class="form-check form-switch mb-4">
                                <input
                                    id="is_active"
                                    type="checkbox"
                                    class="form-check-input"
                                    bind:checked={form.is_active}
                                />
                                <label for="is_active" class="form-check-label"
                                    >Active</label
                                >
                            </div>

                            <div class="d-flex gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    disabled={form.processing}
                                >
                                    {form.processing
                                        ? 'Saving...'
                                        : isEditing
                                          ? 'Update Rule'
                                          : 'Create Rule'}
                                </button>
                                {#if isEditing}<button
                                        type="button"
                                        class="btn btn-light"
                                        onclick={resetForm}>Cancel</button
                                    >{/if}
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title mb-0">Pricing Rules</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Route</th>
                                        <th>Base</th>
                                        <th>Per KM</th>
                                        <th>Minimum</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {#each pricing_rules as rule}
                                        <tr>
                                            <td
                                                ><div class="fw-semibold">
                                                    {rule.pickup_zone?.name ??
                                                        '-'} to {rule
                                                        .dropoff_zone?.name ??
                                                        '-'}
                                                </div></td
                                            >
                                            <td
                                                >{formatCurrency(
                                                    rule.base_price,
                                                )}</td
                                            >
                                            <td
                                                >{formatCurrency(
                                                    rule.price_per_km,
                                                )}</td
                                            >
                                            <td
                                                >{formatCurrency(
                                                    rule.minimum_price,
                                                )}</td
                                            >
                                            <td>
                                                <span
                                                    class="badge {rule.is_active
                                                        ? 'bg-success-subtle text-success'
                                                        : 'bg-secondary-subtle text-secondary'}"
                                                >
                                                    {rule.is_active
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary me-1"
                                                    onclick={() =>
                                                        editRule(rule)}
                                                    >Edit</button
                                                >
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick={() =>
                                                        deleteRule(rule)}
                                                    >Delete</button
                                                >
                                            </td>
                                        </tr>
                                    {:else}
                                        <tr
                                            ><td
                                                colspan="6"
                                                class="text-center text-muted py-4"
                                                >No pricing rules yet.</td
                                            ></tr
                                        >
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
