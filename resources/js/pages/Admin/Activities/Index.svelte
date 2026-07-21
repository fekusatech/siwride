<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { activities, filters } = $props();
    let search = $state(filters.search ?? '');

    function deleteActivity(id: number) {
        if (confirm('Are you sure you want to delete this activity?')) {
            router.delete(`/admin/activities/${id}`);
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/activities', { search }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let activityList = $derived(activities.data);
</script>

<AppHead title="Activities Management" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Activities</h4>
                <p class="text-muted mb-0">Manage bookable activities & experiences</p>
            </div>
            <Link href="/admin/activities/create" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="ti ti-plus fs-18"></i>
                Add Activity
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
                                    placeholder="Search activities..."
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
                                <th>Activity</th>
                                <th>Price / Pax</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each activityList as activity}
                                <tr>
                                    <td>
                                        <img
                                            src={activity.image_url}
                                            alt={activity.title}
                                            class="rounded"
                                            style="width: 60px; height: 40px; object-fit: cover; border: 1px solid #dee2e6;"
                                        />
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{activity.title}</div>
                                        <small class="text-muted">{activity.subtitle || '-'}</small>
                                    </td>
                                    <td>
                                        <span class="fw-medium">Rp {Number(activity.price_per_pax).toLocaleString('id-ID')}</span>
                                    </td>
                                    <td>{activity.duration_label || '-'}</td>
                                    <td>
                                        {#if activity.is_active}
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        {:else}
                                            <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                        {/if}
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link
                                                href={`/admin/activities/${activity.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() => deleteActivity(activity.id)}
                                                class="btn btn-sm btn-icon btn-danger"
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
                                                No activities match "{search}"
                                            {:else}
                                                No activities found.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={activities.links} />
            </div>
        </div>
    </div>
</AdminLayout>
