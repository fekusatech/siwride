<script lang="ts">
    import { onMount, type Snippet } from 'svelte';
    import Sidebar from '../components/Admin/Sidebar.svelte';
    import Topbar from '../components/Admin/Topbar.svelte';
    import Footer from '../components/Admin/Footer.svelte';

    let { children }: { children: Snippet } = $props();

    let sidebarSize = $state('default');

    function toggleSidebar(e?: Event) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        const currentSize = document.documentElement.getAttribute('data-sidenav-size') || 'default';
        let newSize = 'default';
        
        if (currentSize === 'default') {
            newSize = 'condensed';
        } else if (currentSize === 'condensed' || currentSize === 'sm' || currentSize === 'full') {
            newSize = 'default';
        }
        
        console.log(`[SidebarToggle] Current: ${currentSize} -> New: ${newSize}`);
        
        document.documentElement.setAttribute('data-sidenav-size', newSize);
        document.documentElement.classList.toggle('sidebar-enable');
        sidebarSize = newSize;
        
        // Persist for Boron's config.js - Merge with existing to avoid TypeErrors
        try {
            const defaultConfig = {
                "theme": "light",
                "topbar": { "color": "light" },
                "menu": { "color": "dark" },
                "sidenav": { "size": "default", "user": true },
                "layout": { "mode": "fluid" }
            };
            
            const rawConfig = sessionStorage.getItem('__BORON_CONFIG__');
            const existingConfig = rawConfig ? JSON.parse(rawConfig) : defaultConfig;
            
            // Deep merge essential structure
            const newConfig = {
                ...defaultConfig,
                ...existingConfig,
                'sidenav': { 
                    ...(defaultConfig.sidenav),
                    ...(existingConfig.sidenav || {}), 
                    'size': newSize 
                }
            };
            
            sessionStorage.setItem('__BORON_CONFIG__', JSON.stringify(newConfig));
            console.log(`[AdminLayout] Config persisted: ${newSize}`);
        } catch (e) {
            console.error('[AdminLayout] Failed to update sessionStorage config', e);
        }
        
        window.dispatchEvent(new Event('resize'));
    }

    onMount(() => {
        // Initial sync
        const currentSize = document.documentElement.getAttribute('data-sidenav-size');
        if (currentSize) sidebarSize = currentSize;
        
        const observer = new MutationObserver(() => {
            const attrSize = document.documentElement.getAttribute('data-sidenav-size');
            if (attrSize && attrSize !== sidebarSize) {
                sidebarSize = attrSize;
            }
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-sidenav-size'] });
        
        return () => observer.disconnect();
    });
</script>

<style>
    /* Admin Sidebar Custom Styles */
    :global(.sidenav-menu) {
        padding-top: 10px;
    }
    
    :global(.sidenav-menu .logo) {
        height: 70px;
        display: flex;
        align-items: center;
        padding-left: 24px;
        margin-bottom: 5px;
    }

    :global(.sidenav-menu .side-nav) {
        margin-top: -7px !important; /* Fine-tuned to achieve ~62px gap from logo */
    }

    /* Force visibility and color for sidebar sub-menu items */
    :global([data-menu-color="dark"] .sidenav-menu .side-nav .sub-menu .side-nav-item .side-nav-link),
    :global([data-menu-color="dark"] .sidenav-menu .side-nav .sub-menu .side-nav-item .menu-text) {
        color: #97aac1 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
        padding: 5px 20px 5px 45px !important;
    }

    :global([data-menu-color="dark"] .sidenav-menu .side-nav .sub-menu .side-nav-item .side-nav-link:hover),
    :global([data-menu-color="dark"] .sidenav-menu .side-nav .sub-menu .side-nav-item .side-nav-link:hover .menu-text) {
        color: #ffffff !important;
    }
</style>

<div class="wrapper">
    <Sidebar {toggleSidebar} />

    <Topbar {toggleSidebar} />

    <div class="page-content">
        <main class="page-container">
            {@render children()}
        </main>

        <Footer />
    </div>
</div>
