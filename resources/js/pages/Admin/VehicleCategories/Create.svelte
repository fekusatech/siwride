<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, Link, page } from '@inertiajs/svelte';

    let { category = null } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        _method: category ? 'put' : 'post',
        title: category?.title ?? '',
        vehicle_type: category?.vehicle_type ?? 'economy',
        capacity: category?.capacity ?? '',
        passenger_capacity: category?.passenger_capacity ?? '',
        luggage_capacity: category?.luggage_capacity ?? '',
        base_price: category?.base_price ?? '',
        price_per_km: category?.price_per_km ?? '',
        examples: category?.examples ?? '',
        description: category?.description ?? '',
        advantages: category?.advantages ?? [],
        image: null as File | null,
    });

    function addAdvantage() {
        if (form.advantages.length < 3) {
            form.advantages = [...form.advantages, ''];
        }
    }

    function removeAdvantage(index: number) {
        form.advantages = form.advantages.filter((_, i) => i !== index);
    }

    function submit(e: Event) {
        e.preventDefault();
        if (category) {
            form.post(`/admin/vehicle-categories/${category.id}`);
        } else {
            form.post('/admin/vehicle-categories');
        }
    }
</script>

<AppHead title={category ? 'Edit Vehicle Category' : 'Add Vehicle Category'} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/vehicle-categories"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to List
            </Link>
            <h4 class="mb-0">{category ? 'Edit Vehicle Category' : 'Add Vehicle Category'}</h4>
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

        <div class="card">
            <div class="card-body">
                <form onsubmit={submit}>
                    <div class="row">
                        <!-- Title -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="title" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Category Title</label
                                >
                                <input
                                    type="text"
                                    id="title"
                                    class="form-control"
                                    bind:value={form.title}
                                    required
                                    disabled={form.processing}
                                    maxlength="100"
                                    placeholder="e.g. Standard Vehicle, Luxury SUV"
                                />
                                {#if form.errors.title}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.title}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Vehicle Type -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_type" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Vehicle Type (System Key)</label
                                >
                                <select
                                    id="vehicle_type"
                                    class="form-select"
                                    bind:value={form.vehicle_type}
                                    required
                                    disabled={form.processing}
                                >
                                    <option value="economy">Economy (e.g. Standard/Sedan)</option>
                                    <option value="suv">SUV / MPV</option>
                                    <option value="minibus">Minibus</option>
                                    <option value="premium">Premium</option>
                                    <option value="van">Van</option>
                                    <option value="bus">Bus</option>
                                    <option value="special">Special (e.g. Electric/Modified)</option>
                                </select>
                                {#if form.errors.vehicle_type}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.vehicle_type}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Capacity Label -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Capacity Label (Display)</label
                                >
                                <input
                                    type="text"
                                    id="capacity"
                                    class="form-control"
                                    bind:value={form.capacity}
                                    disabled={form.processing}
                                    maxlength="100"
                                    placeholder="e.g. 1-4 passengers, Up to 6 pax"
                                />
                                {#if form.errors.capacity}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.capacity}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Passenger Capacity (Number) -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="passenger_capacity" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Passenger Capacity</label
                                >
                                <input
                                    type="number"
                                    id="passenger_capacity"
                                    class="form-control"
                                    bind:value={form.passenger_capacity}
                                    disabled={form.processing}
                                    min="1"
                                    max="100"
                                    placeholder="e.g. 4"
                                />
                                {#if form.errors.passenger_capacity}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.passenger_capacity}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Luggage Capacity -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="luggage_capacity" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Luggage Capacity</label
                                >
                                <input
                                    type="number"
                                    id="luggage_capacity"
                                    class="form-control"
                                    bind:value={form.luggage_capacity}
                                    disabled={form.processing}
                                    min="0"
                                    max="100"
                                    placeholder="e.g. 2"
                                />
                                {#if form.errors.luggage_capacity}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.luggage_capacity}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Base Price -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="base_price" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Base Price</label
                                >
                                <input
                                    type="number"
                                    id="base_price"
                                    class="form-control"
                                    bind:value={form.base_price}
                                    disabled={form.processing}
                                    min="0"
                                    step="0.01"
                                    placeholder="e.g. 150000"
                                />
                                {#if form.errors.base_price}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.base_price}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Price Per KM -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="price_per_km" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Price Per KM</label
                                >
                                <input
                                    type="number"
                                    id="price_per_km"
                                    class="form-control"
                                    bind:value={form.price_per_km}
                                    disabled={form.processing}
                                    min="0"
                                    step="0.01"
                                    placeholder="e.g. 10000"
                                />
                                {#if form.errors.price_per_km}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.price_per_km}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Examples -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="examples" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Vehicle Examples</label
                                >
                                <input
                                    type="text"
                                    id="examples"
                                    class="form-control"
                                    bind:value={form.examples}
                                    disabled={form.processing}
                                    maxlength="150"
                                    placeholder="e.g. Toyota Avanza, Suzuki Ertiga"
                                />
                                {#if form.errors.examples}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.examples}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="description" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Description</label
                                >
                                <textarea
                                    id="description"
                                    class="form-control"
                                    rows="4"
                                    bind:value={form.description}
                                    disabled={form.processing}
                                    maxlength="255"
                                    placeholder="Enter description text for this vehicle category..."
                                ></textarea>
                                {#if form.errors.description}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.description}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Advantages -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Advantages / Features</label
                                >
                                <div class="d-flex flex-column gap-2 mb-2">
                                    {#each form.advantages as adv, i}
                                        <div class="d-flex gap-2 align-items-center">
                                            <input
                                                type="text"
                                                class="form-control"
                                                bind:value={form.advantages[i]}
                                                placeholder="e.g. Air conditioning, Comfortable seats"
                                                maxlength="100"
                                                disabled={form.processing}
                                            />
                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm"
                                                onclick={() => removeAdvantage(i)}
                                                disabled={form.processing}
                                                title="Remove Feature"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    {/each}
                                </div>
                                {#if form.advantages.length < 3}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                                        onclick={addAdvantage}
                                        disabled={form.processing}
                                    >
                                        <i class="ti ti-plus fs-14"></i> Add Feature
                                    </button>
                                {:else}
                                    <div class="text-muted small mt-1"><i class="ti ti-info-circle"></i> Maximum of 3 features reached.</div>
                                {/if}
                                {#if form.errors.advantages}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.advantages}
                                    </div>{/if}
                            </div>
                        </div>

                        <!-- Image -->
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="image" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Category Image</label
                                >
                                <div
                                    class="border-dashed py-3 rounded text-center mb-2 bg-light bg-opacity-10"
                                    style="cursor: pointer; border: 2px dashed #dee2e6;"
                                    onclick={() =>
                                        (
                                            document.getElementById(
                                                'image',
                                            ) as HTMLInputElement
                                        ).click()}
                                    onkeydown={(e) =>
                                        e.key === 'Enter' &&
                                        (
                                            document.getElementById(
                                                'image',
                                            ) as HTMLInputElement
                                        ).click()}
                                    role="button"
                                    tabindex="0"
                                >
                                    <i class="ti ti-cloud-upload fs-32 text-muted"></i>
                                    <h5 class="mt-2 mb-1 fs-13 fw-bold text-primary">
                                        Upload Category Image
                                    </h5>
                                    {#if form.image}
                                        <div class="mt-1 badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="ti ti-check fs-10"></i>
                                            {form.image.name}
                                        </div>
                                    {:else}
                                        <p class="text-muted fs-11 mb-0">
                                            Drag & drop or click
                                        </p>
                                    {/if}
                                </div>
                                <input
                                    type="file"
                                    id="image"
                                    class="d-none"
                                    onchange={(e) => {
                                        const target = e.target as HTMLInputElement;
                                        if (target.files) form.image = target.files[0];
                                    }}
                                    disabled={form.processing}
                                    accept="image/*"
                                />

                                {#if category?.image_url}
                                    <div class="d-flex align-items-center gap-2 mt-2">
                                        <img
                                            src={category.image_url}
                                            alt={category.title}
                                            class="rounded border"
                                            style="width: 100px; height: 60px; object-fit: cover;"
                                        />
                                        <span class="fs-11 text-muted">Current Image</span>
                                    </div>
                                {/if}
                                {#if form.errors.image}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.image}
                                    </div>{/if}
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
                            {category ? 'Update Category' : 'Save Category'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
