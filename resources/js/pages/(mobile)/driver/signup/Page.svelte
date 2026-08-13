<script module lang="ts">
    export const layout = {
        title: 'Driver Registration',
        description: 'Join Siwride as a partner driver',
    };
</script>

<script lang="ts">
    import { page, useForm } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const settings = $derived(page.props.settings as any);

    let form = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        vehicle_type: '',
        vehicle_registration_number: '',
    });

    function submit(event: Event) {
        event.preventDefault();
        form.post('/driver/register', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<AppHead title="Driver Register" />

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
            <h2 class="page-header__title bw-split-in-right">Become a Driver Partner</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Driver Registration</span></li>
            </ul>
        </div>
    </section>

    <section class="contact-page" style="padding: 90px 0 100px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="sec-title text-center mb-5">
                        <div class="sec-title__tagline bw-split-in-right">
                            Join Our Network<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                        </div>
                        <h3 class="sec-title__title bw-split-in-left">Driver Partner Registration</h3>
                        <p class="sec-title__text bw-split-in-up-fast">
                            Share your local expertise with travelers and grow your business with Siwride.
                        </p>
                    </div>

                    {#if Object.keys(form.errors).length > 0}
                        <div class="alert alert-danger mb-4" role="alert">
                            {#each Object.values(form.errors) as error}
                                <div>{error}</div>
                            {/each}
                        </div>
                    {/if}

                    <form class="form-one" onsubmit={submit}>
                        <div class="form-one__group">
                            <div class="form-one__control">
                                <label for="name">Full Name *</label>
                                <input id="name" type="text" bind:value={form.name} required placeholder="Enter your full name" />
                            </div>
                            <div class="form-one__control">
                                <label for="email">Email Address *</label>
                                <input id="email" type="email" bind:value={form.email} required placeholder="your.email@example.com" />
                            </div>
                            <div class="form-one__control">
                                <label for="phone">WhatsApp / Phone *</label>
                                <input id="phone" type="tel" bind:value={form.phone} required placeholder="+62 812 3456 7890" />
                            </div>
                            <div class="form-one__control">
                                <label for="vehicle_type">Vehicle Type *</label>
                                <select id="vehicle_type" bind:value={form.vehicle_type} required>
                                    <option value="">Select vehicle</option>
                                    <option value="Car (Economy)">Car (Economy)</option>
                                    <option value="Car (Comfort)">Car (Comfort)</option>
                                    <option value="Car (Business)">Car (Business)</option>
                                    <option value="Motorcycle">Motorcycle</option>
                                </select>
                            </div>
                            <div class="form-one__control form-one__control--full">
                                <label for="vehicle_registration_number">Vehicle Registration Number (Plat Nomor) *</label>
                                <input id="vehicle_registration_number" type="text" bind:value={form.vehicle_registration_number} required placeholder="e.g. N 1234 AB" />
                            </div>
                            <div class="form-one__control">
                                <label for="password">Password *</label>
                                <input id="password" type="password" bind:value={form.password} required placeholder="Create a password" />
                            </div>
                            <div class="form-one__control">
                                <label for="password_confirmation">Confirm Password *</label>
                                <input id="password_confirmation" type="password" bind:value={form.password_confirmation} required placeholder="Repeat your password" />
                            </div>
                            <div class="form-one__control form-one__control--full text-center">
                                <button type="submit" class="travhub-btn" disabled={form.processing}>
                                    <span>{form.processing ? 'Submitting...' : 'Apply as Driver'}</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Already have a driver account? <a href="/driver/login">Log in here</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
