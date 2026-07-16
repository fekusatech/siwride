<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm, router } from '@inertiajs/svelte';

    let { service = null, activities = [] } = $props();
    let isEdit = $derived(!!service);

    let form = useForm({
        title: service?.title || '',
        subtitle: service?.subtitle || '',
        description: service?.description || '',
        image: null as File | null,
        href: service?.href || '',
        is_active: service?.is_active ?? true,
    });

    // Preview state
    let previewUrl = $state(service?.image_url ?? null);

    function handleImageChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            form.image = file;
            previewUrl = URL.createObjectURL(file);
        }
    }

    function submit() {
        // Inertia needs _method for put requests with file uploads
        if (isEdit) {
            router.post(`/admin/services/${service.id}`, {
                _method: 'put',
                title: form.title,
                subtitle: form.subtitle,
                description: form.description,
                href: form.href,
                is_active: form.is_active,
                image: form.image,
            }, {
                preserveScroll: true,
                forceFormData: true,
            });
        } else {
            form.post('/admin/services', {
                preserveScroll: true,
                forceFormData: true,
            });
        }
    }
</script>

<AppHead title={isEdit ? 'Edit Service' : 'Add Service'} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">{isEdit ? 'Edit Service' : 'Add Service'}</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><Link href="/admin/services">Services</Link></li>
                    <li class="breadcrumb-item active">{isEdit ? 'Edit' : 'Add'}</li>
                </ol>
            </div>
            <Link href="/admin/services" class="btn btn-outline-secondary">
                <i class="ti ti-arrow-left me-1"></i> Back to List
            </Link>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form onsubmit={(e) => { e.preventDefault(); submit(); }}>
                            <div class="mb-3">
                                <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" bind:value={form.title} placeholder="e.g. Airport Transfer" required>
                                {#if form.errors.title}<div class="text-danger mt-1 small">{form.errors.title}</div>{/if}
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="subtitle">Subtitle</label>
                                <input type="text" class="form-control" id="subtitle" bind:value={form.subtitle} placeholder="e.g. Point-to-Point">
                                {#if form.errors.subtitle}<div class="text-danger mt-1 small">{form.errors.subtitle}</div>{/if}
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" rows="3" bind:value={form.description}></textarea>
                                {#if form.errors.description}<div class="text-danger mt-1 small">{form.errors.description}</div>{/if}
                            </div>

                            <div class="mb-4">
                                <label for="href" class="form-label">Link/URL (Optional)</label>
                                <div class="input-group">
                                    <input
                                        type="text"
                                        class="form-control {form.errors.href ? 'is-invalid' : ''}"
                                        id="href"
                                        bind:value={form.href}
                                        placeholder="e.g. /booking/airport-transfer"
                                    />
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Link to Activity
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        {#each activities as act}
                                            <li><button type="button" class="dropdown-item" onclick={() => form.href = `/activities/${act.slug}`}>{act.title}</button></li>
                                        {:else}
                                            <li><span class="dropdown-item text-muted">No activities found</span></li>
                                        {/each}
                                    </ul>
                                </div>
                                {#if form.errors.href}
                                    <div class="invalid-feedback d-block">{form.errors.href}</div>
                                {/if}
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="image">Service Image</label>
                                <input type="file" class="form-control" id="image" accept="image/*" onchange={handleImageChange}>
                                <small class="text-muted d-block mt-1">Recommended size: 600x400px</small>
                                {#if form.errors.image}<div class="text-danger mt-1 small">{form.errors.image}</div>{/if}
                                
                                {#if previewUrl}
                                    <div class="mt-3">
                                        <p class="mb-2 fw-medium">Image Preview:</p>
                                        <img src={previewUrl} alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                {/if}
                            </div>
                            
                            <div class="mb-4">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="is_active" bind:checked={form.is_active}>
                                    <label class="form-check-label" for="is_active">Active (Visible to customers)</label>
                                </div>
                                {#if form.errors.is_active}
                                    <div class="invalid-feedback d-block">{form.errors.is_active}</div>
                                {/if}
                            </div>

                            <hr class="my-4">
                            
                            <div class="d-flex justify-content-end gap-2">
                                <Link href="/admin/services" class="btn btn-light">Cancel</Link>
                                <button type="submit" class="btn btn-primary" disabled={form.processing}>
                                    {#if form.processing}
                                        <i class="ti ti-loader ti-spin me-1"></i> Saving...
                                    {:else}
                                        <i class="ti ti-device-floppy me-1"></i> Save Service
                                    {/if}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Service Guidelines</h5>
                        <p class="text-muted small">
                            Services represent the main offerings in your platform (e.g. Airport Transfer, Hourly Service). They are shown on the booking landing page.
                        </p>
                        <ul class="text-muted small ps-3">
                            <li class="mb-2"><strong>Title</strong>: Main name of the service</li>
                            <li class="mb-2"><strong>Subtitle</strong>: Short subtitle to describe the service (e.g. Point-to-Point)</li>
                            <li class="mb-2"><strong>Description</strong>: Short description of the service</li>
                            <li class="mb-2"><strong>Destination URL</strong>: Where the "Book" button should navigate to</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
