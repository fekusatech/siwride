<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, router } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import { MapPin, Trash2, Edit2, Plus, Check, X, Layers } from 'lucide-svelte';

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
    let drawingManager: any;
    let selectedPolygon: any = null;
    let currentPolygons: any[] = [];
    let isEditing = $state(false);

    function initMap() {
        if (!google_maps_api_key) return;
        
        const scriptExists = document.querySelector('script[src*="maps.googleapis.com"]');
        if (!scriptExists) {
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${google_maps_api_key}&libraries=drawing,places`;
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
        });

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: null,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: [google.maps.drawing.OverlayType.POLYGON],
            },
            polygonOptions: {
                fillColor: '#3b82f6',
                fillOpacity: 0.3,
                strokeWeight: 2,
                clickable: true,
                editable: true,
                zIndex: 1,
            },
        });

        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'polygoncomplete', (polygon: any) => {
            // If we're already editing or creating, remove the previous one if it wasn't saved
            if (selectedPolygon && !isEditing) {
                selectedPolygon.setMap(null);
            }
            
            selectedPolygon = polygon;
            updateCoordinatesFromPolygon(polygon);
            drawingManager.setDrawingMode(null);
        });

        // Load existing zones
        renderZones();
    }

    function renderZones() {
        // Clear existing polygons from map
        currentPolygons.forEach(p => p.setMap(null));
        currentPolygons = [];

        const bounds = new google.maps.LatLngBounds();
        let hasValidCoords = false;

        zones.forEach((zone: any) => {
            if (!zone.coordinates || zone.coordinates.length === 0) return;

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

    function editZone(zone: any) {
        isEditing = true;
        form.id = zone.id;
        form.name = zone.name;
        form.coordinates = [...zone.coordinates];
        form.is_active = zone.is_active;

        // Clear previous selected polygon if any
        if (selectedPolygon) selectedPolygon.setMap(null);

        // Create an editable polygon for this zone
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

        map.setCenter(zone.coordinates[0]);
        map.setZoom(12);

        google.maps.event.addListener(selectedPolygon.getPath(), 'set_at', () => updateCoordinatesFromPolygon(selectedPolygon));
        google.maps.event.addListener(selectedPolygon.getPath(), 'insert_at', () => updateCoordinatesFromPolygon(selectedPolygon));
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
            alert('Please draw a polygon on the map first.');
            return;
        }

        if (form.id) {
            form.put(`/admin/zones/${form.id}`, {
                onSuccess: () => {
                    isEditing = false;
                    form.reset();
                    selectedPolygon.setMap(null);
                    selectedPolygon = null;
                }
            });
        } else {
            form.post('/admin/zones', {
                onSuccess: () => {
                    form.reset();
                    selectedPolygon.setMap(null);
                    selectedPolygon = null;
                }
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

    // Re-render when zones change
    $effect(() => {
        if (map && zones) {
            renderZones();
        }
    });
</script>

<AppHead title="Manage Zones" />

<AdminLayout>
    <div class="py-6 px-4">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                    <Layers class="size-6 text-blue-600" />
                    Service Zones
                </h1>
                <p class="text-gray-500">Define operational areas by drawing polygons on the map.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Sidebar / List -->
            <div class="lg:col-span-1 flex flex-col gap-6">
                <!-- Zone Form -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gray-50">
                        <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                            {isEditing ? 'Edit Zone' : 'Create New Zone'}
                        </h3>
                    </div>
                    <form onsubmit={(e) => { e.preventDefault(); handleSubmit(); }} class="p-4 space-y-4">
                        <div>
                            <label for="zone-name" class="block text-sm font-medium text-gray-700 mb-1">Zone Name</label>
                            <input 
                                type="text" 
                                id="zone-name" 
                                bind:value={form.name}
                                placeholder="e.g. Bali South"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all"
                                required
                            />
                            {#if form.errors.name}
                                <p class="text-red-500 text-xs mt-1">{form.errors.name}</p>
                            {/if}
                        </div>

                        <div class="flex items-center gap-2">
                            <input 
                                type="checkbox" 
                                id="is-active" 
                                bind:checked={form.is_active}
                                class="size-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                            />
                            <label for="is-active" class="text-sm text-gray-700">Active Zone</label>
                        </div>

                        <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                            <p class="text-xs text-blue-700 flex items-start gap-2">
                                <MapPin class="size-4 shrink-0 mt-0.5" />
                                {#if form.coordinates.length === 0}
                                    Use the polygon tool on the map to draw the zone area.
                                {:else}
                                    {form.coordinates.length} points defined for this zone.
                                {/if}
                            </p>
                        </div>

                        <div class="flex gap-2 pt-2">
                            <button 
                                type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                                disabled={form.processing}
                            >
                                {form.processing ? 'Saving...' : (isEditing ? 'Update Zone' : 'Save Zone')}
                            </button>
                            {#if isEditing}
                                <button 
                                    type="button" 
                                    onclick={cancelEdit}
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors"
                                >
                                    Cancel
                                </button>
                            {/if}
                        </div>
                    </form>
                </div>

                <!-- Zone List -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-semibold text-gray-800">Existing Zones</h3>
                        <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full">{zones.length}</span>
                    </div>
                    <div class="max-h-[400px] overflow-y-auto">
                        {#if zones.length === 0}
                            <div class="p-8 text-center text-gray-500">
                                <Layers class="size-8 mx-auto mb-2 opacity-20" />
                                <p class="text-sm">No zones created yet.</p>
                            </div>
                        {:else}
                            <ul class="divide-y divide-gray-100">
                                {#each zones as zone (zone.id)}
                                    <li class="p-4 hover:bg-gray-50 transition-colors group">
                                        <div class="flex justify-between items-center">
                                            <div>
                                                <h4 class="font-medium text-gray-900 flex items-center gap-2">
                                                    {zone.name}
                                                    {#if !zone.is_active}
                                                        <span class="text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded uppercase">Inactive</span>
                                                    {/if}
                                                </h4>
                                                <p class="text-xs text-gray-500 mt-1">{zone.coordinates ? zone.coordinates.length : 0} points defined</p>
                                            </div>
                                            <div class="flex gap-1">
                                                <button 
                                                    onclick={() => editZone(zone)}
                                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded"
                                                    title="Edit"
                                                >
                                                    <Edit2 class="size-4" />
                                                </button>
                                                <button 
                                                    onclick={() => deleteZone(zone.id)}
                                                    class="p-1.5 text-red-600 hover:bg-red-50 rounded"
                                                    title="Delete"
                                                >
                                                    <Trash2 class="size-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </li>
                                {/each}
                            </ul>
                        {/if}
                    </div>
                </div>
            </div>

            <!-- Map Area -->
            <div class="lg:col-span-2 h-[600px] lg:h-auto min-h-[600px] relative">
                <div 
                    bind:this={mapElement} 
                    class="w-full h-full rounded-xl border border-gray-200 shadow-sm"
                ></div>
                
                <div class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm p-3 rounded-lg shadow-md border border-white/50 text-xs text-gray-700 max-w-xs">
                    <p class="font-semibold mb-1">How to draw:</p>
                    <ol class="list-decimal list-inside space-y-1">
                        <li>Select the polygon tool at the top center.</li>
                        <li>Click on the map to place vertices.</li>
                        <li>Click the first point to close the polygon.</li>
                        <li>Click a zone in the list or on the map to edit.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    /* Add any custom styles here */
</style>
