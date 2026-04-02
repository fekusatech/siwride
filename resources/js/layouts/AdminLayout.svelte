<script lang="ts">
    import { onMount, type Snippet } from 'svelte';
    import Sidebar from '../components/Admin/Sidebar.svelte';
    import Topbar from '../components/Admin/Topbar.svelte';
    import Footer from '../components/Admin/Footer.svelte';

    let { children }: { children: Snippet } = $props();

    let isHydrated = $state(false);

    function toggleSidebar(e?: Event) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        if (!isHydrated) return;

        const isMobile = window.innerWidth < 768;
        const currentSize =
            document.documentElement.getAttribute('data-sidenav-size') ||
            'default';
        let newSize = 'default';

        if (isMobile) {
            // Mobile: toggle between default (hidden) and full (visible overlay)
            newSize = currentSize === 'default' ? 'full' : 'default';
        } else {
            // Desktop: toggle between default and sm-hover-active
            newSize = currentSize === 'default' ? 'sm-hover-active' : 'default';
        }

        console.log(
            `[SidebarToggle] Mobile: ${isMobile}, Current: ${currentSize} -> New: ${newSize}`,
        );

        document.documentElement.setAttribute('data-sidenav-size', newSize);

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
        // Wait for hydration to complete
        isHydrated = true;

        // Initial sync from DOM
        const currentSize =
            document.documentElement.getAttribute('data-sidenav-size') ||
            'default';

        // Cleanup
        return () => {
            isHydrated = false;
        };
    });
</script>

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
