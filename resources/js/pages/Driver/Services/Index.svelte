<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link } from '@inertiajs/svelte';

    const publicUrl = (slug: string) => `${window.location.origin}/services/${slug}`;

    async function shareService(service: any) {
        const url = publicUrl(service.slug);
        if (navigator.share) {
            await navigator.share({ title: service.title, url });
            return;
        }
        await navigator.clipboard.writeText(url);
        alert('Service link copied.');
    }

    let { services } = $props();

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        approved: 'bg-success-subtle text-success',
        rejected: 'bg-danger-subtle text-danger',
    };

    let serviceList = $derived(services.data);

    function formatRp(amount: number): string {
        return 'Rp ' + amount.toLocaleString('id-ID');
    }
</script>

<AppHead title="My Services" />

<DriverLayout>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">My Services</h4>
        <Link href="/driver/services/create" class="btn btn-primary">
            <i class="ti ti-plus me-1"></i> New Service
        </Link>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Title</th>
                            <th>Price / pax</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-center">Share</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each serviceList as service}
                            <tr>
                                <td class="fw-medium">{service.title}</td>
                                <td>{service.price_per_pax ? formatRp(Number(service.price_per_pax)) : '—'}</td>
                                <td>
                                    <span class="badge {statusColors[service.status] ?? ''}">{service.status}</span>
                                    {#if service.status === 'rejected' && service.rejection_reason}
                                        <div class="small text-danger mt-1">{service.rejection_reason}</div>
                                    {/if}
                                </td>
                                <td>{new Date(service.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                <td class="text-center">
                                    {#if service.status === 'approved'}
                                        <button onclick={() => shareService(service)} class="btn btn-sm btn-outline-primary">
                                            <i class="ti ti-share me-1"></i> Share
                                        </button>
                                    {:else}
                                        <span class="text-muted">—</span>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    <Link href={`/driver/services/${service.id}/edit`} class="btn btn-sm btn-icon btn-primary">
                                        <i class="ti ti-edit"></i>
                                    </Link>
                                </td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    You haven't posted any services yet.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            <Pagination links={services.links} />
        </div>
    </div>
</DriverLayout>
