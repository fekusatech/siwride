<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { articles, filters } = $props();
    let search = $state(filters.search ?? '');
    let status = $state(filters.status ?? '');

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/driver-articles', { search, status }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
        status = filters.status ?? '';
    });

    function filterByStatus(newStatus: string) {
        status = newStatus;
        router.get('/admin/driver-articles', { search, status }, { preserveState: true, replace: true });
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    let articleList = $derived(articles.data);
</script>

<AppHead title="Driver Articles" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Driver Articles</h4>
                <p class="text-muted mb-0">Review and approve tour & guide content submitted by drivers</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search by title or driver..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex gap-2">
                                {#each ['', 'pending', 'approved', 'rejected'] as s}
                                    <button
                                        onclick={() => filterByStatus(s)}
                                        class="btn btn-sm {status === s ? 'btn-primary' : 'btn-outline-secondary'}"
                                    >
                                        {s === '' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1)}
                                    </button>
                                {/each}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Title</th>
                                <th>Driver</th>
                                <th>Linked Tour</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each articleList as article}
                                <tr>
                                    <td>
                                        <div class="fw-medium">{article.title}</div>
                                    </td>
                                    <td>{article.driver?.name ?? '-'}</td>
                                    <td>{article.activity?.title ?? '-'}</td>
                                    <td>
                                        <span class="badge {statusColors[article.status] ?? ''}">
                                            {article.status}
                                        </span>
                                    </td>
                                    <td>{new Date(article.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                    <td class="text-center">
                                        <Link
                                            href={`/admin/driver-articles/${article.id}`}
                                            class="btn btn-sm btn-icon btn-primary"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </Link>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">No driver articles found.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={articles.links} />
            </div>
        </div>
    </div>
</AdminLayout>
