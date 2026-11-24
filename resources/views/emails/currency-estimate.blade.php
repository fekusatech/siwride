@if(($quote['selected'] ?? 'IDR') !== 'IDR')
    <p style="font-size: 13px; color: #475569;">
        Estimated online payment: {{ $quote['selected'] }} {{ number_format((float) $quote['amount'], 2, '.', ',') }}.
        Payment currency: IDR {{ number_format((float) $quote['base_amount'], 0, ',', '.') }}.
        Reference rate date: {{ $quote['date'] }}. Your bank determines the final conversion rate and any fees.
        Remaining cash payments are in IDR.
    </p>
@endif
