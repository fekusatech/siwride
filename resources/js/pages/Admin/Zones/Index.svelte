<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import {
        MapPin,
        Trash2,
        Edit2,
        Layers,
        Pencil,
        MousePointerClick,
    } from 'lucide-svelte';

    declare const google: any;

    let { zones = [], google_maps_api_key } = $props();

    const form = useForm({
        id: null as number | null,
        name: '',
        coordinates: [] as { lat: number; lng: number }[],
        is_active: true,
    });

    let mapElement: HTMLDivElement;
    let map: any;
    let selectedPolygon: any = null;
    let currentPolygons: any[] = [];
    let drawMarkers: any[] = [];
    let drawPolyline: any = null;
    let isEditing = $state(false);
    let isDrawing = $state(false);
    let drawPoints = $state<{ lat: number; lng: number }[]>([]);
    let boundaryQuery = $state('');
    let boundarySuggestions = $state<any[]>([]);
    let searchingBoundary = $state(false);
    let boundaryError = $state<string | null>(null);
    let mapClickListener: any = null;

    function initMap() {
        if (!google_maps_api_key) return;

        const scriptExists = document.querySelector(
            'script[src*="maps.googleapis.com"]',
        );
        if (!scriptExists) {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${google_maps_api_key}&libraries=places`;
            script.async = true;
            script.defer = true;
            script.onload = () => setupMap();
            document.head.appendChild(script);
        } else {
            if (typeof google !== 'undefined' && google.maps) {
                setupMap();
            }
        }
    }

    function setupMap() {
        const bali = { lat: -8.4095, lng: 115.1889 };
        map = new google.maps.Map(mapElement, {
            center: bali,
            zoom: 10,
            styles: [
                {
                    featureType: 'poi',
                    elementType: 'labels',
                    stylers: [{ visibility: 'off' }],
                },
            ],
        });

        renderZones();
    }

    function startDrawing() {
        isDrawing = true;
        drawPoints = [];
        clearDrawingArtifacts();

        // Change cursor
        if (map) map.setOptions({ draggableCursor: 'crosshair' });

        // Listen for map clicks
        mapClickListener = google.maps.event.addListener(
            map,
            'click',
            (e: any) => {
                const latLng = { lat: e.latLng.lat(), lng: e.latLng.lng() };
                drawPoints = [...drawPoints, latLng];
                updateDrawingVisuals();
            },
        );
    }

    function updateDrawingVisuals() {
        // Clear previous markers and polyline
        drawMarkers.forEach((m) => m.setMap(null));
        drawMarkers = [];
        if (drawPolyline) drawPolyline.setMap(null);

        // Draw markers
        drawPoints.forEach((pt, i) => {
            const marker = new google.maps.Marker({
                position: pt,
                map,
                icon: {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: i === 0 ? 8 : 6,
                    fillColor: i === 0 ? '#22c55e' : '#3b82f6',
                    fillOpacity: 1,
                    strokeColor: '#fff',
                    strokeWeight: 2,
                },
                title: i === 0 ? 'Start (click to close)' : `Point ${i + 1}`,
            });

            // Click first marker to close polygon
            if (i === 0 && drawPoints.length >= 3) {
                marker.addListener('click', () => finishDrawing());
            }

            drawMarkers.push(marker);
        });

        // Draw polyline connecting points
        if (drawPoints.length > 1) {
            drawPolyline = new google.maps.Polyline({
                path: drawPoints,
                strokeColor: '#3b82f6',
                strokeWeight: 2,
                strokeOpacity: 0.8,
                map,
            });
        }
    }

    function finishDrawing() {
        if (drawPoints.length < 3) {
            alert('You need at least 3 points to create a polygon.');
            return;
        }

        isDrawing = false;
        form.coordinates = [...drawPoints];

        // Remove drawing artifacts
        clearDrawingArtifacts();

        // Reset cursor
        if (map) map.setOptions({ draggableCursor: '' });

        // Remove click listener
        if (mapClickListener) {
            google.maps.event.removeListener(mapClickListener);
            mapClickListener = null;
        }

        // Show the polygon
        if (selectedPolygon) selectedPolygon.setMap(null);
        selectedPolygon = new google.maps.Polygon({
            paths: form.coordinates,
            editable: true,
            draggable: true,
            fillColor: '#22c55e',
            fillOpacity: 0.35,
            strokeColor: '#16a34a',
            strokeWeight: 2,
            map,
        });

        google.maps.event.addListener(
            selectedPolygon.getPath(),
            'set_at',
            () => updateCoordinatesFromPolygon(selectedPolygon),
        );
        google.maps.event.addListener(
            selectedPolygon.getPath(),
            'insert_at',
            () => updateCoordinatesFromPolygon(selectedPolygon),
        );
    }

    function cancelDrawing() {
        isDrawing = false;
        drawPoints = [];
        clearDrawingArtifacts();
        if (map) map.setOptions({ draggableCursor: '' });
        if (mapClickListener) {
            google.maps.event.removeListener(mapClickListener);
            mapClickListener = null;
        }
    }

    function undoLastPoint() {
        if (drawPoints.length > 0) {
            drawPoints = drawPoints.slice(0, -1);
            updateDrawingVisuals();
        }
    }

    function clearDrawingArtifacts() {
        drawMarkers.forEach((m) => m.setMap(null));
        drawMarkers = [];
        if (drawPolyline) {
            drawPolyline.setMap(null);
            drawPolyline = null;
        }
    }

    function renderZones() {
        currentPolygons.forEach((p) => p.setMap(null));
        currentPolygons = [];

        const bounds = new google.maps.LatLngBounds();
        let hasValidCoords = false;

        zones.forEach((zone: any) => {
            if (!zone.coordinates || zone.coordinates.length === 0) return;

            if (isEditing && form.id === zone.id) {
                zone.coordinates.forEach((coord: any) => {
                    bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
                    hasValidCoords = true;
                });
                return;
            }

            const polygon = new google.maps.Polygon({
                paths: zone.coordinates,
                strokeColor: zone.is_active ? '#3b82f6' : '#9ca3af',
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: zone.is_active ? '#3b82f6' : '#9ca3af',
                fillOpacity: 0.2,
                map: map,
            });

            polygon.addListener('click', () => {
                editZone(zone);
            });

            zone.coordinates.forEach((coord: any) => {
                bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
                hasValidCoords = true;
            });

            currentPolygons.push(polygon);
        });

        if (hasValidCoords && !isEditing) {
            map.fitBounds(bounds);
        }
    }

    function updateCoordinatesFromPolygon(polygon: any) {
        const path = polygon.getPath();
        const coords = [];
        for (let i = 0; i < path.getLength(); i++) {
            const point = path.getAt(i);
            coords.push({ lat: point.lat(), lng: point.lng() });
        }
        form.coordinates = coords;
    }

    async function searchBoundaries() {
        if (boundaryQuery.trim().length < 3) {
            boundaryError = 'Type at least 3 characters.';
            return;
        }

        searchingBoundary = true;
        boundaryError = null;

        try {
            const response = await fetch(
                `/admin/zones/boundary-suggestions?query=${encodeURIComponent(boundaryQuery.trim())}`,
            );
            const result = await response.json();
            boundarySuggestions = result.data ?? [];

            if (boundarySuggestions.length === 0) {
                boundaryError =
                    'No boundary polygon found. Try another keyword.';
            }
        } catch (error) {
            boundaryError = 'Failed to search boundaries.';
            console.error('Boundary search error:', error);
        } finally {
            searchingBoundary = false;
        }
    }

    function useBoundarySuggestion(suggestion: any) {
        form.name = suggestion.name.split(',')[0] ?? suggestion.name;
        form.coordinates = suggestion.coordinates;

        if (selectedPolygon) selectedPolygon.setMap(null);

        if (map && typeof google !== 'undefined' && google.maps) {
            selectedPolygon = new google.maps.Polygon({
                paths: suggestion.coordinates,
                editable: true,
                draggable: true,
                fillColor: '#22c55e',
                fillOpacity: 0.35,
                strokeColor: '#16a34a',
                strokeWeight: 2,
                map,
            });

            google.maps.event.addListener(
                selectedPolygon.getPath(),
                'set_at',
                () => updateCoordinatesFromPolygon(selectedPolygon),
            );
            google.maps.event.addListener(
                selectedPolygon.getPath(),
                'insert_at',
                () => updateCoordinatesFromPolygon(selectedPolygon),
            );

            const bounds = new google.maps.LatLngBounds();
            suggestion.coordinates.forEach((coord: any) => {
                bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
            });
            map.fitBounds(bounds);
        }
    }

    function editZone(zone: any) {
        isEditing = true;
        isDrawing = false;
        cancelDrawing();
        form.id = zone.id;
        form.name = zone.name;
        form.coordinates = [...zone.coordinates];
        form.is_active = zone.is_active;

        if (selectedPolygon) selectedPolygon.setMap(null);

        renderZones();

        selectedPolygon = new google.maps.Polygon({
            paths: zone.coordinates,
            editable: true,
            draggable: true,
            fillColor: '#f59e0b',
            fillOpacity: 0.4,
            strokeColor: '#f59e0b',
            strokeWeight: 2,
            map: map,
        });

        const bounds = new google.maps.LatLngBounds();
        zone.coordinates.forEach((coord: any) => {
            bounds.extend(new google.maps.LatLng(coord.lat, coord.lng));
        });
        map.fitBounds(bounds);

        google.maps.event.addListener(selectedPolygon.getPath(), 'set_at', () =>
            updateCoordinatesFromPolygon(selectedPolygon),
        );
        google.maps.event.addListener(
            selectedPolygon.getPath(),
            'insert_at',
            () => updateCoordinatesFromPolygon(selectedPolygon),
        );
    }

    function cancelEdit() {
        isEditing = false;
        form.reset();
        if (selectedPolygon) {
            selectedPolygon.setMap(null);
            selectedPolygon = null;
        }
        renderZones();
    }

    function handleSubmit() {
        if (form.coordinates.length < 3) {
            alert('Please draw a polygon on the map first (minimum 3 points).');
            return;
        }

        if (form.id) {
            form.put(`/admin/zones/${form.id}`, {
                onSuccess: () => {
                    isEditing = false;
                    form.reset();
                    if (selectedPolygon) selectedPolygon.setMap(null);
                    selectedPolygon = null;
                },
            });
        } else {
            form.post('/admin/zones', {
                onSuccess: () => {
                    form.reset();
                    if (selectedPolygon) selectedPolygon.setMap(null);
                    selectedPolygon = null;
                },
            });
        }
    }

    function deleteZone(id: number) {
        if (confirm('Are you sure you want to delete this zone?')) {
            router.delete(`/admin/zones/${id}`);
        }
    }

    onMount(() => {
        initMap();
    });

    $effect(() => {
        if (map && zones) {
            void isEditing;
            renderZones();
        }
    });
</script>

<AppHead title="Manage Zones" />

<AdminLayout>
    <div class="zones-page">
        <div class="zones-header">
            <div>
                <h1 class="zones-title">
                    <Layers class="zones-title-icon" />
                    Service Zones
                </h1>
                <p class="zones-subtitle">
                    Define operational areas by drawing polygons on the map.
                </p>
            </div>
        </div>

        <!-- Map first on mobile -->
        <div class="zones-grid">
            <!-- Map Area -->
            <div class="zones-map-wrapper">
                <div bind:this={mapElement} class="zones-map"></div>

                <!-- Drawing controls overlay -->
                <div class="map-controls">
                    {#if !isDrawing}
                        <button
                            type="button"
                            onclick={startDrawing}
                            class="map-btn map-btn--draw"
                            title="Draw polygon"
                        >
                            <Pencil class="map-btn-icon" />
                            <span class="map-btn-label">Draw Polygon</span>
                        </button>
                    {:else}
                        <div class="map-drawing-bar">
                            <span class="drawing-status">
                                <MousePointerClick class="drawing-status-icon" />
                                {drawPoints.length} points — Click map to add, click
                                first point to close
                            </span>
                            <div class="drawing-actions">
                                {#if drawPoints.length > 0}
                                    <button
                                        type="button"
                                        onclick={undoLastPoint}
                                        class="map-btn map-btn--sm"
                                    >
                                        Undo
                                    </button>
                                {/if}
                                {#if drawPoints.length >= 3}
                                    <button
                                        type="button"
                                        onclick={finishDrawing}
                                        class="map-btn map-btn--sm map-btn--finish"
                                    >
                                        Finish
                                    </button>
                                {/if}
                                <button
                                    type="button"
                                    onclick={cancelDrawing}
                                    class="map-btn map-btn--sm map-btn--cancel"
                                >
                                    Cancel
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>
            </div>

            <!-- Sidebar -->
            <div class="zones-sidebar">
                <!-- Zone Form -->
                <div class="zone-card">
                    <div class="zone-card__header">
                        <h3>{isEditing ? 'Edit Zone' : 'Create New Zone'}</h3>
                    </div>
                    <form
                        onsubmit={(e) => {
                            e.preventDefault();
                            handleSubmit();
                        }}
                        class="zone-card__body"
                    >
                        <!-- Boundary Search -->
                        <div class="boundary-search-box">
                            <label for="boundary-search" class="form-label-sm"
                                >Search Boundary</label
                            >
                            <div class="boundary-search-row">
                                <input
                                    id="boundary-search"
                                    type="search"
                                    bind:value={boundaryQuery}
                                    placeholder="e.g. Denpasar, Badung, Bali"
                                    class="input-sm"
                                    onkeydown={(e) => {
                                        if (e.key === 'Enter') {
                                            e.preventDefault();
                                            searchBoundaries();
                                        }
                                    }}
                                />
                                <button
                                    type="button"
                                    onclick={searchBoundaries}
                                    disabled={searchingBoundary}
                                    class="btn-sm btn-sm--primary"
                                >
                                    {searchingBoundary ? '...' : 'Search'}
                                </button>
                            </div>

                            {#if boundaryError}
                                <p class="text-warning-sm">{boundaryError}</p>
                            {/if}

                            {#if boundarySuggestions.length > 0}
                                <div class="suggestions-list">
                                    {#each boundarySuggestions as suggestion}
                                        <button
                                            type="button"
                                            onclick={() =>
                                                useBoundarySuggestion(
                                                    suggestion,
                                                )}
                                            class="suggestion-item"
                                        >
                                            <span class="suggestion-name"
                                                >{suggestion.name}</span
                                            >
                                            <span class="suggestion-meta"
                                                >{suggestion.coordinates
                                                    .length} pts</span
                                            >
                                        </button>
                                    {/each}
                                </div>
                            {/if}
                        </div>

                        <!-- Zone Name -->
                        <div class="form-group-sm">
                            <label for="zone-name" class="form-label-sm"
                                >Zone Name</label
                            >
                            <input
                                type="text"
                                id="zone-name"
                                bind:value={form.name}
                                placeholder="e.g. Bali South"
                                class="input-sm"
                                required
                            />
                            {#if form.errors.name}
                                <p class="text-error-sm">{form.errors.name}</p>
                            {/if}
                        </div>

                        <!-- Active -->
                        <label class="checkbox-row">
                            <input
                                type="checkbox"
                                bind:checked={form.is_active}
                            />
                            <span>Active Zone</span>
                        </label>

                        <!-- Points info -->
                        <div class="points-info">
                            <MapPin class="points-info-icon" />
                            {#if form.coordinates.length === 0}
                                <span
                                    >Click "Draw Polygon" then click on the map
                                    to define the zone area.</span
                                >
                            {:else}
                                <span
                                    >{form.coordinates.length} points defined.</span
                                >
                            {/if}
                        </div>

                        <!-- Actions -->
                        <div class="form-actions">
                            <button
                                type="submit"
                                class="btn-primary-full"
                                disabled={form.processing}
                            >
                                {form.processing
                                    ? 'Saving...'
                                    : isEditing
                                      ? 'Update Zone'
                                      : 'Save Zone'}
                            </button>
                            {#if isEditing}
                                <button
                                    type="button"
                                    onclick={cancelEdit}
                                    class="btn-secondary-full"
                                >
                                    Cancel
                                </button>
                            {/if}
                        </div>
                    </form>
                </div>

                <!-- Zone List -->
                <div class="zone-card">
                    <div class="zone-card__header zone-card__header--between">
                        <h3>Existing Zones</h3>
                        <span class="badge-count">{zones.length}</span>
                    </div>
                    <div class="zone-list">
                        {#if zones.length === 0}
                            <div class="zone-list-empty">
                                <Layers class="zone-list-empty-icon" />
                                <p>No zones created yet.</p>
                            </div>
                        {:else}
                            {#each zones as zone (zone.id)}
                                <div class="zone-list-item">
                                    <div class="zone-list-item__info">
                                        <h4>
                                            {zone.name}
                                            {#if !zone.is_active}
                                                <span class="tag-inactive"
                                                    >Inactive</span
                                                >
                                            {/if}
                                        </h4>
                                        <p>
                                            {zone.coordinates
                                                ? zone.coordinates.length
                                                : 0} points
                                        </p>
                                    </div>
                                    <div class="zone-list-item__actions">
                                        <button
                                            onclick={() => editZone(zone)}
                                            class="icon-btn icon-btn--blue"
                                            title="Edit"
                                        >
                                            <Edit2 class="icon-btn-svg" />
                                        </button>
                                        <button
                                            onclick={() => deleteZone(zone.id)}
                                            class="icon-btn icon-btn--red"
                                            title="Delete"
                                        >
                                            <Trash2 class="icon-btn-svg" />
                                        </button>
                                    </div>
                                </div>
                            {/each}
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .zones-page {
        padding: 1.5rem 1rem;
    }
    .zones-header {
        margin-bottom: 1.5rem;
    }
    .zones-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    :global(.zones-title-icon) {
        width: 1.5rem;
        height: 1.5rem;
        color: #3b82f6;
    }
    .zones-subtitle {
        color: #64748b;
        margin-top: 0.25rem;
        font-size: 0.875rem;
    }

    .zones-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 1024px) {
        .zones-grid {
            grid-template-columns: 2fr 1fr;
        }
    }

    .zones-map-wrapper {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .zones-map {
        width: 100%;
        height: 400px;
    }
    @media (min-width: 768px) {
        .zones-map {
            height: 500px;
        }
    }
    @media (min-width: 1024px) {
        .zones-map {
            height: 100%;
            min-height: 600px;
        }
    }

    .map-controls {
        position: absolute;
        bottom: 12px;
        left: 12px;
        z-index: 10;
        pointer-events: none;
    }
    .map-controls > * {
        pointer-events: auto;
    }

    .map-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        transition: all 0.15s;
    }
    .map-btn--draw {
        background: #3b82f6;
        color: white;
    }
    .map-btn--draw:hover {
        background: #2563eb;
    }
    :global(.map-btn-icon) {
        width: 16px;
        height: 16px;
    }
    .map-btn-label {
        display: none;
    }
    @media (min-width: 480px) {
        .map-btn-label {
            display: inline;
        }
    }

    .map-drawing-bar {
        background: white;
        border-radius: 10px;
        padding: 10px 14px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        display: flex;
        flex-direction: column;
        gap: 8px;
        max-width: calc(100% - 60px);
    }
    @media (min-width: 600px) {
        .map-drawing-bar {
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
        }
    }
    .drawing-status {
        font-size: 0.75rem;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    :global(.drawing-status-icon) {
        width: 14px;
        height: 14px;
        color: #3b82f6;
    }
    .drawing-actions {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .map-btn--sm {
        padding: 5px 10px;
        font-size: 0.75rem;
        border-radius: 6px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .map-btn--sm:hover {
        background: #e2e8f0;
    }
    .map-btn--finish {
        background: #22c55e;
        color: white;
        border-color: #22c55e;
    }
    .map-btn--finish:hover {
        background: #16a34a;
    }
    .map-btn--cancel {
        background: #fee2e2;
        color: #dc2626;
        border-color: #fecaca;
    }
    .map-btn--cancel:hover {
        background: #fecaca;
    }

    .zones-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .zone-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
        overflow: hidden;
    }
    .zone-card__header {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        background: #f8fafc;
    }
    .zone-card__header h3 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
    }
    .zone-card__header--between {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .zone-card__body {
        padding: 16px;
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .boundary-search-box {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        padding: 12px;
    }
    .boundary-search-row {
        display: flex;
        gap: 8px;
        margin-top: 6px;
    }
    .input-sm {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 0.8125rem;
        outline: none;
        transition: border-color 0.15s;
    }
    .input-sm:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
    }
    .btn-sm {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.15s;
    }
    .btn-sm--primary {
        background: #22c55e;
        color: white;
    }
    .btn-sm--primary:hover {
        background: #16a34a;
    }
    .btn-sm--primary:disabled {
        opacity: 0.5;
    }

    .text-warning-sm {
        font-size: 0.75rem;
        color: #b45309;
        margin-top: 6px;
    }
    .text-error-sm {
        font-size: 0.75rem;
        color: #dc2626;
        margin-top: 4px;
    }

    .suggestions-list {
        margin-top: 8px;
        max-height: 160px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .suggestion-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: white;
        border: 1px solid #d1fae5;
        border-radius: 6px;
        padding: 8px 10px;
        cursor: pointer;
        text-align: left;
        transition: border-color 0.15s;
        width: 100%;
    }
    .suggestion-item:hover {
        border-color: #22c55e;
    }
    .suggestion-name {
        font-size: 0.75rem;
        color: #1e293b;
        line-height: 1.3;
        flex: 1;
    }
    .suggestion-meta {
        font-size: 0.625rem;
        color: #64748b;
        margin-left: 8px;
        white-space: nowrap;
    }

    .form-group-sm {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .form-label-sm {
        font-size: 0.8125rem;
        font-weight: 500;
        color: #374151;
    }

    .checkbox-row {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.8125rem;
        color: #374151;
        cursor: pointer;
    }
    .checkbox-row input {
        width: 16px;
        height: 16px;
        accent-color: #3b82f6;
    }

    .points-info {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 0.75rem;
        color: #1d4ed8;
        display: flex;
        align-items: flex-start;
        gap: 8px;
    }
    :global(.points-info-icon) {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .form-actions {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .btn-primary-full {
        width: 100%;
        padding: 10px;
        background: #3b82f6;
        color: white;
        font-weight: 600;
        font-size: 0.875rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-primary-full:hover {
        background: #2563eb;
    }
    .btn-primary-full:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .btn-secondary-full {
        width: 100%;
        padding: 10px;
        background: #f1f5f9;
        color: #475569;
        font-weight: 600;
        font-size: 0.875rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.15s;
    }
    .btn-secondary-full:hover {
        background: #e2e8f0;
    }

    .badge-count {
        font-size: 0.6875rem;
        background: #e2e8f0;
        color: #475569;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
    }

    .zone-list {
        max-height: 350px;
        overflow-y: auto;
    }
    .zone-list-empty {
        padding: 2rem;
        text-align: center;
        color: #94a3b8;
    }
    :global(.zone-list-empty-icon) {
        width: 2rem;
        height: 2rem;
        margin: 0 auto 0.5rem;
        opacity: 0.3;
    }
    .zone-list-empty p {
        font-size: 0.8125rem;
    }

    .zone-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        transition: background 0.1s;
    }
    .zone-list-item:hover {
        background: #f8fafc;
    }
    .zone-list-item:last-child {
        border-bottom: none;
    }
    .zone-list-item__info h4 {
        font-size: 0.875rem;
        font-weight: 600;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .zone-list-item__info p {
        font-size: 0.6875rem;
        color: #94a3b8;
        margin: 2px 0 0;
    }
    .tag-inactive {
        font-size: 0.5625rem;
        background: #f1f5f9;
        color: #64748b;
        padding: 1px 5px;
        border-radius: 4px;
        text-transform: uppercase;
        font-weight: 700;
    }

    .zone-list-item__actions {
        display: flex;
        gap: 4px;
    }
    .icon-btn {
        padding: 6px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        transition: background 0.15s;
    }
    .icon-btn--blue {
        color: #3b82f6;
        background: transparent;
    }
    .icon-btn--blue:hover {
        background: #eff6ff;
    }
    .icon-btn--red {
        color: #ef4444;
        background: transparent;
    }
    .icon-btn--red:hover {
        background: #fef2f2;
    }
    :global(.icon-btn-svg) {
        width: 16px;
        height: 16px;
    }
</style>
