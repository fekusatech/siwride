<script lang="ts">
    import { onMount, tick } from 'svelte';

    let { 
        id, 
        label, 
        options = [], 
        value = $bindable(''), 
        placeholder = 'Select an option', 
        error = '', 
        disabled = false 
    } = $props();

    let isOpen = $state(false);
    let search = $state('');
    let container: HTMLDivElement;

    let filteredOptions = $derived(
        search.trim() === '' 
            ? options 
            : options.filter(opt => opt.label.toLowerCase().includes(search.toLowerCase()))
    );

    let selectedLabel = $derived(
        options.find(opt => opt.value == value)?.label || placeholder
    );

    function clear() {
        value = '';
        isOpen = false;
        search = '';
    }

    function toggle() {
        if (disabled) return;
        isOpen = !isOpen;
        if (isOpen) {
            search = '';
            tick().then(() => {
                const input = container.querySelector('input');
                input?.focus();
            });
        }
    }

    function select(val: any) {
        value = val;
        isOpen = false;
        search = '';
    }

    // Close when clicking outside
    onMount(() => {
        const handleClick = (e: MouseEvent) => {
            if (container && !container.contains(e.target as Node)) {
                isOpen = false;
            }
        };
        document.addEventListener('click', handleClick);
        return () => document.removeEventListener('click', handleClick);
    });
</script>

<div class="searchable-select-container position-relative" bind:this={container}>
    {#if label}
        <label for={id} class="form-label">{label}</label>
    {/if}
    
    <div 
        class="form-select d-flex align-items-center justify-content-between {error ? 'is-invalid' : ''} {disabled ? 'bg-light' : 'bg-white cursor-pointer'}"
        onclick={toggle}
        onkeydown={(e) => e.key === 'Enter' && toggle()}
        role="button"
        tabindex="0"
    >
        <span class={value ? 'text-dark' : 'text-muted'}>{selectedLabel}</span>
    </div>

    {#if isOpen}
        <div class="dropdown-menu show w-100 shadow-lg border-light mt-1 p-2" style="max-height: 300px; overflow-y: auto; z-index: 1060;">
            <div class="mb-2">
                <input 
                    type="text" 
                    class="form-control form-control-sm" 
                    placeholder="Type to search..." 
                    bind:value={search}
                    onclick={(e) => e.stopPropagation()}
                />
            </div>
            <div class="options-list">
                {#if value}
                    <button 
                        type="button"
                        class="dropdown-item text-danger border-bottom mb-1"
                        onclick={(e) => { e.stopPropagation(); clear(); }}
                    >
                        <i class="ti ti-x me-1"></i> Clear Selection
                    </button>
                {/if}
                {#each filteredOptions as option}
                    <button 
                        type="button"
                        class="dropdown-item rounded py-2 {value == option.value ? 'active' : ''}"
                        onclick={(e) => { e.stopPropagation(); select(option.value); }}
                    >
                        {option.label}
                    </button>
                {:else}
                    <div class="dropdown-item disabled text-muted italic">No results found</div>
                {/each}
            </div>
        </div>
    {/if}

    {#if error}
        <div class="invalid-feedback d-block">{error}</div>
    {/if}
</div>

<style>
    .cursor-pointer {
        cursor: pointer;
    }
    .searchable-select-container :global(.dropdown-item.active) {
        background-color: var(--bs-primary);
        color: white;
    }
    .options-list {
        max-height: 200px;
        overflow-y: auto;
    }
    /* Simple scrollbar styling */
    .options-list::-webkit-scrollbar {
        width: 4px;
    }
    .options-list::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 2px;
    }
</style>
