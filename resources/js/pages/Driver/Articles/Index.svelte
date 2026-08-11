<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link } from '@inertiajs/svelte';

    let { articles } = $props();

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    let articleList = $derived(articles.data);
</script>

<AppHead title="My Articles" />

<DriverLayout>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">My Articles</h4>
        <Link href="/driver/articles/create" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Article
        </Link>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each articleList as article}
                            <tr>
                                <td class="fw-medium">{article.title}</td>
                                <td>
                                    <span class="badge {statusColors[article.status] ?? ''}">{article.status}</span>
                                    {#if article.status === 'rejected' && article.rejection_reason}
                                        <div class="small text-danger mt-1">{article.rejection_reason}</div>
                                    {/if}
                                </td>
                                <td>{new Date(article.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                <td class="text-center">
                                    <Link href={`/driver/articles/${article.id}/edit`} class="btn btn-sm btn-icon btn-primary">
                                        <i class="ti ti-edit"></i>
                                    </Link>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    You haven't written any articles yet.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            <Pagination links={articles.links} />
        </div>
    </div>
</DriverLayout>
