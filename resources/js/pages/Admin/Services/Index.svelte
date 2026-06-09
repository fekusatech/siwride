<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { services, filters } = $props();
    let search = $state(filters.search ?? '');

    function deleteService(id: number) {
        if (confirm('Are you sure you want to delete this service?')) {
            router.delete(`/admin/services/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get(
                    '/admin/services',
                    { search },
                    { preserveState: true, replace: true }
                );
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let serviceList = $derived(services.data);
</script>

<AppHead title="Services Management" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Services</h4>
                <p class="text-muted mb-0">Manage booking services displayed to customers</p>
            </div>
            <Link
                href="/admin/services/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add Service
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
                                    placeholder="Search services..."
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
                                <th style="width: 80px;">Image</th>
                                <th>Service Details</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each serviceList as service}
                                <tr>
                                    <td>
                                        <img
                                            src={service.image_url}
                                            alt={service.title}
                                            class="rounded"
                                            style="width: 60px; height: 40px; object-fit: cover; border: 1px solid #dee2e6;"
                                        />
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{service.title}</div>
                                        <small class="text-muted text-wrap d-block" style="max-width: 300px;">
                                            {service.subtitle || '-'}
                                        </small>
                                    </td>
                                    <td>
                                        <div class="text-muted text-wrap" style="max-width: 250px; font-size: 0.85rem;">
                                            {#if service.description}
                                                {service.description.length > 80 ? service.description.substring(0, 80) + '...' : service.description}
                                            {:else}
                                                -
                                            {/if}
                                        </div>
                                    </td>
                                    <td>
                                        {#if service.is_active}
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        {:else}
                                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link
                                                href={`/admin/services/${service.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() => deleteService(service.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                            >
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
                                                No services match "{search}"
                                            {:else}
                                                No services found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={services.links} />
            </div>
        </div>
    </div>
</AdminLayout>
