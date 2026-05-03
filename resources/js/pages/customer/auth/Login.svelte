<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { useForm, Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { onMount } from 'svelte';

    const settings = $derived(page.props.settings as any);
    const recaptchaEnabled = $derived(settings.recaptcha_enabled === '1');

    let form = useForm({
        email: '',
        password: '',
        remember: false,
        'g-recaptcha-response': '',
    });

    let showPassword = $state(false);
    let emailError = $state('');

    const isFormValid = $derived(
        form.email.length > 0 && emailError === '' && form.password.length >= 8,
    );

    function loadRecaptcha() {
        if (!recaptchaEnabled || window.grecaptcha) {
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://www.google.com/recaptcha/api.js';
        script.async = true;
        script.defer = true;
        document.head.appendChild(script);
    }

    const handleSubmit = (e: Event) => {
        e.preventDefault();
        form.post('/customer/login', {
            onFinish: () => {
                form.reset('password', 'g-recaptcha-response');
                if (recaptchaEnabled && window.grecaptcha) {
                    window.grecaptcha.reset();
                }
                form['g-recaptcha-response'] = '';
            },
        });
    };

    onMount(() => {
        window.handleCustomerRecaptchaSuccess = (token: string) => {
            form['g-recaptcha-response'] = token;
        };
        window.handleCustomerRecaptchaExpired = () => {
            form['g-recaptcha-response'] = '';
        };
        loadRecaptcha();
    });

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

<AppHead title="Login - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Login</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><Link href="/">Home</Link></li>
                <li><span>Login</span></li>
            </ul>
        </div>
    </section>

    <section
        class="contact-page"
        style="padding-top: 80px; padding-bottom: 80px;"
    >
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 wow fadeInUp" data-wow-delay="100ms">
                    <div
                        class="contact-page__form-wrapper"
                        style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0px 10px 60px 0px rgba(0, 0, 0, 0.05);"
                    >
                        <div class="sec-title text-center mb-4">
                            <h3 class="sec-title__title">Welcome Back</h3>
                            <p class="sec-title__text mt-2">
                                Sign in to your account to continue
                            </p>
                        </div>

                        {#if page.props.flash?.error}
                            <div class="alert alert-danger mb-4">
                                {page.props.flash.error}
                            </div>
                        {/if}

                        <form
                            class="contact-page__form form-one"
                            onsubmit={handleSubmit}
                        >
                            <div
                                class="form-one__group"
                                style="display: block;"
                            >
                                <div
                                    class="form-one__control form-one__control--full mb-4"
                                >
                                    <label
                                        for="email"
                                        style="display: block; margin-bottom: 8px; font-weight: 600;"
                                        >Email Address *</label
                                    >
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={form.email}
                                        oninput={validateEmail}
                                        required
                                        maxlength="50"
                                        placeholder="your.email@example.com"
                                        style="width: 100%; height: 60px; padding: 0 20px; border: 1px solid {emailError ||
                                        form.errors.email
                                            ? '#dc3545'
                                            : '#e1e1e1'}; border-radius: 5px; color: #1a1a1a;"
                                        class:is-invalid={form.errors.email ||
                                            emailError}
                                    />
                                    {#if emailError}
                                        <div
                                            class="text-danger mt-1"
                                            style="font-size: 14px;"
                                        >
                                            {emailError}
                                        </div>
                                    {/if}
                                    {#if form.errors.email}
                                        <div
                                            class="text-danger mt-1"
                                            style="font-size: 14px;"
                                        >
                                            {form.errors.email}
                                        </div>
                                    {/if}
                                </div>

                                <div
                                    class="form-one__control form-one__control--full mb-4"
                                >
                                    <label
                                        for="password"
                                        style="display: block; margin-bottom: 8px; font-weight: 600;"
                                        >Password *</label
                                    >
                                    <div style="position: relative;">
                                        <input
                                            id="password"
                                            name="password"
                                            type={showPassword
                                                ? 'text'
                                                : 'password'}
                                            bind:value={form.password}
                                            required
                                            minlength="8"
                                            placeholder="Enter your password"
                                            style="width: 100%; height: 60px; padding: 0 45px 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #1a1a1a;"
                                            class:is-invalid={form.errors
                                                .password}
                                        />
                                        <button
                                            type="button"
                                            onclick={() =>
                                                (showPassword = !showPassword)}
                                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0;"
                                        >
                                            {#if showPassword}
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    ><path
                                                        d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"
                                                    ></path><line
                                                        x1="1"
                                                        y1="1"
                                                        x2="23"
                                                        y2="23"
                                                    ></line></svg
                                                >
                                            {:else}
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="20"
                                                    height="20"
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    stroke-width="2"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    ><path
                                                        d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"
                                                    ></path><circle
                                                        cx="12"
                                                        cy="12"
                                                        r="3"
                                                    ></circle></svg
                                                >
                                            {/if}
                                        </button>
                                    </div>
                                    {#if form.password && form.password.length < 8}
                                        <div
                                            class="text-danger mt-1"
                                            style="font-size: 14px;"
                                        >
                                            Password must be at least 8
                                            characters.
                                        </div>
                                    {/if}
                                    {#if form.errors.password}
                                        <div
                                            class="text-danger mt-1"
                                            style="font-size: 14px;"
                                        >
                                            {form.errors.password}
                                        </div>
                                    {/if}
                                </div>

                                <div
                                    class="form-one__control form-one__control--full mb-4 d-flex justify-content-between align-items-center"
                                >
                                    <label
                                        class="d-flex align-items-center"
                                        style="cursor: pointer;"
                                    >
                                        <input
                                            type="checkbox"
                                            name="remember"
                                            bind:checked={form.remember}
                                            style="width: auto; height: auto; margin-right: 10px;"
                                        />
                                        <span style="font-weight: 500;"
                                            >Remember me</span
                                        >
                                    </label>
                                </div>

                                {#if recaptchaEnabled}
                                    <div
                                        class="form-one__control form-one__control--full mb-4"
                                    >
                                        <div
                                            class="g-recaptcha"
                                            data-sitekey={settings.recaptcha_site_key}
                                            data-callback="handleCustomerRecaptchaSuccess"
                                            data-expired-callback="handleCustomerRecaptchaExpired"
                                        ></div>
                                        {#if form.errors['g-recaptcha-response']}
                                            <div
                                                class="text-danger mt-1"
                                                style="font-size: 14px;"
                                            >
                                                {form.errors[
                                                    'g-recaptcha-response'
                                                ]}
                                            </div>
                                        {/if}
                                    </div>
                                {/if}

                                <div
                                    class="form-one__control form-one__control--full"
                                >
                                    <button
                                        type="submit"
                                        class="travhub-btn"
                                        disabled={form.processing ||
                                            !isFormValid}
                                        style="width: 100%; {!isFormValid &&
                                        !form.processing
                                            ? 'opacity: 0.6; cursor: not-allowed;'
                                            : ''}"
                                    >
                                        <span
                                            >{form.processing
                                                ? 'Logging in...'
                                                : 'Log In'}</span
                                        >
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div
                            class="text-center mt-4 pt-3"
                            style="border-top: 1px solid #eee;"
                        >
                            <p class="mb-0">
                                Don't have an account? <Link
                                    href="/customer/register"
                                    style="color: var(--travhub-base); font-weight: 600;"
                                    >Sign up</Link
                                >
                            </p>
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
