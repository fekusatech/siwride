<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import DatePicker from '@/components/DatePicker.svelte';
    import TimePicker from '@/components/TimePicker.svelte';
    import LocationSearchInput from '@/components/LocationSearchInput.svelte';
    import { getMinPickupTime } from '@/lib/pickupTime';

    let { vehicleCategories = [] } = $props<{
        vehicleCategories: {
            id: number;
            title: string;
            image_url: string | null;
            passenger_capacity: number | null;
            price_per_hour: number | null;
        }[];
    }>();

    let pickupLocation = $state('');
    let selectedDate = $state('');
    let selectedTime = $state('');
    let selectedHours = $state(4);
    let passengerCount = $state(1);

    const minTime = $derived(getMinPickupTime(selectedDate));

    function formatCurrency(amount: number): string {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    const hourOptions = [2, 3, 4, 5, 6, 8, 10, 12];
</script>

<AppHead title="Hourly Service | Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- ==================== Page Header ==================== -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Hourly Service</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/booking">Booking</a></li>
                <li>Hourly Service</li>
            </ul>
        </div>
    </section>

    <!-- ==================== Hourly Content ==================== -->
    <section style="padding: 80px 0 100px; background: #f7f9fa;">
        <div class="container">
            <!-- Info Banner -->
            <div
                class="wow fadeInUp"
                data-wow-duration="800ms"
                style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); border-radius: 20px; padding: 35px 40px; margin-bottom: 50px; display: flex; align-items: center; gap: 25px; flex-wrap: wrap;"
            >
                <div
                    style="width: 70px; height: 70px; background: rgba(255,255,255,0.2); border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                >
                    <i class="flaticon-time" style="font-size: 32px; color: #fff;"></i>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <h3 style="color: #fff; font-size: 24px; font-weight: 800; margin-bottom: 6px;">
                        Rent a Car + Driver by the Hour
                    </h3>
                    <p style="color: rgba(255,255,255,0.85); font-size: 15px; margin: 0; line-height: 1.6;">
                        Maximum flexibility — explore Bali on your own terms. No fixed destination,
                        just you, a professional driver, and the open road.
                    </p>
                </div>
            </div>

            <div class="row gutter-y-30">
                <!-- Booking Form -->
                <div class="col-lg-5 wow fadeInLeft" data-wow-duration="1000ms">
                    <div
                        style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 6px 25px rgba(0,0,0,0.07); height: 100%;"
                    >
                        <h3
                            style="font-size: 22px; font-weight: 800; margin-bottom: 6px; color: #1e293b;"
                        >
                            Request Hourly Booking
                        </h3>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 28px;">
                            Fill in your details and we'll prepare the best option for you.
                        </p>

                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <!-- Pickup Location -->
                            <div>
                                <label
                                    for="hourly_pickup"
                                    style="display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"
                                >
                                    <i class="icon icon-pin-2" style="color: #6366f1; margin-right: 6px;"></i>
                                    Pickup Location *
                                </label>
                                <input type="hidden" name="pickup" value={pickupLocation} />
                                <LocationSearchInput
                                    id="hourly_pickup"
                                    bind:value={pickupLocation}
                                    placeholder="Hotel, area, or address..."
                                    required
                                />
                            </div>

                            <!-- Date -->
                            <div>
                                <label
                                    for="hourly_date"
                                    style="display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"
                                >
                                    <i class="icon icon-calendar-1" style="color: #6366f1; margin-right: 6px;"></i>
                                    Date *
                                </label>
                                <DatePicker
                                    id="hourly_date"
                                    bind:value={selectedDate}
                                    placeholder="Select date"
                                    required
                                    hideIcon
                                    hideChevron
                                />
                            </div>

                            <!-- Time -->
                            <div>
                                <label
                                    for="hourly_time"
                                    style="display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"
                                >
                                    <i class="flaticon-time" style="color: #6366f1; margin-right: 6px; font-size: 13px;"></i>
                                    Start Time *
                                </label>
                                <TimePicker
                                    id="hourly_time"
                                    bind:value={selectedTime}
                                    minTime={minTime}
                                    placeholder="Select start time"
                                />
                            </div>

                            <!-- Duration -->
                            <div>
                                <label
                                    style="display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"
                                >
                                    <i class="flaticon-time" style="color: #6366f1; margin-right: 6px; font-size: 13px;"></i>
                                    Duration
                                </label>
                                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                    {#each hourOptions as hrs}
                                        <button
                                            type="button"
                                            onclick={() => (selectedHours = hrs)}
                                            style="padding: 9px 18px; border-radius: 50px; border: 2px solid {selectedHours === hrs ? '#6366f1' : '#e2e8f0'}; background: {selectedHours === hrs ? '#6366f1' : '#fff'}; color: {selectedHours === hrs ? '#fff' : '#475569'}; font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.2s ease;"
                                        >
                                            {hrs}h
                                        </button>
                                    {/each}
                                </div>
                            </div>

                            <!-- Passengers -->
                            <div>
                                <label
                                    style="display: block; font-size: 13px; font-weight: 700; color: #374151; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;"
                                >
                                    <i class="icon icon-traveler-with-a-suitcase-1" style="color: #6366f1; margin-right: 6px;"></i>
                                    Passengers
                                </label>
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <button
                                        type="button"
                                        onclick={() => { if (passengerCount > 1) passengerCount--; }}
                                        style="background: #f5f3ff; border: 2px solid #6366f1; color: #6366f1; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 20px; font-weight: 700; padding-bottom: 2px; transition: all 0.2s;"
                                    >−</button>
                                    <span style="font-size: 20px; font-weight: 700; min-width: 32px; text-align: center; color: #1e293b;">{passengerCount}</span>
                                    <button
                                        type="button"
                                        onclick={() => passengerCount++}
                                        style="background: #f5f3ff; border: 2px solid #6366f1; color: #6366f1; width: 36px; height: 36px; border-radius: 50%; display: flex; justify-content: center; align-items: center; cursor: pointer; font-size: 20px; font-weight: 700; padding-bottom: 2px; transition: all 0.2s;"
                                    >+</button>
                                </div>
                            </div>

                            <!-- Submit -->
                            <a
                                href="/contact"
                                class="travhub-btn"
                                style="text-align: center; margin-top: 6px;"
                            >
                                <span>Request via WhatsApp</span>
                            </a>
                            <p style="text-align: center; color: #94a3b8; font-size: 13px; margin: 0;">
                                Our team will confirm availability and pricing
                            </p>
                        </div>
                    </div>
                </div>

                <!-- How It Works + Vehicle Info -->
                <div class="col-lg-7 wow fadeInRight" data-wow-duration="1000ms">
                    <!-- How It Works -->
                    <div
                        style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 6px 25px rgba(0,0,0,0.07); margin-bottom: 24px;"
                    >
                        <h4
                            style="font-size: 20px; font-weight: 800; margin-bottom: 22px; color: #1e293b;"
                        >
                            How Hourly Service Works
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 18px;">
                            {#each [
                                { num: 1, icon: 'icon-pin-2', title: 'Tell us your pickup & date', desc: 'Share where you want to be picked up and when you need the car.' },
                                { num: 2, icon: 'icon-traveler-with-a-suitcase-1', title: 'Choose your duration', desc: 'Select how many hours you need — from 2 hours up to a full day.' },
                                { num: 3, icon: 'flaticon-check', title: 'Explore freely', desc: 'Your driver will take you wherever you want within the booked time.' },
                            ] as step}
                                <div style="display: flex; align-items: flex-start; gap: 18px;">
                                    <div
                                        style="width: 48px; height: 48px; background: linear-gradient(135deg, #6366f1, #4f46e5); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"
                                    >
                                        <span style="color: #fff; font-weight: 800; font-size: 18px;">{step.num}</span>
                                    </div>
                                    <div>
                                        <h5 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">{step.title}</h5>
                                        <p style="color: #64748b; font-size: 14px; margin: 0; line-height: 1.6;">{step.desc}</p>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    </div>

                    <!-- Pricing Info -->
                    <div
                        style="background: #fff; border-radius: 20px; padding: 36px; box-shadow: 0 6px 25px rgba(0,0,0,0.07);"
                    >
                        <h4
                            style="font-size: 20px; font-weight: 800; margin-bottom: 6px; color: #1e293b;"
                        >
                            Available Vehicles
                        </h4>
                        <p style="color: #64748b; font-size: 14px; margin-bottom: 22px;">
                            Pricing is per hour. Contact us for exact rates.
                        </p>
                        {#if vehicleCategories.length === 0}
                            <p style="color: #94a3b8; font-size: 15px; text-align: center; padding: 30px 0;">
                                Vehicle information coming soon. Contact us for details.
                            </p>
                        {:else}
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                {#each vehicleCategories as vehicle}
                                    <div
                                        style="display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9;"
                                    >
                                        {#if vehicle.image_url}
                                            <img
                                                src={vehicle.image_url}
                                                alt={vehicle.title}
                                                style="width: 70px; height: 50px; object-fit: cover; border-radius: 8px; flex-shrink: 0;"
                                            />
                                        {:else}
                                            <div style="width: 70px; height: 50px; background: #e0e7ff; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                <i class="fas fa-car" style="color: #6366f1; font-size: 22px;"></i>
                                            </div>
                                        {/if}
                                        <div style="flex: 1;">
                                            <h6 style="font-size: 15px; font-weight: 700; color: #1e293b; margin-bottom: 3px;">{vehicle.title}</h6>
                                            {#if vehicle.passenger_capacity}
                                                <p style="color: #64748b; font-size: 13px; margin: 0;">
                                                    <i class="icon icon-traveler-with-a-suitcase-1" style="margin-right: 4px; color: #6366f1;"></i>
                                                    Up to {vehicle.passenger_capacity} passengers
                                                </p>
                                            {/if}
                                        </div>
                                        {#if vehicle.price_per_hour}
                                            <div style="text-align: right;">
                                                <p style="font-size: 12px; color: #94a3b8; margin-bottom: 2px;">per hour</p>
                                                <p style="font-size: 18px; font-weight: 800; color: #6366f1; margin: 0;">{formatCurrency(vehicle.price_per_hour)}</p>
                                            </div>
                                        {:else}
                                            <span style="color: #94a3b8; font-size: 13px; font-style: italic;">Ask for price</span>
                                        {/if}
                                    </div>
                                {/each}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
