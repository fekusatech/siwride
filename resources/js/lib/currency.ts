import { page } from '@inertiajs/svelte';

export type CurrencyContext = {
    selected: 'IDR' | 'USD' | 'GBP';
    settlement: 'IDR';
    date: string | null;
    rates: Partial<Record<'IDR' | 'USD' | 'GBP', string>>;
    amount?: string;
    base_amount?: string;
};

export const idrContext: CurrencyContext = {
    selected: 'IDR',
    settlement: 'IDR',
    date: null,
    rates: { IDR: '1' },
};

export function currencyContext(): CurrencyContext {
    const booking = (page.props.booking ?? page.props.order) as
        | { display_quote?: CurrencyContext }
        | undefined;
    return booking
        ? (booking.display_quote ?? idrContext)
        : ((page.props.currency as CurrencyContext) ?? idrContext);
}

export function formatIdr(value: number | string): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        currencyDisplay: 'code',
        maximumFractionDigits: 0,
    }).format(Number(value));
}

export function formatMoney(
    value: number | string,
    context: CurrencyContext = currencyContext(),
): string {
    const selected = context.selected;
    const amount = Number(value) * Number(context.rates[selected] ?? 1);
    return new Intl.NumberFormat(selected === 'IDR' ? 'id-ID' : 'en-GB', {
        style: 'currency',
        currency: selected,
        currencyDisplay: 'code',
        minimumFractionDigits: selected === 'IDR' ? 0 : 2,
        maximumFractionDigits: selected === 'IDR' ? 0 : 2,
    }).format(amount);
}
