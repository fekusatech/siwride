<script lang="ts">
    import { onMount } from 'svelte';

    let {
        value = $bindable(''),
        placeholder = 'Select pickup date',
        id = '',
        minDate = '',
        required = false,
        hideIcon = false,
        hideChevron = false,
    }: {
        value?: string;
        placeholder?: string;
        id?: string;
        minDate?: string;
        required?: boolean;
        hideIcon?: boolean;
        hideChevron?: boolean;
    } = $props();

    let isOpen = $state(false);
    let viewYear = $state(new Date().getFullYear());
    let viewMonth = $state(new Date().getMonth()); // 0-indexed
    let containerEl: HTMLDivElement;

    const DAYS = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
    const MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    // Tomorrow in local time (minimum allowed date for bookings)
    const tomorrowStr = $derived(() => {
        const d = new Date();
        d.setDate(d.getDate() + 1);
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    });

    // Today in local time (used only to highlight the current day cell in the calendar)
    const todayStr = $derived(() => {
        const d = new Date();
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    });

    const minDateStr = $derived(minDate || tomorrowStr());

    /** Human-readable display value */
    const displayValue = $derived(() => {
        if (!value) return '';
        const [y, m, d] = value.split('-').map(Number);
        const date = new Date(y, m - 1, d);
        const dayName = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][date.getDay()];
        const monthName = MONTHS[date.getMonth()];
        return `${dayName}, ${d} ${monthName} ${y}`;
    });

    /** Calendar cells for current view */
    const calendarDays = $derived(() => {
        const firstDay = new Date(viewYear, viewMonth, 1).getDay();
        const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();
        const daysInPrevMonth = new Date(viewYear, viewMonth, 0).getDate();
        const cells: { date: string; day: number; inMonth: boolean; isToday: boolean; isSelected: boolean; isPast: boolean }[] = [];

        // Fill prefix from prev month
        for (let i = firstDay - 1; i >= 0; i--) {
            const d = daysInPrevMonth - i;
            const prevMonth = viewMonth === 0 ? 12 : viewMonth;
            const prevYear = viewMonth === 0 ? viewYear - 1 : viewYear;
            const dateStr = `${prevYear}-${String(prevMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            cells.push({ date: dateStr, day: d, inMonth: false, isToday: false, isSelected: false, isPast: true });
        }

        // Current month days
        for (let d = 1; d <= daysInMonth; d++) {
            const dateStr = `${viewYear}-${String(viewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            cells.push({
                date: dateStr,
                day: d,
                inMonth: true,
                isToday: dateStr === todayStr(),
                isSelected: dateStr === value,
                isPast: dateStr < minDateStr,
            });
        }

        // Fill suffix to complete last row
        const remaining = 42 - cells.length;
        for (let d = 1; d <= remaining; d++) {
            const nextMonth = viewMonth === 11 ? 1 : viewMonth + 2;
            const nextYear = viewMonth === 11 ? viewYear + 1 : viewYear;
            const dateStr = `${nextYear}-${String(nextMonth).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            cells.push({ date: dateStr, day: d, inMonth: false, isToday: false, isSelected: false, isPast: false });
        }

        return cells;
    });

    function open() {
        // Sync view to selected value, or default to today
        if (value) {
            const [y, m] = value.split('-').map(Number);
            viewYear = y;
            viewMonth = m - 1;
        } else {
            viewYear = new Date().getFullYear();
            viewMonth = new Date().getMonth();
        }
        isOpen = true;
    }

    function close() {
        isOpen = false;
    }

    function selectDate(cell: { date: string; isPast: boolean; inMonth: boolean }) {
        if (cell.isPast && !cell.inMonth) return;
        if (cell.isPast) return;
        value = cell.date;
        close();
    }

    function prevMonth() {
        if (viewMonth === 0) {
            viewMonth = 11;
            viewYear--;
        } else {
            viewMonth--;
        }
    }

    function nextMonth() {
        if (viewMonth === 11) {
            viewMonth = 0;
            viewYear++;
        } else {
            viewMonth++;
        }
    }

    function handleKeyDown(e: KeyboardEvent) {
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'Enter' && !isOpen) { open(); return; }
    }

    function handleClickOutside(e: MouseEvent) {
        if (containerEl && !containerEl.contains(e.target as Node)) {
            close();
        }
    }

    onMount(() => {
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    });
</script>

<div class="dp-container" bind:this={containerEl}>
    <!-- Input trigger -->
    <div class="dp-input-wrap" role="button" tabindex="0" onclick={open} onkeydown={handleKeyDown} aria-haspopup="true" aria-expanded={isOpen}>
        {#if !hideIcon}
            <i class="fas fa-calendar-alt dp-input-icon"></i>
        {/if}
        <input
            {id}
            type="text"
            class="dp-input"
            class:dp-input--no-icon={hideIcon}
            value={displayValue()}
            {placeholder}
            {required}
            readonly
            autocomplete="off"
            style="cursor: pointer;"
            tabindex="-1"
            aria-label={placeholder}
        />
        {#if !hideChevron}
            <i class="fas fa-chevron-down dp-chevron" class:rotated={isOpen}></i>
        {/if}
    </div>

    <!-- Calendar dropdown -->
    {#if isOpen}
        <div class="dp-calendar" role="dialog" aria-label="Date picker">
            <!-- Header -->
            <div class="dp-header">
                <button type="button" class="dp-nav-btn" onclick={prevMonth} aria-label="Previous month">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="dp-month-year">
                    <span class="dp-month-name">{MONTHS[viewMonth]}</span>
                    <span class="dp-year">{viewYear}</span>
                </div>
                <button type="button" class="dp-nav-btn" onclick={nextMonth} aria-label="Next month">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>

            <!-- Day labels -->
            <div class="dp-weekdays">
                {#each DAYS as day}
                    <div class="dp-weekday">{day}</div>
                {/each}
            </div>

            <!-- Date cells -->
            <div class="dp-grid">
                {#each calendarDays() as cell}
                    <button
                        type="button"
                        class="dp-day"
                        class:dp-day--other-month={!cell.inMonth}
                        class:dp-day--today={cell.isToday}
                        class:dp-day--selected={cell.isSelected}
                        class:dp-day--disabled={cell.isPast}
                        disabled={cell.isPast}
                        onclick={() => selectDate(cell)}
                        aria-label={cell.date}
                        aria-pressed={cell.isSelected}
                        aria-disabled={cell.isPast}
                    >
                        {cell.day}
                        {#if cell.isToday}
                            <span class="dp-today-dot"></span>
                        {/if}
                    </button>
                {/each}
            </div>

            <!-- Footer -->
            <div class="dp-footer">
                <button type="button" class="dp-clear-btn" onclick={() => { value = ''; close(); }}>
                    Clear Selection
                </button>
            </div>
        </div>
    {/if}
</div>

<style>
    /* ── Container ── */
    .dp-container {
        position: relative;
        width: 100%;
    }

    /* ── Input Trigger ── */
    .dp-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .dp-input-wrap:focus {
        outline: none;
    }
    .dp-input-icon {
        position: absolute;
        left: 14px;
        color: var(--travhub-base, #e52029);
        font-size: 15px;
        pointer-events: none;
        z-index: 1;
    }
    .dp-input {
        width: 100%;
        padding: 13px 40px 13px 40px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s;
        outline: none;
        cursor: pointer;
    }
    /* When the host already provides its own left icon, remove the left padding offset */
    .dp-input--no-icon {
        padding-left: 0 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        font-size: inherit;
        font-weight: inherit;
        color: inherit;
    }
    .dp-input--no-icon::placeholder { color: inherit; opacity: 0.6; }
    .dp-input-wrap:focus-within .dp-input:not(.dp-input--no-icon),
    .dp-input-wrap:hover .dp-input:not(.dp-input--no-icon) {
        border-color: var(--travhub-base, #e52029);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.08);
    }
    .dp-input::placeholder { color: #94a3b8; }
    .dp-chevron {
        position: absolute;
        right: 14px;
        color: #94a3b8;
        font-size: 12px;
        pointer-events: none;
        transition: transform 0.2s;
    }
    .dp-chevron.rotated { transform: rotate(180deg); }

    /* ── Calendar Dropdown ── */
    .dp-calendar {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 9999;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #f1f5f9;
        min-width: 320px;
        width: 100%;
        max-width: 360px;
        padding: 0;
        overflow: hidden;
        animation: dp-slide-down 0.18s ease-out;
    }
    @keyframes dp-slide-down {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Header ── */
    .dp-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 20px;
        background: linear-gradient(135deg, var(--travhub-base, #e52029), #c0151b);
        color: #fff;
    }
    .dp-nav-btn {
        width: 36px;
        height: 36px;
        border: none;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        transition: background 0.15s;
        flex-shrink: 0;
    }
    .dp-nav-btn:hover { background: rgba(255, 255, 255, 0.35); }
    .dp-month-year {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
    }
    .dp-month-name {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: 0.2px;
    }
    .dp-year {
        font-size: 13px;
        font-weight: 500;
        opacity: 0.85;
    }

    /* ── Weekday Labels ── */
    .dp-weekdays {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        /* same horizontal padding as dp-grid so columns line up */
        padding: 10px 8px 4px;
        background: #fafbfc;
        border-bottom: 1px solid #f1f5f9;
    }
    .dp-weekday {
        text-align: center;
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 4px 0;
    }

    /* ── Date Grid ── */
    .dp-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        /* same horizontal padding as dp-weekdays */
        padding: 6px 8px 10px;
        gap: 3px;
    }
    .dp-day {
        position: relative;
        /* fixed height instead of aspect-ratio, which breaks in narrow containers */
        height: 40px;
        width: 100%;
        border: none;
        background: transparent;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 2px;
        transition: background 0.12s, color 0.12s, transform 0.1s;
    }
    .dp-day:hover:not(:disabled) {
        background: rgba(229, 32, 41, 0.08);
        color: var(--travhub-base, #e52029);
        transform: scale(1.05);
    }
    .dp-day:active:not(:disabled) { transform: scale(0.95); }
    .dp-day--other-month { color: #cbd5e1; font-weight: 400; }
    .dp-day--today {
        outline: 2px solid var(--travhub-base, #e52029);
        outline-offset: -2px;
        color: var(--travhub-base, #e52029);
        font-weight: 800;
    }
    .dp-day--selected {
        background: var(--travhub-base, #e52029) !important;
        color: #fff !important;
        outline: none;
        font-weight: 800;
        box-shadow: 0 4px 12px rgba(229, 32, 41, 0.3);
        transform: scale(1.05);
    }
    .dp-day--disabled {
        color: #cbd5e1;
        cursor: not-allowed;
        opacity: 0.5;
    }
    .dp-day--disabled:hover { background: transparent; transform: none; }
    .dp-today-dot {
        width: 4px;
        height: 4px;
        border-radius: 50%;
        background: currentColor;
    }
    .dp-day--selected .dp-today-dot { background: rgba(255, 255, 255, 0.7); }

    /* ── Footer ── */
    .dp-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px 14px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .dp-clear-btn {
        padding: 6px 16px;
        border: none;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }

    .dp-clear-btn {
        background: #f1f5f9;
        color: #64748b;
    }
    .dp-clear-btn:hover { background: #e2e8f0; color: #334155; }

    /* ── Mobile ── */
    @media (max-width: 400px) {
        .dp-calendar { min-width: 290px; }
        .dp-day { font-size: 13px; min-height: 38px; }
        .dp-grid { padding: 6px 8px; }
    }
</style>
