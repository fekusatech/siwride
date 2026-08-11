<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { article } = $props();

    const statuses = ['pending', 'approved', 'rejected'];

    let rejectionReason = $state(article.rejection_reason ?? '');

    function updateStatus(newStatus: string) {
        router.patch(
            `/admin/driver-articles/${article.id}/status`,
            {
                status: newStatus,
                rejection_reason: newStatus === 'rejected' ? rejectionReason : null,
            },
            { preserveScroll: true },
        );
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };
</script>

<AppHead title="Driver Article Detail" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Article Detail</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><Link href="/admin/driver-articles">Driver Articles</Link></li>
                    <li class="breadcrumb-item active">{article.title}</li>
                </ol>
            </div>
            <Link href="/admin/driver-articles" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-body">
                        {#if article.image_url}
                            <img src={article.image_url} alt={article.title} class="rounded-3 w-100 mb-3" style="max-height: 320px; object-fit: cover;">
                        {/if}
                        <h4 class="mb-1">{article.title}</h4>
                        {#if article.excerpt}
                            <p class="text-muted">{article.excerpt}</p>
                        {/if}
                        <hr>
                        <p style="white-space: pre-wrap;">{article.content}</p>

                        {#if article.gallery_urls?.length}
                            <div class="d-flex flex-wrap gap-2 mt-3">
                                {#each article.gallery_urls as url}
                                    <img src={url} alt={article.title} class="rounded-2" style="width: 100px; height: 100px; object-fit: cover;">
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Article Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Current Status</small>
                            <span class="badge fs-6 {statusColors[article.status] ?? ''}">
                                {article.status}
                            </span>
                        </div>

                        {#if article.status === 'rejected' && article.rejection_reason}
                            <div class="alert alert-danger small mb-3">{article.rejection_reason}</div>
                        {/if}

                        <div class="mb-3">
                            <label class="form-label small" for="rejection_reason">Rejection reason (optional)</label>
                            <textarea id="rejection_reason" class="form-control form-control-sm" rows="2" bind:value={rejectionReason}></textarea>
                        </div>

                        <hr>
                        <p class="small text-muted mb-2">Update status:</p>
                        <div class="d-flex flex-column gap-2">
                            {#each statuses as s}
                                <button
                                    onclick={() => updateStatus(s)}
                                    class="btn btn-sm {article.status === s ? 'btn-secondary disabled' : 'btn-outline-secondary'}"
                                    disabled={article.status === s}
                                >
                                    Mark as {s.charAt(0).toUpperCase() + s.slice(1)}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Driver</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted d-block">Name</small>
                            <span>{article.driver?.name ?? '-'}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Email</small>
                            <span>{article.driver?.email ?? '-'}</span>
                        </div>
                    </div>
                </div>

                {#if article.activity}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-light">
                            <h5 class="card-title mb-0">Linked Tour</h5>
                        </div>
                        <div class="card-body">
                            <span>{article.activity.title}</span>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</AdminLayout>
