<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Form } from '@inertiajs/svelte';
    import { update } from '@/routes/admin/settings/doku';

    type DokuSettings = {
        enabled: boolean;
        environment: string;
        sandbox_client_id: string | null;
        production_client_id: string | null;
        sandbox_has_secret: boolean;
        production_has_secret: boolean;
    };
    let { doku }: { doku: DokuSettings } = $props();
</script>

<AppHead title="DOKU Settings" />
<AdminLayout>
    <div class="py-3">
        <h4>DOKU Settings</h4>
        <p class="alert alert-info">
            Aktivasi mengarahkan pembayaran booking web baru ke DOKU Checkout dalam IDR.
            Jika dinonaktifkan, pembayaran menggunakan Xendit. Pembayaran lama tetap
            diproses oleh provider asal. Konfigurasikan notification URL di dashboard DOKU.
        </p>
        <Form
            action={update.url()}
            method="put"
            transform={(data) => ({ ...data, enabled: data.enabled === '1' })}
            resetOnSuccess={['sandbox_secret_key', 'production_secret_key']}
            resetOnError={['sandbox_secret_key', 'production_secret_key']}
        >
            {#snippet children({ errors, processing, wasSuccessful })}
                {#if wasSuccessful}<div
                        class="alert alert-success"
                        role="status"
                    >
                        Pengaturan DOKU berhasil disimpan.
                    </div>{/if}
                {#if Object.keys(errors).length}<div
                        class="alert alert-danger"
                        role="alert"
                    >
                        Periksa kembali pengaturan yang ditandai.
                    </div>{/if}
                <div class="card">
                    <div class="card-body">
                        <label class="form-check form-switch mb-3">
                            <input
                                type="checkbox"
                                class="form-check-input"
                                name="enabled"
                                value="1"
                                checked={doku.enabled}
                            />
                            <span class="form-check-label"
                                >Aktifkan modul DOKU</span
                            >
                        </label>
                        <label for="environment" class="form-label"
                            >Environment aktif</label
                        >
                        <select
                            id="environment"
                            name="environment"
                            class="form-select"
                            value={doku.environment}
                        >
                            <option value="sandbox">Sandbox</option><option
                                value="production">Production</option
                            >
                        </select>
                        {#if errors.environment}<p class="text-danger">
                                {errors.environment}
                            </p>{/if}
                        {#if errors.enabled}<p class="text-danger">
                                {errors.enabled}
                            </p>{/if}
                    </div>
                </div>
                <div class="row">
                    {#each ['sandbox', 'production'] as environment}
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <h5>
                                        {environment === 'sandbox'
                                            ? 'Sandbox'
                                            : 'Production'}
                                    </h5>
                                    <label
                                        for={environment + '_client_id'}
                                        class="form-label">Client ID</label
                                    >
                                    <input
                                        id={environment + '_client_id'}
                                        name={environment + '_client_id'}
                                        class="form-control mb-2"
                                        value={environment === 'sandbox'
                                            ? (doku.sandbox_client_id ?? '')
                                            : (doku.production_client_id ?? '')}
                                    />
                                    {#if errors[environment + '_client_id']}<p
                                            class="text-danger"
                                        >
                                            {errors[environment + '_client_id']}
                                        </p>{/if}
                                    <label
                                        for={environment + '_secret_key'}
                                        class="form-label">Secret Key</label
                                    >
                                    <input
                                        id={environment + '_secret_key'}
                                        name={environment + '_secret_key'}
                                        type="password"
                                        class="form-control"
                                        autocomplete="new-password"
                                    />
                                    <p class="text-muted">
                                        {(
                                            environment === 'sandbox'
                                                ? doku.sandbox_has_secret
                                                : doku.production_has_secret
                                        )
                                            ? 'Sudah tersimpan'
                                            : 'Belum tersimpan'}. Kosongkan
                                        untuk mempertahankan secret sebelumnya.
                                    </p>
                                    {#if errors[environment + '_secret_key']}<p
                                            class="text-danger"
                                        >
                                            {errors[
                                                environment + '_secret_key'
                                            ]}
                                        </p>{/if}
                                </div>
                            </div>
                        </div>
                    {/each}
                </div>
                <button
                    type="submit"
                    class="btn btn-primary"
                    disabled={processing}
                    >{processing ? 'Menyimpan...' : 'Simpan pengaturan'}</button
                >
            {/snippet}
        </Form>
    </div>
</AdminLayout>
