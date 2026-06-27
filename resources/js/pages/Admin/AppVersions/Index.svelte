<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { versions } = $props();

    let versionList = $derived(versions.data);

    function deleteVersion(id: number) {
        if (confirm('Delete this app version?')) {
            router.delete(`/admin/app-versions/${id}`);
        }
    }
</script>

<AppHead title="App Versions" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">App Versions</h4>
                <p class="text-muted mb-0">Manage APK version updates for driver app</p>
            </div>
            <Link
                href="/admin/app-versions/create"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add Version
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Platform</th>
                                <th>Version</th>
                                <th>Code</th>
                                <th>Force Update</th>
                                <th>Active</th>
                                <th>Created</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each versionList as v}
                                <tr>
                                    <td>
                                        <span class="badge bg-{v.platform === 'android' ? 'success' : 'info'}">
                                            {v.platform}
                                        </span>
                                    </td>
                                    <td class="fw-bold">{v.version_name}</td>
                                    <td>{v.version_code}</td>
                                    <td>
                                        {#if v.is_force_update}
                                            <span class="badge bg-danger">Yes</span>
                                        {:else}
                                            <span class="badge bg-secondary">No</span>
                                        {/if}
                                    </td>
                                    <td>
                                        {#if v.is_active}
                                            <span class="badge bg-success">Active</span>
                                        {:else}
                                            <span class="badge bg-secondary">Inactive</span>
                                        {/if}
                                    </td>
                                    <td class="text-muted">{v.created_at?.substring(0, 10)}</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <Link
                                                href={`/admin/app-versions/${v.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() => deleteVersion(v.id)}
                                                class="btn btn-sm btn-icon btn-danger"
                                            >
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">No app versions added yet.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={versions.links} />
            </div>
        </div>
    </div>
</AdminLayout>
