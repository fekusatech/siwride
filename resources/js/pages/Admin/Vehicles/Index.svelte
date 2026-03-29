<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { vehicles, filters } = $props();
    // svelte-ignore state_referenced_locally
    let search = $state(filters.search ?? '');

    function deleteVehicle(id: number) {
        if (confirm('Are you sure you want to delete this vehicle?')) {
            router.delete(`/admin/vehicles/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/vehicles', { search }, {
                    preserveState: true,
                    replace: true,
                });
            }, 300);
        }
    });

    // Sync state with props
    $effect(() => {
        search = filters.search ?? '';
    });

    let vehicleList = $derived(vehicles.data);
</script>

<AppHead title="Vehicles" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Vehicle Management</h4>
                <p class="text-muted mb-0">List of all vehicles and their owners</p>
            </div>
            <Link href="/admin/vehicles/create" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="ti ti-plus fs-18"></i>
                Add Vehicle
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control border-start-0 ps-0" 
                                    placeholder="Search license, brand, model, or driver..." 
                                    bind:value={search}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Registration #</th>
                                <th>Vehicle Info</th>
                                <th>Driver</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each vehicleList as vehicle}
                                <tr>
                                    <td><span class="fw-bold text-dark">{vehicle.registration_number}</span></td>
                                    <td>
                                        <div class="fw-medium text-dark">{vehicle.brand} {vehicle.model}</div>
                                        <small class="text-muted">{vehicle.type} - {vehicle.color}</small>
                                    </td>
                                    <td>
                                        {#if vehicle.driver}
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px; font-size: 10px;">
                                                    {vehicle.driver.name.charAt(0)}
                                                </div>
                                                <Link href={`/admin/drivers/${vehicle.driver.id}/edit`} class="text-body fw-medium">
                                                    {vehicle.driver.name}
                                                </Link>
                                            </div>
                                        {:else}
                                            <span class="text-muted italic">No Driver Assigned</span>
                                        {/if}
                                    </td>
                                    <td>
                                        <span class="badge bg-{vehicle.status === 'active' ? 'success' : 'danger'}-subtle text-{vehicle.status === 'active' ? 'success' : 'danger'}">
                                            {vehicle.status}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link href={`/admin/vehicles/${vehicle.id}/edit`} class="btn btn-sm btn-icon btn-primary" aria-label="Edit vehicle">
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button onclick={() => deleteVehicle(vehicle.id)} class="btn btn-sm btn-icon btn-danger" aria-label="Delete vehicle">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            {#if search}
                                                No vehicles match "{search}"
                                            {:else}
                                                No vehicles found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={vehicles.links} />
            </div>
        </div>
    </div>
</AdminLayout>
