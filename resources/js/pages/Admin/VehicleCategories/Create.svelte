<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, Link } from '@inertiajs/svelte';

    let { category = null } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        _method: category ? 'put' : 'post',
        title: category?.title ?? '',
        vehicle_type: category?.vehicle_type ?? 'economy',
        capacity: category?.capacity ?? '',
        examples: category?.examples ?? '',
        description: category?.description ?? '',
        image: null as File | null,
    });

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
                                    <option value="economy">Economy (e.g. Standard/Minivan)</option>
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

                        <!-- Capacity -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="capacity" class="form-label text-uppercase fs-12 fw-bold text-muted"
                                    >Passenger Capacity</label
                                >
                                <input
                                    type="text"
                                    id="capacity"
                                    class="form-control"
                                    bind:value={form.capacity}
                                    disabled={form.processing}
                                    maxlength="50"
                                    placeholder="e.g. Up to 4 passengers"
                                />
                                {#if form.errors.capacity}<div
                                        class="text-danger small mt-1"
                                    >
                                        {form.errors.capacity}
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
