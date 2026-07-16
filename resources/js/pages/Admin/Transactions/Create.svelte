<script lang="ts">
    import { useForm, Link, page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AdminLayout from '@/layouts/AdminLayout.svelte';

    const form = useForm({
        description: '',
        amount: '',
    });

    function submit(e: Event) {
        e.preventDefault();
        form.post('/admin/transactions');
    }
</script>

<AppHead title="New Transaction" />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <Link
                href="/admin/transactions"
                class="text-primary d-inline-flex align-items-center gap-1 mb-2"
            >
                <i class="ti ti-arrow-left"></i> Back to List
            </Link>
            <h4 class="mb-0">New Transaction</h4>
        </div>

        {#if (page.props as any).flash?.error}
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ti ti-alert-triangle me-2"></i>
                {(page.props as any).flash.error}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        {/if}

        <div class="card">
            <div class="card-body">
                <form onsubmit={submit}>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input
                            type="text"
                            id="description"
                            class="form-control"
                            class:is-invalid={form.errors.description}
                            bind:value={form.description}
                            placeholder="e.g. Manual booking payment for John Doe"
                        />
                        {#if form.errors.description}
                            <div class="invalid-feedback">{form.errors.description}</div>
                        {/if}
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (IDR)</label>
                        <input
                            type="number"
                            id="amount"
                            class="form-control"
                            class:is-invalid={form.errors.amount}
                            bind:value={form.amount}
                            min="1"
                            step="1"
                            placeholder="e.g. 150000"
                        />
                        {#if form.errors.amount}
                            <div class="invalid-feedback">{form.errors.amount}</div>
                        {/if}
                    </div>

                    <button type="submit" class="btn btn-primary" disabled={form.processing}>
                        {#if form.processing}
                            <span class="spinner-border spinner-border-sm me-1"></span>
                        {/if}
                        Generate QRIS
                    </button>
                </form>
            </div>
        </div>
    </div>
</AdminLayout>
