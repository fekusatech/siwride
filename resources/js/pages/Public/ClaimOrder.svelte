<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { useForm } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';

    let { order, isAssigned, isClaimPending, claimedData, flash } = $props();

    let form = useForm({
        nid: '',
    });

    function submitClaim() {
        form.post(`/c/${order.booking_code}`, {
            preserveScroll: true,
        });
    }

    // Helper to format currency
    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    // Prepare WhatsApp Link if claimed
    let waLink = $derived.by(() => {
        if (claimedData?.customer_phone) {
            let cleanPhone = claimedData.customer_phone.replace(/\D/g, '');
            if (cleanPhone.startsWith('0')) {
                cleanPhone = '62' + cleanPhone.substring(1);
            }
            return `https://wa.me/${cleanPhone}`;
        }
        return '';
    });
</script>

<AppHead title={`Klaim Order - ${order.booking_code}`} />

<div class="min-vh-100 py-5" style="background-color: #f3f4f7;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <!-- Branding / Title -->
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary mb-1">SIWRIDE PORTAL</h3>
                    <p class="text-muted">Direct Driver Order Assignment</p>
                </div>

                {#if flash?.success}
                    <div
                        class="alert alert-success d-flex align-items-center mb-4 shadow-sm"
                        transition:fade
                    >
                        <i class="ti ti-circle-check fs-24 me-2"></i>
                        <div>{flash.success}</div>
                    </div>
                {/if}

                {#if flash?.error}
                    <div
                        class="alert alert-danger d-flex align-items-center mb-4 shadow-sm"
                        transition:fade
                    >
                        <i class="ti ti-alert-triangle fs-24 me-2"></i>
                        <div>{flash.error}</div>
                    </div>
                {/if}

                <div
                    class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4"
                >
                    <!-- Header -->
                    <div class="bg-primary text-white p-4">
                        <div
                            class="d-flex justify-content-between align-items-center mb-2"
                        >
                            <span class="badge bg-white text-primary"
                                >Booking Code</span
                            >
                            <span class="fw-bold">{order.booking_code}</span>
                        </div>
                        <h4 class="text-white mb-0">
                            {new Date(order.date).toLocaleDateString('id-ID', {
                                weekday: 'long',
                                year: 'numeric',
                                month: 'long',
                                day: 'numeric',
                            })}
                        </h4>
                        <div class="opacity-75">
                            <i class="ti ti-clock me-1 mt-1"></i>
                            {order.time} WITA
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <!-- Route Details -->
                        <div class="mb-4">
                            <div class="d-flex mb-3">
                                <div class="mt-1 me-3 text-success">
                                    <i class="ti ti-map-pin fs-22"></i>
                                </div>
                                <div>
                                    <small
                                        class="text-uppercase text-muted fw-bold d-block mb-1"
                                        >Pick Up From</small
                                    >
                                    <div class="fw-medium text-dark">
                                        {order.pickup_address}
                                    </div>
                                </div>
                            </div>
                            <!-- Connector Line -->
                            <div
                                class="border-start border-2 h-20px my-2 ms-3"
                                style="border-style: dashed !important; height: 20px; border-color: #cbd5e1 !important"
                            ></div>

                            <div class="d-flex mt-3">
                                <div class="mt-1 me-3 text-danger">
                                    <i class="ti ti-map-pin fs-22"></i>
                                </div>
                                <div>
                                    <small
                                        class="text-uppercase text-muted fw-bold d-block mb-1"
                                        >Drop Off</small
                                    >
                                    <div class="fw-medium text-dark">
                                        {order.dropoff_address}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Extra Details -->
                        <div class="row g-3 bg-light rounded-3 p-3 mb-4 mx-0">
                            <div class="col-6 px-2">
                                <small class="text-muted d-block"
                                    ><i class="ti ti-users me-1"></i> Passenger</small
                                >
                                <span class="fw-bold text-dark"
                                    >{order.passengers} Pax</span
                                >
                            </div>
                            <div class="col-6 px-2 border-start">
                                <small class="text-muted d-block"
                                    ><i class="ti ti-plane me-1"></i> Flight</small
                                >
                                <span class="fw-bold text-dark"
                                    >{order.flight_number || '-'}</span
                                >
                            </div>
                            <div class="col-6 px-2 mt-3 pt-3 border-top">
                                <small class="text-muted d-block"
                                    ><i class="ti ti-wallet me-1"></i> Price</small
                                >
                                <span class="fw-bold text-primary"
                                    >{formatCurrency(order.price)}</span
                                >
                            </div>
                            <div
                                class="col-6 px-2 mt-3 pt-3 border-top border-start"
                            >
                                <small class="text-muted d-block"
                                    ><i class="ti ti-info-circle me-1"></i> Parkir
                                    & Toll</small
                                >
                                <span class="fw-bold text-dark"
                                    >{formatCurrency(
                                        order.parking_gas_fee,
                                    )}</span
                                >
                            </div>
                        </div>

                        <!-- State Management -->
                        {#if claimedData}
                            <!-- SUCCESS: Data Revealed -->
                            <div
                                class="alert alert-success border-0 rounded-4 p-4 mb-0"
                                style="background-color: #ecfdf5;"
                            >
                                <h5
                                    class="text-success mb-3 fw-bold d-flex align-items-center"
                                >
                                    <i class="ti ti-lock-open me-2 fs-22"></i> Assignment
                                    Private Details
                                </h5>

                                <div class="mb-3">
                                    <small class="text-muted d-block mb-1"
                                        >Diambil Oleh Driver:</small
                                    >
                                    <div class="fw-bold fs-16 text-dark">
                                        {claimedData.driver_name}
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <small class="text-muted d-block mb-2"
                                        >Informasi Penumpang:</small
                                    >
                                    <div
                                        class="fw-bold fs-16 text-dark mb-2 d-flex align-items-center gap-2"
                                    >
                                        <i class="ti ti-user text-muted fs-18"
                                        ></i>
                                        {claimedData.customer_name}
                                    </div>
                                    <div
                                        class="fw-medium text-dark d-flex align-items-center gap-2"
                                    >
                                        <i class="ti ti-phone text-muted fs-18"
                                        ></i>
                                        {claimedData.customer_phone}
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    {#if waLink}
                                        <a
                                            href={waLink}
                                            target="_blank"
                                            class="btn btn-success flex-grow-1 fw-bold d-flex justify-content-center align-items-center gap-2 py-2 shadow-sm"
                                        >
                                            <i
                                                class="ti ti-brand-whatsapp fs-20"
                                            ></i> Kirim Pesan WA
                                        </a>
                                    {/if}
                                </div>
                            </div>
                        {:else if isClaimPending && !claimedData}
                            <!-- WAITING ADMIN CONFIRMATION -->
                            <div
                                class="text-center py-4 text-muted bg-warning bg-opacity-10 rounded-4 border border-warning"
                            >
                                <i
                                    class="ti ti-clock-hour-9 mb-2 d-block text-warning"
                                    style="font-size: 40px;"
                                ></i>
                                <h5 class="mb-1 text-dark fw-bold">
                                    Menunggu Konfirmasi Admin
                                </h5>
                                <p class="mb-0">
                                    Claim Anda sedang diproses. Admin akan segera mengkonfirmasi.
                                </p>
                            </div>
                        {:else if isAssigned && !claimedData}
                            <!-- ALREADY TAKEN -->
                            <div
                                class="text-center py-4 text-muted bg-light rounded-4"
                            >
                                <i
                                    class="ti ti-lock mb-2 d-block text-danger opacity-75"
                                    style="font-size: 40px;"
                                ></i>
                                <h5 class="mb-1 text-dark fw-bold">
                                    Order Ditutup
                                </h5>
                                <p class="mb-0">
                                    Maaf, jadwal ini sudah diambil oleh Supir
                                    Lain.
                                </p>
                            </div>
                        {:else}
                            <!-- CLAIM FORM -->
                            <form
                                onsubmit={(event) => {
                                    event.preventDefault();
                                    submitClaim();
                                }}
                            >
                                <hr class="border-light my-4" />
                                <div class="mb-4">
                                    <label
                                        for="nid"
                                        class="form-label text-dark fw-bold"
                                        >Masukkan Kode Driver (NID)</label
                                    >
                                    <input
                                        type="text"
                                        class="form-control form-control-lg shadow-none border-2 {form
                                            .errors.nid
                                            ? 'is-invalid'
                                            : ''}"
                                        id="nid"
                                        bind:value={form.nid}
                                        placeholder="Contoh: DRV-1234"
                                        style="border-radius: 12px;"
                                        required
                                        autocomplete="off"
                                    />
                                    {#if form.errors.nid}
                                        <div class="invalid-feedback">
                                            {form.errors.nid}
                                        </div>
                                    {/if}
                                </div>
                                <button
                                    type="submit"
                                    class="btn btn-primary btn-lg w-100 fw-bold py-2 shadow-sm"
                                    style="border-radius: 12px;"
                                    disabled={form.processing}
                                >
                                    {#if form.processing}
                                        <span
                                            class="spinner-border spinner-border-sm me-2"
                                            role="status"
                                            aria-hidden="true"
                                        ></span>
                                        Memproses...
                                    {:else}
                                        <i class="ti ti-check me-2"></i> Klaim Jadwal
                                        Ini
                                    {/if}
                                </button>
                                <div class="text-center mt-3">
                                    <small
                                        class="text-muted d-inline-block px-4"
                                        >Hanya klaim jika Anda bersedia
                                        melakukan penjemputan sesuai rute di
                                        atas.</small
                                    >
                                </div>
                            </form>
                        {/if}
                    </div>
                </div>

                <div class="text-center">
                    <small class="text-muted text-uppercase fw-medium"
                        >&copy; SiWRIDE Management System</small
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    :global(body) {
        background-color: #f3f4f7 !important;
    }
</style>
