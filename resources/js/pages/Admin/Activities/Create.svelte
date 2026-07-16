<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router, useForm } from '@inertiajs/svelte';

    let { activity = null } = $props();
    let isEdit = $derived(!!activity);

    function arrayToLines(arr: string[] | null | undefined): string {
        if (!arr || !Array.isArray(arr)) return '';
        return arr.join('\n');
    }

    let form = useForm({
        title: activity?.title || '',
        subtitle: activity?.subtitle || '',
        description: activity?.description || '',
        image: null as File | null,
        price_per_pax: activity?.price_per_pax || '',
        min_pax: activity?.min_pax || 1,
        max_pax: activity?.max_pax || '',
        duration_label: activity?.duration_label || '',
        meeting_point: activity?.meeting_point || '',
        includes: arrayToLines(activity?.includes),
        excludes: arrayToLines(activity?.excludes),
        highlights: arrayToLines(activity?.highlights),
        is_active: activity?.is_active ?? true,
        sort_order: activity?.sort_order || 0,
    });

    let previewUrl = $state(activity?.image_url ?? null);

    function handleImageChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            form.image = file;
            previewUrl = URL.createObjectURL(file);
        }
    }

    function submit() {
        if (isEdit) {
            router.post(`/admin/activities/${activity.id}`, {
                _method: 'put',
                title: form.title,
                subtitle: form.subtitle,
                description: form.description,
                price_per_pax: form.price_per_pax,
                min_pax: form.min_pax,
                max_pax: form.max_pax,
                duration_label: form.duration_label,
                meeting_point: form.meeting_point,
                includes: form.includes,
                excludes: form.excludes,
                highlights: form.highlights,
                is_active: form.is_active,
                sort_order: form.sort_order,
                image: form.image,
            }, { preserveScroll: true, forceFormData: true });
        } else {
            form.post('/admin/activities', { preserveScroll: true, forceFormData: true });
        }
    }
</script>

<AppHead title={isEdit ? 'Edit Activity' : 'Add Activity'} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">{isEdit ? 'Edit Activity' : 'Add Activity'}</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><Link href="/admin/activities">Activities</Link></li>
                    <li class="breadcrumb-item active">{isEdit ? 'Edit' : 'Add'}</li>
                </ol>
            </div>
            <Link href="/admin/activities" class="btn btn-outline-secondary">
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
                                <input type="text" class="form-control" id="title" bind:value={form.title} placeholder="e.g. ATV Ride" required>
                                {#if form.errors.title}<div class="text-danger mt-1 small">{form.errors.title}</div>{/if}
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="subtitle">Subtitle</label>
                                <input type="text" class="form-control" id="subtitle" bind:value={form.subtitle} placeholder="e.g. Adventure on volcanic terrain">
                                {#if form.errors.subtitle}<div class="text-danger mt-1 small">{form.errors.subtitle}</div>{/if}
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="description">Description</label>
                                <textarea class="form-control" id="description" rows="4" bind:value={form.description}></textarea>
                                {#if form.errors.description}<div class="text-danger mt-1 small">{form.errors.description}</div>{/if}
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label" for="price_per_pax">Price per Pax (IDR) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="price_per_pax" bind:value={form.price_per_pax} min="0" required>
                                    {#if form.errors.price_per_pax}<div class="text-danger mt-1 small">{form.errors.price_per_pax}</div>{/if}
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="min_pax">Min Pax</label>
                                    <input type="number" class="form-control" id="min_pax" bind:value={form.min_pax} min="1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label" for="max_pax">Max Pax (blank = unlimited)</label>
                                    <input type="number" class="form-control" id="max_pax" bind:value={form.max_pax} min="1">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label" for="duration_label">Duration</label>
                                    <input type="text" class="form-control" id="duration_label" bind:value={form.duration_label} placeholder="e.g. 3-4 hours">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label" for="meeting_point">Meeting Point</label>
                                    <input type="text" class="form-control" id="meeting_point" bind:value={form.meeting_point} placeholder="e.g. Kintamani Village">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="includes">Includes (one per line)</label>
                                <textarea class="form-control" id="includes" rows="4" bind:value={form.includes} placeholder="Helmet&#10;Safety briefing&#10;Bottled water"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="excludes">Excludes (one per line)</label>
                                <textarea class="form-control" id="excludes" rows="3" bind:value={form.excludes} placeholder="Personal insurance&#10;Tips"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="highlights">Highlights (one per line)</label>
                                <textarea class="form-control" id="highlights" rows="3" bind:value={form.highlights} placeholder="Scenic volcano view&#10;Suitable for beginners"></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="image">Activity Image</label>
                                <input type="file" class="form-control" id="image" accept="image/*" onchange={handleImageChange}>
                                <small class="text-muted d-block mt-1">Recommended size: 800x500px</small>
                                {#if form.errors.image}<div class="text-danger mt-1 small">{form.errors.image}</div>{/if}
                                {#if previewUrl}
                                    <div class="mt-3">
                                        <p class="mb-2 fw-medium">Preview:</p>
                                        <img src={previewUrl} alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                {/if}
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label" for="sort_order">Sort Order</label>
                                    <input type="number" class="form-control" id="sort_order" bind:value={form.sort_order}>
                                </div>
                            </div>

                            <div class="mb-4">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" bind:checked={form.is_active}>
                                    <label class="form-check-label" for="is_active">Active (Visible to customers)</label>
                                </div>
                            </div>

                            <hr class="my-4">

                            <div class="d-flex justify-content-end gap-2">
                                <Link href="/admin/activities" class="btn btn-light">Cancel</Link>
                                <button type="submit" class="btn btn-primary" disabled={form.processing}>
                                    {#if form.processing}
                                        <i class="ti ti-loader ti-spin me-1"></i> Saving...
                                    {:else}
                                        <i class="ti ti-device-floppy me-1"></i> Save Activity
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
                        <h5 class="card-title mb-3">Activity Guidelines</h5>
                        <p class="text-muted small">
                            Activities are bookable experiences shown on the website (e.g. ATV Ride, Rafting, Jeep Sunrise).
                        </p>
                        <ul class="text-muted small ps-3">
                            <li class="mb-2"><strong>Price per Pax</strong>: Base price per person</li>
                            <li class="mb-2"><strong>Min/Max Pax</strong>: Booking participant limits</li>
                            <li class="mb-2"><strong>Includes/Excludes</strong>: One item per line</li>
                            <li class="mb-2"><strong>Sort Order</strong>: Lower = shown first</li>
                        </ul>
                        <hr>
                        <p class="text-muted small mb-0">
                            After creating an activity, update the "Book" button link in <strong>Admin → Services</strong> to point to <code>/activities/{slug}</code>.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
