import type { LinkComponentBaseProps } from '@inertiajs/core';
import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

export function toUrl(
    href: NonNullable<LinkComponentBaseProps['href']>,
): string {
    return typeof href === 'string' ? href : href.url;
}

/**
 * Format a numeric value as Indonesian Rupiah.
 *
 * Examples:
 *   formatRupiah(525000)  → "Rp 525.000"
 *   formatRupiah(1500000) → "Rp 1.500.000"
 *   formatRupiah(35)      → "Rp 35"
 */
export function formatRupiah(value: number | string): string {
    const amount = Math.round(parseFloat(String(value)));
    return 'Rp ' + amount.toLocaleString('id-ID');
}

