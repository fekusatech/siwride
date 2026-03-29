<script lang="ts">
    import { page, router, Link } from '@inertiajs/svelte';
    import { onMount } from 'svelte';

    let currentTheme = $state('light');

    let user = $derived(page.props.auth?.user);

    let { toggleSidebar } = $props();

    onMount(() => {
        // Sync with current attributes on html tag
        currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        
        // Listen for changes
        const observer = new MutationObserver(() => {
            currentTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-bs-theme', 'data-sidenav-size'] });
        
        return () => observer.disconnect();
    });

    function toggleTheme() {
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        document.documentElement.setAttribute('data-bs-theme', newTheme);
        
        // Also sync topbar color if needed (Boron often links them)
        document.documentElement.setAttribute('data-topbar-color', newTheme);
    }


    function logout(e: Event) {
        e.preventDefault();
        router.post('/logout', {}, {
            onSuccess: () => router.visit('/login-admin'),
        });
    }
</script>

<!-- Topbar Start -->
<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Sidebar Menu Toggle Button -->
            <button class="nav-link button-menu-mobile" onclick={(e) => toggleSidebar(e)} aria-label="Toggle Sidebar">
                <i class="ti ti-menu-deep fs-24"></i>
            </button>

            <!-- Search Bar -->
            <button type="button" class="topbar-search text-muted d-none d-xl-flex gap-2 align-items-center bg-transparent border-0 text-start" data-bs-toggle="modal" data-bs-target="#searchModal">
                <i class="ti ti-search fs-18"></i>
                <span class="me-2">Search something..</span>
                <span class="ms-auto btn btn-sm btn-primary shadow-none">⌘K</span>
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link btn btn-outline-primary btn-icon" id="light-dark-mode" type="button" onclick={toggleTheme} aria-label="Toggle Dark Mode">
                    <i class="ti {currentTheme === 'light' ? 'ti-moon' : 'ti-sun'} fs-22"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link btn btn-outline-primary dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown" data-bs-offset="0,22" type="button" aria-haspopup="true" aria-expanded="false" aria-label="User Menu">
                        {#if user?.image}
                            <img src={`/storage/${user.image}`} width="24" height="24" class="rounded-circle me-lg-2 d-flex" alt="User Avatar" style="object-fit: cover;">
                        {:else}
                            <img src="/assets-admin/images/users/avatar-1.jpg" width="24" class="rounded-circle me-lg-2 d-flex" alt="User Avatar">
                        {/if}
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            {user?.firstname ?? 'User Name'} {user?.lastname ?? ''}
                        </span>
                        <i class="ti ti-chevron-down d-none d-lg-block align-middle ms-2"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome !</h6>
                        </div>
                        <Link href="/admin/profile" class="dropdown-item">
                            <i class="ti ti-user-hexagon me-1 fs-17 align-middle"></i>
                            <span class="align-middle">My Account</span>
                        </Link>
                        <div class="dropdown-divider"></div>
                        <a href="#!" onclick={logout} class="dropdown-item active fw-semibold text-danger">
                            <i class="ti ti-logout me-1 fs-17 align-middle"></i>
                            <span class="align-middle">Sign Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- Topbar End -->
