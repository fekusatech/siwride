<script lang="ts">
    import { onMount, type Snippet } from 'svelte';
    import { router } from '@inertiajs/svelte';
    import Sidebar from '../components/Admin/Sidebar.svelte';
    import Topbar from '../components/Admin/Topbar.svelte';
    import Footer from '../components/Admin/Footer.svelte';

    let { children }: { children: Snippet } = $props();

    let isHydrated = $state(false);
    let isMobileSidebarOpen = $state(false);

    function closeSidebar() {
        isMobileSidebarOpen = false;
        const html = document.documentElement;
        html.classList.remove('sidebar-enable');
        html.setAttribute('data-sidenav-size', 'default');
    }

    function toggleSidebar(e?: Event) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!isHydrated) return;

        const isMobile = window.innerWidth < 992;
        const html = document.documentElement;
        const currentSize = html.getAttribute('data-sidenav-size') || 'default';
        let newSize = 'default';

        if (isMobile) {
            // Mobile: toggle between default (hidden) and full (visible overlay)
            isMobileSidebarOpen = !isMobileSidebarOpen;
            if (isMobileSidebarOpen) {
                html.classList.add('sidebar-enable');
                newSize = 'full';
            } else {
                html.classList.remove('sidebar-enable');
                newSize = 'default';
            }
        } else {
            // Desktop: toggle between default and sm-hover-active
            newSize = currentSize === 'default' ? 'sm-hover-active' : 'default';
        }

        html.setAttribute('data-sidenav-size', newSize);

        // Persist to sessionStorage
        try {
            const config = {
                theme: 'light',
                topbar: { color: 'light' },
                menu: { color: 'dark' },
                sidenav: { size: newSize, user: true },
                layout: { mode: 'fluid' },
            };

            sessionStorage.setItem('__BORON_CONFIG__', JSON.stringify(config));
        } catch (e) {
            console.error('[AdminLayout] Failed to persist config', e);
        }
    }

    onMount(() => {
        isHydrated = true;

        // Sync initial state
        const html = document.documentElement;
        if (html.classList.contains('sidebar-enable')) {
            isMobileSidebarOpen = true;
        }

        // Listen for Inertia navigation events to close mobile sidebar
        const unsubscribe = router.on('navigate', () => {
            if (window.innerWidth < 992 && isMobileSidebarOpen) {
                closeSidebar();
            }
        });

        // Cleanup
        return () => {
            isHydrated = false;
            unsubscribe();
        };
    });
</script>

<div class="wrapper">
    <Sidebar {toggleSidebar} {closeSidebar} />

    <Topbar {toggleSidebar} {closeSidebar} />

    <div class="page-content">
        <main class="page-container">
            {@render children()}
        </main>

        <Footer />
    </div>
</div>

{#if isMobileSidebarOpen}
    <!-- svelte-ignore a11y_click_events_have_key_events -->
    <!-- svelte-ignore a11y_no_static_element_interactions -->
    <div 
        class="offcanvas-backdrop fade show custom-backdrop" 
        onclick={closeSidebar} 
        style="z-index: 1040;"
    ></div>
{/if}

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
        margin-top: -7px !important;
    }

    /* Force visibility and color for sidebar sub-menu items */
    :global(
        [data-menu-color='dark']:not([data-sidenav-size='condensed'])
            .sidenav-menu
            .side-nav
            .sub-menu
            .side-nav-item
            .side-nav-link
    ),
    :global(
        [data-menu-color='dark']:not([data-sidenav-size='condensed'])
            .sidenav-menu
            .side-nav
            .sub-menu
            .side-nav-item
            .menu-text
    ) {
        color: #97aac1 !important;
        visibility: visible !important;
        opacity: 1 !important;
        display: block !important;
        padding: 5px 20px 5px 45px !important;
    }

    :global(
        [data-menu-color='dark']:not([data-sidenav-size='condensed'])
            .sidenav-menu
            .side-nav
            .sub-menu
            .side-nav-item
            .side-nav-link:hover
    ),
    :global(
        [data-menu-color='dark']:not([data-sidenav-size='condensed'])
            .sidenav-menu
            .side-nav
            .sub-menu
            .side-nav-item
            .side-nav-link:hover
            .menu-text
    ) {
        color: #ffffff !important;
    }
</style>
