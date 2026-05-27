<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { categories, filters } = $props();
    // svelte-ignore state_referenced_locally
    let search = $state(filters.search ?? '');

    function deleteCategory(id: number) {
        if (confirm('Are you sure you want to delete this vehicle category? This may affect customer display.')) {
            router.delete(`/admin/vehicle-categories/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/vehicle-categories',
                    { search },
                    {
                        preserveState: true,
                        replace: true,
                    },
                );
            }, 300);
        }
    });

    // Sync state with props
    $effect(() => {
        search = filters.search ?? '';
    });

    let categoryList = $derived(categories.data);
</script>

<AppHead title="Vehicle Categories" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Vehicle Categories</h4>
                <p class="text-muted mb-0">
                    Manage categories displayed on the customer pages and booking dropdown
                </p>
            </div>
            <Link
                href="/admin/vehicle-categories/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add Category
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
                                    placeholder="Search categories..."
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
                                <th style="width: 80px;">Image</th>
                                <th>Category Details</th>
                                <th>Vehicle Type</th>
                                <th>Capacity</th>
                                <th>Examples</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each categoryList as category}
                                <tr>
                                    <td>
                                        {#if category.image_url}
                                            <img
                                                src={category.image_url}
                                                alt={category.title}
                                                class="rounded"
                                                style="width: 60px; height: 40px; object-fit: cover; border: 1px solid #dee2e6;"
                                            />
                                        {:else}
                                            <div
                                                class="bg-light rounded text-muted d-flex align-items-center justify-content-center"
                                                style="width: 60px; height: 40px;"
                                            >
                                                <i class="ti ti-photo fs-18"></i>
                                            </div>
                                        {/if}
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">
                                            {category.title}
                                        </div>
                                        <small class="text-muted text-wrap d-block" style="max-width: 300px;">
                                            {category.description || 'No description provided.'}
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-subtle text-info text-capitalize">
                                            {category.vehicle_type}
                                        </span>
                                    </td>
                                    <td>{category.capacity || '-'}</td>
                                    <td class="text-wrap" style="max-width: 250px;">{category.examples || '-'}</td>
                                    <td class="text-center">
                                        <div
                                            class="d-flex align-items-center justify-content-center gap-2"
                                        >
                                            <Link
                                                href={`/admin/vehicle-categories/${category.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                                aria-label="Edit category"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() =>
                                                    deleteCategory(category.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                                aria-label="Delete category"
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
                                                No vehicle categories match "{search}"
                                            {:else}
                                                No vehicle categories found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={categories.links} />
            </div>
        </div>
    </div>
</AdminLayout>
