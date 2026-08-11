<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, router, useForm } from '@inertiajs/svelte';

    let { article = null, activities = [] } = $props();
    let isEdit = $derived(!!article);

    let form = useForm({
        title: article?.title || '',
        excerpt: article?.excerpt || '',
        content: article?.content || '',
        activity_id: article?.activity_id || '',
        gallery: [] as File[],
    });

    // One ordered photo list — existing (already-uploaded) + newly picked
    // files, reorderable by drag-and-drop. First photo = cover image.
    type GalleryItem =
        | { kind: 'existing'; path: string; url: string }
        | { kind: 'new'; file: File; url: string };

    let galleryItems = $state<GalleryItem[]>(
        (article?.gallery ?? []).map((path: string, i: number) => ({
            kind: 'existing' as const,
            path,
            url: article?.gallery_urls?.[i] ?? path,
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
            router.post(`/driver/articles/${article.id}`, {
                _method: 'put',
                title: form.title,
                excerpt: form.excerpt,
                content: form.content,
                activity_id: form.activity_id,
                gallery,
                existing_gallery,
                gallery_order,
            }, { preserveScroll: true, forceFormData: true });
        } else {
            form.gallery = gallery;
            form.post('/driver/articles', { preserveScroll: true, forceFormData: true });
        }
    }
</script>

<AppHead title={isEdit ? 'Edit Article' : 'New Article'} />

<DriverLayout>
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h4 class="fw-bold mb-0">{isEdit ? 'Edit Article' : 'Write a New Article'}</h4>
        <Link href="/driver/articles" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left me-1"></i> Back to My Articles
        </Link>
    </div>

    {#if isEdit && article.status === 'rejected'}
        <div class="alert alert-danger">
            <strong>This article was rejected.</strong>
            {#if article.rejection_reason}<div class="mt-1">{article.rejection_reason}</div>{/if}
            <div class="mt-1 small">Saving changes will resubmit it for review.</div>
        </div>
    {/if}

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form onsubmit={(e) => { e.preventDefault(); submit(); }}>
                        <div class="mb-3">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" bind:value={form.title} placeholder="e.g. A Day Trip Around Ubud" required>
                            {#if form.errors.title}<div class="text-danger mt-1 small">{form.errors.title}</div>{/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="excerpt">Short Summary</label>
                            <input type="text" class="form-control" id="excerpt" bind:value={form.excerpt} placeholder="One sentence that shows up in the guide list">
                            {#if form.errors.excerpt}<div class="text-danger mt-1 small">{form.errors.excerpt}</div>{/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="content">Content <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" rows="10" bind:value={form.content} placeholder="Share your route, tips, and recommendations..." required></textarea>
                            {#if form.errors.content}<div class="text-danger mt-1 small">{form.errors.content}</div>{/if}
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="activity_id">Link a Bookable Tour (optional)</label>
                            <select class="form-select" id="activity_id" bind:value={form.activity_id}>
                                <option value="">None</option>
                                {#each activities as act}
                                    <option value={act.id}>{act.title}</option>
                                {/each}
                            </select>
                            <small class="text-muted d-block mt-1">If your article is about a specific tour, link it here so readers can book it directly.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="gallery">Photos</label>
                            <input type="file" class="form-control" id="gallery" accept="image/*" multiple onchange={handleGalleryChange}>
                            <small class="text-muted d-block mt-1">Drag to reorder — the first photo becomes the cover image.</small>
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
                                                alt="Article photo {index + 1}"
                                                class="img-thumbnail"
                                                style="width: 110px; height: 110px; object-fit: cover; {index === 0 ? 'border-color: var(--bs-primary); border-width: 2px;' : ''}"
                                            />
                                            {#if index === 0}
                                                <span class="badge bg-primary position-absolute top-0 start-0 m-1">Cover</span>
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
                            <Link href="/driver/articles" class="btn btn-light">Cancel</Link>
                            <button type="submit" class="btn btn-primary" disabled={form.processing}>
                                {#if form.processing}
                                    <i class="ti ti-loader ti-spin me-1"></i> Saving...
                                {:else}
                                    <i class="ti ti-device-floppy me-1"></i> {isEdit ? 'Save Changes' : 'Submit for Review'}
                                {/if}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Writing Guidelines</h5>
                    <ul class="text-muted small ps-3">
                        <li class="mb-2">Every article is reviewed by our team before it goes live.</li>
                        <li class="mb-2">Once published, your article gets its own page — share that link anywhere.</li>
                        <li class="mb-2">If someone books a tour after visiting your article, you earn a referral commission automatically.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</DriverLayout>
