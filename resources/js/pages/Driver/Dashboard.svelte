<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
import { Link } from '@inertiajs/svelte';
    import { router } from '@inertiajs/svelte';
    import { wallet as walletRoute } from '@/routes/driver';

    let { serviceCounts, earnings, orders, serviceBookings } = $props<{
        serviceCounts: { pending: number; approved: number; rejected: number };
        earnings: { pending: number; paid: number };
        orders: { data: any[]; links: any };
        serviceBookings: { data: any[]; links: any };
    }>();

    const statusColors: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        confirmed: 'bg-success-subtle text-success',
        completed: 'bg-primary-subtle text-primary',
        cancelled: 'bg-danger-subtle text-danger',
    };

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }

    function acceptBooking(bookingCode: string): void {
        router.post(`/driver/service-bookings/${bookingCode}/accept`, {}, { preserveScroll: true });
    }

    function completeBooking(bookingCode: string): void {
        if (window.confirm('Confirm that cash has been received from the customer?')) {
            router.post(`/driver/service-bookings/${bookingCode}/cash-received`, {}, { preserveScroll: true });
        }
    }
</script>

<AppHead title="Driver Dashboard" />

<DriverLayout>
    <h4 class="fw-bold mb-4">Dashboard</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold">{serviceCounts.pending}</div>
                <div class="text-muted small">Pending Review</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-success">{serviceCounts.approved}</div>
                <div class="text-muted small">Published</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold">{formatRp(earnings.pending)}</div>
                <div class="text-muted small">Commission (Pending)</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-success">{formatRp(earnings.paid)}</div>
                <div class="text-muted small">Commission (Paid)</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-2">Post a service</h5>
            <p class="text-muted mb-3">
                Every approved service gets its own public, bookable page. When a customer books it, you earn a commission directly.
            </p>
            <Link href="/driver/services/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> New Service
            </Link>
            <Link href={walletRoute.url()} class="btn btn-outline-primary ms-2">
                <i class="ti ti-wallet me-1"></i> View Wallet
            </Link>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3">My Service Bookings</h5>
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Booking</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each serviceBookings.data as booking}
                            <tr>
                                <td class="fw-medium">{booking.booking_code}</td>
                                <td>{booking.driver_service?.title ?? '—'}</td>
                                <td>{booking.booking_date}</td>
                                <td>{booking.customer_name}</td>
                                <td>{formatRp(Number(booking.total_price))}</td>
                                <td><span class="badge {statusColors[booking.status] ?? ''}">{booking.status}</span></td>
                                <td>{booking.payment_status}</td>
                                <td>
                                    {#if booking.payment_status === 'paid' && booking.status === 'confirmed' && booking.assignment_status === 'assigned'}
                                        <button type="button" class="btn btn-sm btn-primary" onclick={() => acceptBooking(booking.booking_code)}>Accept</button>
                                    {:else if booking.payment_status === 'paid' && booking.status === 'confirmed' && booking.assignment_status === 'accepted'}
                                        <button type="button" class="btn btn-sm btn-success" onclick={() => completeBooking(booking.booking_code)}>Cash Received - Complete</button>
                                    {:else}
                                        <span class="text-muted small">—</span>
                                    {/if}
                                </td>
                            </tr>
                        {:else}
                            <tr><td colspan="8" class="text-center py-4 text-muted">No service bookings yet.</td></tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            <Pagination links={serviceBookings.links} />
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-3">My Orders</h5>
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Booking Code</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {#each orders.data as order}
                            <tr>
                                <td class="fw-medium">{order.booking_code}</td>
                                <td>{order.date}</td>
                                <td>{order.customer_name}</td>
                                <td>{formatRp(order.price)}</td>
                                <td><span class="badge {statusColors[order.status] ?? ''}">{order.status}</span></td>
                            </tr>
                        {:else}
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No orders assigned to you yet.
                                </td>
                            </tr>
                        {/each}
                    </tbody>
                </table>
            </div>
            <Pagination links={orders.links} />
        </div>
    </div>
</DriverLayout>
