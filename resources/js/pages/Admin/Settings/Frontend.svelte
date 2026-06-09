<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, page } from '@inertiajs/svelte';

    let { settings } = $derived(page.props);

    let activeTab = $state('company');

    // form
    // svelte-ignore state_referenced_locally
    const form = useForm({
        
        company_phone: settings.company_phone || '',
        company_email: settings.company_email || '',
        company_address: settings.company_address || '',
        company_facebook: settings.company_facebook || '',
        company_twitter: settings.company_twitter || '',
        company_instagram: settings.company_instagram || '',
        company_linkedin: settings.company_linkedin || '',

        hero_welcome_text: settings.hero_welcome_text || '',
        hero_title: settings.hero_title || '',
        hero_subtitle: settings.hero_subtitle || '',

        coverage_area_title: settings.coverage_area_title || '',
        coverage_area_image: null as File | string | null,

        destinations_title: settings.destinations_title || '',
        destinations_subtitle: settings.destinations_subtitle || '',
        popular_destinations: JSON.parse(JSON.stringify(settings.popular_destinations || [])),

        why_choose_us_title: settings.why_choose_us_title || '',
        why_choose_us_subtitle: settings.why_choose_us_subtitle || '',
        why_choose_us_text: settings.why_choose_us_text || '',
        why_choose_us_passenger_count: settings.why_choose_us_passenger_count || '',
        why_choose_us_features: JSON.parse(JSON.stringify(settings.why_choose_us_features || [])),

        customer_testimonials: JSON.parse(JSON.stringify(settings.customer_testimonials || [])),

        faq_items: JSON.parse(JSON.stringify(settings.faq_items || [])),
        terms_content: settings.terms_content || '',
        privacy_content: settings.privacy_content || '',
        booking_extras: JSON.parse(JSON.stringify(settings.booking_extras || [])),
    });

    let coveragePreview = $state<string | null>(settings.coverage_area_image || null);

    // Image previews
    function handleImageUpload(e: Event, type: 'coverage') {
        const file = (e.target as HTMLInputElement).files?.[0];
        if (file) {
            if (type === 'coverage') {
                form.coverage_area_image = file;
                const reader = new FileReader();
                reader.onload = (e) => (coveragePreview = e.target?.result as string);
                reader.readAsDataURL(file);
            }
        }
    }

    // Helper for adding/removing array items
    function addItem(arrayName: string, template: any) {
        (form as any)[arrayName] = [...(form as any)[arrayName], { ...template }];
    }

    function removeItem(arrayName: string, index: number) {
        (form as any)[arrayName] = (form as any)[arrayName].filter((_: any, i: number) => i !== index);
    }

    function handleArrayImage(e: Event, arrayName: string, index: number) {
        const file = (e.target as HTMLInputElement).files?.[0];
        if (file) {
            (form as any)[arrayName][index].img = file;
        }
    }

    function submit(e: Event) {
        e.preventDefault();
        form.post('/admin/settings/frontend', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                // Reset file inputs if needed or just keep current state
            }
        });
    }
</script>

<AppHead title="Frontend Settings" />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Frontend Content Settings</h4>
                <p class="text-muted mb-0">Manage texts, images, and content displayed to customers.</p>
            </div>
            <button class="btn btn-primary" onclick={submit} disabled={form.processing}>
                {#if form.processing}
                    <span class="spinner-border spinner-border-sm me-1"></span> Saving...
                {:else}
                    <i class="ti ti-device-floppy me-1"></i> Save All Changes
                {/if}
            </button>
        </div>

        {#if page.props.flash?.success}
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ti ti-circle-check me-2"></i>
                {page.props.flash.success}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        {/if}

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom p-0">
                <ul class="nav nav-tabs nav-tabs-custom border-0 px-3" role="tablist">
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'company' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'company'; }}>Company Info</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'hero' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'hero'; }}>Hero Section</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'destinations' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'destinations'; }}>Destinations</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'why' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'why'; }}>Why Choose Us</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'testimonials' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'testimonials'; }}>Testimonials</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'coverage' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'coverage'; }}>Coverage Area</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'faq' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'faq'; }}>FAQ</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'legal' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'legal'; }}>Legal Pages</button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link {activeTab === 'extras' ? 'active' : ''} py-3" onclick={(e) => { e.preventDefault(); activeTab = 'extras'; }}>Booking Extras</button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body p-4">
                <form onsubmit={submit}>

                    <!-- COMPANY INFO -->
                    {#if activeTab === 'company'}
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" bind:value={form.company_phone} />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" bind:value={form.company_email} />
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" bind:value={form.company_address} />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Facebook URL</label>
                                <input type="text" class="form-control" bind:value={form.company_facebook} />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Twitter URL</label>
                                <input type="text" class="form-control" bind:value={form.company_twitter} />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Instagram URL</label>
                                <input type="text" class="form-control" bind:value={form.company_instagram} />
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">LinkedIn URL</label>
                                <input type="text" class="form-control" bind:value={form.company_linkedin} />
                            </div>
                        </div>
                    {/if}

                    <!-- HERO -->
                    {#if activeTab === 'hero'}
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Welcome Text (Small text above title)</label>
                                <input type="text" class="form-control" bind:value={form.hero_welcome_text} />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Main Title</label>
                                <input type="text" class="form-control" bind:value={form.hero_title} />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Subtitle / Description</label>
                                <textarea class="form-control" rows="3" bind:value={form.hero_subtitle}></textarea>
                            </div>
                        </div>
                    {/if}

                    <!-- DESTINATIONS -->
                    {#if activeTab === 'destinations'}
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Section Title</label>
                                <input type="text" class="form-control" bind:value={form.destinations_title} />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Section Subtitle</label>
                                <input type="text" class="form-control" bind:value={form.destinations_subtitle} />
                            </div>
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Destination List</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick={() => addItem('popular_destinations', {name: '', location: '', img: null})}>+ Add Destination</button>
                        </div>
                        <div class="row g-4">
                            {#each form.popular_destinations as dest, i}
                                <div class="col-md-4">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold">Dest {i + 1}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick={() => removeItem('popular_destinations', i)}><i class="ti ti-trash"></i></button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Name</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={dest.name} />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Location</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={dest.location} />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Image</label>
                                                <input type="file" class="form-control form-control-sm" accept="image/*" onchange={(e) => handleArrayImage(e, 'popular_destinations', i)} />
                                                {#if typeof dest.img === 'string' && dest.img}
                                                    <small class="text-muted d-block mt-1 text-truncate">Current: {dest.img.split('/').pop()}</small>
                                                {/if}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    <!-- WHY CHOOSE US -->
                    {#if activeTab === 'why'}
                        <div class="row g-3 mb-4">
                            <div class="col-md-12">
                                <label class="form-label">Section Title</label>
                                <input type="text" class="form-control" bind:value={form.why_choose_us_title} />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Section Subtitle</label>
                                <input type="text" class="form-control" bind:value={form.why_choose_us_subtitle} />
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Section Text</label>
                                <textarea class="form-control" rows="3" bind:value={form.why_choose_us_text}></textarea>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Passenger Count Highlight (e.g. 10k+)</label>
                                <input type="text" class="form-control" bind:value={form.why_choose_us_passenger_count} />
                            </div>
                        </div>
                        <hr />
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Features (Tick marks)</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick={() => addItem('why_choose_us_features', {title: '', text: '', icon: ''})}>+ Add Feature</button>
                        </div>
                        <div class="row g-4">
                            {#each form.why_choose_us_features as feat, i}
                                <div class="col-md-6">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold">Feature {i + 1}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick={() => removeItem('why_choose_us_features', i)}>Remove</button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Title</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={feat.title} />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Text</label>
                                                <textarea class="form-control form-control-sm" rows="2" bind:value={feat.text}></textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Icon Class</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={feat.icon} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    <!-- TESTIMONIALS -->
                    {#if activeTab === 'testimonials'}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Testimonials List</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick={() => addItem('customer_testimonials', {name: '', country: '', comment: '', rating: 5, img: null})}>+ Add Testimonial</button>
                        </div>
                        <div class="row g-4">
                            {#each form.customer_testimonials as testm, i}
                                <div class="col-md-6">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold">Testimonial {i + 1}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick={() => removeItem('customer_testimonials', i)}><i class="ti ti-trash"></i></button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Name</label>
                                                    <input type="text" class="form-control form-control-sm" bind:value={testm.name} />
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Country</label>
                                                    <input type="text" class="form-control form-control-sm" bind:value={testm.country} />
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Comment</label>
                                                <textarea class="form-control form-control-sm" rows="2" bind:value={testm.comment}></textarea>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Rating (1-5)</label>
                                                    <input type="number" min="1" max="5" class="form-control form-control-sm" bind:value={testm.rating} />
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Avatar Image</label>
                                                    <input type="file" class="form-control form-control-sm" accept="image/*" onchange={(e) => handleArrayImage(e, 'customer_testimonials', i)} />
                                                    {#if typeof testm.img === 'string' && testm.img}
                                                        <small class="text-muted d-block mt-1 text-truncate">Current: {testm.img.split('/').pop()}</small>
                                                    {/if}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    <!-- COVERAGE AREA -->
                    {#if activeTab === 'coverage'}
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Section Title</label>
                                <input type="text" class="form-control" bind:value={form.coverage_area_title} />
                            </div>
                            <div class="col-md-12 mt-4">
                                <label class="form-label d-block">Coverage Area Image</label>
                                {#if coveragePreview}
                                    <img src={coveragePreview} alt="Coverage Area" class="img-fluid border rounded mb-3" style="max-height: 300px;" />
                                {/if}
                                <input type="file" class="form-control" accept="image/*" onchange={(e) => handleImageUpload(e, 'coverage')} />
                            </div>
                        </div>
                    {/if}

                    <!-- FAQ -->
                    {#if activeTab === 'faq'}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Frequently Asked Questions</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick={() => addItem('faq_items', {question: '', answer: ''})}>+ Add FAQ</button>
                        </div>
                        <div class="row g-4">
                            {#each form.faq_items as faq, i}
                                <div class="col-md-6">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold">FAQ {i + 1}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick={() => removeItem('faq_items', i)}><i class="ti ti-trash"></i></button>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Question</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={faq.question} />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Answer</label>
                                                <textarea class="form-control form-control-sm" rows="3" bind:value={faq.answer}></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}

                    <!-- LEGAL PAGES -->
                    {#if activeTab === 'legal'}
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Terms and Conditions Content (HTML allowed)</label>
                                <textarea class="form-control" rows="8" bind:value={form.terms_content}></textarea>
                            </div>
                            <div class="col-md-12 mt-4">
                                <label class="form-label">Privacy Policy Content (HTML allowed)</label>
                                <textarea class="form-control" rows="8" bind:value={form.privacy_content}></textarea>
                            </div>
                        </div>
                    {/if}

                    <!-- BOOKING EXTRAS -->
                    {#if activeTab === 'extras'}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Booking Extras</h5>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick={() => addItem('booking_extras', {id: '', label: '', description: '', price: 0})}>+ Add Extra</button>
                        </div>
                        <div class="row g-4">
                            {#each form.booking_extras as extra, i}
                                <div class="col-md-6">
                                    <div class="card border mb-0">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold">Extra {i + 1}</span>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1" onclick={() => removeItem('booking_extras', i)}><i class="ti ti-trash"></i></button>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Unique ID (e.g. english_driver)</label>
                                                    <input type="text" class="form-control form-control-sm" bind:value={extra.id} />
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fs-12">Label</label>
                                                    <input type="text" class="form-control form-control-sm" bind:value={extra.label} />
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Description</label>
                                                <input type="text" class="form-control form-control-sm" bind:value={extra.description} />
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fs-12">Price (IDR)</label>
                                                <input type="number" class="form-control form-control-sm" bind:value={extra.price} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            {/each}
                        </div>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
