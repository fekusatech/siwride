<script lang="ts">
    import { untrack } from 'svelte';
    import { page, router } from '@inertiajs/svelte';
    import { update } from '@/routes/currency';
    import { idrContext, type CurrencyContext } from '@/lib/currency';

    let processing = $state(false);
    let error = $state('');
    const context = $derived(
        (page.props.currency as CurrencyContext) ?? idrContext,
    );
    const id = $props.id();
    const currencies: CurrencyContext['selected'][] = ['IDR', 'USD', 'GBP'];
    let selected = $state(untrack(() => context.selected));

    $effect(() => {
        selected = context.selected;
    });

    function showRequestError(): false {
        error = 'Unable to change currency. Please try again.';
        return false;
    }

    function change(event: Event) {
        const currency = (event.currentTarget as HTMLInputElement).value;
        processing = true;
        error = '';
        router.put(
            update(),
            { currency },
            {
                preserveScroll: true,
                preserveState: true,
                only: ['currency'],
                onError: (errors) => {
                    error = errors.currency ?? 'Unable to change currency.';
                },
                onHttpException: showRequestError,
                onNetworkError: showRequestError,
                onFinish: () => {
                    processing = false;
                    selected = context.selected;
                },
            },
        );
    }
</script>

<fieldset
    class="currency-picker"
    disabled={processing}
    aria-busy={processing}
    aria-describedby={`${id}-help`}
>
    <legend>Display currency</legend>
    <div class="currency-picker__options">
        {#each currencies as currency}
            <label
                title={!context.rates[currency]
                    ? 'Reference rate unavailable'
                    : currency}
            >
                <input
                    type="radio"
                    name={`${id}-currency`}
                    value={currency}
                    bind:group={selected}
                    onchange={change}
                    disabled={!context.rates[currency]}
                />
                <span>{currency}</span>
            </label>
        {/each}
    </div>
    <p id={`${id}-help`} class="currency-picker__help">
        USD/GBP are estimates. Payments in IDR.
    </p>
    <p class="currency-picker__status" aria-live="polite">
        {#if processing}
            Saving display currency…
        {:else if currencies.some((currency) => !context.rates[currency])}
            Some currencies are unavailable while reference rates are missing or
            expired.
        {/if}
    </p>
    {#if error}<p class="currency-picker__error" role="alert">{error}</p>{/if}
</fieldset>

<style>
    .currency-picker {
        min-width: 0;
        max-width: 100%;
        margin: 0;
        padding: 0;
        border: 0;
        color: #172033;
        text-align: left;
    }

    legend {
        float: none;
        width: auto;
        margin: 0 0 6px;
        padding: 0;
        color: #475569;
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }

    .currency-picker__options {
        display: inline-flex;
        gap: 4px;
        max-width: 100%;
        padding: 4px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
    }

    label {
        position: relative;
        margin: 0;
    }

    input {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip-path: inset(50%);
    }

    span {
        display: flex;
        min-width: 56px;
        min-height: 44px;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: 8px;
        color: #475569;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.5;
        cursor: pointer;
    }

    input:checked + span {
        background: var(--travhub-base, #d11f1f);
        color: #fff;
    }

    input:focus-visible + span {
        outline: 2px solid var(--travhub-base, #d11f1f);
        outline-offset: 2px;
    }

    input:not(:checked):not(:disabled) + span:hover {
        background: #f1f5f9;
    }

    input:disabled + span,
    .currency-picker:disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .currency-picker__help,
    .currency-picker__status,
    .currency-picker__error {
        max-width: 260px;
        margin: 6px 0 0;
        color: #475569;
        font-size: 11px;
        line-height: 1.5;
    }

    .currency-picker__status:empty {
        display: none;
    }

    .currency-picker__error {
        color: #b91c1c;
    }
</style>
