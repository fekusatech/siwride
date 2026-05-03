<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    const settings = $derived(page.props.settings as any);
    const recaptchaEnabled = $derived(settings.recaptcha_enabled === '1');

    let form = useForm({
        email: '',
        password: '',
        remember: false,
        'g-recaptcha-response': '',
    });

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

    const submit = (e: Event) => {
        e.preventDefault();
        form.post('/login', {
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
        window.handleAdminRecaptchaSuccess = (token: string) => {
            form['g-recaptcha-response'] = token;
        };
        window.handleAdminRecaptchaExpired = () => {
            form['g-recaptcha-response'] = '';
        };
        loadRecaptcha();
    });
</script>

<AppHead title="Admin Login" />

<div
    class="auth-bg d-flex min-vh-100 justify-content-center align-items-center"
>
    <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div
                class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0"
            >
                <a href="/" class="auth-brand mb-3 d-block">
                    {#if settings.logo}
                        <img
                            src="/storage/{settings.logo}?v={new Date(
                                settings.updated_at,
                            ).getTime()}"
                            alt="{settings.business_name} logo"
                            height="45"
                            class="mx-auto"
                        />
                    {:else}
                        <img
                            src="/assets-admin/images/logo-dark.png"
                            alt="logo dark"
                            height="30"
                            class="logo-dark mx-auto"
                        />
                        <img
                            src="/assets-admin/images/logo.png"
                            alt="logo light"
                            height="30"
                            class="logo-light mx-auto"
                        />
                    {/if}
                </a>

                <h5 class="fw-semibold mb-2">
                    Welcome to {settings.business_name}!
                </h5>

                <p class="text-muted mb-4 small">
                    Enter your email address and password to access the admin
                    panel.
                </p>

                {#if Object.keys(form.errors).length > 0}
                    <div
                        class="alert alert-danger alert-dismissible fade show text-start mb-3"
                        role="alert"
                    >
                        <i class="ti ti-alert-triangle me-2 fs-18"></i>
                        <strong>Error!</strong> Please check your credentials.
                        <ul class="mb-0 mt-1 fs-13">
                            {#each Object.values(form.errors) as error}
                                <li>{error}</li>
                            {/each}
                        </ul>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Close"
                        ></button>
                    </div>
                {/if}

                <form onsubmit={submit} class="text-start mb-3">
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            bind:value={form.email}
                            class="form-control"
                            placeholder="Enter your email"
                            required
                        />
                        {#if form.errors.email}
                            <div class="text-danger fs-13 mt-1">
                                {form.errors.email}
                            </div>
                        {/if}
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label
                        >
                        <input
                            type="password"
                            id="password"
                            name="password"
                            bind:value={form.password}
                            class="form-control"
                            placeholder="Enter your password"
                            required
                        />
                        {#if form.errors.password}
                            <div class="text-danger fs-13 mt-1">
                                {form.errors.password}
                            </div>
                        {/if}
                    </div>

                    <div class="d-flex justify-content-between mb-3">
                        <div class="form-check">
                            <input
                                type="checkbox"
                                name="remember"
                                class="form-check-input"
                                id="checkbox-signin"
                                bind:checked={form.remember}
                            />
                            <label
                                class="form-check-label"
                                for="checkbox-signin">Remember me</label
                            >
                        </div>

                        <a
                            href="/forgot-password"
                            class="text-muted border-bottom border-dashed"
                            >Forget Password</a
                        >
                    </div>

                    {#if recaptchaEnabled}
                        <div class="mb-3">
                            <div
                                class="g-recaptcha"
                                data-sitekey={settings.recaptcha_site_key}
                                data-callback="handleAdminRecaptchaSuccess"
                                data-expired-callback="handleAdminRecaptchaExpired"
                            ></div>
                            {#if form.errors['g-recaptcha-response']}
                                <div class="text-danger fs-13 mt-1">
                                    {form.errors['g-recaptcha-response']}
                                </div>
                            {/if}
                        </div>
                    {/if}

                    <div class="d-grid">
                        <button
                            class="btn btn-primary"
                            type="submit"
                            disabled={form.processing}
                        >
                            {#if form.processing}
                                <span
                                    class="spinner-border spinner-border-sm me-1"
                                    role="status"
                                    aria-hidden="true"
                                ></span>
                            {/if}
                            Login
                        </button>
                    </div>
                </form>

                <p class="mt-auto mb-0">
                    {new Date().getFullYear()} © {settings.business_name}
                </p>
            </div>
        </div>
    </div>
</div>
