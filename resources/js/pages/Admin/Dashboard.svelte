<script lang="ts">
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { onMount } from 'svelte';
    import { Link } from '@inertiajs/svelte';

    let { stats, charts, recent_orders } = $props();

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(amount);
    }

    onMount(() => {
        // Revenue Trend Chart
        if (document.getElementById('revenue-chart') && typeof ApexCharts !== 'undefined') {
            const revenueOptions = {
                chart: {
                    height: 320,
                    type: 'area',
                    toolbar: { show: false }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                series: [{
                    name: 'Revenue',
                    data: charts.revenue_trend.map(d => d.total)
                }],
                xaxis: {
                    categories: charts.revenue_trend.map(d => d.date),
                },
                colors: ['#4f46e5'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.3,
                        opacityTo: 0.1,
                        stops: [0, 90, 100]
                    }
                },
                grid: { borderColor: '#f1f1f1' }
            };
            new ApexCharts(document.getElementById('revenue-chart'), revenueOptions).render();
        }

        // Status Distribution Chart
        if (document.getElementById('status-chart') && typeof ApexCharts !== 'undefined') {
            const statusOptions = {
                chart: {
                    height: 280,
                    type: 'donut',
                },
                series: charts.status_distribution.series,
                labels: charts.status_distribution.labels.map(l => l.toUpperCase()),
                colors: ['#4f46e5', '#10b981', '#ef4444', '#f59e0b'],
                legend: { position: 'bottom' },
                responsive: [{
                    breakpoint: 480,
                    options: { chart: { width: 200 } }
                }]
            };
            new ApexCharts(document.getElementById('status-chart'), statusOptions).render();
        }
    });

    function getStatusBadgeClass(status: string) {
        switch (status) {
            case 'completed': return 'bg-success-subtle text-success';
            case 'pending': return 'bg-warning-subtle text-warning';
            case 'cancelled': return 'bg-danger-subtle text-danger';
            default: return 'bg-primary-subtle text-primary';
        }
    }
</script>

<AppHead title="Admin Dashboard" />

<AdminLayout>
    <div class="row">
        <div class="col-12">
            <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 text-uppercase fw-bold m-0">Command Center</h4>
                    <p class="text-muted mb-0">Overview of your transportation network</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="row mt-3">
        <div class="col-md-6 col-xxl-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Total Bookings</span>
                            <h3 class="mb-0 mt-1 fw-bold">{stats.total_orders}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="avatar-sm bg-primary-subtle rounded-circle">
                                <span class="avatar-title text-primary fs-20">
                                    <i class="ti ti-calendar-event"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Today's Schedule</span>
                            <h3 class="mb-0 mt-1 fw-bold">{stats.today_orders}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="avatar-sm bg-info-subtle rounded-circle">
                                <span class="avatar-title text-info fs-20">
                                    <i class="ti ti-clock-play"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Unassigned Orders</span>
                            <h3 class="mb-0 mt-1 fw-bold text-danger">{stats.pending_claims}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="avatar-sm bg-danger-subtle rounded-circle">
                                <span class="avatar-title text-danger fs-20">
                                    <i class="ti ti-lock-open"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xxl-3">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <span class="text-muted text-uppercase fs-12 fw-bold">Estimated Revenue</span>
                            <h3 class="mb-0 mt-1 fw-bold text-success">{formatCurrency(stats.total_revenue)}</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <div class="avatar-sm bg-success-subtle rounded-circle">
                                <span class="avatar-title text-success fs-20">
                                    <i class="ti ti-wallet"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row">
        <div class="col-xxl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom border-dashed d-flex justify-content-between align-items-center">
                    <h4 class="header-title mb-0">Revenue Trend (Last 7 Days)</h4>
                    <Link href="/admin/orders" class="btn btn-sm btn-soft-primary">View All Orders</Link>
                </div>
                <div class="card-body">
                    <div dir="ltr">
                        <div id="revenue-chart" class="apex-charts"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xxl-4">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Order Status Distribution</h4>
                </div>
                <div class="card-body text-center">
                    <div id="status-chart" class="apex-charts d-flex justify-content-center"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders Table -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom border-dashed d-flex justify-content-between align-items-center">
                    <h4 class="header-title mb-0">Recent Activity</h4>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-4">Booking Code</th>
                                    <th>Schedule</th>
                                    <th>Customer</th>
                                    <th>Route</th>
                                    <th>Driver / Vehicle</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {#each recent_orders as or}
                                    <tr>
                                        <td class="ps-4 fw-medium text-primary">#{or.booking_code}</td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{new Date(or.date).toLocaleDateString('id-ID')}</span>
                                                <small class="text-muted">{or.time} WITA</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">{or.customer_name}</span>
                                                <small class="text-muted">{or.customer_phone}</small>
                                            </div>
                                        </td>
                                        <td style="max-width: 200px;">
                                            <div class="text-truncate" title={or.pickup_address}>
                                                <i class="ti ti-map-pin text-success me-1"></i> {or.pickup_address}
                                            </div>
                                            <div class="text-truncate mt-1" title={or.dropoff_address}>
                                                <i class="ti ti-map-pin text-danger me-1"></i> {or.dropoff_address}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                {#if or.driver}
                                                    <div class="avatar-xs me-2">
                                                        <span class="avatar-title bg-soft-primary text-primary rounded-circle fs-12">
                                                            {or.driver.name.charAt(0)}
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="d-block fw-medium">{or.driver.name}</span>
                                                        <small class="text-muted">{or.vehicle?.name || 'No Vehicle'}</small>
                                                    </div>
                                                {:else}
                                                    <span class="badge bg-danger-subtle text-danger p-2">
                                                        <i class="ti ti-alert-circle me-1"></i> UNASSIGNED
                                                    </span>
                                                {/if}
                                            </div>
                                        </td>
                                        <td>
                                            <span class={`badge px-2 py-1 ${getStatusBadgeClass(or.status)}`}>
                                                {or.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td class="text-end pe-4">
                                            <Link href={`/admin/orders?search=${or.booking_code}`} class="btn btn-sm btn-icon btn-soft-secondary">
                                                <i class="ti ti-eye"></i>
                                            </Link>
                                        </td>
                                    </tr>
                                {/each}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .avatar-sm {
        height: 48px;
        width: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .avatar-xs {
        height: 28px;
        width: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .bg-primary-subtle { background-color: rgba(79, 70, 229, 0.1); }
    .bg-info-subtle { background-color: rgba(59, 130, 246, 0.1); }
    .bg-danger-subtle { background-color: rgba(239, 68, 68, 0.1); }
    .bg-success-subtle { background-color: rgba(16, 185, 129, 0.1); }
    .bg-warning-subtle { background-color: rgba(245, 158, 11, 0.1); }
    
    .btn-soft-primary {
        background-color: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
        border: none;
    }
    .btn-soft-primary:hover {
        background-color: #4f46e5;
        color: white;
    }
</style>
