<script lang="ts">
    import { useForm, Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { token, email }: { token: string; email: string } = $props();

    let form = useForm({
        token: token,
        email: email,
        password: '',
        password_confirmation: '',
    });

    let showPassword = $state(false);
    let showPasswordConfirmation = $state(false);

    const isFormValid = $derived(
        form.email.length > 0 &&
            form.password.length >= 8 &&
            form.password === form.password_confirmation,
    );

    const handleSubmit = (e: Event) => {
        e.preventDefault();
        form.post('/customer/reset-password', {
            onFinish: () => {
                form.reset('password', 'password_confirmation');
            },
        });
    };
</script>

<AppHead title="Reset Password - Siwride" />

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
                            <h3 class="sec-title__title">Reset Password</h3>
                            <p class="sec-title__text mt-2">
                                Enter your new password below.
                            </p>
                        </div>

                        {#if form.errors.email}
                            <div
                                class="mb-4"
                                style="padding: 14px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24; font-size: 14px;"
                            >
                                {form.errors.email}
                            </div>
                        {/if}

                        {#if form.errors.password}
                            <div
                                class="mb-4"
                                style="padding: 14px 16px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; color: #721c24; font-size: 14px;"
                            >
                                {form.errors.password}
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
                                        >Email Address</label
                                    >
                                    <input
                                        id="email"
                                        type="email"
                                        name="email"
                                        value={form.email}
                                        readonly
                                        style="width: 100%; height: 60px; padding: 0 20px; border: 1px solid #e1e1e1; border-radius: 5px; color: #666; background: #f8f9fa;"
                                    />
                                </div>

                                <div
                                    class="form-one__control form-one__control--full mb-4"
                                >
                                    <label
                                        for="password"
                                        style="display: block; margin-bottom: 8px; font-weight: 600;"
                                        >New Password *</label
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
                                            placeholder="Enter new password"
                                            style="width: 100%; height: 60px; padding: 0 45px 0 20px; border: 1px solid {form.errors.password
                                                ? '#dc3545'
                                                : '#e1e1e1'}; border-radius: 5px; color: #1a1a1a;"
                                        />
                                        <button
                                            type="button"
                                            onclick={() =>
                                                (showPassword = !showPassword)}
                                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0;"
                                        >
                                            {#if showPassword}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            {:else}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
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
                                </div>

                                <div
                                    class="form-one__control form-one__control--full mb-4"
                                >
                                    <label
                                        for="password_confirmation"
                                        style="display: block; margin-bottom: 8px; font-weight: 600;"
                                        >Confirm Password *</label
                                    >
                                    <div style="position: relative;">
                                        <input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type={showPasswordConfirmation
                                                ? 'text'
                                                : 'password'}
                                            bind:value={form.password_confirmation}
                                            required
                                            minlength="8"
                                            placeholder="Confirm new password"
                                            style="width: 100%; height: 60px; padding: 0 45px 0 20px; border: 1px solid {form.password &&
                                            form.password !== form.password_confirmation
                                                ? '#dc3545'
                                                : '#e1e1e1'}; border-radius: 5px; color: #1a1a1a;"
                                        />
                                        <button
                                            type="button"
                                            onclick={() =>
                                                (showPasswordConfirmation = !showPasswordConfirmation)}
                                            style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #666; padding: 0;"
                                        >
                                            {#if showPasswordConfirmation}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>
                                            {:else}
                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                            {/if}
                                        </button>
                                    </div>
                                    {#if form.password &&
                                        form.password_confirmation &&
                                        form.password !== form.password_confirmation}
                                        <div
                                            class="text-danger mt-1"
                                            style="font-size: 14px;"
                                        >
                                            Passwords do not match.
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
                                                ? 'Resetting...'
                                                : 'Reset Password'}</span
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
