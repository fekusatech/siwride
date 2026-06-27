<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm } from '@inertiajs/svelte';

    let { version } = $props();
    let isEditing = $derived(!!version);

    let form = useForm({
        platform: version?.platform ?? 'android',
        version_name: version?.version_name ?? '',
        version_code: version?.version_code ?? '',
        apk_url: version?.apk_url ?? '',
        whats_new: version?.whats_new ?? '',
        is_force_update: version?.is_force_update ?? false,
        is_active: version?.is_active ?? true,
    });

    function submit(e: Event) {
        e.preventDefault();
        if (isEditing) {
            form.put(`/admin/app-versions/${version.id}`);
        } else {
            form.post('/admin/app-versions');
        }
    }
</script>

<AppHead title={isEditing ? 'Edit App Version' : 'Add App Version'} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">{isEditing ? 'Edit App Version' : 'Add App Version'}</h4>
                <p class="text-muted mb-0">Manage APK version for driver mobile app</p>
            </div>
            <Link href="/admin/app-versions" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="ti ti-arrow-left fs-18"></i>
                Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form onsubmit={submit}>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="platform" class="form-label">Platform <span class="text-danger">*</span></label>
                                    <select class="form-control {form.errors.platform ? 'is-invalid' : ''}" id="platform" bind:value={form.platform}>
                                        <option value="android">Android</option>
                                        <option value="ios">iOS</option>
                                    </select>
                                    {#if form.errors.platform}
                                        <div class="invalid-feedback">{form.errors.platform}</div>
                                    {/if}
                                </div>
                                <div class="col-md-3">
                                    <label for="version_name" class="form-label">Version Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control {form.errors.version_name ? 'is-invalid' : ''}" id="version_name" bind:value={form.version_name} placeholder="e.g. 1.2.0" required />
                                    {#if form.errors.version_name}
                                        <div class="invalid-feedback">{form.errors.version_name}</div>
                                    {/if}
                                </div>
                                <div class="col-md-3">
                                    <label for="version_code" class="form-label">Version Code <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control {form.errors.version_code ? 'is-invalid' : ''}" id="version_code" bind:value={form.version_code} placeholder="e.g. 3" required />
                                    {#if form.errors.version_code}
                                        <div class="invalid-feedback">{form.errors.version_code}</div>
                                    {/if}
                                </div>
                                <div class="col-md-12">
                                    <label for="apk_url" class="form-label">APK URL</label>
                                    <input type="url" class="form-control {form.errors.apk_url ? 'is-invalid' : ''}" id="apk_url" bind:value={form.apk_url} placeholder="https://siwride.com/storage/apk/app-v1.2.0.apk" />
                                    {#if form.errors.apk_url}
                                        <div class="invalid-feedback">{form.errors.apk_url}</div>
                                    {/if}
                                </div>
                                <div class="col-md-12">
                                    <label for="whats_new" class="form-label">What's New</label>
                                    <textarea class="form-control {form.errors.whats_new ? 'is-invalid' : ''}" id="whats_new" bind:value={form.whats_new} rows="4" placeholder="• New feature A&#10;• Bug fix B&#10;• Performance improvement"></textarea>
                                    {#if form.errors.whats_new}
                                        <div class="invalid-feedback">{form.errors.whats_new}</div>
                                    {/if}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_force_update" bind:checked={form.is_force_update} />
                                        <label class="form-check-label" for="is_force_update">Force Update</label>
                                        <div class="text-muted small">User must update before using the app</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-4">
                                        <input class="form-check-input" type="checkbox" id="is_active" bind:checked={form.is_active} />
                                        <label class="form-check-label" for="is_active">Active</label>
                                        <div class="text-muted small">Only active versions are returned via API</div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-top d-flex gap-2">
                                <button type="submit" class="btn btn-primary" disabled={form.processing}>
                                    {#if form.processing}
                                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                                        Saving...
                                    {:else}
                                        {isEditing ? 'Update' : 'Save'}
                                    {/if}
                                </button>
                                <Link href="/admin/app-versions" class="btn btn-light">Cancel</Link>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 bg-light">
                    <div class="card-body">
                        <h5 class="card-title d-flex align-items-center gap-2 mb-3">
                            <i class="ti ti-info-circle text-primary"></i>
                            Guidelines
                        </h5>
                        <ul class="text-muted small ps-3 mb-0" style="line-height: 1.8;">
                            <li><strong>Version Code:</strong> Incremental integer. Higher = newer.</li>
                            <li><strong>Version Name:</strong> Display version (e.g. "1.2.0").</li>
                            <li><strong>Force Update:</strong> If enabled, user MUST update to proceed.</li>
                            <li><strong>APK URL:</strong> Direct download link for Android APK.</li>
                            <li>Flutter app compares its version_code with the API response to determine if update is available.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
