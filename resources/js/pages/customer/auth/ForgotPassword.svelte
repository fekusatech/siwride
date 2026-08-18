<script lang="ts">
    import { useForm, Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { status = '' }: { status?: string } = $props();

    let form = useForm({
        email: '',
    });

    let emailError = $state('');

    const isFormValid = $derived(
        form.email.length > 0 && emailError === '',
    );

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
        form.post('/customer/forgot-password', {
            onFinish: () => form.reset('email'),
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

                        {#if form.errors.email}
                            <div
                                class="mb-4"
                                style="padding: 14px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24; font-size: 14px;"
                            >
                                {form.errors.email}
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
                                </div>

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
