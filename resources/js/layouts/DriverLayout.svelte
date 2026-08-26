<script lang="ts">
    import { type Snippet } from 'svelte';
    import { Link, page, router } from '@inertiajs/svelte';
    import {
        wallet as walletRoute,
        profile as profileRoute,
    } from '@/routes/driver';

    let { children }: { children: Snippet } = $props();

    const settings = $derived(page.props.settings as any);
    const driver = $derived(page.props.auth?.driver as any);

    let menuOpen = $state(false);
    let menuRef: HTMLDivElement | undefined = $state();

    function logout() {
        menuOpen = false;
        router.post('/driver/logout');
    }

    function toggleMenu() {
        menuOpen = !menuOpen;
    }

    function closeMenu() {
        menuOpen = false;
    }

    function handleOutsideClick(event: MouseEvent) {
        if (menuRef && !menuRef.contains(event.target as Node)) {
            menuOpen = false;
        }
    }

    $effect(() => {
        if (menuOpen) {
            document.addEventListener('click', handleOutsideClick);
            return () =>
                document.removeEventListener('click', handleOutsideClick);
        }
    });

    const avatarUrl = $derived(
        driver?.image ? `/storage/${driver.image}` : null,
    );
    const initials = $derived(
        driver?.firstname?.charAt(0)?.toUpperCase() ||
            driver?.name?.charAt(0)?.toUpperCase() ||
            '?',
    );
</script>

<div class="d-flex flex-column min-vh-100" style="background: #f7f9fa;">
    <nav
        class="navbar navbar-expand-lg navbar-dark"
        style="background: #1e293b;"
    >
        <div class="container">
            <Link
                href="/driver/dashboard"
                class="navbar-brand d-flex align-items-center gap-2"
            >
                <img
                    src={settings?.logo || '/assets/images/siwride_logo.png'}
                    alt="Siwride"
                    style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #fff; padding: 2px;"
                />
                <span>Driver Portal</span>
            </Link>
            <div class="d-flex align-items-center gap-3">
                <Link href="/driver/dashboard" class="nav-link text-white-50"
                    >Dashboard</Link
                >
                <Link href={walletRoute.url()} class="nav-link text-white-50"
                    >Wallet</Link
                >
                <Link href="/driver/services" class="nav-link text-white-50"
                    >My Services</Link
                >
                <Link
                    href="/driver/services/create"
                    class="nav-link text-white-50">New Service</Link
                >

                <div class="dropdown position-relative" bind:this={menuRef}>
                    <button
                        type="button"
                        class="btn btn-link p-0 border-0"
                        onclick={toggleMenu}
                        aria-label="Account menu"
                        style="width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center;"
                    >
                        {#if avatarUrl}
                            <img
                                src={avatarUrl}
                                alt={driver?.name || 'Profile'}
                                class="rounded-circle"
                                style="width: 36px; height: 36px; object-fit: cover; border: 2px solid rgba(255,255,255,0.3);"
                            />
                        {:else}
                            <div
                                class="rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                style="width: 36px; height: 36px; background: var(--travhub-base, #d11f1f); color: #fff; font-size: 14px;"
                            >
                                {initials}
                            </div>
                        {/if}
                    </button>

                    {#if menuOpen}
                        <div
                            class="dropdown-menu dropdown-menu-end shadow-sm show"
                            style="min-width: 160px; margin-top: 8px;"
                        >
                            <div class="px-3 py-2 border-bottom">
                                <div class="small text-muted">Signed in as</div>
                                <div
                                    class="fw-semibold text-truncate"
                                    style="max-width: 180px;"
                                >
                                    {driver?.name}
                                </div>
                            </div>
                            <Link
                                href={profileRoute.url()}
                                class="dropdown-item d-flex align-items-center gap-2"
                                onclick={closeMenu}
                            >
                                <i class="ti ti-user"></i>Profile
                            </Link>
                            <button
                                type="button"
                                class="dropdown-item d-flex align-items-center gap-2 text-danger"
                                onclick={logout}
                            >
                                <i class="ti ti-logout"></i>Logout
                            </button>
                        </div>
                    {/if}
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4">
        <div class="container">
            {@render children()}
        </div>
    </main>
</div>
