<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { drivers, filters } = $props();
    // svelte-ignore state_referenced_locally
    let search = $state(filters.search ?? '');
    let searchTimeout: any;

    function deleteDriver(id: number) {
        if (confirm('Are you sure you want to delete this driver?')) {
            router.delete(`/admin/drivers/${id}`);
        }
    }

    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/drivers',
                    { search },
                    {
                        preserveState: true,
                        replace: true,
                    },
                );
            }, 300);
        }
    });

    // Handle back/forward navigation or external attribute updates
    $effect(() => {
        search = filters.search ?? '';
    });

    // Reactive drivers list from paginated object
    let driverList = $derived(drivers.data);
</script>

<AppHead title="Drivers" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Driver Management</h4>
                <p class="text-muted mb-0">List of all registered drivers</p>
            </div>
            <Link
                href="/admin/drivers/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add Driver
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span
                                    class="input-group-text bg-transparent border-end-0"
                                >
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search name, phone, email, or NID..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-hover table-centered mb-0 text-nowrap"
                    >
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Vehicles</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each driverList as driver}
                                <tr>
                                    <td>
                                        <div
                                            class="d-flex align-items-center gap-2"
                                        >
                                            {#if driver.image}
                                                <img
                                                    src={driver.image}
                                                    alt={driver.name}
                                                    class="rounded-circle"
                                                    width="32"
                                                />
                                            {:else}
                                                <div
                                                    class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                    style="width: 32px; height: 32px;"
                                                >
                                                    {driver.name.charAt(0)}
                                                </div>
                                            {/if}
                                            <div>
                                                <span
                                                    class="fw-medium d-block text-dark"
                                                    >{driver.name}</span
                                                >
                                                <small class="text-muted"
                                                    >{driver.nid ||
                                                        'No NID'}</small
                                                >
                                            </div>
                                        </div>
                                    </td>
                                    <td>{driver.email}</td>
                                    <td>{driver.phone}</td>
                                    <td>
                                        <span
                                            class="badge bg-info-subtle text-info"
                                            >{driver.vehicles_count} Vehicles</span
                                        >
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{driver.status ===
                                            'active'
                                                ? 'success'
                                                : 'danger'}-subtle text-{driver.status ===
                                            'active'
                                                ? 'success'
                                                : 'danger'}"
                                        >
                                            {driver.status}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="d-flex align-items-center justify-content-center gap-2"
                                        >
                                            <Link
                                                href={`/admin/drivers/${driver.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                                aria-label="Edit driver"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() =>
                                                    deleteDriver(driver.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                                aria-label="Delete driver"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            {#if search}
                                                No drivers match "{search}"
                                            {:else}
                                                No drivers found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={drivers.links} />
            </div>
        </div>
    </div>
</AdminLayout>
