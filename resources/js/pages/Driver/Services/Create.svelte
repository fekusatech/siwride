<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router, useForm } from '@inertiajs/svelte';

    let { service = null } = $props();
    let isEdit = $derived(!!service);

    function arrayToLines(arr: string[] | null | undefined): string {
        if (!arr || !Array.isArray(arr)) return '';
        return arr.join('\n');
    }

    let form = useForm({
        title: service?.title || '',
        description: service?.description || '',
        price_per_pax: service?.price_per_pax || '',
        min_pax: service?.min_pax || 1,
        max_pax: service?.max_pax || '',
        duration_label: service?.duration_label || '',
        meeting_point: service?.meeting_point || '',
        includes: arrayToLines(service?.includes),
        excludes: arrayToLines(service?.excludes),
        highlights: arrayToLines(service?.highlights),
        gallery: [] as File[],
    });

    type GalleryItem =
        | { kind: 'existing'; path: string; url: string }
        | { kind: 'new'; file: File; url: string };

    let galleryItems = $state<GalleryItem[]>(
        (service?.gallery ?? []).map((path: string, i: number) => ({
            kind: 'existing' as const,
            path,
            url: service?.gallery_urls?.[i] ?? path,
        })),
    );
    let draggedIndex = $state<number | null>(null);

    function handleGalleryChange(event: Event) {
        const input = event.target as HTMLInputElement;
        if (input.files) {
            for (const file of Array.from(input.files)) {
                galleryItems.push({ kind: 'new', file, url: URL.createObjectURL(file) });
            }
        }
        input.value = '';
    }

    function removeGalleryItem(index: number) {
        galleryItems.splice(index, 1);
    }

    function moveGalleryItem(index: number, direction: -1 | 1) {
        const target = index + direction;
        if (target < 0 || target >= galleryItems.length) return;
        const [item] = galleryItems.splice(index, 1);
        galleryItems.splice(target, 0, item);
    }

    function handleDrop(index: number) {
        if (draggedIndex === null || draggedIndex === index) return;
        const [item] = galleryItems.splice(draggedIndex, 1);
        galleryItems.splice(index, 0, item);
        draggedIndex = null;
    }

    function submit() {
        const existing_gallery: string[] = [];
        const gallery: File[] = [];
        const gallery_order: string[] = [];

        for (const item of galleryItems) {
            if (item.kind === 'existing') {
                gallery_order.push(`e${existing_gallery.length}`);
                existing_gallery.push(item.path);
            } else {
                gallery_order.push(`n${gallery.length}`);
                gallery.push(item.file);
            }
        }

        if (isEdit) {
            router.post(`/driver/services/${service.id}`, {
                _method: 'put',
                title: form.title,
                description: form.description,
                price_per_pax: form.price_per_pax,
                min_pax: form.min_pax,
                max_pax: form.max_pax,
                duration_label: form.duration_label,
                meeting_point: form.meeting_point,
                includes: form.includes,
                excludes: form.excludes,
                highlights: form.highlights,
                gallery,
                existing_gallery,
                gallery_order,
            }, { preserveScroll: true, forceFormData: true });
        } else {
            form.gallery = gallery;
            form.post('/driver/services', { preserveScroll: true, forceFormData: true });
        }
    }
</script>

<AppHead title={isEdit ? 'Edit Service' : 'New Service'} />

<DriverLayout>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">{isEdit ? 'Edit Service' : 'New Service'}</h4>
        <Link href="/driver/services" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to My Services
        </Link>
    </div>

    {#if isEdit && service.status !== 'pending'}
        <div class="alert alert-info">Saving changes will send this service back for review.</div>
    {/if}

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form onsubmit={(e) => { e.preventDefault(); submit(); }}>
                <div class="mb-3">
                    <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" bind:value={form.title} placeholder="e.g. Sunrise Trekking with Local Driver" required>
                    {#if form.errors.title}<div class="text-danger mt-1 small">{form.errors.title}</div>{/if}
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
                        <input type="text" class="form-control" id="meeting_point" bind:value={form.meeting_point} placeholder="e.g. Ubud Center">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="includes">Includes (one per line)</label>
                    <textarea class="form-control" id="includes" rows="4" bind:value={form.includes} placeholder="Bottled water&#10;Local guide"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="excludes">Excludes (one per line)</label>
                    <textarea class="form-control" id="excludes" rows="3" bind:value={form.excludes} placeholder="Personal insurance&#10;Tips"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="highlights">Highlights (one per line)</label>
                    <textarea class="form-control" id="highlights" rows="3" bind:value={form.highlights} placeholder="Scenic sunrise view&#10;Suitable for beginners"></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label" for="gallery">Photos</label>
                    <input type="file" class="form-control" id="gallery" accept="image/*" multiple onchange={handleGalleryChange}>
                    <small class="text-muted d-block mt-1">
                        Drag photos to reorder, or use the arrows. The first photo is used as the cover/thumbnail.
                    </small>
                    {#if form.errors.gallery}<div class="text-danger mt-1 small">{form.errors.gallery}</div>{/if}

                    {#if galleryItems.length}
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            {#each galleryItems as item, index (item.url)}
                                <div
                                    class="position-relative"
                                    draggable="true"
                                    role="group"
                                    aria-label="Photo {index + 1}"
                                    ondragstart={() => (draggedIndex = index)}
                                    ondragover={(e) => e.preventDefault()}
                                    ondrop={() => handleDrop(index)}
                                    style="cursor: grab;"
                                >
                                    <img
                                        src={item.url}
                                        alt="Service photo {index + 1}"
                                        class="img-thumbnail"
                                        style="width: 110px; height: 110px; object-fit: cover; {index === 0 ? 'border-color: var(--bs-primary); border-width: 2px;' : ''}"
                                    />
                                    {#if index === 0}
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-1">Thumbnail</span>
                                    {/if}
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger position-absolute top-0 end-0"
                                        style="padding: 0 6px; line-height: 1.5;"
                                        onclick={() => removeGalleryItem(index)}
                                        aria-label="Remove photo"
                                    >&times;</button>
                                    <div class="d-flex justify-content-between position-absolute bottom-0 start-0 end-0 p-1">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border"
                                            style="padding: 0 6px; line-height: 1.4; opacity: {index === 0 ? 0.3 : 1};"
                                            onclick={() => moveGalleryItem(index, -1)}
                                            disabled={index === 0}
                                            aria-label="Move left"
                                        >‹</button>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-light border"
                                            style="padding: 0 6px; line-height: 1.4; opacity: {index === galleryItems.length - 1 ? 0.3 : 1};"
                                            onclick={() => moveGalleryItem(index, 1)}
                                            disabled={index === galleryItems.length - 1}
                                            aria-label="Move right"
                                        >›</button>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-end gap-2">
                    <Link href="/driver/services" class="btn btn-light">Cancel</Link>
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
</DriverLayout>
