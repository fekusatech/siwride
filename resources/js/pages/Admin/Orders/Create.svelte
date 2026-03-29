<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import OrderForm from '@/components/Orders/OrderForm.svelte';
    import { router } from '@inertiajs/svelte';

    let { drivers, google_maps_api_key } = $props();

    function handleSuccess() {
        router.visit('/admin/orders');
    }
</script>

<AppHead title="Create Order" />

<AdminLayout>
    <div class="py-3 px-1">
        <div class="mb-4">
            <h4 class="mb-0">Input New Order</h4>
            <p class="text-muted">Fill in the details to create a new booking</p>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <OrderForm 
                    {drivers} 
                    {google_maps_api_key} 
                    onSuccess={handleSuccess}
                >
                    {#snippet footer({ processing })}
                        <a href="/admin/orders" class="btn btn-light px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm" disabled={processing}>
                            {processing ? 'Saving...' : 'Save Order'}
                        </button>
                    {/snippet}
                </OrderForm>
            </div>
        </div>
    </div>
</AdminLayout>
