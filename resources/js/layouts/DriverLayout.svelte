<script lang="ts">
    import { type Snippet } from 'svelte';
    import { Link, page, router } from '@inertiajs/svelte';

    let { children }: { children: Snippet } = $props();

    const settings = $derived(page.props.settings as any);
    const driver = $derived(page.props.auth?.driver as any);

    function logout() {
        router.post('/driver/logout');
    }
</script>

<div class="d-flex flex-column min-vh-100" style="background: #f7f9fa;">
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #1e293b;">
        <div class="container">
            <Link href="/driver/dashboard" class="navbar-brand d-flex align-items-center gap-2">
                <img
                    src={settings?.logo || '/assets/images/siwride_logo.png'}
                    alt="Siwride"
                    style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; background: #fff; padding: 2px;"
                />
                <span>Driver Portal</span>
            </Link>
            <div class="d-flex align-items-center gap-3">
                <Link href="/driver/dashboard" class="nav-link text-white-50">Dashboard</Link>
                <Link href="/driver/services" class="nav-link text-white-50">My Services</Link>
                <Link href="/driver/services/create" class="nav-link text-white-50">New Service</Link>
                <span class="text-white-50 small d-none d-md-inline">{driver?.name}</span>
                <button type="button" class="btn btn-sm btn-outline-light" onclick={logout}>Logout</button>
            </div>
        </div>
    </nav>

    <main class="flex-grow-1 py-4">
        <div class="container">
            {@render children()}
        </div>
    </main>
</div>
