<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, Link } from '@inertiajs/svelte';

    let { vehicle = null, drivers = [] } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        driver_id: vehicle?.driver_id ?? '',
        brand: vehicle?.brand ?? '',
        model: vehicle?.model ?? '',
        type: vehicle?.type ?? '',
        registration_number: vehicle?.registration_number ?? '',
        color: vehicle?.color ?? '',
        status: vehicle?.status ?? 'active',
    });

    function submit(e: Event) {
        e.preventDefault();
        if (vehicle) {
            form.put(`/admin/vehicles/${vehicle.id}`);
        } else {
            form.post('/admin/vehicles');
        }
    }
</script>

<AppHead title={vehicle ? 'Edit Vehicle' : 'Add Vehicle'} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/vehicles"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to List
            </Link>
            <h4 class="mb-0">{vehicle ? 'Edit Vehicle' : 'Add Vehicle'}</h4>
        </div>

        <div class="card">
            <div class="card-body">
                <form onsubmit={submit}>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="driver_id" class="form-label"
                                    >Driver Owner</label
                                >
                                <select
                                    id="driver_id"
                                    class="form-select"
                                    bind:value={form.driver_id}
                                    required
                                    disabled={form.processing}
                                >
                                    <option value="">Select Driver</option>
                                    {#each drivers as driver}
                                        <option value={driver.id}
                                            >{driver.name} ({driver.phone})</option
                                        >
                                    {/each}
                                </select>
                                {#if form.errors.driver_id}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.driver_id}
                                    </div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label
                                    for="registration_number"
                                    class="form-label"
                                    >Registration Number (Plat No)</label
                                >
                                <input
                                    type="text"
                                    id="registration_number"
                                    class="form-control"
                                    bind:value={form.registration_number}
                                    required
                                    disabled={form.processing}
                                    placeholder="Enter registration number"
                                />
                                {#if form.errors.registration_number}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.registration_number}
                                    </div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="brand" class="form-label"
                                    >Brand (e.g. Toyota)</label
                                >
                                <input
                                    type="text"
                                    id="brand"
                                    class="form-control"
                                    bind:value={form.brand}
                                    disabled={form.processing}
                                    placeholder="Enter brand"
                                />
                                {#if form.errors.brand}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.brand}
                                    </div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="model" class="form-label"
                                    >Model (e.g. Avanza)</label
                                >
                                <input
                                    type="text"
                                    id="model"
                                    class="form-control"
                                    bind:value={form.model}
                                    disabled={form.processing}
                                    placeholder="Enter model"
                                />
                                {#if form.errors.model}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.model}
                                    </div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="type" class="form-label"
                                    >Vehicle Type</label
                                >
                                <select
                                    id="type"
                                    class="form-select"
                                    bind:value={form.type}
                                    disabled={form.processing}
                                >
                                    <option value="">Select Type</option>
                                    <option value="Sedan">Sedan</option>
                                    <option value="SUV">SUV</option>
                                    <option value="MPV">MPV</option>
                                    <option value="Van">Van</option>
                                </select>
                                {#if form.errors.type}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.type}
                                    </div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="color" class="form-label"
                                    >Color</label
                                >
                                <input
                                    type="text"
                                    id="color"
                                    class="form-control"
                                    bind:value={form.color}
                                    disabled={form.processing}
                                    placeholder="Enter color"
                                />
                                {#if form.errors.color}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.color}
                                    </div>{/if}
                            </div>
                        </div>
                        {#if vehicle}
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <span
                                        class="form-label d-block text-uppercase fs-12 fw-bold text-muted mb-2"
                                        >Status</span
                                    >
                                    <div class="form-check form-check-inline">
                                        <input
                                            type="radio"
                                            id="vh_status_active"
                                            name="v_status"
                                            class="form-check-input"
                                            value="active"
                                            bind:group={form.status}
                                            disabled={form.processing}
                                        />
                                        <label
                                            class="form-check-label fw-medium"
                                            for="vh_status_active">Active</label
                                        >
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input
                                            type="radio"
                                            id="vh_status_inactive"
                                            name="v_status"
                                            class="form-check-input"
                                            value="inactive"
                                            bind:group={form.status}
                                            disabled={form.processing}
                                        />
                                        <label
                                            class="form-check-label fw-medium"
                                            for="vh_status_inactive"
                                            >Inactive</label
                                        >
                                    </div>
                                    {#if form.errors.status}<div
                                            class="text-danger small mt-1"
                                        >
                                            {form.errors.status}
                                        </div>{/if}
                                </div>
                            </div>
                        {/if}
                    </div>

                    <div class="mt-2">
                        <button
                            type="submit"
                            class="btn btn-primary d-flex align-items-center gap-1"
                            disabled={form.processing}
                        >
                            <i class="ti ti-device-floppy fs-18"></i>
                            {vehicle ? 'Update Vehicle' : 'Save Vehicle'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
