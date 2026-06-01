<script lang="ts">
    import type { Snippet } from 'svelte';
    import { page } from '@inertiajs/svelte';

    let {
        title = '',
        children,
    }: {
        title?: string;
        children?: Snippet;
    } = $props();

    const appName = $derived(page.props.settings?.business_name || import.meta.env.VITE_APP_NAME || 'Siwride');
    const fullTitle = $derived(title ? `${title} - ${appName}` : appName);
    const faviconUrl = $derived(page.props.settings?.logo || '/assets/images/siwride_logo.png');
</script>

<svelte:head>
    <title>{fullTitle}</title>
    <link rel="icon" href={faviconUrl} />
    {@render children?.()}
</svelte:head>
