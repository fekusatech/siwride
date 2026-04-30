<script lang="ts">
    import { onMount } from 'svelte';
    import { page } from '@inertiajs/svelte';
    import { useForm, Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let form = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
    });

    onMount(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const emailParam = urlParams.get('email');
        if (emailParam) {
            form.email = emailParam;
        }
    });

    let showPassword = $state(false);
    let showConfirmPassword = $state(false);
    let emailError = $state('');

    const isFormValid = $derived(
        form.name.length > 0 &&
        form.email.length > 0 &&
        emailError === '' &&
        form.password.length >= 8 &&
        form.password === form.password_confirmation
    );

    const handleSubmit = (e: Event) => {
        e.preventDefault();
        form.post('/customer/register', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    const validateEmail = (e: Event) => {
        const target = e.target as HTMLInputElement;
        target.value = target.value.replace(/\s/g, '');
        form.email = target.value;

        if (target.value.length > 0) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(target.value)) {
                emailError = 'Please enter a valid email address';
            } else {
                emailError = '';
            }
        } else {
            emailError = '';
        }
    };
</script>

<AppHead title="Customer Registration - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Register</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><Link href="/">Home</Link></li>
                <li><span>Register</span></li>
            </ul>
        </div>
    </section>

    <section class="contact-page" style="padding-top: 80px; padding-bottom: 80px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 wow fadeInUp" data-wow-delay="100ms">
                    <div class="contact-page__form-wrapper" style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0px 10px 60px 0px rgba(0, 0, 0, 0.05);">
                        <div class="sec-title text-center mb-4">
                            <h3 class="sec-title__title">Create an Account</h3>
                            <p class="sec-title__text mt-2">Sign up to manage your bookings and enjoy exclusive offers</p>
                        </div>

                        {#if page.props.flash?.error}
                            <div class="alert alert-danger mb-4">
                                {page.props.flash.error}
                            </div>
                        {/if}

                        <form class="contact-page__form form-one" onsubmit={handleSubmit}>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-one__control form-one__control--full">
                                        <label for="name" style="display: block; margin-bottom: 8px; font-weight: 600;">Full Name *</label>
                                        <input
                                            id="name"
                                            type="text" maxlength="50"
                                            bind:value={form.name}
                                            required
                                            placeholder="John Doe"
                                            style="width: 100%; height: 60px; padding: 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #1a1a1a;"
                                            class:is-invalid={form.errors.name}
                                            oninput={(e) => {
                                                e.currentTarget.value = e.currentTarget.value.replace(/[^a-zA-Z\s]/g, '');
                                                form.name = e.currentTarget.value;
                                            }}
                                        />
                                        {#if form.errors.name}
                                            <div class="text-danger mt-1" style="font-size: 14px;">{form.errors.name}</div>
                                        {/if}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-one__control form-one__control--full">
                                        <label for="phone" style="display: block; margin-bottom: 8px; font-weight: 600;">Phone / WhatsApp</label>
                                        <input
                                            id="phone"
                                            type="tel"
                                            bind:value={form.phone}
                                            maxlength="15"
                                            placeholder="081234567890"
                                            style="width: 100%; height: 60px; padding: 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #1a1a1a;"
                                            class:is-invalid={form.errors.phone}
                                            oninput={(e) => {
                                                e.currentTarget.value = e.currentTarget.value.replace(/[^0-9]/g, '');
                                                form.phone = e.currentTarget.value;
                                            }}
                                        />
                                        {#if form.errors.phone}
                                            <div class="text-danger mt-1" style="font-size: 14px;">{form.errors.phone}</div>
                                        {/if}
                                    </div>
                                </div>
                                
                                <div class="col-12 mb-4">
                                    <div class="form-one__control form-one__control--full">
                                        <label for="email" style="display: block; margin-bottom: 8px; font-weight: 600;">Email Address *</label>
                                        <input
                                            id="email"
                                            type="email"
                                            value={form.email}
                                            oninput={validateEmail}
                                            required
                                            maxlength="50"
                                            placeholder="your.email@example.com"
                                            style="width: 100%; height: 60px; padding: 0 20px; border: 1px solid {emailError || form.errors.email ? '#dc3545' : '#e1e1e1'}; border-radius: 5px; color: #1a1a1a;"
                                            class:is-invalid={form.errors.email || emailError}
                                        />
                                        {#if emailError}
                                            <div class="text-danger mt-1" style="font-size: 14px;">{emailError}</div>
                                        {/if}
                                        {#if form.errors.email}
                                            <div class="text-danger mt-1" style="font-size: 14px;">{form.errors.email}</div>
                                        {/if}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-one__control form-one__control--full">
                                        <label for="password" style="display: block; margin-bottom: 8px; font-weight: 600;">Password *</label>
                                        <div style="position: relative;">
                                            <input
                                                id="password"
                                                type={showPassword ? "text" : "password"}
                                                bind:value={form.password}
                                                required
                                                minlength="8"
                                                placeholder="Create a password"
                                                style="width: 100%; height: 60px; padding: 0 45px 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #1a1a1a;"
                                                class:is-invalid={form.errors.password}
                                            />
                                            <button type="button" onclick={() => showPassword = !showPassword} style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0;">
                                                {#if showPassword}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                                {:else}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                {/if}
                                            </button>
                                        </div>
                                        {#if form.password && form.password.length < 8}
                                            <div class="text-danger mt-1" style="font-size: 14px;">Password must be at least 8 characters.</div>
                                        {/if}
                                        {#if form.errors.password}
                                            <div class="text-danger mt-1" style="font-size: 14px;">{form.errors.password}</div>
                                        {/if}
                                    </div>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <div class="form-one__control form-one__control--full">
                                        <label for="password_confirmation" style="display: block; margin-bottom: 8px; font-weight: 600;">Confirm Password *</label>
                                        <div style="position: relative;">
                                            <input
                                                id="password_confirmation"
                                                type={showConfirmPassword ? "text" : "password"}
                                                bind:value={form.password_confirmation}
                                                required
                                                minlength="8"
                                                placeholder="Repeat password"
                                                style="width: 100%; height: 60px; padding: 0 45px 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #1a1a1a;"
                                            />
                                            <button type="button" onclick={() => showConfirmPassword = !showConfirmPassword} style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0;">
                                                {#if showConfirmPassword}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                                {:else}
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                {/if}
                                            </button>
                                        </div>
                                        {#if form.password_confirmation && form.password !== form.password_confirmation}
                                            <div class="text-danger mt-1" style="font-size: 14px;">Passwords do not match.</div>
                                        {/if}
                                    </div>
                                </div>

                                <div class="col-12 mt-2">
                                    <div class="form-one__control form-one__control--full">
                                        <button type="submit" class="travhub-btn" disabled={form.processing || !isFormValid} style="width: 100%; {(!isFormValid && !form.processing) ? 'opacity: 0.6; cursor: not-allowed;' : ''}">
                                            <span>{form.processing ? 'Registering...' : 'Sign Up'}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4 pt-3" style="border-top: 1px solid #eee;">
                            <p class="mb-0">Already have an account? <Link href="/customer/login" style="color: var(--travhub-base); font-weight: 600;">Log in</Link></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    input::placeholder {
        color: #999999 !important;
        opacity: 1;
    }
</style>
