<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm } from '@inertiajs/svelte';

    let { city } = $props();
    let isEditing = $derived(!!city);

    let form = useForm({
        name: city?.name ?? '',
        address: city?.address ?? '',
    });

    function submit(e: Event) {
        e.preventDefault();
        
        if (isEditing) {
            form.put(`/admin/rs-cities/${city.id}`);
        } else {
            form.post('/admin/rs-cities');
        }
    }
</script>

<AppHead title={isEditing ? 'Edit City' : 'Add City'} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">{isEditing ? 'Edit City' : 'Add City'}</h4>
                <p class="text-muted mb-0">Manage predefined cities for ride sharing</p>
            </div>
            <Link
                href="/admin/rs-cities"
                class="btn btn-outline-secondary d-flex align-items-center gap-1"
            >
                <i class="ti ti-arrow-left fs-18"></i>
                Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form onsubmit={submit}>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label for="name" class="form-label">City Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        class="form-control {form.errors.name ? 'is-invalid' : ''}"
                                        id="name"
                                        bind:value={form.name}
                                        placeholder="e.g. Malang"
                                        required
                                    />
                                    {#if form.errors.name}
                                        <div class="invalid-feedback">{form.errors.name}</div>
                                    {/if}
                                </div>
                                <div class="col-md-12">
                                    <label for="address" class="form-label">Detailed Address (Pickup Point) <span class="text-muted">(Optional)</span></label>
                                    <textarea
                                        class="form-control {form.errors.address ? 'is-invalid' : ''}"
                                        id="address"
                                        bind:value={form.address}
                                        placeholder="e.g. Alun Alun Tugu Malang, Jl. Tugu..."
                                        rows="3"
                                    ></textarea>
                                    {#if form.errors.address}
                                        <div class="invalid-feedback">{form.errors.address}</div>
                                    {/if}
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex gap-2">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    disabled={form.processing}
                                >
                                    {#if form.processing}
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Saving...
                                    {:else}
                                        {isEditing ? 'Update City' : 'Save City'}
                                    {/if}
                                </button>
                                <Link href="/admin/rs-cities" class="btn btn-light">
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2 mb-3">
                            <i class="ti ti-info-circle text-primary"></i>
                            Guidelines
                        </h5>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Name:</strong> The name of the city (e.g., "Malang", "Surabaya").</li>
                            <li><strong>Address:</strong> Optional detailed pickup point for this city.</li>
                            <li>You can create multiple entries with the same City Name but different addresses (e.g., Malang - Alun Alun, Malang - Terminal).</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
