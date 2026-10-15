<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { partners, filters } = $props();
    let search = $state(filters.search ?? '');
    let status = $state(filters.status ?? '');

    const statusTabs = [
        { value: '', label: 'All' },
        { value: 'pending', label: 'Pending' },
        { value: 'active', label: 'Active' },
        { value: 'rejected', label: 'Rejected' },
    ];

    const statusBadge: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        active: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    function setStatus(value: string) {
        status = value;
        router.get('/admin/partners', { search, status: value }, { preserveState: true, replace: true });
    }

    function approvePartner(id: number) {
        if (confirm('Approve this partner? They will appear in the public catalog.')) {
            router.patch(`/admin/partners/${id}/approve`, {}, { preserveScroll: true });
        }
    }

    function rejectPartner(id: number) {
        if (confirm('Reject this partner application?')) {
            router.patch(`/admin/partners/${id}/reject`, {}, { preserveScroll: true });
        }
    }

    function deletePartner(id: number) {
        if (confirm('Permanently delete this partner? This cannot be undone.')) {
            router.delete(`/admin/partners/${id}`, { preserveScroll: true });
        }
    }

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/partners', { search, status }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
    });

    let partnerList = $derived(partners.data);
</script>

<AppHead title="Partners Management" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Partners</h4>
                <p class="text-muted mb-0">Review applications and manage travel partners &amp; tour guides</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-md-5">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search name, email, or phone..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <ul class="nav nav-pills gap-1">
                                {#each statusTabs as tab}
                                    <li class="nav-item">
                                        <button
                                            class="nav-link py-1 px-3 {status === tab.value ? 'active' : ''}"
                                            onclick={() => setStatus(tab.value)}
                                        >
                                            {tab.label}
                                        </button>
                                    </li>
                                {/each}
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Partner</th>
                                <th>Type</th>
                                <th>Contact</th>
                                <th>Orders</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each partnerList as partner}
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <img
                                                src={partner.image_url}
                                                alt={partner.name}
                                                class="rounded-circle"
                                                style="width: 40px; height: 40px; object-fit: cover; border: 1px solid #dee2e6;"
                                            />
                                            <div>
                                                <div class="fw-bold text-dark">{partner.name}</div>
                                                <small class="text-muted">{partner.email}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        {#if partner.type === 'guide'}
                                            <span class="badge bg-success-subtle text-success">Tour Guide</span>
                                        {:else}
                                            <span class="badge bg-primary-subtle text-primary">Travel Partner</span>
                                        {/if}
                                    </td>
                                    <td>{partner.phone}</td>
                                    <td>{partner.orders_count ?? 0}</td>
                                    <td>
                                        <span class="badge {statusBadge[partner.status] ?? ''}">
                                            {partner.status}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            {#if partner.status === 'pending'}
                                                <button
                                                    onclick={() => approvePartner(partner.id)}
                                                    class="btn btn-sm btn-icon btn-success"
                                                    title="Approve"
                                                >
                                                    <i class="ti ti-check"></i>
                                                </button>
                                                <button
                                                    onclick={() => rejectPartner(partner.id)}
                                                    class="btn btn-sm btn-icon btn-warning"
                                                    title="Reject"
                                                >
                                                    <i class="ti ti-x"></i>
                                                </button>
                                            {/if}
                                            <Link
                                                href={`/admin/partners/${partner.id}/edit`}
                                                class="btn btn-sm btn-icon btn-primary"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </Link>
                                            <button
                                                onclick={() => deletePartner(partner.id)}
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
                                            {#if search || status}
                                                No partners match your filters.
                                            {:else}
                                                No partners yet.
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={partners.links} />
            </div>
        </div>
    </div>
</AdminLayout>
