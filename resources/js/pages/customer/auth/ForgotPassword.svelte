<script lang="ts">
    import { useForm, Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    import { onMount } from 'svelte';

    const settings = $derived(page.props.settings as any);
    const recaptchaEnabled = $derived(settings.recaptcha_enabled === '1');

    let status = $state('');
    let showError = $state('');

    let form = useForm({
        email: '',
        'g-recaptcha-response': '',
    });

    let emailError = $state('');

    const isFormValid = $derived(
        form.email.length > 0 && emailError === '',
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

    onMount(() => {
        const params = new URLSearchParams(window.location.search);
        const statusParam = params.get('status');
        if (statusParam === 'sent') {
            status = 'Password reset link has been sent to your email. Please check your inbox.';
        } else if (statusParam === 'not_found') {
            showError = 'No account found with this email address. Please check your email or register a new account.';
        } else if (statusParam === 'failed') {
            showError = 'Failed to send email. Please try again later.';
        }

        window.handleForgotPasswordRecaptchaSuccess = (token: string) => {
            form['g-recaptcha-response'] = token;
        };
        window.handleForgotPasswordRecaptchaExpired = () => {
            form['g-recaptcha-response'] = '';
        };
        loadRecaptcha();
    });

    $effect(() => {
        if (form.errors.email) {
            showError = form.errors.email;
        }
        if (form.errors['g-recaptcha-response']) {
            showError = form.errors['g-recaptcha-response'];
        }
    });

    const validateEmail = (e: Event) => {
        const target = e.target as HTMLInputElement;
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

    const handleSubmit = (e: Event) => {
        e.preventDefault();
        showError = '';
        status = '';
        form.post('/customer/forgot-password', {
            onFinish: () => {
                form.reset('email', 'g-recaptcha-response');
                if (recaptchaEnabled && window.grecaptcha) {
                    window.grecaptcha.reset();
                }
                form['g-recaptcha-response'] = '';
            },
        });
    };
</script>

<AppHead title="Forgot Password - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

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
                            <h3 class="sec-title__title">Forgot Password?</h3>
                            <p class="sec-title__text mt-2">
                                Enter your email address and we'll send you a link to reset your password.
                            </p>
                        </div>

                        {#if status}
                            <div
                                class="mb-4"
                                style="padding: 14px 16px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 8px; color: #155724; font-size: 14px;"
                            >
                                {status}
                            </div>
                        {/if}

                        {#if showError}
                            <div
                                class="mb-4"
                                style="padding: 14px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24; font-size: 14px;"
                            >
                                {showError}
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
                                        showError
                                            ? '#dc3545'
                                            : '#e1e1e1'}; border-radius: 5px; color: #1a1a1a;"
                                        class:is-invalid={showError ||
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
                                </div>

                                {#if recaptchaEnabled}
                                    <div
                                        class="form-one__control form-one__control--full mb-4"
                                    >
                                        <div
                                            class="g-recaptcha"
                                            data-sitekey={settings.recaptcha_site_key}
                                            data-callback="handleForgotPasswordRecaptchaSuccess"
                                            data-expired-callback="handleForgotPasswordRecaptchaExpired"
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
                                                ? 'Sending...'
                                                : 'Send Reset Link'}</span
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
                                Remember your password? <Link
                                    href="/customer/login"
                                    style="color: var(--travhub-base); font-weight: 600;"
                                    >Log in</Link
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
