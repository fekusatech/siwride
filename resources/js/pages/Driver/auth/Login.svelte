<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, page } from '@inertiajs/svelte';

    const settings = $derived(page.props.settings as any);

    let form = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit = (e: Event) => {
        e.preventDefault();
        form.post('/driver/login', {
            onFinish: () => form.reset('password'),
        });
    };
</script>

<AppHead title="Driver Login" />

<div class="auth-bg d-flex min-vh-100 justify-content-center align-items-center">
    <div class="row g-0 justify-content-center w-100 m-xxl-5 px-xxl-4 m-3">
        <div class="col-xl-4 col-lg-5 col-md-6">
            <div class="card overflow-hidden text-center h-100 p-xxl-4 p-3 mb-0">
                <a href="/" class="auth-brand mb-3 d-flex justify-content-center align-items-center gap-2 text-decoration-none">
                    <img
                        src={settings?.logo || '/assets/images/siwride_logo.png'}
                        alt="{settings?.business_name || 'Siwride'} logo"
                        style="width: 55px; height: 55px; border-radius: 50%; object-fit: cover; background: white; padding: 3px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                    />
                    <span style="font-size: 26px; font-weight: bold; color: var(--bs-heading-color); letter-spacing: 0.5px;">
                        {settings?.business_name || 'Siwride'}
                    </span>
                </a>

                <h5 class="fw-semibold mb-2">Driver Login</h5>
                <p class="text-muted mb-4 small">
                    Sign in to write guides and track your referral earnings.
                </p>

                {#if Object.keys(form.errors).length > 0}
                    <div class="alert alert-danger text-start mb-3" role="alert">
                        <i class="ti ti-alert-triangle me-2 fs-18"></i>
                        {#each Object.values(form.errors) as error}
                            <div>{error}</div>
                        {/each}
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
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            bind:value={form.password}
                            class="form-control"
                            placeholder="Enter your password"
                            required
                        />
                    </div>

                    <div class="mb-3 form-check">
                        <input
                            type="checkbox"
                            name="remember"
                            class="form-check-input"
                            id="checkbox-signin"
                            bind:checked={form.remember}
                        />
                        <label class="form-check-label" for="checkbox-signin">Remember me</label>
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit" disabled={form.processing}>
                            {#if form.processing}
                                <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            {/if}
                            Login
                        </button>
                    </div>
                </form>

                <p class="mt-auto mb-0 small text-muted">
                    Not a driver yet? <a href="/driver/register">Register here</a>
                </p>
            </div>
        </div>
    </div>
</div>
