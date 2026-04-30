<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { useForm, Link, router } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const { orders } = $props();
    const customer = page.props.auth.customer;

    let form = useForm({
        name: customer.name || '',
        phone: customer.phone || '',
        password: '',
        password_confirmation: '',
    });

    let isEditModalOpen = $state(false);
    let showPassword = $state(false);
    let showConfirmPassword = $state(false);

    const submit = () => {
        form.put('/customer/profile', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('password', 'password_confirmation');
                isEditModalOpen = false;
            },
        });
    };

    const logout = () => {
        router.post('/customer/logout');
    };

    const formatStatus = (status: string) => {
        switch(status) {
            case 'pending': return { text: 'Pending', bg: '#fff3cd', color: '#856404' };
            case 'confirmed': return { text: 'Confirmed', bg: '#d1ecf1', color: '#0c5460' };
            case 'completed': return { text: 'Completed', bg: '#d4edda', color: '#155724' };
            case 'cancelled': return { text: 'Cancelled', bg: '#f8d7da', color: '#721c24' };
            default: return { text: status, bg: '#e2e3e5', color: '#383d41' };
        }
    };
</script>

<AppHead title="My Dashboard - Siwride" />
<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">My Dashboard</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><Link href="/">Home</Link></li>
                <li><span>Dashboard</span></li>
            </ul>
        </div>
    </section>

    <!-- Dashboard Content -->
    <section class="dashboard-section">
        <div class="container">
            {#if page.props.flash?.success}
                <div class="alert alert-success alert-dismissible fade show premium-alert" role="alert">
                    <i class="fas fa-check-circle mr-2"></i> {page.props.flash.success}
                </div>
            {/if}

            <div class="row">
                <!-- User Summary Card -->
                <div class="col-xl-4 col-lg-5 mb-5 mb-lg-0">
                    <div class="profile-card premium-shadow">
                        <div class="profile-card__header text-center">
                            <div class="avatar-circle">
                                {customer.name.charAt(0).toUpperCase()}
                            </div>
                            <h3 class="profile-name">{customer.name}</h3>
                            <p class="profile-email"><i class="fas fa-envelope text-muted"></i> {customer.email}</p>
                            {#if customer.phone}
                                <p class="profile-phone"><i class="fas fa-phone-alt text-muted"></i> {customer.phone}</p>
                            {:else}
                                <p class="profile-phone text-warning"><i class="fas fa-exclamation-triangle"></i> Add your phone number</p>
                            {/if}
                        </div>
                        
                        <div class="profile-card__actions">
                            <button onclick={() => isEditModalOpen = true} class="btn-edit-profile">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </button>
                            <button onclick={logout} class="btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Log Out
                            </button>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <h4>{orders.length}</h4>
                                <span>Total Rides</span>
                            </div>
                            <div class="stat-item">
                                <h4>{orders.filter((o: any) => o.status === 'completed').length}</h4>
                                <span>Completed</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order History -->
                <div class="col-xl-8 col-lg-7">
                    <div class="dashboard-card premium-shadow">
                        <div class="dashboard-card__header d-flex justify-content-between align-items-center">
                            <h3 class="dashboard-title"><i class="fas fa-history" style="color: var(--travhub-base);"></i> Order History</h3>
                            <Link href="/booking" class="btn-new-booking"><i class="fas fa-plus"></i> New Booking</Link>
                        </div>
                        
                        <div class="dashboard-card__body">
                            {#if orders.length === 0}
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="fas fa-car-side"></i></div>
                                    <h4>No bookings yet</h4>
                                    <p>Looks like you haven't made any bookings. Ready for a ride?</p>
                                    <Link href="/booking" class="travhub-btn mt-3"><span>Book Now</span></Link>
                                </div>
                            {:else}
                                <div class="order-list">
                                    {#each orders as order}
                                        {@const statusInfo = formatStatus(order.status)}
                                        <div class="order-item">
                                            <div class="order-item__left">
                                                <div class="order-date">
                                                    <span class="day">{new Date(order.date).getDate()}</span>
                                                    <span class="month">{new Date(order.date).toLocaleString('default', { month: 'short' })}</span>
                                                </div>
                                                <div class="order-details">
                                                    <div class="d-flex align-items-center mb-1">
                                                        <h5 class="booking-code">{order.booking_code}</h5>
                                                        <span class="status-badge mobile-badge d-md-none ml-2" style="background-color: {statusInfo.bg}; color: {statusInfo.color};">
                                                            {statusInfo.text}
                                                        </span>
                                                    </div>
                                                    <div class="route">
                                                        <div class="route-point"><i class="fas fa-map-marker-alt text-primary"></i> <span class="text-truncate-custom">{order.pickup_address}</span></div>
                                                        <div class="route-arrow"><i class="fas fa-long-arrow-alt-down"></i></div>
                                                        <div class="route-point"><i class="fas fa-flag-checkered text-success"></i> <span class="text-truncate-custom">{order.dropoff_address}</span></div>
                                                    </div>
                                                    <div class="order-meta mt-2">
                                                        <span><i class="fas fa-clock text-muted"></i> {order.time.substring(0,5)}</span>
                                                        <span class="ml-3"><i class="fas fa-users text-muted"></i> {order.passengers} Pax</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="order-item__right text-right d-none d-md-flex flex-column justify-content-center align-items-end">
                                                <span class="status-badge" style="background-color: {statusInfo.bg}; color: {statusInfo.color};">
                                                    {statusInfo.text}
                                                </span>
                                                <Link href="/booking/{order.booking_code}" class="btn-details mt-3">View Details <i class="fas fa-chevron-right"></i></Link>
                                            </div>
                                            <!-- Mobile View Details -->
                                            <div class="d-block d-md-none mt-3 w-100 border-top pt-2 text-center">
                                                <Link href="/booking/{order.booking_code}" class="btn-details-mobile">View Details <i class="fas fa-chevron-right ml-1"></i></Link>
                                            </div>
                                        </div>
                                    {/each}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<!-- Edit Profile Modal -->
{#if isEditModalOpen}
<!-- svelte-ignore a11y_click_events_have_key_events -->
<!-- svelte-ignore a11y_no_static_element_interactions -->
<div class="premium-modal-backdrop" onclick={(e) => { if (e.target === e.currentTarget) isEditModalOpen = false; }}>
    <div class="premium-modal">
        <div class="premium-modal-header">
            <h4>Update Profile</h4>
            <button class="close-btn" onclick={() => isEditModalOpen = false}><i class="fas fa-times"></i></button>
        </div>
        <div class="premium-modal-body">
            <form onsubmit={(e) => { e.preventDefault(); submit(); }}>
                <div class="form-group mb-4">
                    <label class="form-label">Full Name *</label>
                    <input type="text" bind:value={form.name} required maxlength="50" class="premium-input" class:is-invalid={form.errors.name} oninput={(e) => { const val = e.currentTarget.value.replace(/[0-9]/g, ''); e.currentTarget.value = val; form.name = val; }} />
                    {#if form.errors.name}<div class="text-danger small mt-1">{form.errors.name}</div>{/if}
                </div>
                
                <div class="form-group mb-4">
                    <label class="form-label">Email Address</label>
                    <input type="email" value={customer.email} disabled class="premium-input disabled-input" />
                    <small class="text-muted mt-1 d-block"><i class="fas fa-info-circle mr-1"></i> Email cannot be changed</small>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Phone / WhatsApp</label>
                    <input type="tel" bind:value={form.phone} class="premium-input" maxlength="15" class:is-invalid={form.errors.phone} placeholder="e.g. +62 812 3456 7890" oninput={(e) => { const val = e.currentTarget.value.replace(/[^0-9+\-\s()]/g, ''); e.currentTarget.value = val; form.phone = val; }} />
                    {#if form.errors.phone}<div class="text-danger small mt-1">{form.errors.phone}</div>{/if}
                </div>

                <div class="password-section mt-4 pt-3" style="border-top: 1px dashed #e2e8f0;">
                    <h5 class="mb-2" style="font-size: 16px; font-weight: 700; color: #1e293b;">Change Password</h5>
                    <p class="text-muted small mb-3">Leave blank if you don't want to change your current password.</p>
                    
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">New Password</label>
                            <div class="position-relative">
                                <input type={showPassword ? 'text' : 'password'} bind:value={form.password} class="premium-input" class:is-invalid={form.errors.password} style="padding-right: 45px;" />
                                <button type="button" class="password-toggle-btn" onclick={() => showPassword = !showPassword}>
                                    <i class="fas {showPassword ? 'fa-eye-slash' : 'fa-eye'}"></i>
                                </button>
                            </div>
                            {#if form.errors.password}<div class="text-danger small mt-1">{form.errors.password}</div>{/if}
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="form-label">Confirm Password</label>
                            <div class="position-relative">
                                <input type={showConfirmPassword ? 'text' : 'password'} bind:value={form.password_confirmation} class="premium-input" class:is-invalid={form.password_confirmation.length > 0 && form.password !== form.password_confirmation} class:is-valid={form.password_confirmation.length > 0 && form.password === form.password_confirmation} style="padding-right: 45px;" />
                                <button type="button" class="password-toggle-btn" onclick={() => showConfirmPassword = !showConfirmPassword}>
                                    <i class="fas {showConfirmPassword ? 'fa-eye-slash' : 'fa-eye'}"></i>
                                </button>
                            </div>
                            {#if form.password_confirmation.length > 0 && form.password !== form.password_confirmation}
                                <div class="text-danger small mt-1"><i class="fas fa-exclamation-circle"></i> Passwords do not match</div>
                            {:else if form.password_confirmation.length > 0 && form.password === form.password_confirmation}
                                <div class="text-success small mt-1"><i class="fas fa-check-circle"></i> Passwords match</div>
                            {/if}
                        </div>
                    </div>
                </div>

                <div class="premium-modal-footer mt-4">
                    <button type="button" class="btn-cancel" onclick={() => isEditModalOpen = false}>Cancel</button>
                    <button type="submit" class="travhub-btn" disabled={form.processing}><span>{form.processing ? 'Saving...' : 'Save Changes'}</span></button>
                </div>
            </form>
        </div>
    </div>
</div>
{/if}

<style>
    .dashboard-section {
        padding: 60px 0 100px;
        background-color: #f8fafc;
        min-height: calc(100vh - 300px);
    }

    .premium-alert {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
        border-radius: 12px;
        font-weight: 600;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .premium-shadow {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.02);
        overflow: hidden;
    }

    /* Profile Card */
    .profile-card {
        padding: 40px 30px;
    }

    .avatar-circle {
        width: 90px;
        height: 90px;
        background: linear-gradient(135deg, var(--travhub-base) 0%, #ff4b4b 100%);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 800;
        margin: 0 auto 20px;
        box-shadow: 0 8px 20px rgba(229, 32, 41, 0.25);
    }

    .profile-name {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .profile-email, .profile-phone {
        color: #64748b;
        font-size: 15px;
        margin-bottom: 5px;
    }
    
    .profile-email i, .profile-phone i {
        width: 20px;
        text-align: center;
    }

    .profile-card__actions {
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-edit-profile {
        width: 100%;
        background: #f1f5f9;
        color: #334155;
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.3s ease;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-edit-profile:hover {
        background: var(--travhub-base);
        color: white;
        box-shadow: 0 4px 12px rgba(229, 32, 41, 0.2);
    }

    .btn-logout {
        width: 100%;
        background: transparent;
        color: #ef4444;
        border: 1px solid #fecaca;
        padding: 12px 20px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-logout:hover {
        background: #fef2f2;
        border-color: #ef4444;
    }

    .profile-stats {
        display: flex;
        justify-content: space-around;
        margin-top: 30px;
        padding-top: 25px;
        border-top: 1px solid #f1f5f9;
    }

    .stat-item {
        text-align: center;
    }

    .stat-item h4 {
        font-size: 24px;
        font-weight: 800;
        color: var(--travhub-base);
        margin-bottom: 2px;
    }

    .stat-item span {
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Dashboard Card (Orders) */
    .dashboard-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .dashboard-card__header {
        padding: 25px 30px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
    }

    .dashboard-title {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }

    .btn-new-booking {
        background: #f1f5f9;
        color: #334155;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 14px;
        transition: all 0.2s ease;
    }

    .btn-new-booking:hover {
        background: var(--travhub-base);
        color: white;
    }

    .dashboard-card__body {
        padding: 0;
        flex: 1;
        background: #fafbfc;
    }

    .empty-state {
        text-align: center;
        padding: 60px 30px;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: #f1f5f9;
        color: #cbd5e1;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        margin: 0 auto 20px;
    }

    .empty-state h4 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: #64748b;
        margin-bottom: 20px;
    }

    .order-list {
        display: flex;
        flex-direction: column;
    }

    .order-item {
        display: flex;
        justify-content: space-between;
        padding: 25px 30px;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
        transition: all 0.2s ease;
        flex-wrap: wrap;
    }

    .order-item:hover {
        background: #fafbfc;
    }

    .order-item__left {
        display: flex;
        gap: 20px;
        flex: 1;
        min-width: 0; /* allows text truncation */
    }

    .order-date {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 65px;
        height: 65px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        flex-shrink: 0;
    }

    .order-date .day {
        font-size: 22px;
        font-weight: 800;
        color: var(--travhub-base);
        line-height: 1;
    }

    .order-date .month {
        font-size: 12px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        margin-top: 4px;
    }

    .order-details {
        flex: 1;
        min-width: 0;
    }

    .booking-code {
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .route {
        margin-bottom: 10px;
    }

    .route-point {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-size: 14px;
        font-weight: 500;
    }

    .text-truncate-custom {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        max-width: 250px;
    }

    .route-arrow {
        color: #cbd5e1;
        margin: 4px 0 4px 5px;
        font-size: 12px;
    }

    .order-meta {
        font-size: 13px;
        font-weight: 600;
    }

    .status-badge {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 700;
        display: inline-block;
    }

    .btn-details {
        color: var(--travhub-base);
        font-weight: 700;
        font-size: 14px;
        transition: color 0.2s;
    }
    
    .btn-details:hover {
        color: #b91c21;
    }

    .btn-details-mobile {
        color: var(--travhub-base);
        font-weight: 700;
        font-size: 14px;
        padding: 10px;
        display: inline-block;
    }

    /* Modal Styles */
    .premium-modal-backdrop {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .premium-modal {
        background: white;
        width: 100%;
        max-width: 600px;
        border-radius: 24px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        max-height: 90vh;
    }

    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    .premium-modal-header {
        padding: 24px 30px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f8fafc;
    }

    .premium-modal-header h4 {
        margin: 0;
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
    }

    .close-btn {
        background: white;
        border: 1px solid #e2e8f0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .close-btn:hover {
        background: #f1f5f9;
        color: #ef4444;
        transform: rotate(90deg);
    }

    .premium-modal-body {
        padding: 30px;
        overflow-y: auto;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #334155;
        font-size: 14px;
    }

    .premium-input {
        width: 100%; 
        padding: 14px 18px; 
        border: 2px solid #e2e8f0; 
        background-color: #f8fafc;
        border-radius: 12px; 
        font-size: 15px; 
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s ease;
    }

    .premium-input:focus {
        outline: none;
        background-color: #fff;
        border-color: var(--travhub-base);
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }

    .password-toggle-btn {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .password-toggle-btn:hover {
        color: #1e293b;
    }

    .disabled-input {
        background-color: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .premium-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        padding-top: 25px;
        border-top: 1px solid #f1f5f9;
    }

    .btn-cancel {
        padding: 12px 24px;
        background: transparent;
        border: 1px solid #cbd5e1;
        color: #64748b;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15px;
        transition: all 0.2s;
    }

    .btn-cancel:hover {
        background: #f1f5f9;
        color: #334155;
    }

    .travhub-btn {
        border-radius: 12px;
        padding: 12px 30px;
    }

    @media (max-width: 768px) {
        .dashboard-section {
            padding: 40px 0 80px;
        }
        
        .profile-card {
            padding: 30px 20px;
        }
        
        .order-item {
            flex-direction: column;
            padding: 20px;
        }
        
        .text-truncate-custom {
            max-width: 200px;
        }
    }
</style>
