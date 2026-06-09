<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm } from '@inertiajs/svelte';

    let { cities = [] } = $props();

    let form = useForm({
        start_city_id: '',
        end_city_id: '',
        is_active: true,
    });

    function submit(e: Event) {
        e.preventDefault();
        form.post('/admin/rs-routes');
    }
</script>

<AppHead title="Add Route" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Add Route</h4>
                <p class="text-muted mb-0">Create a new main travel route</p>
            </div>
            <Link
                href="/admin/rs-routes"
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
                                <div class="col-md-6">
                                    <label for="start_city_id" class="form-label">Origin City <span class="text-danger">*</span></label>
                                    <select
                                        id="start_city_id"
                                        class="form-select {form.errors.start_city_id ? 'is-invalid' : ''}"
                                        bind:value={form.start_city_id}
                                        required
                                    >
                                        <option value="">-- Select Origin City --</option>
                                        {#each cities as city}
                                            <option value={city.id}>{city.name}{city.address ? ` - ${city.address}` : ''}</option>
                                        {/each}
                                    </select>
                                    {#if form.errors.start_city_id}
                                        <div class="invalid-feedback">{form.errors.start_city_id}</div>
                                    {/if}
                                </div>

                                <div class="col-md-6">
                                    <label for="end_city_id" class="form-label">Destination City <span class="text-danger">*</span></label>
                                    <select
                                        id="end_city_id"
                                        class="form-select {form.errors.end_city_id ? 'is-invalid' : ''}"
                                        bind:value={form.end_city_id}
                                        required
                                    >
                                        <option value="">-- Select Destination City --</option>
                                        {#each cities as city}
                                            <option value={city.id} disabled={form.start_city_id === city.id}>{city.name}{city.address ? ` - ${city.address}` : ''}</option>
                                        {/each}
                                    </select>
                                    {#if form.errors.end_city_id}
                                        <div class="invalid-feedback">{form.errors.end_city_id}</div>
                                    {/if}
                                </div>

                                <div class="col-12 mt-4">
                                    <div class="form-check form-switch">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            role="switch"
                                            id="is_active"
                                            bind:checked={form.is_active}
                                        />
                                        <label class="form-check-label" for="is_active">
                                            Status Active
                                        </label>
                                    </div>
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
                                        Create Route
                                    {/if}
                                </button>
                                <Link href="/admin/rs-routes" class="btn btn-light">
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
