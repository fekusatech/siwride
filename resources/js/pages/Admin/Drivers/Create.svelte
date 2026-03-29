<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, Link } from '@inertiajs/svelte';

    let { driver = null } = $props();

    // svelte-ignore state_referenced_locally
    const form = useForm({
        _method: driver ? 'put' : 'post',
        name: driver?.name ?? '',
        email: driver?.email ?? '',
        phone: driver?.phone ?? '',
        password: '',
        status: driver?.status ?? 'active',
        nik: driver?.nik ?? '',
        nik_image: null as File | null,
        sim: driver?.sim ?? '',
        sim_image: null as File | null,
        
        // Vehicle fields
        add_vehicle: false,
        v_brand: '',
        v_model: '',
        v_type: 'MPV',
        v_registration_number: '',
        v_color: '',
    });

    function submit(e: Event) {
        e.preventDefault();
        if (driver) {
            form.post(`/admin/drivers/${driver.id}`);
        } else {
            form.post('/admin/drivers');
        }
    }
</script>

<AppHead title={driver ? 'Edit Driver' : 'Add Driver'} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link href="/admin/drivers" class="text-primary d-inline-flex align-items-center gap-1 mb-2">
                <i class="ti ti-arrow-left"></i> Back to List
            </Link>
            <h4 class="mb-0">{driver ? 'Edit Driver' : 'Add Driver'}</h4>
        </div>

        <div class="card">
            <div class="card-body">
                <form onsubmit={submit}>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label text-uppercase fs-12 fw-bold text-muted">Full Name</label>
                                <input type="text" id="name" class="form-control" bind:value={form.name} required disabled={form.processing} placeholder="Enter full name">
                                {#if form.errors.name}<div class="text-danger small mt-1">{form.errors.name}</div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label text-uppercase fs-12 fw-bold text-muted">Email Address</label>
                                <input type="email" id="email" class="form-control" bind:value={form.email} required disabled={form.processing} placeholder="Enter email address">
                                {#if form.errors.email}<div class="text-danger small mt-1">{form.errors.email}</div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="phone" class="form-label text-uppercase fs-12 fw-bold text-muted">Phone Number</label>
                                <input type="text" id="phone" class="form-control" bind:value={form.phone} required disabled={form.processing} placeholder="Enter phone number">
                                {#if form.errors.phone}<div class="text-danger small mt-1">{form.errors.phone}</div>{/if}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label text-uppercase fs-12 fw-bold text-muted">Password {driver ? '(Leave blank to stay same)' : ''}</label>
                                <input type="password" id="password" class="form-control" bind:value={form.password} required={!driver} disabled={form.processing} placeholder="Enter password">
                                {#if form.errors.password}<div class="text-danger small mt-1">{form.errors.password}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr class="my-4 border-dashed">
                            <h5 class="mb-3 fs-14 text-uppercase fw-bold">Identification Documents</h5>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="nik" class="form-label text-uppercase fs-12 fw-bold text-muted">NIK Number <small class="text-lowercase fw-normal">(Optional)</small></label>
                                <input type="text" id="nik" class="form-control" bind:value={form.nik} disabled={form.processing} placeholder="Enter NIK (National ID)">
                                {#if form.errors.nik}<div class="text-danger small mt-1">{form.errors.nik}</div>{/if}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="nik_image" class="form-label text-uppercase fs-12 fw-bold text-muted">NIK Image <small class="text-lowercase fw-normal">(Optional)</small></label>
                                <div 
                                    class="border-dashed py-3 rounded text-center mb-2 bg-light bg-opacity-10" 
                                    style="cursor: pointer; border: 2px dashed #dee2e6;"
                                    onclick={() => (document.getElementById('nik_image') as HTMLInputElement).click()}
                                    onkeydown={(e) => e.key === 'Enter' && (document.getElementById('nik_image') as HTMLInputElement).click()}
                                    role="button"
                                    tabindex="0"
                                >
                                    <i class="ti ti-cloud-upload fs-32 text-muted"></i>
                                    <h5 class="mt-2 mb-1 fs-13 fw-bold text-primary">Upload NIK Image</h5>
                                    {#if form.nik_image}
                                        <div class="mt-1 badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="ti ti-check fs-10"></i> {form.nik_image.name}
                                        </div>
                                    {:else}
                                        <p class="text-muted fs-11 mb-0">Drag & drop or click</p>
                                    {/if}
                                </div>
                                <input type="file" id="nik_image" class="d-none" onchange={(e) => {
                                    const target = e.target as HTMLInputElement;
                                    if (target.files) form.nik_image = target.files[0];
                                }} disabled={form.processing} accept="image/*">
                                
                                {#if driver?.nik_image}
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <a href={`/storage/${driver.nik_image}`} target="_blank" class="text-primary fs-11 fw-medium text-decoration-underline">
                                            <i class="ti ti-photo fs-14"></i> View Current NIK
                                        </a>
                                    </div>
                                {/if}
                                {#if form.errors.nik_image}<div class="text-danger small mt-1">{form.errors.nik_image}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="sim" class="form-label text-uppercase fs-12 fw-bold text-muted">SIM Number <small class="text-lowercase fw-normal">(Optional)</small></label>
                                <input type="text" id="sim" class="form-control" bind:value={form.sim} disabled={form.processing} placeholder="Enter SIM (Driver's License)">
                                {#if form.errors.sim}<div class="text-danger small mt-1">{form.errors.sim}</div>{/if}
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="sim_image" class="form-label text-uppercase fs-12 fw-bold text-muted">SIM Image <small class="text-lowercase fw-normal">(Optional)</small></label>
                                <div 
                                    class="border-dashed py-3 rounded text-center mb-2 bg-light bg-opacity-10" 
                                    style="cursor: pointer; border: 2px dashed #dee2e6;"
                                    onclick={() => (document.getElementById('sim_image') as HTMLInputElement).click()}
                                    onkeydown={(e) => e.key === 'Enter' && (document.getElementById('sim_image') as HTMLInputElement).click()}
                                    role="button"
                                    tabindex="0"
                                >
                                    <i class="ti ti-cloud-upload fs-32 text-muted"></i>
                                    <h5 class="mt-2 mb-1 fs-13 fw-bold text-primary">Upload SIM Image</h5>
                                    {#if form.sim_image}
                                        <div class="mt-1 badge bg-success-subtle text-success border border-success border-opacity-25 px-2 py-1">
                                            <i class="ti ti-check fs-10"></i> {form.sim_image.name}
                                        </div>
                                    {:else}
                                        <p class="text-muted fs-11 mb-0">Drag & drop or click</p>
                                    {/if}
                                </div>
                                <input type="file" id="sim_image" class="d-none" onchange={(e) => {
                                    const target = e.target as HTMLInputElement;
                                    if (target.files) form.sim_image = target.files[0];
                                }} disabled={form.processing} accept="image/*">
                                
                                {#if driver?.sim_image}
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <a href={`/storage/${driver.sim_image}`} target="_blank" class="text-primary fs-11 fw-medium text-decoration-underline">
                                            <i class="ti ti-photo fs-14"></i> View Current SIM
                                        </a>
                                    </div>
                                {/if}
                                {#if form.errors.sim_image}<div class="text-danger small mt-1">{form.errors.sim_image}</div>{/if}
                            </div>
                        </div>

                        <div class="col-md-12">
                            <hr class="my-4 border-dashed">
                        </div>

                        {#if driver}
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <span class="form-label d-block text-uppercase fs-12 fw-bold text-muted mb-2">Account Status</span>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="status_active" name="status" class="form-check-input" value="active" bind:group={form.status} disabled={form.processing}>
                                        <label class="form-check-label fw-medium" for="status_active">Active</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input type="radio" id="status_inactive" name="status" class="form-check-input" value="inactive" bind:group={form.status} disabled={form.processing}>
                                        <label class="form-check-label fw-medium" for="status_inactive">Inactive</label>
                                    </div>
                                    {#if form.errors.status}<div class="text-danger small mt-1">{form.errors.status}</div>{/if}
                                </div>
                            </div>
                        {/if}

                        {#if !driver}
                            <div class="col-md-12 mt-2">
                                <div class="card bg-light bg-opacity-10 border-dashed">
                                    <div class="card-body p-3">
                                        <div class="form-check form-switch mb-0">
                                            <input type="checkbox" id="add_vehicle" class="form-check-input" bind:checked={form.add_vehicle}>
                                            <label class="form-check-label fw-bold text-uppercase fs-12" for="add_vehicle">Add Vehicle for this Driver now?</label>
                                        </div>

                                        {#if form.add_vehicle}
                                            <div class="row mt-3">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="v_registration_number" class="form-label text-uppercase fs-11 fw-bold text-muted">Vehicle Plat Number</label>
                                                        <input type="text" id="v_registration_number" class="form-control" bind:value={form.v_registration_number} placeholder="e.g. B 1234 ABC">
                                                        {#if form.errors.v_registration_number}<div class="text-danger small mt-1">{form.errors.v_registration_number}</div>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="v_brand" class="form-label text-uppercase fs-11 fw-bold text-muted">Brand</label>
                                                        <input type="text" id="v_brand" class="form-control" bind:value={form.v_brand} placeholder="e.g. Toyota">
                                                        {#if form.errors.v_brand}<div class="text-danger small mt-1">{form.errors.v_brand}</div>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label for="v_model" class="form-label text-uppercase fs-11 fw-bold text-muted">Model</label>
                                                        <input type="text" id="v_model" class="form-control" bind:value={form.v_model} placeholder="e.g. Avanza">
                                                        {#if form.errors.v_model}<div class="text-danger small mt-1">{form.errors.v_model}</div>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="v_type" class="form-label text-uppercase fs-11 fw-bold text-muted">Type</label>
                                                        <select id="v_type" class="form-select" bind:value={form.v_type}>
                                                            <option value="Sedan">Sedan</option>
                                                            <option value="SUV">SUV</option>
                                                            <option value="MPV">MPV</option>
                                                            <option value="Van">Van</option>
                                                        </select>
                                                        {#if form.errors.v_type}<div class="text-danger small mt-1">{form.errors.v_type}</div>{/if}
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-3">
                                                        <label for="v_color" class="form-label text-uppercase fs-11 fw-bold text-muted">Color</label>
                                                        <input type="text" id="v_color" class="form-control" bind:value={form.v_color} placeholder="e.g. White">
                                                        {#if form.errors.v_color}<div class="text-danger small mt-1">{form.errors.v_color}</div>{/if}
                                                    </div>
                                                </div>
                                            </div>
                                        {/if}
                                    </div>
                                </div>
                            </div>
                        {/if}
                    </div>

                    <div class="mt-4 border-top pt-3 text-end">
                        <button type="submit" class="btn btn-primary d-inline-flex align-items-center gap-1 px-4" disabled={form.processing}>
                             <i class="ti ti-device-floppy fs-18"></i>
                             {driver ? 'Update Driver' : 'Save Driver'}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
