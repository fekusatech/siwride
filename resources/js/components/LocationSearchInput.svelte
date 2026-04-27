<script lang="ts">
    import { search } from '@/actions/App/Http/Controllers/LocationSearchController';

    interface Suggestion {
        name: string;
        address: string;
        place_id: string | null;
    }

    interface Props {
        /** Current value bound from the parent */
        value: string;
        /** Input ID for label association */
        id?: string;
        /** Placeholder text */
        placeholder?: string;
        /** Whether the field is required */
        required?: boolean;
        /**
         * Visual style variant.
         * - 'default' – no extra styling, inherits parent form styles (hero banner).
         * - 'premium' – matches the booking form's bordered, rounded input look.
         */
        variant?: 'default' | 'premium';
        /** Callback fired when the user selects a suggestion or clears input */
        onchange?: (value: string) => void;
    }

    let {
        value = $bindable(''),
        id = '',
        placeholder = 'Hotel name, area, or landmark...',
        required = false,
        variant = 'default',
        onchange,
    }: Props = $props();

    let suggestions = $state<Suggestion[]>([]);
    let isOpen = $state(false);
    let isLoading = $state(false);
    let activeIndex = $state(-1);
    let debounceTimer: ReturnType<typeof setTimeout> | null = null;
    let inputEl: HTMLInputElement | null = null;
    let containerEl: HTMLDivElement | null = null;

    function handleInput(e: Event) {
        const target = e.target as HTMLInputElement;
        value = target.value;
        onchange?.(value);

        if (debounceTimer) clearTimeout(debounceTimer);

        if (value.length < 2) {
            suggestions = [];
            isOpen = false;
            return;
        }

        debounceTimer = setTimeout(() => fetchSuggestions(value), 280);
    }

    async function fetchSuggestions(q: string) {
        isLoading = true;
        try {
            const url = search.url({ query: { q } });
            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (res.ok) {
                const data = await res.json();
                suggestions = data.suggestions ?? [];
                isOpen = suggestions.length > 0;
                activeIndex = -1;
            }
        } catch {
            suggestions = [];
            isOpen = false;
        } finally {
            isLoading = false;
        }
    }

    function selectSuggestion(suggestion: Suggestion) {
        value = suggestion.name;
        onchange?.(value);
        suggestions = [];
        isOpen = false;
        activeIndex = -1;
    }

    function handleKeydown(e: KeyboardEvent) {
        if (!isOpen) return;

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
        }
    }

    function handleBlur(e: FocusEvent) {
        // Delay so click on suggestion registers first
        setTimeout(() => {
            if (!containerEl?.contains(document.activeElement)) {
                isOpen = false;
                activeIndex = -1;
            }
        }, 150);
    }
</script>

<div class="location-search-wrapper" class:is-premium={variant === 'premium'} bind:this={containerEl}>
    <div class="location-search-field">
        <input
            bind:this={inputEl}
            {id}
            type="text"
            {placeholder}
            {required}
            autocomplete="off"
            bind:value
            oninput={handleInput}
            onkeydown={handleKeydown}
            onblur={handleBlur}
        />
        {#if isLoading}
            <span class="location-search-spinner" aria-hidden="true"></span>
        {/if}
    </div>

    {#if isOpen && suggestions.length > 0}
        <ul class="location-search-dropdown" role="listbox" aria-label="Location suggestions">
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
                    <span class="suggestion-text">
                        <strong>{suggestion.name}</strong>
                        <small>{suggestion.address}</small>
                    </span>
                </li>
            {/each}
        </ul>
    {/if}
</div>

<style>
    .location-search-wrapper {
        position: relative;
        width: 100%;
    }

    .location-search-field {
        position: relative;
        width: 100%;
    }

    /* ── Premium variant: matches booking form's .premium-input styling ── */
    .is-premium input {
        width: 100%;
        padding: 15px 20px;
        border: 2px solid #eef2f5;
        background-color: #fcfcfc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #333;
        transition: border-color 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease;
        outline: none;
        line-height: 1.5;
    }

    .is-premium input::placeholder {
        color: #bbb;
        font-weight: 400;
    }

    .is-premium input:focus {
        background-color: #fff;
        border-color: var(--travhub-base, #e52029);
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }

    /* Spinner sits inside the premium input box */
    .is-premium .location-search-spinner {
        right: 18px;
    }

    .location-search-spinner {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        border: 2px solid rgba(229, 32, 41, 0.2);
        border-top-color: var(--travhub-base, #e52029);
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        pointer-events: none;
    }

    @keyframes spin {
        to { transform: translateY(-50%) rotate(360deg); }
    }

    .location-search-dropdown {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        background: #fff;
        border: 1px solid #e8ecef;
        border-radius: 12px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        list-style: none;
        margin: 0;
        padding: 6px 0;
        z-index: 9999;
        max-height: 300px;
        overflow-y: auto;
    }

    .location-search-dropdown li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 10px 16px;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .location-search-dropdown li:hover,
    .location-search-dropdown li.active {
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
