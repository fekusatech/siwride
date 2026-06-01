/**
 * Pickup-time validation utilities.
 *
 * When the customer selects today as the pickup date, the earliest
 * allowed time is the current time plus PICKUP_BUFFER_MINUTES.
 * Future dates have no restriction.
 *
 * The backend applies an additional PICKUP_GRACE_PERIOD_MINUTES window
 * to accommodate time passing while the customer fills out the form.
 */

/** Minimum lead time the customer must book ahead (minutes). */
export const PICKUP_BUFFER_MINUTES = 30;

/**
 * Backend grace period (minutes).
 *
 * The backend will accept a booking whose pickup time is up to
 * PICKUP_GRACE_PERIOD_MINUTES behind the strict minimum, as long as it
 * does not exceed the buffer entirely. This forgives small delays that
 * occur while the customer reviews the form.
 */
export const PICKUP_GRACE_PERIOD_MINUTES = 10;

/** Returns today's date as a YYYY-MM-DD string in local time. */
export function getTodayString(): string {
    const d = new Date();
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

/**
 * Returns the minimum allowed pickup time (HH:MM 24hr) for the given date.
 * Returns '' if the date is in the future (no restriction).
 *
 * @param date    YYYY-MM-DD date string
 * @param nowRef  Optional Date to use as "now" (defaults to new Date()). Pass
 *                a reactive clock value so callers can recalculate every minute.
 */
export function getMinPickupTime(date: string, nowRef?: Date): string {
    if (!date || date !== getTodayString()) return '';

    const now = nowRef ? new Date(nowRef) : new Date();
    now.setMinutes(now.getMinutes() + PICKUP_BUFFER_MINUTES);
    // Round up to the nearest 5-minute slot (matches TimePicker step)
    const remainder = now.getMinutes() % 5;
    if (remainder !== 0) {
        now.setMinutes(now.getMinutes() + (5 - remainder));
    }
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    return `${h}:${m}`;
}

/**
 * Returns true when the given HH:MM time is valid for the selected date.
 * Always returns true for future dates.
 *
 * A grace period of PICKUP_GRACE_PERIOD_MINUTES is subtracted from the strict
 * minimum before comparing, so bookings that are slightly behind the strict
 * cutoff (due to time passing while the customer fills out the form) are still
 * accepted. This mirrors the backend StoreCustomerOrderRequest grace logic.
 *
 * Example (30-min buffer, 10-min grace, now = 22:09):
 *   strictMin  = round_up_5(22:09 + 30) = 22:40
 *   effectiveMin = round_down_5(22:40 - 10) = 22:30
 *   user picks 22:30 → VALID  ✓
 *   user picks 22:25 → INVALID ✗
 */
export function isPickupTimeValid(date: string, time: string, nowRef?: Date): boolean {
    if (!time) return false;
    const strictMin = getMinPickupTime(date, nowRef);
    if (!strictMin) return true; // future date — all times valid

    // Convert strictMin to total minutes, subtract grace, round down to nearest 5.
    const [sh, sm] = strictMin.split(':').map(Number);
    const effectiveTotalMinutes = Math.floor((sh * 60 + sm - PICKUP_GRACE_PERIOD_MINUTES) / 5) * 5;
    const effectiveH = Math.floor(effectiveTotalMinutes / 60);
    const effectiveM = effectiveTotalMinutes % 60;
    const effectiveMin = `${String(effectiveH).padStart(2, '0')}:${String(effectiveM).padStart(2, '0')}`;

    return time >= effectiveMin;
}

/**
 * Returns a human-readable formatted time string, e.g. "10:30 AM".
 */
export function formatTime12(hhmm: string): string {
    if (!hhmm) return '';
    const [hStr, mStr] = hhmm.split(':');
    const h = parseInt(hStr, 10);
    const m = parseInt(mStr, 10);
    const ampm = h < 12 ? 'AM' : 'PM';
    const h12 = h % 12 === 0 ? 12 : h % 12;
    return `${String(h12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${ampm}`;
}

/**
 * Returns the earliest allowed pickup time formatted for display, e.g. "10:30 AM".
 * Returns '' if no restriction (future date).
 *
 * @param date    YYYY-MM-DD date string
 * @param nowRef  Optional reactive clock Date (pass to recalculate live).
 */
export function formatEarliestTime(date: string, nowRef?: Date): string {
    return formatTime12(getMinPickupTime(date, nowRef));
}
