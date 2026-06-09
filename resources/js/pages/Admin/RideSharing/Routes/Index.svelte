<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { routes, filters } = $props();
    let search = $state(filters.search ?? '');

    function deleteRoute(id: number) {
        if (confirm('Are you sure you want to delete this route? This will delete all its paths, prices, and schedules!')) {
            router.delete(`/admin/rs-routes/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/rs-routes',
                    { search },
                    { preserveState: true, replace: true }
                );
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let routeList = $derived(routes.data);
</script>

<AppHead title="Ride Sharing Routes" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Routes</h4>
                <p class="text-muted mb-0">Manage main travel routes</p>
            </div>
            <Link
                href="/admin/rs-routes/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add Route
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
                                    placeholder="Search routes..."
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
                                <th>Name</th>
                                <th>Cities Traversed</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each routeList as route}
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{route.name}</div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {route.paths_count} cities
                                        </span>
                                    </td>
                                    <td>
                                        {#if route.is_active}
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        {:else}
                                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link
                                                href={`/admin/rs-routes/${route.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                                title="Configure Paths & Prices"
                                            >
                                                <i class="ti ti-settings"></i>
                                            </Link>
                                            <button
                                                onclick={() => deleteRoute(route.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            {#if search}
                                                No routes match "{search}"
                                            {:else}
                                                No routes found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={routes.links} />
            </div>
        </div>
    </div>
</AdminLayout>
