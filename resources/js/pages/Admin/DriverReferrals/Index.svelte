<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router } from '@inertiajs/svelte';

    let { referrals, filters } = $props();
    let status = $state(filters.status ?? '');

    function filterByStatus(newStatus: string) {
        status = newStatus;
        router.get('/admin/driver-referrals', { status }, { preserveState: true, replace: true });
    }

    function markPaid(id: number) {
        router.patch(`/admin/driver-referrals/${id}/mark-paid`, {}, { preserveScroll: true });
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        paid: 'bg-success-subtle text-success',
        void: 'bg-secondary-subtle text-secondary',
    };

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    let referralList = $derived(referrals.data);
</script>

<AppHead title="Driver Referrals" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Driver Referrals</h4>
                <p class="text-muted mb-0">Commission earned by drivers from their service bookings</p>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="d-flex gap-2">
                        {#each ['', 'pending', 'paid', 'void'] as s}
                            <button
                                onclick={() => filterByStatus(s)}
                                class="btn btn-sm {status === s ? 'btn-primary' : 'btn-outline-secondary'}"
                            >
                                {s === '' ? 'All' : s.charAt(0).toUpperCase() + s.slice(1)}
                            </button>
                        {/each}
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light">
                            <tr>
                                <th>Driver</th>
                                <th>Service</th>
                                <th>Booking</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each referralList as referral}
                                <tr>
                                    <td>{referral.driver?.name ?? '-'}</td>
                                    <td>{referral.service?.title ?? '-'}</td>
                                    <td>
                                        <code class="small">{referral.order?.booking_code ?? referral.activity_booking?.booking_code ?? referral.service_booking?.booking_code ?? '-'}</code>
                                    </td>
                                    <td>{formatRp(referral.commission_amount)}</td>
                                    <td>
                                        <span class="badge {statusColors[referral.status] ?? ''}">
                                            {referral.status}
                                        </span>
                                    </td>
                                    <td>{new Date(referral.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                    <td class="text-center">
                                        {#if referral.status === 'pending'}
                                            <button onclick={() => markPaid(referral.id)} class="btn btn-sm btn-outline-success">
                                                Mark Paid
                                            </button>
                                        {/if}
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="7" class="text-center py-5">
                                        <div class="text-muted">No referrals found.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={referrals.links} />
            </div>
        </div>
    </div>
</AdminLayout>
