<script lang="ts">
    import { onMount } from 'svelte';
    import { Sunrise, Sun, Sunset, MoonStar } from 'lucide-svelte';
    import { formatTime12 } from '@/lib/pickupTime';

    let {
        value = $bindable(''),
        placeholder = 'Select pickup time',
        id = '',
        required = false,
        minTime = '',
    }: {
        value?: string;
        placeholder?: string;
        id?: string;
        required?: boolean;
        /** Earliest allowed HH:MM (24hr). '' means no restriction. */
        minTime?: string;
    } = $props();

    let isOpen = $state(false);
    let containerEl: HTMLDivElement;

    // Internal state in 12hr format — initialised from `value` if provided, else 9:00 AM
    function parseInitial(): { h12: number; min: number; pm: boolean } {
        if (value) {
            const parts = value.split(':');
            if (parts.length >= 2) {
                const h24 = parseInt(parts[0]);
                const m = parseInt(parts[1]);
                if (!isNaN(h24) && !isNaN(m)) {
                    const pm = h24 >= 12;
                    const h12 = h24 % 12 === 0 ? 12 : h24 % 12;
                    const min = Math.min(Math.round(m / 5) * 5, 55);
                    return { h12, min, pm };
                }
            }
        }
        return { h12: 9, min: 0, pm: false };
    }

    const _init = parseInitial();
    let hour12 = $state(_init.h12);   // 1–12
    let minute = $state(_init.min);   // 0–55 (step 5)
    let isPm = $state(_init.pm);

    // Time period definitions
    const TIME_PERIODS = [
        { label: 'Morning',   component: Sunrise,  from: 5,  to: 10,  defaultHour12: 8,  defaultMin: 0,  pm: false,
          bannerBg: 'linear-gradient(135deg,#fef3c7,#fde68a)', iconColor: '#d97706', badgeBg: '#fef9ee', badgeText: '#92400e' },
        { label: 'Afternoon', component: Sun,      from: 11, to: 14,  defaultHour12: 1,  defaultMin: 0,  pm: true,
          bannerBg: 'linear-gradient(135deg,#fefce8,#fef08a)', iconColor: '#ca8a04', badgeBg: '#fefde9', badgeText: '#713f12' },
        { label: 'Evening',   component: Sunset,   from: 15, to: 18,  defaultHour12: 4,  defaultMin: 0,  pm: true,
          bannerBg: 'linear-gradient(135deg,#fff7ed,#fed7aa)', iconColor: '#ea580c', badgeBg: '#fff4eb', badgeText: '#7c2d12' },
        { label: 'Night',     component: MoonStar, from: 19, to: 4,   defaultHour12: 9,  defaultMin: 0,  pm: true,
          bannerBg: 'linear-gradient(135deg,#eef2ff,#c7d2fe)', iconColor: '#4f46e5', badgeBg: '#eef2ff', badgeText: '#312e81' },
    ];

    /** Compute 24hr from internal state */
    function to24hr(h12: number, min: number, pm: boolean): { h: number; m: number } {
        let h = h12 % 12;
        if (pm) h += 12;
        return { h, m: min };
    }

    /** Get time period for a given 24hr hour */
    function getPeriod(h24: number) {
        if (h24 >= 5  && h24 <= 10) return TIME_PERIODS[0];
        if (h24 >= 11 && h24 <= 14) return TIME_PERIODS[1];
        if (h24 >= 15 && h24 <= 18) return TIME_PERIODS[2];
        return TIME_PERIODS[3]; // night
    }

    const current24 = $derived(() => to24hr(hour12, minute, isPm));
    /** Period derived from the bound `value` string (for display badge before picker opens) */
    const valuePeriod = $derived(() => {
        if (!value) return TIME_PERIODS[3];
        const parts = value.split(':');
        if (parts.length < 2) return TIME_PERIODS[3];
        const h = parseInt(parts[0]);
        return isNaN(h) ? TIME_PERIODS[3] : getPeriod(h);
    });

    /** Write back to `value` whenever internal state changes — but only once the picker has been opened. */
    let _pickerHasOpened = $state(false);

    const currentPeriod = $derived(() => _pickerHasOpened ? getPeriod(current24().h) : valuePeriod());

    /** Formatted display string for input: "09:30 AM · Morning" */
    const displayValue = $derived(() => {
        if (!value) return '';
        // Parse directly from `value` string so display is always in sync with the bound prop
        const parts = value.split(':');
        if (parts.length < 2) return '';
        const h = parseInt(parts[0]);
        const m = parseInt(parts[1]);
        if (isNaN(h) || isNaN(m)) return '';
        const h12Disp = h % 12 === 0 ? 12 : h % 12;
        const ampm = h < 12 ? 'AM' : 'PM';
        const minStr = String(m).padStart(2, '0');
        const p = getPeriod(h);
        return `${String(h12Disp).padStart(2, '0')}:${minStr} ${ampm}  ·  ${p.label}`;
    });

    /** Sync internal state from `value` (ISO HH:MM 24hr) */
    function syncFromValue() {
        if (!value) return;
        const parts = value.split(':');
        if (parts.length < 2) return;
        const h24 = parseInt(parts[0]);
        const m = parseInt(parts[1]);
        if (isNaN(h24) || isNaN(m)) return;
        isPm = h24 >= 12;
        hour12 = h24 % 12 === 0 ? 12 : h24 % 12;
        // Round minute to nearest 5
        minute = Math.round(m / 5) * 5;
        if (minute >= 60) minute = 55;
    }

    /** Write back to `value` whenever internal state changes — but only once the picker has been opened. */
    // let _pickerHasOpened = $state(false);
    $effect(() => {
        if (!_pickerHasOpened) return;
        const { h, m } = to24hr(hour12, minute, isPm);
        value = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    });

    function open() {
        if (value) {
            syncFromValue();
        } else {
            // Default to 9:00 AM (or minTime if that's later)
            hour12 = 9; minute = 0; isPm = false;
        }
        // If current selection is before minTime, auto-advance
        snapToMinTime();
        _pickerHasOpened = true;
        isOpen = true;
    }

    /** True when the currently-displayed time is before minTime. */
    const isBelowMinTime = $derived(() => {
        if (!minTime) return false;
        const { h, m } = current24();
        const currentStr = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        return currentStr < minTime;
    });

    function close() {
        isOpen = false;
    }

    function incrementHour() {
        hour12 = hour12 >= 12 ? 1 : hour12 + 1;
    }
    function decrementHour() {
        hour12 = hour12 <= 1 ? 12 : hour12 - 1;
    }
    function incrementMinute() {
        if (minute >= 55) { minute = 0; incrementHour(); }
        else minute += 5;
    }
    function decrementMinute() {
        if (minute <= 0) { minute = 55; decrementHour(); }
        else minute -= 5;
    }

    /** Returns true if the period's entire time range is before minTime. */
    function isPeriodFullyPast(period: typeof TIME_PERIODS[0]): boolean {
        if (!minTime) return false;
        // Build HH:MM for the last minute of the period
        const periodEndH = period.to;
        const periodEndStr = `${String(periodEndH).padStart(2, '0')}:59`;
        // Special case: Night wraps to next day (to = 4), never fully past
        if (period.label === 'Night') return false;
        return periodEndStr < minTime;
    }

    /** Snap HH:MM to minTime if the given 24-hr values are before it. */
    function snapToMinTime() {
        if (!minTime) return;
        const { h, m } = to24hr(hour12, minute, isPm);
        const currentStr = `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        if (currentStr < minTime) {
            const [mh, mm] = minTime.split(':').map(Number);
            isPm = mh >= 12;
            hour12 = mh % 12 === 0 ? 12 : mh % 12;
            minute = mm;
        }
    }

    function selectQuickTime(period: typeof TIME_PERIODS[0]) {
        hour12 = period.defaultHour12;
        minute = period.defaultMin;
        isPm = period.pm;
        // If the chosen period default is before minTime, snap forward
        snapToMinTime();
        close();
    }

    function handleClickOutside(e: MouseEvent) {
        if (containerEl && !containerEl.contains(e.target as Node)) {
            close();
        }
    }

    function handleKeyDown(e: KeyboardEvent) {
        if (e.key === 'Escape') { close(); return; }
        if (e.key === 'Enter' && !isOpen) { open(); return; }
    }

    onMount(() => {
        if (value) syncFromValue();
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    });
</script>

<div class="tp-container" bind:this={containerEl}>
    <!-- Input trigger -->
    <div
        class="tp-input-wrap"
        role="button"
        tabindex="0"
        onclick={open}
        onkeydown={handleKeyDown}
        aria-haspopup="true"
        aria-expanded={isOpen}
    >
        <!-- Period icon badge inside input -->
        <span class="tp-input-period-badge" style="background:{value ? currentPeriod().badgeBg : '#f1f5f9'}; color:{value ? currentPeriod().iconColor : '#94a3b8'}">
            {#if value}
                <svelte:component this={currentPeriod().component} size={15} strokeWidth={2} />
            {:else}
                <i class="fas fa-clock" style="font-size:13px;"></i>
            {/if}
        </span>
        <input
            {id}
            type="text"
            class="tp-input tp-input--with-badge"
            value={displayValue()}
            {placeholder}
            {required}
            readonly
            autocomplete="off"
            style="cursor: pointer;"
            tabindex="-1"
            aria-label={placeholder}
        />
        <i class="fas fa-chevron-down tp-chevron" class:rotated={isOpen}></i>
    </div>

    <!-- Picker dropdown -->
    {#if isOpen}
        <div class="tp-dropdown" role="dialog" aria-label="Time picker">

            <!-- Period indicator banner -->
            <div class="tp-period-banner" style="background: {currentPeriod().bannerBg}">
                <span class="tp-period-icon" style="color: {currentPeriod().iconColor}">
                    <svelte:component this={currentPeriod().component} size={36} strokeWidth={1.75} />
                </span>
                <div class="tp-period-text">
                    <span class="tp-period-name" style="color: {currentPeriod().badgeText}">{currentPeriod().label}</span>
                    <span class="tp-period-hint">
                        {#if currentPeriod().label === 'Morning'}05:00 – 10:59
                        {:else if currentPeriod().label === 'Afternoon'}11:00 – 14:59
                        {:else if currentPeriod().label === 'Evening'}15:00 – 18:59
                        {:else}19:00 – 04:59{/if}
                    </span>
                </div>
            </div>

            <!-- Earliest-time hint (shown only when today is selected) -->
            {#if minTime}
                <div class="tp-mintime-hint">
                    <i class="fas fa-clock tp-mintime-hint__icon"></i>
                    <span>Earliest pickup for today is <strong>{formatTime12(minTime)}</strong></span>
                </div>
            {/if}

            <!-- Spinner row -->
            <div class="tp-spinner-row">

                <!-- Hour -->
                <div class="tp-spinner">
                    <button type="button" class="tp-spin-btn tp-spin-btn--up" onclick={incrementHour} aria-label="Increase hour">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <div class="tp-spin-value">{String(hour12).padStart(2, '0')}</div>
                    <button type="button" class="tp-spin-btn tp-spin-btn--down" onclick={decrementHour} aria-label="Decrease hour">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <span class="tp-spin-label">Hour</span>
                </div>

                <div class="tp-colon">:</div>

                <!-- Minute -->
                <div class="tp-spinner">
                    <button type="button" class="tp-spin-btn tp-spin-btn--up" onclick={incrementMinute} aria-label="Increase minute">
                        <i class="fas fa-chevron-up"></i>
                    </button>
                    <div class="tp-spin-value">{String(minute).padStart(2, '0')}</div>
                    <button type="button" class="tp-spin-btn tp-spin-btn--down" onclick={decrementMinute} aria-label="Decrease minute">
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <span class="tp-spin-label">Minute</span>
                </div>

                <!-- AM/PM -->
                <div class="tp-ampm-col">
                    <button
                        type="button"
                        class="tp-ampm-btn"
                        class:tp-ampm-btn--active={!isPm}
                        onclick={() => { isPm = false; }}
                        aria-label="AM"
                        aria-pressed={!isPm}
                    >AM</button>
                    <button
                        type="button"
                        class="tp-ampm-btn"
                        class:tp-ampm-btn--active={isPm}
                        onclick={() => { isPm = true; }}
                        aria-label="PM"
                        aria-pressed={isPm}
                    >PM</button>
                    <span class="tp-spin-label">Period</span>
                </div>
            </div>

            <!-- Quick-select chips -->
            <div class="tp-chips-section">
                <span class="tp-chips-label">Quick select:</span>
                <div class="tp-chips">
                    {#each TIME_PERIODS as period}
                        {@const fullyPast = isPeriodFullyPast(period)}
                        <button
                            type="button"
                            class="tp-chip"
                            class:tp-chip--active={currentPeriod().label === period.label}
                            class:tp-chip--past={fullyPast}
                            style={currentPeriod().label === period.label && !fullyPast ? `background:${period.badgeBg};border-color:${period.iconColor};color:${period.badgeText}` : ''}
                            onclick={() => selectQuickTime(period)}
                            aria-label={fullyPast ? `${period.label} — not available for today` : period.label}
                            title={fullyPast ? `${period.label} has passed for today` : ''}
                        >
                            <span class="tp-chip-icon" style="color:{currentPeriod().label === period.label && !fullyPast ? period.iconColor : 'inherit'}">
                                <svelte:component this={period.component} size={14} strokeWidth={2} />
                            </span>
                            {period.label}
                        </button>
                    {/each}
                </div>
            </div>

            <!-- Confirm / Close -->
            <div class="tp-footer">
                {#if isBelowMinTime()}
                    <!-- Time is too early — show snap-to-minimum button -->
                    <button
                        type="button"
                        class="tp-confirm-btn tp-confirm-btn--warn"
                        onclick={() => { snapToMinTime(); close(); }}
                        title="Jump to earliest available time"
                    >
                        <i class="fas fa-clock"></i>
                        Use Earliest Time ({formatTime12(minTime)})
                    </button>
                {:else}
                    <button type="button" class="tp-confirm-btn" onclick={close}>
                        <i class="fas fa-check"></i> Confirm Time
                    </button>
                {/if}
            </div>
        </div>
    {/if}
</div>

<style>
    /* ── Container ── */
    .tp-container {
        position: relative;
        width: 100%;
    }

    /* ── Input ── */
    .tp-input-wrap {
        position: relative;
        display: flex;
        align-items: center;
        cursor: pointer;
    }
    .tp-input-wrap:focus { outline: none; }

    /* Period badge inside input (left side) */
    .tp-input-period-badge {
        position: absolute;
        left: 10px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        pointer-events: none;
        transition: background 0.25s ease, color 0.25s ease;
        flex-shrink: 0;
    }
    .tp-input {
        width: 100%;
        padding: 13px 40px 13px 14px;
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
    .tp-input--with-badge {
        padding-left: 48px;
    }
    .tp-input-wrap:hover .tp-input,
    .tp-input-wrap:focus-within .tp-input {
        border-color: var(--travhub-base, #e52029);
        background: #fff;
        box-shadow: 0 0 0 4px rgba(229, 32, 41, 0.08);
    }
    .tp-input::placeholder { color: #94a3b8; }
    .tp-chevron {
        position: absolute;
        right: 14px;
        color: #94a3b8;
        font-size: 12px;
        pointer-events: none;
        transition: transform 0.2s;
    }
    .tp-chevron.rotated { transform: rotate(180deg); }

    /* ── Dropdown ── */
    .tp-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        z-index: 9999;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15), 0 4px 16px rgba(0, 0, 0, 0.08);
        border: 1px solid #f1f5f9;
        width: 100%;
        max-width: 360px;
        min-width: 300px;
        overflow: hidden;
        animation: tp-slide-down 0.18s ease-out;
    }
    @keyframes tp-slide-down {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* ── Period Banner ── */
    .tp-period-banner {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 20px;
        border-bottom: 1px solid rgba(0,0,0,0.06);
        transition: background 0.3s ease;
    }
    .tp-period-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255,255,255,0.55);
        backdrop-filter: blur(4px);
        flex-shrink: 0;
        transition: color 0.3s ease;
    }
    .tp-period-text { display: flex; flex-direction: column; gap: 3px; }
    .tp-period-name { font-size: 17px; font-weight: 800; transition: color 0.25s ease; }
    .tp-period-hint { font-size: 12px; color: #64748b; font-weight: 500; }

    /* ── Spinner Row ── */
    .tp-spinner-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 24px 20px 16px;
    }
    .tp-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        position: relative;
    }
    .tp-spin-btn {
        width: 48px;
        height: 44px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 10px;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        transition: all 0.15s;
    }
    .tp-spin-btn:hover {
        border-color: var(--travhub-base, #e52029);
        background: rgba(229, 32, 41, 0.06);
        color: var(--travhub-base, #e52029);
        transform: scale(1.08);
    }
    .tp-spin-btn:active { transform: scale(0.95); }
    .tp-spin-value {
        font-size: 36px;
        font-weight: 900;
        color: #1e293b;
        line-height: 1;
        min-width: 56px;
        text-align: center;
        font-variant-numeric: tabular-nums;
        letter-spacing: -1px;
    }
    .tp-spin-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .tp-colon {
        font-size: 36px;
        font-weight: 900;
        color: #94a3b8;
        margin-top: -20px;
        user-select: none;
    }

    /* ── AM/PM ── */
    .tp-ampm-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
    }
    .tp-ampm-btn {
        width: 52px;
        height: 38px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 8px;
        color: #64748b;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tp-ampm-btn:hover { border-color: var(--travhub-base, #e52029); color: var(--travhub-base, #e52029); }
    .tp-ampm-btn--active {
        background: var(--travhub-base, #e52029);
        border-color: var(--travhub-base, #e52029);
        color: #fff;
        box-shadow: 0 4px 12px rgba(229, 32, 41, 0.25);
    }
    .tp-ampm-btn--active:hover { background: #c0151b; }

    /* ── Quick-Select Chips ── */
    .tp-chips-section {
        padding: 0 20px 16px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
    .tp-chips-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #94a3b8;
    }
    .tp-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .tp-chip {
        padding: 7px 14px;
        border: 2px solid #e2e8f0;
        background: #f8fafc;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .tp-chip-icon {
        display: flex;
        align-items: center;
        transition: color 0.2s ease;
    }
    .tp-chip:hover {
        border-color: var(--travhub-base, #e52029);
        background: rgba(229, 32, 41, 0.04);
        color: var(--travhub-base, #e52029);
    }
    .tp-chip:hover .tp-chip-icon { color: var(--travhub-base, #e52029); }
    .tp-chip--active {
        font-weight: 700;
    }
    /* Fully-past chip (entire period is before minTime today) */
    .tp-chip--past {
        opacity: 0.38;
        cursor: not-allowed;
        pointer-events: none;
    }

    /* ── Footer ── */
    .tp-footer {
        padding: 12px 20px 16px;
        border-top: 1px solid #f1f5f9;
        background: #fafbfc;
    }
    .tp-confirm-btn {
        width: 100%;
        padding: 12px;
        background: var(--travhub-base, #e52029);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(229, 32, 41, 0.25);
    }
    .tp-confirm-btn:hover { background: #c0151b; transform: translateY(-1px); }
    .tp-confirm-btn:active { transform: translateY(0); }
    /* Warn variant — shown when current time selection is before minTime */
    .tp-confirm-btn--warn {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
    }
    .tp-confirm-btn--warn:hover { background: linear-gradient(135deg, #d97706, #b45309); transform: translateY(-1px); }

    /* ── Min-time hint banner ── */
    .tp-mintime-hint {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: #fffbeb;
        border-bottom: 1px solid #fde68a;
        font-size: 13px;
        color: #92400e;
        font-weight: 500;
    }
    .tp-mintime-hint__icon { color: #d97706; font-size: 13px; flex-shrink: 0; }
    .tp-mintime-hint strong { font-weight: 800; color: #78350f; }

    /* ── Mobile ── */
    @media (max-width: 400px) {
        .tp-dropdown { min-width: 280px; }
        .tp-spin-value { font-size: 28px; }
        .tp-spin-btn { width: 42px; height: 38px; }
    }
</style>
