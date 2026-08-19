<x-mail::message>
# Status Penarikan Anda Diperbarui

Halo {{ $withdrawal->driver->name }},

Penarikan saldo Anda **Rp {{ number_format((float) $withdrawal->amount, 0, ',', '.') }}** kini berstatus:

**{{ strtoupper($withdrawal->status) }}**

@if ($withdrawal->status === 'rejected' && $withdrawal->rejection_reason)
**Alasan penolakan:** {{ $withdrawal->rejection_reason }}
@endif

@if ($withdrawal->status === 'paid')
Dana telah dikirim ke rekening **{{ $withdrawal->bank_name }}** {{ $withdrawal->bank_account_number }} a.n. **{{ $withdrawal->bank_account_name }}**.
@endif

@if ($withdrawal->status !== 'rejected')
Jika Anda memiliki pertanyaan, silakan hubungi kami.
@endif

Terima kasih,
{{ config('app.name') }}
</x-mail::message>