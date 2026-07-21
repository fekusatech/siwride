<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { booking } = $props();

    const statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

    function updateStatus(newStatus: string) {
        router.patch(`/admin/activity-bookings/${booking.id}/status`, { status: newStatus }, { preserveScroll: true });
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
</script>

<AppHead title="Activity Booking Detail" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Booking Detail</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><Link href="/admin/activity-bookings">Activity Bookings</Link></li>
                    <li class="breadcrumb-item active">{booking.booking_code}</li>
                </ol>
            </div>
            <Link href="/admin/activity-bookings" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Booking Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Booking Code</small>
                                <code class="fs-6">{booking.booking_code}</code>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Activity</small>
                                <span class="fw-medium">{booking.activity?.title ?? '-'}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Activity Date</small>
                                <span>{new Date(booking.booking_date).toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Participants</small>
                                <span>{booking.pax} pax</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Price per Pax</small>
                                <span>Rp {Number(booking.price_per_pax).toLocaleString('id-ID')}</span>
                            </div>
                            <div class="col-sm-6">
                                <small class="text-muted d-block">Total Price</small>
                                <span class="fw-bold fs-5">Rp {Number(booking.total_price).toLocaleString('id-ID')}</span>
                            </div>
                            {#if booking.notes}
                                <div class="col-12">
                                    <small class="text-muted d-block">Notes</small>
                                    <span>{booking.notes}</span>
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0 mb-3">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Name</small>
                                <span>{booking.customer_name}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Email</small>
                                <span>{booking.customer_email}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Phone</small>
                                <span>{booking.customer_phone || '-'}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Payment Status</small>
                                <span class="badge {paymentColors[booking.payment_status] ?? ''}">
                                    {booking.payment_status}
                                </span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Payment Method</small>
                                <span>{booking.payment_method || '-'}</span>
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted d-block">Payment Reference</small>
                                {#if booking.payment_reference && booking.payment_reference.startsWith('http')}
                                    <a href={booking.payment_reference} target="_blank" class="small">View Invoice</a>
                                {:else}
                                    <span>{booking.payment_reference || '-'}</span>
                                {/if}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">Booking Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">Current Status</small>
                            <span class="badge fs-6 {statusColors[booking.status] ?? ''}">
                                {booking.status}
                            </span>
                        </div>
                        <hr>
                        <p class="small text-muted mb-2">Update status:</p>
                        <div class="d-flex flex-column gap-2">
                            {#each statuses as s}
                                <button
                                    onclick={() => updateStatus(s)}
                                    class="btn btn-sm {booking.status === s ? 'btn-secondary disabled' : 'btn-outline-secondary'}"
                                    disabled={booking.status === s}
                                >
                                    Mark as {s.charAt(0).toUpperCase() + s.slice(1)}
                                </button>
                            {/each}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
