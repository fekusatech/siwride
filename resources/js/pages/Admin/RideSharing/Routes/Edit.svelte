<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link, useForm, router } from '@inertiajs/svelte';

    let { routeData, cities, vehicleCategories } = $props();

    let activeTab = $state('settings');

    // -- SETTINGS TAB --
    let settingsForm = useForm({
        name: routeData.name,
        is_active: routeData.is_active,
    });
    function saveSettings(e: Event) {
        e.preventDefault();
        settingsForm.put(`/admin/rs-routes/${routeData.id}`);
    }

    // -- PATHS TAB --
    let pathsForm = useForm({
        paths: routeData.paths.map((p: any) => ({ city_id: p.city_id, name: p.city.name, address: p.city.address }))
    });
    let selectedCityId = $state('');

    function addCityToPath() {
        if (!selectedCityId) return;
        const city = cities.find((c: any) => c.id == selectedCityId);
        if (!city) return;
        
        // Prevent duplicate
        if (pathsForm.paths.find((p: any) => p.city_id == selectedCityId)) {
            alert('City is already in the route path');
            return;
        }

        pathsForm.paths = [...pathsForm.paths, { city_id: city.id, name: city.name, address: city.address }];
        selectedCityId = '';
    }

    function removeCityFromPath(index: number) {
        if (confirm('Removing a city will also clear any prices associated with it. Continue?')) {
            pathsForm.paths = pathsForm.paths.filter((_, i) => i !== index);
        }
    }

    function movePathUp(index: number) {
        if (index === 0) return;
        const newPaths = [...pathsForm.paths];
        const temp = newPaths[index - 1];
        newPaths[index - 1] = newPaths[index];
        newPaths[index] = temp;
        pathsForm.paths = newPaths;
    }

    function movePathDown(index: number) {
        if (index === pathsForm.paths.length - 1) return;
        const newPaths = [...pathsForm.paths];
        const temp = newPaths[index + 1];
        newPaths[index + 1] = newPaths[index];
        newPaths[index] = temp;
        pathsForm.paths = newPaths;
    }

    function savePaths() {
        pathsForm.put(`/admin/rs-routes/${routeData.id}/paths`);
    }

    // -- PRICES --
    // We dynamically generate the matrix based on SAVED paths to display inside the schedule form.
    function generateEmptyMatrix() {
        const savedPaths = routeData.paths; // Use saved paths from DB
        const matrix = [];
        for (let i = 0; i < savedPaths.length; i++) {
            for (let j = i + 1; j < savedPaths.length; j++) {
                const fromCityId = savedPaths[i].city_id;
                const toCityId = savedPaths[j].city_id;

                matrix.push({
                    from_city_id: fromCityId,
                    from_name: savedPaths[i].city.name,
                    from_address: savedPaths[i].city.address,
                    to_city_id: toCityId,
                    to_name: savedPaths[j].city.name,
                    to_address: savedPaths[j].city.address,
                    price: '',
                    is_active: true
                });
            }
        }
        return matrix;
    }

    // -- SCHEDULES TAB --
    let defaultTimes = $state<Record<string, string>>({});
    $effect(() => {
        // Initialize default times once we have paths
        if (routeData.paths && Object.keys(defaultTimes).length === 0) {
            const times: Record<string, string> = {};
            routeData.paths.forEach((p: any) => {
                times[p.city_id] = '08:00';
            });
            defaultTimes = times;
            schedulesForm.departure_times = { ...times };
        }
    });

    let schedulesForm = useForm({
        route_id: routeData.id,
        vehicle_category_id: '',
        days: [] as string[],
        departure_times: {} as Record<string, string>,
        quota: 12,
        is_active: true,
        prices: generateEmptyMatrix()
    });

    let editingScheduleId = $state<number | null>(null);

    function editSchedule(schedule: any) {
        editingScheduleId = schedule.id;
        schedulesForm.vehicle_category_id = schedule.vehicle_category_id || '';
        schedulesForm.days = [...schedule.days];
        schedulesForm.departure_times = { ...schedule.departure_times };
        schedulesForm.quota = schedule.quota;
        
        // Populate existing prices for this schedule
        const matrix = generateEmptyMatrix();
        if (schedule.prices) {
            matrix.forEach((item, index) => {
                const existing = schedule.prices.find((p: any) => p.from_city_id == item.from_city_id && p.to_city_id == item.to_city_id);
                if (existing) {
                    matrix[index].price = existing.price;
                    matrix[index].is_active = existing.is_active;
                }
            });
        }
        schedulesForm.prices = matrix;
    }

    function cancelEditSchedule() {
        editingScheduleId = null;
        schedulesForm.reset('quota', 'vehicle_category_id');
        schedulesForm.days = [];
        schedulesForm.departure_times = { ...defaultTimes };
        schedulesForm.prices = generateEmptyMatrix();
        schedulesForm.clearErrors();
    }

    function saveSchedule(e: Event) {
        e.preventDefault();
        if (editingScheduleId) {
            schedulesForm.put(`/admin/rs-schedules/${editingScheduleId}`, {
                onSuccess: () => cancelEditSchedule()
            });
        } else {
            schedulesForm.post(`/admin/rs-schedules`, {
                onSuccess: () => cancelEditSchedule()
            });
        }
    }

    function deleteSchedule(id: number) {
        if (confirm('Delete this schedule?')) {
            router.delete(`/admin/rs-schedules/${id}`);
        }
    }
</script>

<AppHead title={`Manage Route: ${routeData.name}`} />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">Manage Route: <span class="text-primary">{routeData.name}</span></h4>
                <p class="text-muted mb-0">Configure paths, pricing matrix, and schedules</p>
            </div>
            <Link href="/admin/rs-routes" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="ti ti-arrow-left fs-18"></i>
                Back to Routes
            </Link>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-transparent border-bottom pt-3 pb-0">
                <ul class="nav nav-tabs nav-tabs-custom border-0" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {activeTab === 'settings' ? 'active' : ''}" onclick={() => activeTab = 'settings'}>
                            <i class="ti ti-settings me-1"></i> Settings
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {activeTab === 'paths' ? 'active' : ''}" onclick={() => activeTab = 'paths'}>
                            <i class="ti ti-map-2 me-1"></i> Paths (Urutan Kota)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {activeTab === 'schedules' ? 'active' : ''}" onclick={() => activeTab = 'schedules'}>
                            <i class="ti ti-clock me-1"></i> Schedules & Prices
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                {#if activeTab === 'settings'}
                    <div class="row">
                        <div class="col-lg-6">
                            <form onsubmit={saveSettings}>
                                <div class="mb-3">
                                    <label class="form-label" for="name">Route Name</label>
                                    <input type="text" class="form-control" id="name" bind:value={settingsForm.name} required />
                                </div>
                                <div class="mb-4">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" bind:checked={settingsForm.is_active} />
                                        <label class="form-check-label" for="is_active">Status Active</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary" disabled={settingsForm.processing}>
                                    { settingsForm.processing ? 'Saving...' : 'Update Settings' }
                                </button>
                            </form>
                        </div>
                    </div>
                {/if}

                {#if activeTab === 'paths'}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-4 bg-light p-3 rounded">
                                <label class="form-label">Add City to Route</label>
                                <div class="d-flex gap-2">
                                    <select class="form-select" bind:value={selectedCityId}>
                                        <option value="">-- Select City --</option>
                                        {#each cities as city}
                                            <option value={city.id}>{city.name}{city.address ? ` - ${city.address}` : ''}</option>
                                        {/each}
                                    </select>
                                    <button class="btn btn-success text-nowrap" type="button" onclick={addCityToPath}>
                                        <i class="ti ti-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            <h5 class="mb-3">Current Path Sequence</h5>
                            {#if pathsForm.paths.length === 0}
                                <div class="alert alert-info">No cities added yet. Add cities to define the route path.</div>
                            {:else}
                                <ul class="list-group mb-4">
                                    {#each pathsForm.paths as path, index}
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="badge bg-primary rounded-circle">{index + 1}</span>
                                                <span class="d-flex flex-column">
                                                    <span class="fw-bold fs-5">{path.name}</span>
                                                    {#if path.address}
                                                        <small class="text-muted">{path.address}</small>
                                                    {/if}
                                                </span>
                                            </div>
                                            <div class="d-flex gap-1">
                                                <button class="btn btn-sm btn-light" onclick={() => movePathUp(index)} disabled={index === 0}>
                                                    <i class="ti ti-arrow-up"></i>
                                                </button>
                                                <button class="btn btn-sm btn-light" onclick={() => movePathDown(index)} disabled={index === pathsForm.paths.length - 1}>
                                                    <i class="ti ti-arrow-down"></i>
                                                </button>
                                                <button class="btn btn-sm btn-danger ms-2" onclick={() => removeCityFromPath(index)}>
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            </div>
                                        </li>
                                    {/each}
                                </ul>
                            {/if}

                            <button class="btn btn-primary" onclick={savePaths} disabled={pathsForm.processing}>
                                <i class="ti ti-device-floppy"></i> {pathsForm.processing ? 'Saving...' : 'Save Paths'}
                            </button>
                        </div>
                        <div class="col-lg-6">
                            <div class="alert alert-warning">
                                <strong>Important:</strong> After updating the paths, you MUST verify the pricing inside the Schedules tab.
                            </div>
                        </div>
                    </div>
                {/if}



                {#if activeTab === 'schedules'}
                    <div class="row">
                        <div class="col-lg-7">
                            <h5 class="mb-3">{editingScheduleId ? 'Edit Schedule' : 'Add New Schedule'}</h5>
                            <form onsubmit={saveSchedule} class="bg-light p-3 rounded border">
                                <div class="mb-3">
                                    <label class="form-label d-block">Days Active <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-2">
                                        {#each ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as day}
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id={`day-${day}`} value={day} bind:group={schedulesForm.days} />
                                                <label class="form-check-label" for={`day-${day}`}>{day}</label>
                                            </div>
                                        {/each}
                                    </div>
                                    {#if schedulesForm.errors.days}
                                        <div class="text-danger small mt-1">{schedulesForm.errors.days}</div>
                                    {/if}
                                </div>
                                {#if routeData.paths.length === 0}
                                    <div class="alert alert-warning mb-3">Add paths first to define departure times.</div>
                                {:else}
                                    <div class="mb-3">
                                        <label class="form-label d-block">Departure Times per Point <span class="text-danger">*</span></label>
                                        {#each routeData.paths as path}
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <div class="d-flex flex-column text-truncate flex-grow-1">
                                                    <span>{path.city.name}</span>
                                                    {#if path.city.address}
                                                        <small class="text-muted fw-normal" style="font-size: 0.75em;">{path.city.address}</small>
                                                    {/if}
                                                </div>
                                                <input type="time" class="form-control form-control-sm" style="width: 120px;" bind:value={schedulesForm.departure_times[path.city_id]} required />
                                            </div>
                                        {/each}
                                        {#if schedulesForm.errors.departure_times}
                                            <div class="text-danger small mt-1">{schedulesForm.errors.departure_times}</div>
                                        {/if}
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="vehicle_category_id">Vehicle Category (Optional)</label>
                                        <select class="form-select" id="vehicle_category_id" bind:value={schedulesForm.vehicle_category_id}>
                                            <option value="">-- Select Vehicle --</option>
                                            {#each vehicleCategories as cat}
                                                <option value={cat.id}>{cat.title} ({cat.passenger_capacity} Seats)</option>
                                            {/each}
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="quota">Quota (Seats)</label>
                                        <input type="number" class="form-control" id="quota" bind:value={schedulesForm.quota} min="1" required />
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label d-block border-bottom pb-2">Pricing per City Pair <span class="text-danger">*</span></label>
                                        {#if schedulesForm.prices.length === 0}
                                            <div class="text-muted small">No city combinations available.</div>
                                        {:else}
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered align-middle mb-0">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th style="width: 40px;" class="text-center">Active</th>
                                                            <th>From</th>
                                                            <th>To</th>
                                                            <th style="width: 150px;">Price (Rp)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        {#each schedulesForm.prices as priceItem}
                                                            <tr>
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-flex justify-content-center m-0">
                                                                        <input class="form-check-input" type="checkbox" bind:checked={priceItem.is_active} />
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="small fw-bold {priceItem.is_active ? '' : 'text-muted'}">{priceItem.from_name}</div>
                                                                    {#if priceItem.from_address}
                                                                        <div class="text-muted" style="font-size: 0.7em;">{priceItem.from_address}</div>
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    <div class="small fw-bold {priceItem.is_active ? '' : 'text-muted'}">{priceItem.to_name}</div>
                                                                    {#if priceItem.to_address}
                                                                        <div class="text-muted" style="font-size: 0.7em;">{priceItem.to_address}</div>
                                                                    {/if}
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="form-control form-control-sm text-end" 
                                                                        value={priceItem.price ? new Intl.NumberFormat('id-ID').format(priceItem.price) : ''}
                                                                        oninput={(e) => {
                                                                            const raw = e.currentTarget.value.replace(/\D/g, '');
                                                                            priceItem.price = raw ? parseInt(raw, 10) : '';
                                                                            e.currentTarget.value = raw ? new Intl.NumberFormat('id-ID').format(parseInt(raw, 10)) : '';
                                                                        }}
                                                                        placeholder="0" required={priceItem.is_active} disabled={!priceItem.is_active} />
                                                                </td>
                                                            </tr>
                                                        {/each}
                                                    </tbody>
                                                </table>
                                            </div>
                                        {/if}
                                    </div>
                                {/if}
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success flex-grow-1" disabled={schedulesForm.processing || routeData.paths.length === 0}>
                                        <i class="ti {editingScheduleId ? 'ti-check' : 'ti-plus'}"></i> {editingScheduleId ? 'Save Changes' : 'Add Schedule'}
                                    </button>
                                    {#if editingScheduleId}
                                        <button type="button" class="btn btn-light border" onclick={cancelEditSchedule}>Cancel</button>
                                    {/if}
                                </div>
                            </form>
                        </div>

                        <div class="col-lg-5">
                            <h5 class="mb-3">Existing Schedules</h5>
                            {#if routeData.schedules.length === 0}
                                <div class="alert alert-info">No schedules added yet.</div>
                            {:else}
                                <div class="table-responsive">
                                    <table class="table table-hover border">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Times</th>
                                                <th>Days</th>
                                                <th>Quota & Price</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {#each routeData.schedules as schedule}
                                                <tr>
                                                    <td>
                                                        <ul class="list-unstyled mb-0 small">
                                                            {#each routeData.paths as path}
                                                                <li>
                                                                    <span class="text-muted">{path.city.name}:</span> 
                                                                    <span class="fw-bold">{schedule.departure_times && schedule.departure_times[path.city_id] ? schedule.departure_times[path.city_id].substring(0, 5) : '-'}</span>
                                                                </li>
                                                            {/each}
                                                        </ul>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex flex-wrap gap-1">
                                                            {#if schedule.days && Array.isArray(schedule.days)}
                                                                {#each schedule.days as day}
                                                                    <span class="badge bg-light text-dark border">{day.substring(0, 3)}</span>
                                                                {/each}
                                                            {/if}
                                                        </div>
                                                        {#if schedule.vehicle_category}
                                                            <div><small class="text-muted"><i class="ti ti-car"></i> {schedule.vehicle_category.title}</small></div>
                                                        {/if}
                                                    </td>
                                                    <td>
                                                        <div class="mb-1">{schedule.quota} seats</div>
                                                        {#if schedule.prices}
                                                            {@const activePrices = schedule.prices.filter((p: any) => p.is_active)}
                                                            {#if activePrices.length > 0}
                                                                <div class="small text-muted">
                                                                    {#if activePrices.length === 1}
                                                                        Rp {activePrices[0].price.toLocaleString('id-ID')}
                                                                    {:else}
                                                                        {@const minPrice = Math.min(...activePrices.map((p: any) => p.price))}
                                                                        {@const maxPrice = Math.max(...activePrices.map((p: any) => p.price))}
                                                                        Rp {minPrice.toLocaleString('id-ID')} - Rp {maxPrice.toLocaleString('id-ID')}
                                                                    {/if}
                                                                </div>
                                                            {:else}
                                                                <span class="badge bg-danger">No active prices</span>
                                                            {/if}
                                                        {:else}
                                                            <span class="badge bg-danger">No active prices</span>
                                                        {/if}
                                                    </td>
                                                    <td class="text-center">
                                                        <button class="btn btn-sm btn-icon btn-primary me-1" onclick={() => editSchedule(schedule)}>
                                                            <i class="ti ti-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-sm btn-icon btn-danger" onclick={() => deleteSchedule(schedule.id)}>
                                                            <i class="ti ti-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            {/each}
                                        </tbody>
                                    </table>
                                </div>
                            {/if}
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</AdminLayout>
