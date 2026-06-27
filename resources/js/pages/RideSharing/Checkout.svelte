<script lang="ts">
    import { page, useForm } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { formatRupiah } from '@/lib/utils';

    let { checkoutData, customer } = $props<{
        checkoutData: any;
        customer: any | null;
    }>();

    let currentStep = $state(1);
    let stepError = $state('');
    let agreedToTerms = $state(false);

    const form = useForm({
        rs_route_id: checkoutData.route.id,
        rs_schedule_id: checkoutData.schedule.id,
        pickup_location_id: checkoutData.pickup.id,
        dropoff_location_id: checkoutData.dropoff.id,
        date: checkoutData.date,
        passengers: checkoutData.passengers,
        
        customer_name: customer?.name || '',
        email: customer?.email || '',
        customer_phone: customer?.phone || '',
        
        pickup_detail: '',
        dropoff_detail: '',
        notes: '',
        
        create_account: false,
        password: '',
        password_confirmation: '',
    });

    const stepLabels = ['Transfer Details', 'Passenger Info', 'Confirmation'];

    function nextStep() {
        stepError = '';
        if (currentStep === 1) {
            currentStep = 2;
        } else if (currentStep === 2) {
            if (!form.customer_name || form.customer_name.length < 3) {
                stepError = 'Please enter a valid full name.';
                return;
            }
            if (!form.email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
                stepError = 'Please enter a valid email address.';
                return;
            }
            if (form.create_account && (!form.password || form.password.length < 8)) {
                stepError = 'Password must be at least 8 characters.';
                return;
            }
            if (form.create_account && form.password !== form.password_confirmation) {
                stepError = 'Passwords do not match.';
                return;
            }
            currentStep = 3;
        }
    }

    function prevStep() {
        stepError = '';
        if (currentStep > 1) currentStep--;
    }

    function submitForm() {
        if (!agreedToTerms) {
            stepError = 'You must agree to the Terms & Conditions to proceed.';
            return;
        }
        form.post('/booking/sharing-ride/orders');
    }

    function formatDisplayDate(isoDate: string): string {
        if (!isoDate) return '';
        const [y, m, d] = isoDate.split('-').map(Number);
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return `${d} ${months[m - 1]} ${y}`;
    }
</script>

<AppHead title="Checkout Ride Sharing - Siwride" />
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
            <h2 class="page-header__title bw-split-in-right">Complete Your Booking</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/ride-sharing">Ride Sharing</a></li>
                <li><span>Checkout</span></li>
            </ul>
        </div>
    </section>

    <section style="padding: 60px 0 100px; background: #f4f7f9;">
        <div class="container">
            <div class="checkout-layout">
                <!-- Left: Multi-step form -->
                <div class="checkout-main">
                    <!-- Stepper -->
                    <div class="stepper">
                        {#each stepLabels as label, i}
                            <div class="stepper-item {currentStep > i + 1 ? 'completed' : ''} {currentStep === i + 1 ? 'active' : ''}">
                                <div class="stepper-circle">
                                    {#if currentStep > i + 1}
                                        <i class="fas fa-check"></i>
                                    {:else}
                                        {i + 1}
                                    {/if}
                                </div>
                                <span class="stepper-label">{label}</span>
                            </div>
                            {#if i < stepLabels.length - 1}
                                <div class="stepper-line {currentStep > i + 1 ? 'active' : ''}"></div>
                            {/if}
                        {/each}
                    </div>

                    {#if stepError}
                        <div class="alert-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {stepError}
                        </div>
                    {/if}

                    <form onsubmit={(e) => { e.preventDefault(); if (currentStep === 3) submitForm(); else nextStep(); }}>
                        <!-- STEP 1: Transfer Details -->
                        {#if currentStep === 1}
                            <div class="step-card">
                                <div class="step-card__header">
                                    <h4>Step 1. Transfer Details</h4>
                                    <p>Verify your shared ride information</p>
                                </div>
                                <div class="step-card__body">
                                    <div class="helper-notice">
                                        <i class="fas fa-info-circle"></i>
                                        Please review the basic ride details. This is a shared ride. The vehicle may pick up other passengers along the route.
                                    </div>
                                    
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label mb-0">Departure City (Pickup) *</label>
                                            <input type="text" value={checkoutData.pickup.name} class="premium-input" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;" />
                                            <small class="text-muted mt-1"><i class="fas fa-map-marker-alt"></i> {checkoutData.pickup.address}</small>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label mb-0">Destination City (Drop-off) *</label>
                                            <input type="text" value={checkoutData.dropoff.name} class="premium-input" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;" />
                                            <small class="text-muted mt-1"><i class="fas fa-flag-checkered"></i> {checkoutData.dropoff.address}</small>
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Departure Date *</label>
                                            <input type="text" value={formatDisplayDate(checkoutData.date)} class="premium-input" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;" />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Departure Time *</label>
                                            <input type="text" value={checkoutData.departure_time} class="premium-input" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #475569;" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <!-- STEP 2: Passenger Information -->
                        {#if currentStep === 2}
                            <div class="step-card">
                                <div class="step-card__header">
                                    <h4>Step 2. Passenger Information</h4>
                                    <p>Tell us who is travelling</p>
                                </div>
                                <div class="step-card__body">
                                    {#if customer}
                                        <div class="use-profile-box">
                                            <label class="checkbox-label" style="cursor: default;">
                                                <i class="fas fa-user-circle" style="color: #10b981; font-size: 18px;"></i>
                                                Using saved profile information
                                            </label>
                                        </div>
                                    {/if}

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Full Name *</label>
                                            <input type="text" class="premium-input" bind:value={form.customer_name} required disabled={!!customer} />
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Email Address *</label>
                                            <input type="email" class="premium-input" bind:value={form.email} required disabled={!!customer} />
                                        </div>
                                    </div>
                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Phone / WhatsApp *</label>
                                            <input type="tel" class="premium-input" bind:value={form.customer_phone} required disabled={!!customer} />
                                        </div>
                                    </div>

                                    <div class="helper-notice mt-4">
                                        <i class="fas fa-map-pin"></i>
                                        If you require door-to-door service within the city, please provide the exact address below. Otherwise, the driver will wait at the pool.
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label class="form-label">Exact Pickup Address in {checkoutData.pickup.name} (Optional)</label>
                                            <textarea class="premium-input" rows="2" bind:value={form.pickup_detail} placeholder="E.g., Hotel Name, Street..."></textarea>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Exact Drop-off Address in {checkoutData.dropoff.name} (Optional)</label>
                                            <textarea class="premium-input" rows="2" bind:value={form.dropoff_detail} placeholder="E.g., Home address..."></textarea>
                                        </div>
                                    </div>

                                    <div class="form-group mt-3">
                                        <label class="form-label">Additional Notes</label>
                                        <textarea class="premium-input" rows="2" bind:value={form.notes} placeholder="Any special requests or luggage details?"></textarea>
                                    </div>

                                    {#if !customer}
                                        <div class="create-account-box mt-4">
                                            <div style="width: 100%;">
                                                <label class="checkbox-label" for="create_account_check">
                                                    <input type="checkbox" id="create_account_check" bind:checked={form.create_account} />
                                                    Create an account for faster booking next time
                                                </label>
                                                {#if form.create_account}
                                                    <div class="form-row mt-3">
                                                        <div class="form-group">
                                                            <label class="form-label">Password *</label>
                                                            <input type="password" class="premium-input" bind:value={form.password} />
                                                        </div>
                                                        <div class="form-group">
                                                            <label class="form-label">Confirm Password *</label>
                                                            <input type="password" class="premium-input" bind:value={form.password_confirmation} />
                                                        </div>
                                                    </div>
                                                {/if}
                                            </div>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        {/if}

                        <!-- STEP 3: Confirmation -->
                        {#if currentStep === 3}
                            <div class="step-card">
                                <div class="step-card__header">
                                    <h4>Step 3. Confirmation</h4>
                                    <p>Review and proceed to payment</p>
                                </div>
                                <div class="step-card__body">
                                    <div class="summary-box mb-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5 class="mb-0" style="font-weight: 700; color: #1e293b;">Booking Summary</h5>
                                        </div>
                                        <div class="summary-item">
                                            <span class="text-muted">Departure City</span>
                                            <span class="font-weight-bold">{checkoutData.pickup.name}</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="text-muted">Destination City</span>
                                            <span class="font-weight-bold">{checkoutData.dropoff.name}</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="text-muted">Passengers</span>
                                            <span class="font-weight-bold">{checkoutData.passengers} Pax</span>
                                        </div>
                                        <div class="summary-item">
                                            <span class="text-muted">Departure Time</span>
                                            <span class="font-weight-bold">{checkoutData.departure_time}</span>
                                        </div>
                                    </div>

                                    <div class="terms-box">
                                        <label class="terms-label" for="agree_terms">
                                            <input type="checkbox" id="agree_terms" class="terms-checkbox" bind:checked={agreedToTerms} />
                                            <span>
                                                I agree to the <a href="/terms" target="_blank">Terms & Conditions</a> and <a href="/privacy" target="_blank">Privacy Policy</a>.
                                            </span>
                                        </label>
                                        <p class="terms-provider">By clicking "Proceed to Payment", you agree to the conditions of the shared ride service.</p>
                                    </div>
                                </div>
                            </div>
                        {/if}

                        <div class="step-nav">
                            {#if currentStep > 1}
                                <button type="button" class="btn-back" onclick={prevStep} disabled={form.processing}>
                                    <i class="fas fa-arrow-left"></i> Back
                                </button>
                            {:else}
                                <div></div>
                            {/if}

                            {#if currentStep < 3}
                                <button type="button" class="btn-next" onclick={nextStep}>
                                    Next Step <i class="fas fa-arrow-right"></i>
                                </button>
                            {:else}
                                <button type="submit" class="btn-submit" disabled={form.processing}>
                                    {form.processing ? 'Processing...' : 'Proceed to Payment'} <i class="fas fa-check"></i>
                                </button>
                            {/if}
                        </div>
                    </form>
                </div>

                <!-- Right: Sidebar -->
                <div class="checkout-sidebar">
                    <div class="sidebar-card">
                        <!-- Sidebar Content -->
                        <div class="sidebar-vehicle">
                            {#if checkoutData.schedule.vehicle_category?.image_url}
                                <img src={checkoutData.schedule.vehicle_category.image_url} alt="Vehicle" />
                            {:else}
                                <img src="/assets/images/resources/vehicle-default.png" alt="Vehicle" />
                            {/if}
                            <div class="sidebar-vehicle-info">
                                <h5>{checkoutData.schedule.vehicle_category?.title || 'Sharing Ride Vehicle'}</h5>
                                <span>Max {checkoutData.schedule.vehicle_category?.passenger_capacity || 4} Passengers</span>
                            </div>
                        </div>
                        
                        <div class="sidebar-divider"></div>

                        <div class="sidebar-section-title">Transfer Details</div>
                        <div class="sidebar-route">
                            <div class="sidebar-route-point">
                                <div class="sidebar-route-dot sidebar-route-dot--from"></div>
                                <div class="sidebar-route-text">
                                    {checkoutData.pickup.name} <br/>
                                    <span class="route-full-address text-muted">{checkoutData.pickup.address}</span>
                                </div>
                            </div>
                            <div class="sidebar-route-line"></div>
                            <div class="sidebar-route-point">
                                <div class="sidebar-route-dot sidebar-route-dot--to"></div>
                                <div class="sidebar-route-text">
                                    {checkoutData.dropoff.name} <br/>
                                    <span class="route-full-address text-muted">{checkoutData.dropoff.address}</span>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-info-grid">
                            <div class="sidebar-info-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <span class="sidebar-info-label">Date</span>
                                    <span class="sidebar-info-value">{formatDisplayDate(checkoutData.date)}</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <span class="sidebar-info-label">Time</span>
                                    <span class="sidebar-info-value">{checkoutData.departure_time}</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <span class="sidebar-info-label">Passengers</span>
                                    <span class="sidebar-info-value">{checkoutData.passengers} Pax</span>
                                </div>
                            </div>
                            <div class="sidebar-info-item">
                                <i class="fas fa-car"></i>
                                <div>
                                    <span class="sidebar-info-label">Transfer Type</span>
                                    <span class="sidebar-info-value">Shared Ride</span>
                                </div>
                            </div>
                        </div>

                        <div class="sidebar-divider"></div>

                        <div class="sidebar-section-title">Price Breakdown</div>
                        <div class="sidebar-row">
                            <span>Price per seat</span>
                            <span>{formatRupiah(checkoutData.basePrice)}</span>
                        </div>
                        <div class="sidebar-row">
                            <span>Passengers</span>
                            <span>x {checkoutData.passengers}</span>
                        </div>

                        <div class="sidebar-divider"></div>
                        <div class="sidebar-total">
                            <span>Total</span>
                            <span class="total-amount">{formatRupiah(checkoutData.totalPrice)}</span>
                        </div>

                        <div class="sidebar-note">
                            <i class="fas fa-shield-alt"></i>
                            Secure booking. No hidden fees.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .checkout-layout {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 28px;
        align-items: start;
    }
    @media (max-width: 1024px) {
        .checkout-layout {
            grid-template-columns: 1fr;
        }
    }

    /* Stepper */
    .stepper {
        display: flex;
        align-items: center;
        margin-bottom: 28px;
        background: #fff;
        border-radius: 14px;
        padding: 20px 24px;
        border: 1px solid #eaeef2;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    }
    .stepper-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .stepper-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    .stepper-item.active .stepper-circle {
        background: var(--travhub-base, #e52029);
        color: #fff;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.15);
    }
    .stepper-item.completed .stepper-circle {
        background: #10b981;
        color: #fff;
    }
    .stepper-label {
        font-size: 12px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
    }
    .stepper-item.active .stepper-label {
        color: var(--travhub-base, #e52029);
    }
    .stepper-item.completed .stepper-label {
        color: #10b981;
    }
    .stepper-line {
        flex: 1;
        height: 3px;
        background: #f1f5f9;
        margin: 0 6px;
        margin-bottom: 22px;
        border-radius: 2px;
        transition: all 0.3s;
    }
    .stepper-line.active {
        background: #10b981;
    }

    /* Step Card */
    .step-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
        margin-bottom: 20px;
    }
    .step-card__header {
        padding: 24px 28px 10px;
        border-bottom: 1px solid #f0f4f8;
    }
    .step-card__header h4 {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0;
    }
    .step-card__header p {
        font-size: 14px;
        color: #64748b;
        margin: 4px 0 0;
    }
    .step-card__body {
        padding: 28px;
    }

    /* Form */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }
    @media (max-width: 640px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .form-label {
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .premium-input {
        width: 100%;
        padding: 13px 18px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s;
        outline: none;
    }
    .premium-input:focus {
        border-color: var(--travhub-base, #e52029);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.08);
    }
    .helper-notice {
        padding: 12px 16px;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        font-size: 14px;
        color: #1d4ed8;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 20px;
    }
    .helper-notice i {
        margin-top: 2px;
        flex-shrink: 0;
    }
    .alert-error {
        padding: 14px 18px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 10px;
        font-size: 14px;
        color: #dc2626;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    /* Account boxes */
    .use-profile-box,
    .create-account-box {
        padding: 14px 18px;
        background: #f0fdf4;
        border: 1.5px dashed #86efac;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .create-account-box {
        background: #f8fafc;
        border-color: #cbd5e1;
    }
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #334155;
        margin: 0;
    }
    .checkbox-label input {
        width: 18px;
        height: 18px;
        accent-color: var(--travhub-base, #e52029);
        cursor: pointer;
    }

    /* Navigation */
    .step-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 24px;
    }
    .btn-next,
    .btn-submit {
        background: var(--travhub-base, #e52029);
        color: #fff;
        padding: 14px 32px;
        border: none;
        border-radius: 50px;
        font-size: 16px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s;
        box-shadow: 0 6px 20px rgba(229, 32, 41, 0.25);
    }
    .btn-next:hover:not(:disabled),
    .btn-submit:hover:not(:disabled) {
        background: #111;
        transform: translateY(-2px);
    }
    .btn-next:disabled,
    .btn-submit:disabled {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
    }
    .btn-back {
        background: #f1f5f9;
        color: #475569;
        padding: 14px 24px;
        border: none;
        border-radius: 50px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }
    .btn-back:hover:not(:disabled) {
        background: #e2e8f0;
        color: #0f172a;
    }

    /* Summary Box in Step 3 */
    .summary-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 20px;
    }
    .summary-item {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    .summary-item:last-child {
        border-bottom: none;
    }
    .terms-box {
        padding: 16px 18px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }
    .terms-label {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
    }
    .terms-checkbox {
        width: 17px;
        height: 17px;
        accent-color: var(--travhub-base, #e52029);
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .terms-label a {
        color: var(--travhub-base, #e52029);
        text-decoration: underline;
    }
    .terms-provider {
        font-size: 12px;
        color: #94a3b8;
        margin: 0;
        padding-left: 27px;
        line-height: 1.6;
    }

    /* Sidebar */
    .checkout-sidebar {
        position: sticky;
        top: 100px;
    }
    .sidebar-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #eaeef2;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
        padding: 24px;
    }
    .sidebar-vehicle {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .sidebar-vehicle img {
        width: 72px;
        height: 54px;
        object-fit: contain;
        background: #f8fafc;
        border-radius: 8px;
        padding: 4px;
        flex-shrink: 0;
    }
    .sidebar-vehicle-info h5 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 3px;
    }
    .sidebar-vehicle-info span {
        font-size: 12px;
        color: #64748b;
    }
    .sidebar-divider {
        height: 1px;
        background: #f0f4f8;
        margin: 14px 0;
    }
    .sidebar-section-title {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #94a3b8;
        margin-bottom: 10px;
    }
    .sidebar-route {
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .sidebar-route-point {
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }
    .sidebar-route-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .sidebar-route-dot--from {
        background: var(--travhub-base, #e52029);
    }
    .sidebar-route-dot--to {
        background: #10b981;
    }
    .sidebar-route-text {
        font-size: 13px;
        font-weight: 500;
        color: #1e293b;
        line-height: 1.4;
    }
    .route-full-address {
        font-size: 11px;
        letter-spacing: 0.2px;
        line-height: 1.4;
    }
    .sidebar-route-line {
        width: 2px;
        height: 14px;
        margin-left: 4px;
        background: repeating-linear-gradient(
            to bottom,
            #cbd5e1 0,
            #cbd5e1 3px,
            transparent 3px,
            transparent 6px
        );
    }
    .sidebar-info-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .sidebar-info-item {
        display: flex;
        align-items: flex-start;
        gap: 7px;
        background: #f8fafc;
        border-radius: 8px;
        padding: 8px 10px;
    }
    .sidebar-info-item i {
        font-size: 12px;
        color: var(--travhub-base, #e52029);
        margin-top: 2px;
        flex-shrink: 0;
    }
    .sidebar-info-item div {
        display: flex;
        flex-direction: column;
        gap: 1px;
        min-width: 0;
    }
    .sidebar-info-label {
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #94a3b8;
    }
    .sidebar-info-value {
        font-size: 12px;
        font-weight: 700;
        color: #1e293b;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .sidebar-row {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: #475569;
        padding: 4px 0;
    }
    .sidebar-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
    }
    .total-amount {
        font-size: 24px;
        font-weight: 800;
        color: var(--travhub-base, #e52029);
    }
    .sidebar-note {
        margin-top: 14px;
        font-size: 12px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .sidebar-note i {
        color: #10b981;
    }
</style>
