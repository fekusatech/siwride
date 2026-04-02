<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    let { links } = $props();

    function getLabel(label: string) {
        if (label.includes('Previous'))
            return '<i class="ti ti-chevron-left align-middle"></i>';
        if (label.includes('Next'))
            return '<i class="ti ti-chevron-right align-middle"></i>';
        return label;
    }
</script>

{#if links && links.length > 3}
    <div
        class="d-flex align-items-center justify-content-between px-3 py-3 bg-transparent border-top"
    >
        <!-- Mobile View (Previous/Next only) -->
        <div class="d-flex d-sm-none w-100 justify-content-between">
            {#each links as link}
                {#if link.label.includes('Previous') || link.label.includes('Next')}
                    <Link
                        href={link.url || '#'}
                        class="btn btn-sm btn-outline-secondary {!link.url
                            ? 'disabled'
                            : ''}"
                        preserveState={true}
                    >
                        {@html getLabel(link.label)}
                    </Link>
                {/if}
            {/each}
        </div>

        <!-- Desktop View (Numbered) -->
        <div
            class="d-none d-sm-flex align-items-center justify-content-between w-100"
        >
            <div>
                <p class="mb-0 text-muted small">Showing pages of navigation</p>
            </div>
            <nav>
                <ul class="pagination pagination-boxed mb-0">
                    {#each links as link}
                        <li
                            class="page-item {link.active
                                ? 'active'
                                : ''} {!link.url ? 'disabled' : ''}"
                        >
                            <Link
                                href={link.url || '#'}
                                class="page-link"
                                preserveState={true}
                            >
                                {@html getLabel(link.label)}
                            </Link>
                        </li>
                    {/each}
                </ul>
            </nav>
        </div>
    </div>
{/if}
