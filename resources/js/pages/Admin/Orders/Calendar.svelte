<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import OrderForm from '@/components/Orders/OrderForm.svelte';
    import { onMount } from 'svelte';
    import { Link, router } from '@inertiajs/svelte';

    let { orders, drivers, google_maps_api_key } = $props();

    let calendarEl: HTMLDivElement;
    let calendar: any;
    let showModal = $state(false);
    let modalMode = $state<'create' | 'view' | 'edit'>('create');
    let selectedDate = $state('');
    let selectedOrder = $state<any>(null);

    // Format orders for FullCalendar
    let events = $derived(
        orders.map((order) => {
            const datePart = order.date.split('T')[0];
            const timePart = order.time || '00:00:00';

            return {
                id: order.id,
                title: `${order.customer?.name || '-'} (${order.booking_code})`,
                start: `${datePart}T${timePart}`,
                color:
                    order.status === 'completed'
                        ? '#10b981'
                        : order.status === 'cancelled'
                          ? '#ef4444'
                          : '#f59e0b',
                extendedProps: { ...order },
            };
        }),
    );

    function initCalendar() {
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay',
            },
            events: events,
            selectable: true,
            select: (info) => {
                selectedDate = info.startStr;
                selectedOrder = null;
                modalMode = 'create';
                showModal = true;
            },
            eventClick: (info) => {
                selectedOrder = info.event.extendedProps;
                modalMode = 'view';
                showModal = true;
            },
        });
        calendar.render();
    }

    onMount(() => {
        // Load FullCalendar script if not present
        if (typeof FullCalendar === 'undefined') {
            const script = document.createElement('script');
            script.src =
                'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js';
            script.onload = () => initCalendar();
            document.head.appendChild(script);
        } else {
            initCalendar();
        }
    });

    // Update calendar when events change
    $effect(() => {
        if (calendar && events) {
            calendar.removeAllEvents();
            calendar.addEventSource(events);
        }
    });

    function handleOrderSuccess() {
        showModal = false;
        selectedOrder = null;
    }

    function handleDelete() {
        if (confirm('Are you sure you want to delete this booking?')) {
            router.delete(`/admin/orders/${selectedOrder.id}`, {
                onSuccess: () => {
                    showModal = false;
                    selectedOrder = null;
                },
            });
        }
    }

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }
</script>

<AppHead title="Orders Calendar" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Orders Calendar</h4>
                <p class="text-muted mb-0">
                    Manage bookings visually on the calendar
                </p>
            </div>
            <Link
                href="/admin/orders"
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-list fs-18"></i>
                Table View
            </Link>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div bind:this={calendarEl} id="calendar-container"></div>
            </div>
        </div>
    </div>

    <!-- Multi-purpose Modal (Create/View/Edit) -->
    {#if showModal}
        <div
            class="modal fade show d-block"
            tabindex="-1"
            style="background: rgba(0,0,0,0.5); z-index: 1055;"
        >
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content shadow-lg border-0 rounded-3">
                    <div class="modal-header bg-primary text-white py-3">
                        <h5 class="modal-title d-flex align-items-center gap-2">
                            {#if modalMode === 'create'}
                                <i class="ti ti-calendar-plus fs-22"></i>
                                New Booking for {new Date(
                                    selectedDate,
                                ).toLocaleDateString('id-ID', {
                                    day: 'numeric',
                                    month: 'long',
                                    year: 'numeric',
                                })}
                            {:else if modalMode === 'view'}
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
                                                {selectedOrder.customer?.name || '-'}
                                            </h5>
                                            <p class="mb-0 text-muted">
                                                {selectedOrder.customer?.phone || '-'}
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

                            <div
                                class="mt-4 d-flex justify-content-between align-items-center"
                            >
                                <button
                                    type="button"
                                    class="btn btn-link text-danger p-0"
                                    onclick={handleDelete}
                                >
                                    <i class="ti ti-trash"></i> Delete Booking
                                </button>
                                <div class="d-flex gap-2">
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
                            </div>
                        {:else}
                            <OrderForm
                                {drivers}
                                {google_maps_api_key}
                                initialDate={selectedDate}
                                order={selectedOrder}
                                onSuccess={handleOrderSuccess}
                            >
                                {#snippet footer({ processing })}
                                    <button
                                        type="button"
                                        class="btn btn-light px-4"
                                        onclick={() => {
                                            if (modalMode === 'edit') {
                                                modalMode = 'view';
                                            } else {
                                                showModal = false;
                                            }
                                        }}
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
                                            : modalMode === 'edit'
                                              ? 'Update Booking'
                                              : 'Confirm Booking'}
                                    </button>
                                {/snippet}
                            </OrderForm>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    {/if}
</AdminLayout>

<style>
    #calendar-container {
        min-height: 700px;
    }
    :global(.fc) {
        --fc-button-bg-color: #6366f1;
        --fc-button-border-color: #6366f1;
        --fc-button-hover-bg-color: #4f46e5;
        --fc-button-active-bg-color: #4338ca;
        --fc-today-bg-color: rgba(99, 102, 241, 0.05);
        font-family: inherit;
    }
    :global(.fc .fc-toolbar-title) {
        font-size: 1.25rem;
        font-weight: 600;
        color: #1e293b;
    }
    :global(.fc .fc-col-header-cell-cushion) {
        padding: 10px 0;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.025em;
    }
</style>
