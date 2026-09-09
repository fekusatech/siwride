<script lang="ts">
    import L from 'leaflet';
    import 'leaflet/dist/leaflet.css';
    import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
    import markerIcon from 'leaflet/dist/images/marker-icon.png';
    import markerShadow from 'leaflet/dist/images/marker-shadow.png';
    import { onDestroy, onMount } from 'svelte';

    // Leaflet's default icon bakes relative image paths that 404 under Vite.
    delete (L.Icon.Default.prototype as any)._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: markerIcon2x,
        iconUrl: markerIcon,
        shadowUrl: markerShadow,
    });

    let mapElement: HTMLDivElement;
    let map: any;

    onMount(() => {
        const bali: [number, number] = [-8.4095, 115.1889];
        map = L.map(mapElement, { scrollWheelZoom: false }).setView(bali, 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);
    });

    onDestroy(() => {
        map?.remove();
    });
</script>

<div
    bind:this={mapElement}
    style="width: 100%; height: 100%; position: relative; z-index: 0;"
></div>
