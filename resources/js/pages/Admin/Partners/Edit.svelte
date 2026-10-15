<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm } from '@inertiajs/svelte';

    let { partner } = $props();

    let form = useForm({
        _method: 'put',
        type: partner.type,
        name: partner.name,
        email: partner.email,
        phone: partner.phone,
        description: partner.description || '',
        status: partner.status,
    });

    function submit(event: Event) {
        event.preventDefault();
        form.post(`/admin/partners/${partner.id}`);
    }
</script>

<AppHead title="Edit Partner" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Edit Partner</h4>
                <p class="text-muted mb-0">{partner.name}</p>
            </div>
            <Link href="/admin/partners" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="ti ti-arrow-left fs-18"></i>
                Back to Partners
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                {#if Object.keys(form.errors).length > 0}
                    <div class="alert alert-danger" role="alert">
                        {#each Object.values(form.errors) as error}
                            <div>{error}</div>
                        {/each}
                    </div>
                {/if}

                <form onsubmit={submit}>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Partner Type</label>
                            <select class="form-select" bind:value={form.type}>
                                <option value="travel">Travel Partner</option>
                                <option value="guide">Tour Guide</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" bind:value={form.status}>
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Name / Business Name</label>
                            <input type="text" class="form-control" bind:value={form.name} required />
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" bind:value={form.phone} required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" bind:value={form.email} required />
                        </div>
                        <div class="col-12">
                            <label class="form-label">About / Description</label>
                            <textarea class="form-control" rows="4" bind:value={form.description}></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" disabled={form.processing}>
                                {form.processing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
