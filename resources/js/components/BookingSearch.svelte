<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';

    interface Order {
        booking_code: string;
        order_number: string;
        pickup_address: string;
        dropoff_address: string;
        date: string;
        status: string;
        customer_name: string | null;
        driver_name: string | null;
    }

    let { variant = 'default' }: { variant?: 'default' | 'compact' | 'hero' } = $props();

    let searchQuery = $state('');
    let searchResults = $state<Order[]>([]);
    let isSearching = $state(false);
    let showDropdown = $state(false);
    let searchError = $state('');

    let debounceTimer: ReturnType<typeof setTimeout> | null = null;

    const formatStatus = (status: string) => {
        const statusMap: Record<string, { text: string; color: string }> = {
            pending: { text: 'Pending', color: '#f59e0b' },
            confirmed: { text: 'Confirmed', color: '#3b82f6' },
            completed: { text: 'Completed', color: '#10b981' },
            cancelled: { text: 'Cancelled', color: '#ef4444' },
        };
        return statusMap[status] || { text: status, color: '#6b7280' };
    };

    const truncate = (text: string, length: number = 30) => {
        if (text.length <= length) return text;
        return text.substring(0, length) + '...';
    };

    const performSearch = async (query: string) => {
        if (query.length < 2) {
            searchResults = [];
            showDropdown = false;
            return;
        }

        isSearching = true;
        searchError = '';

        try {
            const response = await fetch(`/booking/search?query=${encodeURIComponent(query)}`);
            const data = await response.json();

            if (response.ok) {
                searchResults = data.orders || [];
                showDropdown = searchResults.length > 0;
            } else {
                searchError = 'Failed to search bookings';
                searchResults = [];
                showDropdown = false;
            }
        } catch (error) {
            searchError = 'Network error. Please try again.';
            searchResults = [];
            showDropdown = false;
        } finally {
            isSearching = false;
        }
    };

    const handleInput = (e: Event) => {
        const target = e.target as HTMLInputElement;
        searchQuery = target.value;

        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }

        if (searchQuery.length >= 2) {
            debounceTimer = setTimeout(() => {
                performSearch(searchQuery);
            }, 300);
        } else {
            searchResults = [];
            showDropdown = false;
        }
    };

    const handleKeyDown = (e: KeyboardEvent) => {
        if (e.key === 'Enter' && searchQuery.length >= 2) {
            e.preventDefault();
            if (searchResults.length > 0) {
                // Navigate to first result
                router.visit(`/booking/${searchResults[0].booking_code}`);
            } else {
                // Direct navigation attempt
                router.visit(`/booking/${searchQuery.toUpperCase()}`);
            }
            showDropdown = false;
        }

        if (e.key === 'Escape') {
            showDropdown = false;
        }
    };

    const handleResultClick = (bookingCode: string) => {
        searchQuery = '';
        searchResults = [];
        showDropdown = false;
        router.visit(`/booking/${bookingCode}`);
    };

    const handleClickOutside = (e: MouseEvent) => {
        const target = e.target as HTMLElement;
        if (!target.closest('.booking-search-wrapper')) {
            showDropdown = false;
        }
    };
</script>

<svelte:window onclick={handleClickOutside} />

<div class="booking-search-wrapper {variant}" class:has-dropdown={showDropdown}>
    <div class="search-input-container">
        <i class="fas fa-search search-icon"></i>
        <input
            type="text"
            class="search-input"
            placeholder={variant === 'hero' ? 'Enter your booking code (e.g., SWABC123)' : 'Search booking code...'}
            value={searchQuery}
            oninput={handleInput}
            onkeydown={handleKeyDown}
            autocomplete="off"
        />
        {#if isSearching}
            <i class="fas fa-spinner fa-spin loading-icon"></i>
        {:else if searchQuery}
            <button
                type="button"
                class="clear-btn"
                onclick={() => { searchQuery = ''; searchResults = []; showDropdown = false; }}
            >
                <i class="fas fa-times"></i>
            </button>
        {/if}
    </div>

    {#if searchError}
        <div class="search-error">
            <i class="fas fa-exclamation-circle"></i> {searchError}
        </div>
    {/if}

    {#if showDropdown && searchResults.length > 0}
        <div class="search-dropdown">
            <div class="dropdown-header">
                <span>Found {searchResults.length} result{searchResults.length > 1 ? 's' : ''}</span>
            </div>
            {#each searchResults as order}
                <button
                    type="button"
                    class="result-item"
                    onclick={() => handleResultClick(order.booking_code)}
                >
                    <div class="result-main">
                        <div class="result-code">
                            <i class="fas fa-ticket-alt"></i>
                            <span class="booking-code">{order.booking_code}</span>
                            <span class="status-badge" style="background-color: {formatStatus(order.status).color}20; color: {formatStatus(order.status).color}">
                                {formatStatus(order.status).text}
                            </span>
                        </div>
                        <div class="result-route">
                            <span class="location">{truncate(order.pickup_address)}</span>
                            <i class="fas fa-arrow-right route-arrow"></i>
                            <span class="location">{truncate(order.dropoff_address)}</span>
                        </div>
                    </div>
                    <div class="result-meta">
                        <div class="meta-item">
                            <i class="fas fa-calendar"></i>
                            <span>{order.date}</span>
                        </div>
                        {#if order.customer_name}
                            <div class="meta-item">
                                <i class="fas fa-user"></i>
                                <span>{order.customer_name}</span>
                            </div>
                        {/if}
                    </div>
                    <div class="result-action">
                        <span>View Details</span>
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </button>
            {/each}
        </div>
    {:else if showDropdown && searchQuery.length >= 2 && !isSearching && searchResults.length === 0}
        <div class="search-dropdown empty">
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <p>No bookings found for "{searchQuery}"</p>
                <span>Try entering the full booking code</span>
            </div>
        </div>
    {/if}
</div>

<style>
    .booking-search-wrapper {
        position: relative;
        width: 100%;
    }

    .search-input-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-icon {
        position: absolute;
        left: 16px;
        color: #94a3b8;
        font-size: 18px;
        z-index: 2;
    }

    .search-input {
        width: 100%;
        padding: 14px 44px 14px 48px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 500;
        background: white;
        transition: all 0.2s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: var(--travhub-base, #e52029);
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.1);
    }

    .loading-icon,
    .clear-btn {
        position: absolute;
        right: 16px;
        color: #94a3b8;
        background: none;
        border: none;
        cursor: pointer;
        padding: 4px;
    }

    .clear-btn:hover {
        color: #ef4444;
    }

    /* Hero Variant */
    .hero .search-input {
        padding: 18px 52px 18px 56px;
        font-size: 16px;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .hero .search-icon {
        left: 20px;
        font-size: 20px;
    }

    .hero .loading-icon,
    .hero .clear-btn {
        right: 20px;
    }

    /* Compact Variant */
    .compact .search-input {
        padding: 10px 36px 10px 40px;
        font-size: 14px;
        border-radius: 8px;
    }

    .compact .search-icon {
        left: 12px;
        font-size: 14px;
    }

    .compact .loading-icon,
    .compact .clear-btn {
        right: 12px;
    }

    /* Dropdown */
    .search-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 8px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        z-index: 1000;
        max-height: 400px;
        overflow-y: auto;
    }

    .dropdown-header {
        padding: 12px 16px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .result-item {
        width: 100%;
        padding: 16px;
        border: none;
        border-bottom: 1px solid #f1f5f9;
        background: white;
        text-align: left;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .result-item:last-child {
        border-bottom: none;
        border-radius: 0 0 12px 12px;
    }

    .result-item:hover {
        background: #f8fafc;
    }

    .result-main {
        margin-bottom: 8px;
    }

    .result-code {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }

    .result-code i {
        color: var(--travhub-base, #e52029);
        font-size: 14px;
    }

    .booking-code {
        font-weight: 700;
        font-size: 15px;
        color: #1e293b;
        font-family: monospace;
    }

    .status-badge {
        padding: 2px 8px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
    }

    .result-route {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .route-arrow {
        font-size: 10px;
        color: #94a3b8;
    }

    .result-meta {
        display: flex;
        gap: 16px;
        margin-bottom: 12px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #94a3b8;
    }

    .result-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
        font-size: 13px;
        font-weight: 600;
        color: var(--travhub-base, #e52029);
    }

    .result-action i {
        font-size: 12px;
    }

    /* Empty State */
    .search-dropdown.empty {
        padding: 24px;
    }

    .empty-state {
        text-align: center;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 32px;
        margin-bottom: 12px;
        display: block;
    }

    .empty-state p {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
        margin: 0 0 4px;
    }

    .empty-state span {
        font-size: 12px;
    }

    /* Error */
    .search-error {
        margin-top: 8px;
        padding: 8px 12px;
        background: #fef2f2;
        border-radius: 8px;
        font-size: 13px;
        color: #ef4444;
    }

    .search-error i {
        margin-right: 6px;
    }

    /* Has dropdown */
    .has-dropdown .search-input {
        border-radius: 12px 12px 0 0;
    }
</style>
