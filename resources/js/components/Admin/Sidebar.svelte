<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';

    let { toggleSidebar } = $props();
    const settings = $derived(page.props.settings as any);

    function handleToggleSidebar(e: Event) {
        e.preventDefault();
        e.stopPropagation();
        toggleSidebar(e);
    }
</script>

<!-- Sidenav Menu Start -->
<div class="sidenav-menu">
    <!-- Brand Logo -->
    <Link href="/dashboard" class="logo">
        <span class="logo-light">
            <span class="logo-lg">
                {#if settings.logo}
                    <img
                        src="/storage/{settings.logo}?v={new Date(
                            settings.updated_at,
                        ).getTime()}"
                        alt="{settings.business_name} Logo"
                    />
                {:else}
                    <img src="/assets-admin/images/logo.png" alt="logo" />
                {/if}
            </span>
            <span class="logo-sm text-center">
                {#if settings.favicon}
                    <img
                        src="/storage/{settings.favicon}?v={new Date(
                            settings.updated_at,
                        ).getTime()}"
                        alt="sm logo"
                        style="width: 24px; height: 24px;"
                    />
                {:else}
                    <img
                        src="/assets-admin/images/logo-sm.png"
                        alt="small logo"
                    />
                {/if}
            </span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg">
                {#if settings.logo}
                    <img
                        src="/storage/{settings.logo}?v={new Date(
                            settings.updated_at,
                        ).getTime()}"
                        alt="{settings.business_name} Logo"
                    />
                {:else}
                    <img
                        src="/assets-admin/images/logo-dark.png"
                        alt="dark logo"
                    />
                {/if}
            </span>
            <span class="logo-sm text-center">
                {#if settings.favicon}
                    <img
                        src="/storage/{settings.favicon}?v={new Date(
                            settings.updated_at,
                        ).getTime()}"
                        alt="sm logo"
                        style="width: 24px; height: 24px;"
                    />
                {:else}
                    <img
                        src="/assets-admin/images/logo-sm.png"
                        alt="small logo"
                    />
                {/if}
            </span>
        </span>
    </Link>

    <!-- Sidebar Hover Menu Toggle Button -->
    <button
        class="button-sm-hover"
        onclick={handleToggleSidebar}
        aria-label="Toggle Sidebar Size"
        type="button"
    >
        <i class="ti ti-circle align-middle"></i>
    </button>

    <!-- Close Button for Mobile -->
    <button
        class="button-close-fullsidebar"
        onclick={handleToggleSidebar}
        aria-label="Close Sidebar"
        type="button"
    >
        <i class="ti ti-x align-middle"></i>
    </button>

    <div data-simplebar>
        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-item">
                <Link href="/dashboard" class="side-nav-link">
                    <span class="menu-icon"
                        ><i class="ti ti-dashboard"></i></span
                    >
                    <span class="menu-text"> Dashboard </span>
                </Link>
            </li>

            <li class="side-nav-item">
                <Link href="/admin/orders" class="side-nav-link">
                    <span class="menu-icon"
                        ><i class="ti ti-shopping-cart"></i></span
                    >
                    <span class="menu-text"> Orders </span>
                </Link>
            </li>

            <li class="side-nav-item">
                <Link href="/admin/orders/calendar" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-calendar"></i></span
                    >
                    <span class="menu-text"> Calendar </span>
                </Link>
            </li>

            <li class="side-nav-title mt-2">Master Data</li>

            <li class="side-nav-item">
                <Link href="/admin/drivers" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-users"></i></span>
                    <span class="menu-text"> Drivers </span>
                </Link>
            </li>

            <li class="side-nav-item">
                <Link href="/admin/vehicles" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-car"></i></span>
                    <span class="menu-text"> Vehicles </span>
                </Link>
            </li>

            <li class="side-nav-item">
                <Link href="/admin/zones" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-layers"></i></span>
                    <span class="menu-text"> Zones </span>
                </Link>
            </li>

            <li class="side-nav-title mt-2">System</li>

            <li class="side-nav-item">
                <Link href="/admin/settings/general" class="side-nav-link">
                    <span class="menu-icon"><i class="ti ti-settings"></i></span
                    >
                    <span class="menu-text"> Settings </span>
                </Link>
            </li>
        </ul>
    </div>
</div>
<!-- Sidenav Menu End -->
