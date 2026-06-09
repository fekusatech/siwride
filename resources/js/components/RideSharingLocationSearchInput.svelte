<script lang="ts">
    import { onMount } from 'svelte';

    interface Location {
        id: number | string;
        name: string;
        address?: string;
        area?: string;
    }

    interface Props {
        /** The list of predefined locations from the backend */
        locations: Location[];
        /** The selected location's ID */
        value: string | number;
        /** Input ID for label association */
        id?: string;
        /** Form field name */
        name?: string;
        /** Placeholder text */
        placeholder?: string;
        /** Whether the field is required */
        required?: boolean;
        /** Callback fired when the user selects a suggestion */
        onchange?: (value: string | number) => void;
    }

    let {
        locations = [],
        value = $bindable(''),
        id = '',
        name = '',
        placeholder = 'Search city...',
        required = false,
        onchange,
    }: Props = $props();

    let searchQuery = $state('');
    let suggestions = $state<Location[]>([]);
    let isOpen = $state(false);
    let activeIndex = $state(-1);
    let containerEl: HTMLDivElement | null = null;
    let inputEl: HTMLInputElement | null = null;

    // Initialize searchQuery based on existing value
    onMount(() => {
        if (value) {
            const loc = locations.find((l) => l.id == value);
            if (loc) {
                searchQuery = loc.address ? `${loc.name} - ${loc.address}` : loc.name;
            }
        }
    });

    function handleInput(e?: Event) {
        if (e) {
            const target = e.target as HTMLInputElement;
            searchQuery = target.value;
        }
        
        let matchesSelection = false;
        if (value) {
            const loc = locations.find((l) => l.id == value);
            const expectedStr = loc ? (loc.address ? `${loc.name} - ${loc.address}` : loc.name) : '';
            if (loc && searchQuery === expectedStr) {
                matchesSelection = true;
            } else {
                value = '';
                onchange?.(value);
            }
        }

        if (searchQuery.length < 1) {
            suggestions = locations;
            isOpen = suggestions.length > 0;
            return;
        }

        if (matchesSelection) {
            suggestions = locations;
        } else {
            const query = searchQuery.toLowerCase();
            suggestions = locations.filter((loc) => {
                const searchStr = loc.address ? `${loc.name} - ${loc.address}` : loc.name;
                return searchStr.toLowerCase().includes(query);
            });
        }

        isOpen = suggestions.length > 0;
        activeIndex = -1;
    }

    function selectSuggestion(suggestion: Location) {
        value = suggestion.id;
        searchQuery = suggestion.address ? `${suggestion.name} - ${suggestion.address}` : suggestion.name;
        onchange?.(value);
        
        suggestions = [];
        isOpen = false;
        activeIndex = -1;
    }

    function handleKeydown(e: KeyboardEvent) {
        if (!isOpen && e.key !== 'Escape') {
            // Open on down arrow if there is text
            if (e.key === 'ArrowDown' && searchQuery.length >= 1) {
                handleInput({ target: inputEl } as any);
            }
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeIndex = Math.min(activeIndex + 1, suggestions.length - 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeIndex = Math.max(activeIndex - 1, -1);
        } else if (e.key === 'Enter' && activeIndex >= 0) {
            e.preventDefault();
            selectSuggestion(suggestions[activeIndex]);
        } else if (e.key === 'Escape') {
            isOpen = false;
            activeIndex = -1;
            
            // Revert search query if no valid selection was made
            if (!value) {
                searchQuery = '';
            }
        }
    }

    function handleBlur() {
        // Delay so click on suggestion registers first
        setTimeout(() => {
            if (!containerEl?.contains(document.activeElement)) {
                isOpen = false;
                activeIndex = -1;
                
                // If they typed something but didn't select, reset the input to the selected value or empty
                if (value) {
                    const loc = locations.find((l) => l.id == value);
                    if (loc) {
                        searchQuery = loc.name;
                    }
                } else {
                    searchQuery = '';
                }
            }
        }, 150);
    }
    
    // Watch for value changes from parent
    $effect(() => {
        if (value && !isOpen) {
            const loc = locations.find((l) => l.id == value);
            if (loc) {
                const expectedStr = loc.address ? `${loc.name} - ${loc.address}` : loc.name;
                if (searchQuery !== expectedStr) {
                    searchQuery = expectedStr;
                }
            }
        }
    });
</script>

<div
    class="rs-location-search-wrapper"
    bind:this={containerEl}
>
    <!-- Hidden input to hold actual value for form submissions -->
    <input type="hidden" {name} {value} {required} />
    
    <div class="rs-location-search-field">
        <input
            bind:this={inputEl}
            {id}
            type="text"
            {placeholder}
            autocomplete="off"
            bind:value={searchQuery}
            oninput={handleInput}
            onkeydown={handleKeydown}
            onblur={handleBlur}
            onfocus={() => {
                if (!isOpen) {
                    handleInput();
                }
            }}
        />
        <!-- Custom clear button if value exists -->
        {#if value}
            <button
                type="button"
                class="rs-clear-btn"
                onclick={() => {
                    value = '';
                    searchQuery = '';
                    onchange?.(value);
                    inputEl?.focus();
                }}
                aria-label="Clear selection"
            >
                <i class="ti ti-x"></i>
            </button>
        {/if}
    </div>

    {#if isOpen && suggestions.length > 0}
        <ul
            class="rs-location-search-dropdown"
            role="listbox"
            aria-label="Location suggestions"
        >
            {#each suggestions as suggestion, i}
                <li
                    role="option"
                    aria-selected={i === activeIndex}
                    class:active={i === activeIndex}
                    onmousedown={() => selectSuggestion(suggestion)}
                    onmouseover={() => (activeIndex = i)}
                    onfocus={() => (activeIndex = i)}
                >
                    <span class="suggestion-icon">
                        <i class="icon-pin-2"></i>
                    </span>
                    <span class="suggestion-text d-flex flex-column">
                        <strong>{suggestion.name}</strong>
                        {#if suggestion.address}
                            <small class="text-muted" style="font-size: 0.85em;">{suggestion.address}</small>
                        {/if}
                    </span>
                </li>
            {/each}
        </ul>
    {/if}
</div>

<style>
    .rs-location-search-wrapper {
        position: relative;
        width: 100%;
    }

    .rs-location-search-field {
        position: relative;
        width: 100%;
    }

    /* Style similar to normal form inputs */
    .rs-location-search-field input {
        width: 100%;
        background-color: transparent;
        border: none;
        outline: none;
        font-size: 15px;
        color: var(--travhub-black, #222);
        font-weight: 500;
        padding-right: 30px;
    }

    .rs-location-search-field input::placeholder {
        color: #888;
        font-weight: 400;
    }
    
    .rs-clear-btn {
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        color: #888;
        cursor: pointer;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 5px;
        transition: color 0.2s ease;
    }
    
    .rs-clear-btn:hover {
        color: var(--travhub-base, #e52029);
    }

    .rs-location-search-dropdown {
        position: absolute;
        top: calc(100% + 15px);
        left: 0;
        width: 150%;
        max-width: calc(100vw - 40px);
        min-width: 250px;
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        list-style: none;
        margin: 0;
        padding: 6px 0;
        z-index: 9999;
        max-height: 250px;
        overflow-y: auto;
    }

    .rs-location-search-dropdown li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 16px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .rs-location-search-dropdown li:hover,
    .rs-location-search-dropdown li.active {
        background: #fff5f5;
    }

    .suggestion-icon {
        flex-shrink: 0;
        width: 28px;
        height: 28px;
        background: rgba(229, 32, 41, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--travhub-base, #e52029);
        font-size: 12px;
        margin-top: 2px;
    }

    .suggestion-text {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }

    .suggestion-text strong {
        font-size: 14px;
        font-weight: 600;
        color: #222;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .suggestion-text small {
        font-size: 12px;
        color: #888;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
