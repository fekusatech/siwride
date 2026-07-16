<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { bookings, filters } = $props();
    let search = $state(filters.search ?? '');
    let status = $state(filters.status ?? '');

    let searchTimeout: any;
    $effect(() => {
        const currentSearch = filters.search ?? '';
        if (search !== currentSearch) {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                router.get('/admin/activity-bookings', { search, status }, { preserveState: true, replace: true });
            }, 300);
        }
    });

    $effect(() => {
        search = filters.search ?? '';
        status = filters.status ?? '';
    });

    function filterByStatus(newStatus: string) {
        status = newStatus;
        router.get('/admin/activity-bookings', { search, status }, { preserveState: true, replace: true });
    }

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        confirmed: 'bg-success-subtle text-success',
        cancelled: 'bg-danger-subtle text-danger',
        completed: 'bg-primary-subtle text-primary',
    };

    const paymentColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        paid: 'bg-success-subtle text-success',
        failed: 'bg-danger-subtle text-danger',
        expired: 'bg-secondary-subtle text-secondary',
    };

    let bookingList = $derived(bookings.data);
</script>

<AppHead title="Activity Bookings" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Activity Bookings</h4>
                <p class="text-muted mb-0">Manage all activity & experience bookings</p>
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
                                    placeholder="Search by code, name, email..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-auto">
                            <div class="d-flex gap-2">
                                {#each ['', 'pending', 'confirmed', 'completed', 'cancelled'] as s}
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
                                <th>Booking Code</th>
                                <th>Activity</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Pax</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Payment</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each bookingList as booking}
                                <tr>
                                    <td>
                                        <code class="small">{booking.booking_code}</code>
                                    </td>
                                    <td>
                                        <div class="fw-medium">{booking.activity?.title ?? '-'}</div>
                                    </td>
                                    <td>
                                        <div>{booking.customer_name}</div>
                                        <small class="text-muted">{booking.customer_phone || booking.customer_email}</small>
                                    </td>
                                    <td>{new Date(booking.booking_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
                                    <td>{booking.pax}</td>
                                    <td>Rp {Number(booking.total_price).toLocaleString('id-ID')}</td>
                                    <td>
                                        <span class="badge {statusColors[booking.status] ?? ''}">
                                            {booking.status}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {paymentColors[booking.payment_status] ?? ''}">
                                            {booking.payment_status}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <Link
                                            href={`/admin/activity-bookings/${booking.id}`}
                                            class="btn btn-sm btn-icon btn-primary"
                                        >
                                            <i class="ti ti-eye"></i>
                                        </Link>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">No activity bookings found.</div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={bookings.links} />
            </div>
        </div>
    </div>
</AdminLayout>
