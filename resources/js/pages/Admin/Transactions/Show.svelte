<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import QRCode from 'qrcode';
    import { onMount, onDestroy } from 'svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';

    let { transaction: initialTransaction } = $props();

    // svelte-ignore state_referenced_locally
    let transaction = $state(initialTransaction);
    // svelte-ignore non_reactive_update
    let canvasRef: HTMLCanvasElement;
    let pollTimer: ReturnType<typeof setInterval> | undefined;

    const statusLabel: Record<string, string> = {
        pending: 'Waiting for payment',
        paid: 'Paid',
        failed: 'Payment failed',
        expired: 'QR code expired',
    };

    const statusClass: Record<string, string> = {
        pending: 'bg-warning-subtle text-warning',
        paid: 'bg-success-subtle text-success',
        failed: 'bg-danger-subtle text-danger',
        expired: 'bg-secondary-subtle text-secondary',
    };

    function formatCurrency(amount: number) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(amount);
    }

    function stopPolling() {
        if (pollTimer) {
            clearInterval(pollTimer);
            pollTimer = undefined;
        }
    }

    async function checkStatus() {
        // Client-side safety net: stop polling once the QR has expired even if
        // the expiry webhook from Xendit never arrives (misconfig/network issue).
        if (transaction.expires_at && new Date(transaction.expires_at) < new Date()) {
            stopPolling();

            return;
        }

        try {
            const response = await fetch(`/admin/transactions/${transaction.id}/status`, {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
return;
}

            const data = await response.json();

            if (data.status !== transaction.status) {
                transaction = { ...transaction, status: data.status, paid_at: data.paid_at };
            }

            if (data.status !== 'pending') {
                stopPolling();
            }
        } catch {
            // Transient network error — next poll tick will retry.
        }
    }

    onMount(() => {
        if (transaction.qr_string && canvasRef) {
            QRCode.toCanvas(canvasRef, transaction.qr_string, { width: 260, margin: 1 }).catch(() => {
                // Malformed/oversized qr_string — canvas stays blank, status text still shown.
            });
        }

        if (transaction.status === 'pending') {
            pollTimer = setInterval(checkStatus, 3000);
        }
    });

    onDestroy(stopPolling);
</script>

<AppHead title={`Transaction ${transaction.code}`} />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/transactions"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to List
            </Link>
            <h4 class="mb-0">{transaction.code}</h4>
        </div>

        <div class="card">
            <div class="card-body text-center">
                <p class="text-muted mb-1">{transaction.description}</p>
                <h3 class="mb-3">{formatCurrency(transaction.amount)}</h3>

                <span
                    class="badge fs-14 text-capitalize mb-3 {statusClass[transaction.status] ??
                        'bg-secondary-subtle text-secondary'}"
                >
                    {statusLabel[transaction.status] ?? transaction.status}
                </span>

                {#if transaction.status === 'pending' && transaction.qr_string}
                    <div>
                        <canvas bind:this={canvasRef}></canvas>
                    </div>
                    <p class="text-muted small mt-2">
                        Ask the customer to scan this QRIS code with any bank or e-wallet app.
                        This page updates automatically once payment is received.
                    </p>
                {:else if transaction.status === 'paid'}
                    <div class="text-success py-4">
                        <i class="ti ti-circle-check" style="font-size: 48px;"></i>
                        <p class="mt-2 mb-0">
                            Paid at {new Date(transaction.paid_at).toLocaleString('id-ID')}
                        </p>
                    </div>
                {:else}
                    <div class="text-muted py-4">
                        <i class="ti ti-qrcode-off" style="font-size: 48px;"></i>
                        <p class="mt-2 mb-0">This QRIS code is no longer active.</p>
                    </div>
                {/if}
            </div>
        </div>
    </div>
</AdminLayout>
