<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, page } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';

    let { setting } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        business_name: setting.business_name,
        logo: null as File | null,
        favicon: null as File | null,
    });

    let logoPreview = $state<string | null>(null);
    let faviconPreview = $state<string | null>(null);

    // Sync previews with props when they change (after successful submission)
    $effect(() => {
        logoPreview = setting.logo
            ? `/storage/${setting.logo}?v=${new Date(setting.updated_at).getTime()}`
            : null;
        faviconPreview = setting.favicon
            ? `/storage/${setting.favicon}?v=${new Date(setting.updated_at).getTime()}`
            : null;
    });

    function handleLogoChange(e: Event) {
        const file = (e.target as HTMLInputElement).files?.[0];
        if (file) {
            form.logo = file;
            const reader = new FileReader();
            reader.onload = (e) => (logoPreview = e.target?.result as string);
            reader.readAsDataURL(file);
        }
    }

    function handleFaviconChange(e: Event) {
        const file = (e.target as HTMLInputElement).files?.[0];
        if (file) {
            form.favicon = file;
            const reader = new FileReader();
            reader.onload = (e) =>
                (faviconPreview = e.target?.result as string);
            reader.readAsDataURL(file);
        }
    }

    function submit(e: Event) {
        e.preventDefault();
        form.post('/admin/settings/general', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('logo', 'favicon');
            },
        });
    }
</script>

<AppHead title="General Settings" />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <h4 class="mb-0">General Settings</h4>
            <p class="text-muted mb-0">
                Customize your business identity and branding
            </p>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5
                            class="card-title mb-0 d-flex align-items-center gap-2"
                        >
                            <i class="ti ti-settings fs-20 text-primary"></i>
                            Business Identity
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form onsubmit={submit}>
                            <!-- Business Name -->
                            <div class="mb-4">
                                <label
                                    for="business_name"
                                    class="form-label fw-bold"
                                    >Business Name</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text"
                                        ><i class="ti ti-building"></i></span
                                    >
                                    <input
                                        type="text"
                                        id="business_name"
                                        class="form-control"
                                        bind:value={form.business_name}
                                        placeholder="Enter your business name"
                                        required
                                    />
                                </div>
                                {#if form.errors.business_name}
                                    <div class="text-danger small mt-1">
                                        {form.errors.business_name}
                                    </div>
                                {/if}
                                <small class="text-muted mt-1 d-block"
                                    >This name will appear in the sidebar, tab
                                    title, and reports.</small
                                >
                            </div>

                            <hr class="my-4 opacity-50" />

                            <!-- Logo Upload -->
                            <div class="mb-4">
                                <label
                                    for="logo_input"
                                    class="form-label fw-bold d-block"
                                    >Business Logo</label
                                >
                                <div
                                    class="d-flex align-items-start gap-4 flex-column flex-md-row"
                                >
                                    <div
                                        class="logo-preview-container bg-light rounded-3 d-flex align-items-center justify-content-center border overflow-hidden p-3"
                                        style="width: 200px; height: 120px;"
                                    >
                                        {#if logoPreview}
                                            <img
                                                src={logoPreview}
                                                alt="Logo Preview"
                                                class="img-fluid"
                                                style="max-height: 100%;"
                                            />
                                        {:else}
                                            <div class="text-center text-muted">
                                                <i
                                                    class="ti ti-photo fs-32 d-block mb-1"
                                                ></i>
                                                <small>No Logo</small>
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="flex-grow-1">
                                        <input
                                            id="logo_input"
                                            type="file"
                                            class="form-control mb-2"
                                            accept="image/*"
                                            onchange={handleLogoChange}
                                        />
                                        <div class="text-muted small">
                                            <i class="ti ti-info-circle"></i> Recommended
                                            size: 250x100px. Max size: 2MB.
                                        </div>
                                        {#if form.errors.logo}
                                            <div class="text-danger small mt-1">
                                                {form.errors.logo}
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4 opacity-50" />

                            <!-- Favicon Upload -->
                            <div class="mb-4">
                                <label
                                    for="favicon_input"
                                    class="form-label fw-bold d-block"
                                    >Favicon</label
                                >
                                <div
                                    class="d-flex align-items-center gap-4 flex-column flex-md-row"
                                >
                                    <div
                                        class="favicon-preview-container bg-light rounded-3 d-flex align-items-center justify-content-center border overflow-hidden"
                                        style="width: 60px; height: 60px;"
                                    >
                                        {#if faviconPreview}
                                            <img
                                                src={faviconPreview}
                                                alt="Favicon Preview"
                                                style="width: 32px; height: 32px;"
                                            />
                                        {:else}
                                            <div class="text-center text-muted">
                                                <i
                                                    class="ti ti-world fs-20 d-block"
                                                ></i>
                                            </div>
                                        {/if}
                                    </div>
                                    <div class="flex-grow-1">
                                        <input
                                            id="favicon_input"
                                            type="file"
                                            class="form-control mb-2"
                                            accept=".ico,.png,.jpg,.jpeg,.svg"
                                            onchange={handleFaviconChange}
                                        />
                                        <div class="text-muted small">
                                            <i class="ti ti-info-circle"></i> Best
                                            format: .ico or 32x32px PNG. Max size:
                                            1MB.
                                        </div>
                                        {#if form.errors.favicon}
                                            <div class="text-danger small mt-1">
                                                {form.errors.favicon}
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>

                            <div
                                class="mt-5 boarder-top pt-3 d-flex justify-content-end"
                            >
                                <button
                                    type="submit"
                                    class="btn btn-primary px-4 d-flex align-items-center gap-2"
                                    disabled={form.processing}
                                >
                                    {#if form.processing}
                                        <span
                                            class="spinner-border spinner-border-sm"
                                            role="status"
                                            aria-hidden="true"
                                        ></span>
                                        Saving...
                                    {:else}
                                        <i class="ti ti-device-floppy fs-18"
                                        ></i>
                                        Save Settings
                                    {/if}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-primary-subtle">
                    <div class="card-body p-4 text-center">
                        <div
                            class="bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3"
                            style="width: 60px; height: 60px;"
                        >
                            <i class="ti ti-bulb fs-32"></i>
                        </div>
                        <h5 class="text-primary fw-bold">Branding Tip</h5>
                        <p class="text-primary fs-14 mb-0">
                            Custom logos and business names help build trust
                            with your customers. Make sure to use high-quality
                            transparent PNGs for your logo to match both light
                            and dark modes perfectly.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .logo-preview-container,
    .favicon-preview-container {
        border-style: dashed !important;
        border-width: 2px !important;
    }
</style>
