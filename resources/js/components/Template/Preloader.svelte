<script lang="ts">
    import { onMount } from 'svelte';
    
    onMount(() => {
        // Auto-hide preloader after page loads
        setTimeout(() => {
            const preloader = document.querySelector('.preloader');
            if (preloader) {
                preloader.style.opacity = '0';
                preloader.style.visibility = 'hidden';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 300);
            }
        }, 1500); // Hide after 1.5 seconds
    });
</script>

<div class="preloader">
    <div class="loaderInner">
        <div id="top" class="mask">
            <div class="plane"></div>
        </div>
        <div id="middle" class="mask">
            <div class="plane"></div>
        </div>
        <div id="bottom" class="mask">
            <div class="plane"></div>
        </div>
        <p>LOADING...</p>
    </div>
</div>

<style>
    :global(.preloader) {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: var(--travhub-black) !important;
        z-index: 99999 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: opacity 0.3s ease, visibility 0.3s ease !important;
    }
</style>
