<script lang="ts">
    import {
        currencyContext,
        formatIdr,
        formatMoney,
        type CurrencyContext,
    } from '@/lib/currency';
    let {
        amount,
        quote,
    }: { amount?: number | string; quote?: CurrencyContext } = $props();
    const context = $derived(quote ?? currencyContext());
</script>

<aside
    aria-label="Payment currency"
    style="padding: 12px 16px; background: #eff6ff; color: #1e3a5f; font-size: 13px; line-height: 1.6;"
>
    {#if amount !== undefined}
        {#if context.selected !== 'IDR'}Estimated: <strong
                >{formatMoney(amount, context)}</strong
            >.
        {/if}
        Payment amount: <strong>{formatIdr(amount)}</strong>.
    {/if}
    All online payments and remaining cash payments are in IDR.
    {#if context.selected !== 'IDR'}
        {context.selected} prices are estimates using reference rates dated {context.date}.
        Your bank determines the final conversion rate and any fees.
    {/if}
</aside>
