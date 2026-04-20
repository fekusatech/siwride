<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import OrderForm from '@/components/Orders/OrderForm.svelte';
    import Flatpickr from '@/components/Flatpickr.svelte';
    import { Link, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import { fade, fly } from 'svelte/transition';

    let { orders, filters, drivers, google_maps_api_key } = $props();
    // svelte-ignore state_referenced_locally
    let search = $state(filters.search ?? '');
    // svelte-ignore state_referenced_locally
    let status = $state(filters.status ?? '');
    // svelte-ignore state_referenced_locally
    let driverId = $state(filters.driver_id ?? '');
    // svelte-ignore state_referenced_locally
    let fromDate = $state(filters.from_date ?? '');
    // svelte-ignore state_referenced_locally
    let toDate = $state(filters.to_date ?? '');

    // Sync state with props (important for URL/Back button navigation)
    $effect(() => {
        search = filters.search ?? '';
        status = filters.status ?? '';
        driverId = filters.driver_id ?? '';
        fromDate = filters.from_date ?? '';
        toDate = filters.to_date ?? '';
    });

    let showModal = $state(false);
    let modalMode = $state<'view' | 'edit'>('view');
    let selectedOrder = $state<any>(null);

    let updatingOrderId = $state<number | null>(null);
    let sharingOrderId = $state<number | null>(null);
    let resendingOrderId = $state<number | null>(null);
    let processingClaimId = $state<number | null>(null);
    let processingClaimAction = $state<'accept' | 'reject' | null>(null);
    let toast = $state<{ message: string; type: 'success' | 'error' } | null>(
        null,
    );
    let toastTimeout: any;

    function showToast(message: string, type: 'success' | 'error' = 'success') {
        clearTimeout(toastTimeout);
        toast = { message, type };
        toastTimeout = setTimeout(() => (toast = null), 3000);
    }

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    function openModal(order: any, mode: 'view' | 'edit' = 'view') {
        selectedOrder = order;
        modalMode = mode;
        showModal = true;
    }

    function handleDelete(order: any) {
        if (
            confirm(
                `Are you sure you want to delete booking ${order.booking_code}?`,
            )
        ) {
            router.delete(`/admin/orders/${order.id}`);
        }
    }

    function acceptClaim(orderId: number) {
        processingClaimId = orderId;
        processingClaimAction = 'accept';
        router.post(
            `/admin/orders/${orderId}/accept-claim`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    processingClaimId = null;
                    processingClaimAction = null;
                },
            },
        );
    }

    function rejectClaim(orderId: number) {
        processingClaimId = orderId;
        processingClaimAction = 'reject';
        router.post(
            `/admin/orders/${orderId}/reject-claim`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    processingClaimId = null;
                    processingClaimAction = null;
                },
            },
        );
    }

    function resendWaToDriver(orderId: number) {
        resendingOrderId = orderId;
        router.post(
            `/admin/orders/${orderId}/resend-wa`,
            {},
            {
                preserveScroll: true,
                onFinish: () => (resendingOrderId = null),
            },
        );
    }

    function updateStatus(orderId: number, status: string) {
        updatingOrderId = orderId;
        router.patch(
            `/admin/orders/${orderId}/status`,
            { status },
            {
                onFinish: () => (updatingOrderId = null),
                preserveScroll: true,
            },
        );
    }

    function handleOrderSuccess() {
        showModal = false;
        selectedOrder = null;
    }

    async function copyToClipboard(order: any) {
        const d = new Date(order.date);
        const months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        const dateStr = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        const timeStr = order.time ? order.time.substring(0, 5) : '';
        const priceFormatted = new Intl.NumberFormat('id-ID').format(
            order.price,
        );
        const flightNumber = order.flight_number || '-';

        let distance = '-';
        if (
            order.pickup_latitude &&
            order.pickup_longitude &&
            order.dropoff_latitude &&
            order.dropoff_longitude
        ) {
            try {
                const csrfToken =
                    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const resp = await fetch('/admin/orders/distance', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        pickup_latitude: order.pickup_latitude,
                        pickup_longitude: order.pickup_longitude,
                        dropoff_latitude: order.dropoff_latitude,
                        dropoff_longitude: order.dropoff_longitude,
                    }),
                });
                const data = await resp.json();
                if (data.distance && data.distance !== '-') {
                    distance = data.distance;
                }
            } catch {
                distance = '-';
            }
        }

        let message: string;

        if (order.driver) {
            const vehicleInfo = order.vehicle
                ? `${order.vehicle.brand} ${order.vehicle.model} (${order.vehicle.registration_number})`
                : '-';
            message =
                `*ORDER DIKONFIRMASI ADMIN*\n\n` +
                `Booking Code: ${order.booking_code}\n` +
                `Order Number: ${order.order_number}\n\n` +
                `*Driver:*\n` +
                `Nama: ${order.driver.name}\n` +
                `Mobil: ${vehicleInfo}\n\n` +
                `*Customer:*\n` +
                `Nama: ${order.customer_name}\n` +
                `Telepon: ${order.customer_phone}\n` +
                `Flight Number: ${flightNumber}\n\n` +
                `*Pickup:* ${order.pickup_address}\n` +
                `*Dropoff:* ${order.dropoff_address}\n` +
                (distance !== '-' ? `*Jarak:* ${distance}\n` : '') +
                `*Tanggal:* ${dateStr}\n` +
                `*Jam:* ${timeStr} WITA\n` +
                `*Penumpang:* ${order.passengers} Pax\n` +
                `*Harga:* Rp ${priceFormatted}\n\n` +
                `Silakan hubungi customer untuk konfirmasi penjemputan!`;
        } else {
            const claimUrl = `${window.location.origin}/c/${order.booking_code}`;
            message =
                `*#${order.order_number} - CHECK IN*\n\n` +
                `*Kode Booking:* ${order.booking_code}\n` +
                `*Tanggal:* ${dateStr}\n` +
                `*Jam:* ${timeStr} WITA\n\n` +
                `*📍 Pick Up:*\n${order.pickup_address}\n\n` +
                `*🏁 Drop Off:*\n${order.dropoff_address}\n\n` +
                `*Detail:*\n` +
                `- Penumpang: ${order.passengers} Pax\n` +
                `- Flight Number: ${order.flight_number || '-'}\n` +
                `- Price: *${priceFormatted}*\n\n` +
                `*Klaim Order (Buka Link):*\n${claimUrl}`;
        }

        navigator.clipboard
            .writeText(message)
            .then(() => {
                showToast('Pesan berhasil disalin ke clipboard!');
            })
            .catch(() => {
                showToast('Gagal menyalin pesan.', 'error');
            });
    }

    function shareToWhatsApp(order: any) {
        const d = new Date(order.date);
        const months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        const dateStr = `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
        const timeStr = order.time ? order.time.substring(0, 5) : '';
        const priceFormatted = new Intl.NumberFormat('id-ID').format(
            order.price,
        );

        const claimUrl = `${window.location.origin}/c/${order.booking_code}`;

        const message =
            `*#${order.order_number} - CHECK IN*\n\n` +
            `*Kode Booking:* ${order.booking_code}\n` +
            `*Tanggal:* ${dateStr}\n` +
            `*Jam:* ${timeStr} WITA\n\n` +
            `*📍 Pick Up:*\n${order.pickup_address}\n\n` +
            `*🏁 Drop Off:*\n${order.dropoff_address}\n\n` +
            `*Detail:*\n` +
            `- Penumpang: ${order.passengers} Pax\n` +
            `- Flight Number: ${order.flight_number || '-'}\n` +
            `- Price: *${priceFormatted}*\n\n` +
            `*Klaim Order (Buka Link):*\n${claimUrl}`;

        sharingOrderId = order.id;
        router.post(
            `/admin/orders/${order.id}/share`,
            { message },
            {
                preserveScroll: true,
                onFinish: () => (sharingOrderId = null),
                onSuccess: () => {
                    showToast('Order berhasil dibagikan ke WhatsApp!');
                },
                onError: () => {
                    showToast('Gagal mengirim pesan ke WhatsApp.', 'error');
                },
            },
        );
    }

    let isReady = $state(false);
    let searchTimeout: any;

    function applyFilters() {
        const params: any = { search };
        if (status) params.status = status;
        if (driverId) params.driver_id = driverId;
        if (fromDate) params.from_date = fromDate;
        if (toDate) params.to_date = toDate;

        router.get('/admin/orders', params, {
            preserveState: true,
            replace: true,
            preserveScroll: true,
        });
    }

    $effect(() => {
        // Skip the very first run to avoid redundant request on mount
        if (!isReady) {
            isReady = true;
            return;
        }

        // Debounce only on search input change
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Only apply if the search actually changed (to prevent Prop sync from triggering navigation)
            if (search !== (filters.search ?? '')) {
                applyFilters();
            }
        }, 500);
    });

    // Handle immediate updates for non-debounced filters
    $effect(() => {
        if (
            isReady &&
            (status !== (filters.status ?? '') ||
                driverId !== (filters.driver_id ?? '') ||
                fromDate !== (filters.from_date ?? '') ||
                toDate !== (filters.to_date ?? ''))
        ) {
            applyFilters();
        }
    });

    function clearFilters() {
        search = '';
        status = '';
        driverId = '';
        fromDate = '';
        toDate = '';
        applyFilters();
    }
</script>

<AppHead title="Orders" />

<AdminLayout>
    <style>
        .status-segmented-control {
            background: #f3f4f7;
            padding: 3px;
            border-radius: 8px;
            display: inline-flex;
            gap: 2px;
            border: 1px solid #e2e8f0;
        }
        .status-segment {
            padding: 5px 10px;
            border-radius: 6px;
            border: none;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }
        .status-segment:hover:not(.active) {
            background: rgba(255, 255, 255, 0.5);
            color: #334155;
        }
        .status-segment.active.pending {
            background: #fff;
            color: #f59e0b;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .status-segment.active.completed {
            background: #fff;
            color: #10b981;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .status-segment.active.cancelled {
            background: #fff;
            color: #ef4444;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .status-segment:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .loading-ring {
            width: 16px;
            height: 16px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Order Management</h4>
                <p class="text-muted mb-0">List of all booking orders</p>
            </div>
            <div class="d-flex gap-2">
                <Link
                    href="/admin/orders/import"
                    class="btn btn-outline-success d-flex align-items-center gap-1"
                >
                    <i class="ti ti-file-spreadsheet fs-18"></i>
                    Import Excel
                </Link>
                <Link
                    href="/admin/orders/calendar"
                    class="btn btn-outline-primary d-flex align-items-center gap-1"
                >
                    <i class="ti ti-calendar fs-18"></i>
                    Calendar View
                </Link>
                <Link
                    href="/admin/orders/create"
                    class="btn btn-primary d-flex align-items-center gap-1"
                >
                    <i class="ti ti-plus fs-18"></i>
                    Input Order
                </Link>
            </div>
        </div>

        <div class="card mb-4 shadow-sm border-0">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label
                            for="filter-search"
                            class="form-label small fw-bold text-muted"
                            >Search</label
                        >
                        <div class="input-group">
                            <span
                                class="input-group-text bg-transparent border-end-0"
                            >
                                <i class="ti ti-search text-muted"></i>
                            </span>
                            <input
                                id="filter-search"
                                type="text"
                                class="form-control border-start-0 ps-0"
                                placeholder="Order #, booking, customer..."
                                bind:value={search}
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label
                            for="filter-status"
                            class="form-label small fw-bold text-muted"
                            >Status</label
                        >
                        <select
                            id="filter-status"
                            class="form-select"
                            bind:value={status}
                        >
                            <option value="">All Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label
                            for="filter-driver"
                            class="form-label small fw-bold text-muted"
                            >Driver</label
                        >
                        <select
                            id="filter-driver"
                            class="form-select"
                            bind:value={driverId}
                        >
                            <option value="">All Drivers</option>
                            {#each drivers as driver}
                                <option value={driver.id}>{driver.name}</option>
                            {/each}
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label
                            for="filter-from-date"
                            class="form-label small fw-bold text-muted"
                            >Date Range</label
                        >
                        <div class="input-group">
                            <Flatpickr
                                id="filter-from-date"
                                bind:value={fromDate}
                                placeholder="From"
                                options={{
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    dateFormat: 'Y-m-d',
                                }}
                                class="border-end-0"
                            />
                            <span
                                class="input-group-text bg-white px-1 text-muted"
                                >to</span
                            >
                            <Flatpickr
                                id="filter-to-date"
                                bind:value={toDate}
                                placeholder="To"
                                options={{
                                    altInput: true,
                                    altFormat: 'd/m/Y',
                                    dateFormat: 'Y-m-d',
                                }}
                            />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-none d-md-block" style="height: 24px;">
                            &nbsp;
                        </div>
                        <div class="d-flex gap-2">
                            <button
                                class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-1"
                                style="height: 38px;"
                                onclick={applyFilters}
                            >
                                <i class="ti ti-filter"></i> Filter
                            </button>
                            <button
                                class="btn btn-outline-danger px-2 d-flex align-items-center justify-content-center"
                                style="height: 38px; width: 45px;"
                                onclick={clearFilters}
                                title="Clear All Filters"
                                disabled={!search &&
                                    !status &&
                                    !driverId &&
                                    !fromDate &&
                                    !toDate}
                            >
                                <i class="ti ti-filter-off"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table
                        class="table table-hover table-centered mb-0 text-nowrap"
                    >
                        <thead class="bg-light">
                            <tr>
                                <th>Booking Code</th>
                                <th>Order #</th>
                                <th>Date & Time</th>
                                <th>Customer</th>
                                <th>Flight</th>
                                <th>Driver</th>
                                <th>Pickup / Dropoff</th>
                                <th>Pass</th>
                                <th>Price</th>
                                <th class="text-center">Action / Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each orders.data as order}
                                <tr>
                                    <td
                                        ><span class="fw-bold"
                                            >{order.booking_code}</span
                                        ></td
                                    >
                                    <td>{order.order_number}</td>
                                    <td>
                                        <div>
                                            {new Date(
                                                order.date,
                                            ).toLocaleDateString('id-ID')}
                                        </div>
                                        <small class="text-muted"
                                            >{order.time}</small
                                        >
                                    </td>
                                    <td>
                                        <div class="fw-medium">
                                            {order.customer_name}
                                        </div>
                                        <small class="text-muted"
                                            >{order.customer_phone}</small
                                        >
                                    </td>
                                    <td>{order.flight_number || '-'}</td>
                                    <td>
                                        {#if order.driver}
                                            <div class="fw-medium text-dark">
                                                {order.driver.name}
                                            </div>
                                            {#if order.vehicle}
                                                <small
                                                    class="text-primary fw-bold d-block mt-1"
                                                >
                                                    <i class="ti ti-car"></i>
                                                    {order.vehicle
                                                        .registration_number}
                                                </small>
                                            {/if}
                                        {:else if order.claimed_driver}
                                            <div
                                                class="d-flex flex-column align-items-start gap-1"
                                            >
                                                <div
                                                    class="fw-medium text-warning"
                                                >
                                                    <i
                                                        class="ti ti-user-clock me-1"
                                                    ></i>
                                                    {order.claimed_driver.name}
                                                </div>
                                                <small class="text-muted">
                                                    NID: {order.claimed_driver
                                                        .nid || '-'}
                                                </small>
                                                <div class="d-flex gap-1 mt-1">
                                                    <button
                                                        class="btn btn-xs btn-success d-flex align-items-center gap-1"
                                                        style="font-size: 11px; padding: 2px 8px;"
                                                        disabled={processingClaimId ===
                                                            order.id}
                                                        onclick={() =>
                                                            acceptClaim(
                                                                order.id,
                                                            )}
                                                        title="Accept Claim"
                                                    >
                                                        {#if processingClaimId === order.id && processingClaimAction === 'accept'}
                                                            <span
                                                                class="spinner-border spinner-border-sm"
                                                                style="width: 12px; height: 12px;"
                                                            ></span>
                                                        {:else}
                                                            <i
                                                                class="ti ti-check fs-12"
                                                            ></i>
                                                        {/if}
                                                        Accept
                                                    </button>
                                                    <button
                                                        class="btn btn-xs btn-outline-danger d-flex align-items-center gap-1"
                                                        style="font-size: 11px; padding: 2px 8px;"
                                                        disabled={processingClaimId ===
                                                            order.id}
                                                        onclick={() =>
                                                            rejectClaim(
                                                                order.id,
                                                            )}
                                                        title="Reject Claim"
                                                    >
                                                        {#if processingClaimId === order.id && processingClaimAction === 'reject'}
                                                            <span
                                                                class="spinner-border spinner-border-sm"
                                                                style="width: 12px; height: 12px;"
                                                            ></span>
                                                        {:else}
                                                            <i
                                                                class="ti ti-x fs-12"
                                                            ></i>
                                                        {/if}
                                                        Reject
                                                    </button>
                                                </div>
                                            </div>
                                        {:else}
                                            <div
                                                class="d-flex flex-column align-items-start gap-1"
                                            >
                                                <span
                                                    class="badge bg-soft-warning text-warning border border-warning px-2 py-1"
                                                >
                                                    <i class="ti ti-loader me-1"
                                                    ></i> Open Order
                                                </span>
                                                <small
                                                    class="text-muted italic ps-1"
                                                    >Waiting for driver...</small
                                                >
                                            </div>
                                        {/if}
                                    </td>
                                    <td
                                        style="max-width: 250px;"
                                        class="text-truncate"
                                    >
                                        <div
                                            class="d-flex align-items-center gap-1"
                                        >
                                            <i
                                                class="ti ti-map-pin text-success"
                                            ></i>
                                            <span title={order.pickup_address}
                                                >{order.pickup_address}</span
                                            >
                                        </div>
                                        <div
                                            class="d-flex align-items-center gap-1 mt-1"
                                        >
                                            <i class="ti ti-map-pin text-danger"
                                            ></i>
                                            <span title={order.dropoff_address}
                                                >{order.dropoff_address}</span
                                            >
                                        </div>
                                    </td>
                                    <td>{order.passengers}</td>
                                    <td>
                                        <div class="fw-medium">
                                            {formatCurrency(order.price)}
                                        </div>
                                        <small class="text-muted"
                                            >P/B: {formatCurrency(
                                                order.parking_gas_fee,
                                            )}</small
                                        >
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="d-flex align-items-center justify-content-center gap-3"
                                        >
                                            <!-- Interactive Status Toggle -->
                                            <div
                                                class="status-segmented-control shadow-sm"
                                            >
                                                <button
                                                    class="status-segment {order.status ===
                                                    'pending'
                                                        ? 'active pending'
                                                        : ''}"
                                                    onclick={() =>
                                                        updateStatus(
                                                            order.id,
                                                            'pending',
                                                        )}
                                                    disabled={updatingOrderId ===
                                                        order.id}
                                                    title="Set to Pending"
                                                >
                                                    {#if updatingOrderId === order.id && order.status !== 'pending'}
                                                        <span
                                                            class="loading-ring"
                                                        ></span>
                                                    {:else}
                                                        <i class="ti ti-clock"
                                                        ></i>
                                                    {/if}
                                                </button>
                                                <button
                                                    class="status-segment {order.status ===
                                                    'completed'
                                                        ? 'active completed'
                                                        : ''}"
                                                    onclick={() =>
                                                        updateStatus(
                                                            order.id,
                                                            'completed',
                                                        )}
                                                    disabled={updatingOrderId ===
                                                        order.id}
                                                    title="Set to Completed"
                                                >
                                                    {#if updatingOrderId === order.id && order.status !== 'completed'}
                                                        <span
                                                            class="loading-ring"
                                                        ></span>
                                                    {:else}
                                                        <i class="ti ti-check"
                                                        ></i>
                                                    {/if}
                                                </button>
                                                <button
                                                    class="status-segment {order.status ===
                                                    'cancelled'
                                                        ? 'active cancelled'
                                                        : ''}"
                                                    onclick={() =>
                                                        updateStatus(
                                                            order.id,
                                                            'cancelled',
                                                        )}
                                                    disabled={updatingOrderId ===
                                                        order.id}
                                                    title="Set to Cancelled"
                                                >
                                                    {#if updatingOrderId === order.id && order.status !== 'cancelled'}
                                                        <span
                                                            class="loading-ring"
                                                        ></span>
                                                    {:else}
                                                        <i class="ti ti-x"></i>
                                                    {/if}
                                                </button>
                                            </div>

                                            <div
                                                class="d-flex align-items-center gap-1 border-start ps-3 py-1"
                                            >
                                                {#if order.driver}
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-success btn-icon"
                                                        title="Resend WA to Driver"
                                                        disabled={resendingOrderId ===
                                                            order.id}
                                                        onclick={(e) => {
                                                            e.preventDefault();
                                                            resendWaToDriver(
                                                                order.id,
                                                            );
                                                        }}
                                                    >
                                                        {#if resendingOrderId === order.id}
                                                            <span
                                                                class="loading-ring"
                                                            ></span>
                                                        {:else}
                                                            <i
                                                                class="ti ti-brand-whatsapp fs-16"
                                                            ></i>
                                                        {/if}
                                                    </button>
                                                {/if}
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-soft-success btn-icon"
                                                    title="Share to WA Group"
                                                    disabled={sharingOrderId ===
                                                        order.id}
                                                    onclick={(e) => {
                                                        e.preventDefault();
                                                        shareToWhatsApp(order);
                                                    }}
                                                >
                                                    {#if sharingOrderId === order.id}
                                                        <span
                                                            class="loading-ring"
                                                        ></span>
                                                    {:else}
                                                        <i
                                                            class="ti ti-share fs-16"
                                                        ></i>
                                                    {/if}
                                                </button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-icon"
                                                    title={order.driver
                                                        ? 'Copy WA to Driver'
                                                        : 'Copy WA to Group'}
                                                    data-copy-btn={order.id}
                                                    onclick={(e) => {
                                                        e.preventDefault();
                                                        copyToClipboard(order);
                                                    }}
                                                >
                                                    <i
                                                        class="ti ti-clipboard fs-16"
                                                    ></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-light btn-icon"
                                                    title="View Detail"
                                                    onclick={() =>
                                                        openModal(
                                                            order,
                                                            'view',
                                                        )}
                                                >
                                                    <i class="ti ti-eye fs-16"
                                                    ></i>
                                                </button>
                                                <button
                                                    class="btn btn-sm btn-soft-danger btn-icon"
                                                    title="Delete"
                                                    onclick={() =>
                                                        handleDelete(order)}
                                                >
                                                    <i class="ti ti-trash fs-16"
                                                    ></i>
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <div class="text-muted">
                                            <i
                                                class="ti ti-inbox fs-48 mb-2 d-block"
                                            ></i>
                                            No orders found.
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
            <Pagination links={orders.links} />
        </div>
    </div>

    <!-- Multi-purpose Modal (View/Edit) -->
    {#if showModal}
        <div
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0,0,0,0.5); z-index: 1055;"
            transition:fade={{ duration: 150 }}
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            {#if modalMode === 'view'}
                                <i class="ti ti-info-circle fs-22"></i>
                                Booking Details - {selectedOrder.booking_code}
                            {:else}
                                <i class="ti ti-edit fs-22"></i>
                                Edit Booking - {selectedOrder.booking_code}
                            {/if}
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            onclick={() => (showModal = false)}
                            aria-label="Close"
                        ></button>
                    </div>
                    <div
                        class="modal-body p-4"
                        style="max-height: 80vh; overflow-y: auto;"
                    >
                        {#if modalMode === 'view'}
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div
                                        class="text-muted small text-uppercase fw-bold mb-1 d-block"
                                    >
                                        Customer
                                    </div>
                                    <div
                                        class="d-flex align-items-center gap-2"
                                    >
                                        <div
                                            class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                                            style="width: 40px; height: 40px;"
                                        >
                                            <i class="ti ti-user fs-20"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0">
                                                {selectedOrder.customer_name}
                                            </h5>
                                            <p class="mb-0 text-muted">
                                                {selectedOrder.customer_phone}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div
                                        class="text-muted small text-uppercase fw-bold mb-1 d-block"
                                    >
                                        Status
                                    </div>
                                    <span
                                        class="badge fs-14 bg-{selectedOrder.status ===
                                        'completed'
                                            ? 'success'
                                            : selectedOrder.status ===
                                                'cancelled'
                                              ? 'danger'
                                              : 'warning'}-subtle text-{selectedOrder.status ===
                                        'completed'
                                            ? 'success'
                                            : selectedOrder.status ===
                                                'cancelled'
                                              ? 'danger'
                                              : 'warning'}"
                                    >
                                        {selectedOrder.status.toUpperCase()}
                                    </span>
                                </div>

                                <div class="col-12"><hr class="my-0" /></div>

                                <div class="col-md-6">
                                    <div
                                        class="text-muted small text-uppercase fw-bold mb-2 d-block"
                                    >
                                        Route Details
                                    </div>
                                    <div class="p-3 bg-light rounded-3">
                                        <div class="d-flex gap-3 mb-3">
                                            <i
                                                class="ti ti-map-pin text-primary fs-20 mt-1"
                                            ></i>
                                            <div>
                                                <small
                                                    class="text-muted d-block"
                                                    >Pickup</small
                                                >
                                                <span class="fw-medium"
                                                    >{selectedOrder.pickup_address}</span
                                                >
                                            </div>
                                        </div>
                                        <div class="d-flex gap-3">
                                            <i
                                                class="ti ti-building text-info fs-20 mt-1"
                                            ></i>
                                            <div>
                                                <small
                                                    class="text-muted d-block"
                                                    >Dropoff</small
                                                >
                                                <span class="fw-medium"
                                                    >{selectedOrder.dropoff_address}</span
                                                >
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div
                                        class="text-muted small text-uppercase fw-bold mb-2 d-block"
                                    >
                                        Assignment
                                    </div>
                                    <div class="p-3 border rounded-3 h-100">
                                        <div
                                            class="d-flex align-items-center gap-2 mb-2"
                                        >
                                            <i
                                                class="ti ti-user-check text-muted fs-18"
                                            ></i>
                                            <span class="text-muted"
                                                >Driver:</span
                                            >
                                            <span class="fw-bold"
                                                >{selectedOrder.driver?.name ||
                                                    'Unassigned'}</span
                                            >
                                        </div>
                                        <div
                                            class="d-flex align-items-center gap-2"
                                        >
                                            <i
                                                class="ti ti-car text-muted fs-18"
                                            ></i>
                                            <span class="text-muted"
                                                >Vehicle:</span
                                            >
                                            <span class="fw-medium">
                                                {selectedOrder.vehicle
                                                    ? `${selectedOrder.vehicle.registration_number} (${selectedOrder.vehicle.brand})`
                                                    : 'None'}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div
                                        class="row bg-primary-subtle p-3 rounded-3 mx-0"
                                    >
                                        <div class="col-md-4 text-center">
                                            <small class="text-muted d-block"
                                                >Passengers</small
                                            >
                                            <span class="fw-bold fs-16"
                                                >{selectedOrder.passengers} PAX</span
                                            >
                                        </div>
                                        <div
                                            class="col-md-4 text-center border-start border-primary-subtle"
                                        >
                                            <small class="text-muted d-block"
                                                >Price</small
                                            >
                                            <span
                                                class="fw-bold fs-16 text-primary"
                                                >{formatCurrency(
                                                    selectedOrder.price,
                                                )}</span
                                            >
                                        </div>
                                        <div
                                            class="col-md-4 text-center border-start border-primary-subtle"
                                        >
                                            <small class="text-muted d-block"
                                                >P/B Fee</small
                                            >
                                            <span
                                                class="fw-bold fs-16 text-primary"
                                                >{formatCurrency(
                                                    selectedOrder.parking_gas_fee,
                                                )}</span
                                            >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-end gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light px-4"
                                    onclick={() => (showModal = false)}
                                    >Close</button
                                >
                                <button
                                    type="button"
                                    class="btn btn-primary px-4 d-flex align-items-center gap-1"
                                    onclick={() => (modalMode = 'edit')}
                                >
                                    <i class="ti ti-edit"></i> Edit Booking
                                </button>
                            </div>
                        {:else}
                            <OrderForm
                                {drivers}
                                {google_maps_api_key}
                                order={selectedOrder}
                                onSuccess={handleOrderSuccess}
                            >
                                {#snippet footer({ processing })}
                                    <button
                                        type="button"
                                        class="btn btn-light px-4"
                                        onclick={() => (modalMode = 'view')}
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="btn btn-primary px-4"
                                        disabled={processing}
                                    >
                                        {processing
                                            ? 'Saving...'
                                            : 'Update Booking'}
                                    </button>
                                {/snippet}
                            </OrderForm>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {#if toast}
        <div
            class="position-fixed bottom-0 end-0 p-3"
            style="z-index: 9999;"
            transition:fly={{ y: 30, duration: 200 }}
        >
            <div
                class="toast show align-items-center text-white border-0 shadow-lg"
                class:bg-success={toast.type === 'success'}
                class:bg-danger={toast.type === 'error'}
                role="alert"
                aria-live="assertive"
                aria-atomic="true"
            >
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2">
                        {#if toast.type === 'success'}
                            <i class="ti ti-check fs-20"></i>
                        {:else}
                            <i class="ti ti-alert-circle fs-20"></i>
                        {/if}
                        {toast.message}
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white me-2 m-auto"
                        onclick={() => (toast = null)}
                        aria-label="Close"
                    ></button>
                </div>
            </div>
        </div>
    {/if}
</AdminLayout>
