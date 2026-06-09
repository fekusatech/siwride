<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { cities, filters } = $props();
    let search = $state(filters.search ?? '');

    function deleteCity(id: number) {
        if (confirm('Are you sure you want to delete this city? This might break routes using it.')) {
            router.delete(`/admin/rs-cities/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/rs-cities',
                    { search },
                    { preserveState: true, replace: true }
                );
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let cityList = $derived(cities.data);
</script>

<AppHead title="Ride Sharing Cities" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Cities</h4>
                <p class="text-muted mb-0">Manage predefined cities for Ride Sharing</p>
            </div>
            <Link
                href="/admin/rs-cities/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add City
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
                                    placeholder="Search cities..."
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
                                <th>Address (Pickup Point)</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each cityList as city}
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{city.name}</div>
                                    </td>
                                    <td>
                                        <div class="text-muted small text-wrap" style="max-width: 300px;">
                                            {city.address || '-'}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link
                                                href={`/admin/rs-cities/${city.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() => deleteCity(city.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="text-muted">
                                            {#if search}
                                                No cities match "{search}"
                                            {:else}
                                                No cities found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={cities.links} />
            </div>
        </div>
    </div>
</AdminLayout>
