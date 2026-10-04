<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    type Promo = {
        code: string;
        title: string | null;
        description: string | null;
        type: 'percent' | 'fixed';
        value: number;
        min_spend: number;
        max_discount: number | null;
        valid_until: string | null;
    };

    let { promo }: { promo: Promo } = $props();

    let copied = $state(false);

    const rupiah = (n: number) => 'Rp ' + n.toLocaleString('id-ID');
    const heading = $derived(
        promo.title ?? (promo.type === 'percent' ? `Diskon ${promo.value}%` : `Diskon ${rupiah(promo.value)}`),
    );

    async function copy() {
        try {
            await navigator.clipboard.writeText(promo.code);
            copied = true;
            setTimeout(() => (copied = false), 2000);
        } catch {
            // clipboard blocked: code is still visible for manual copy
        }
    }
</script>

<AppHead title="{heading} - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">{heading}</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><a href="/promos">Promo</a></li>
                <li><span>{promo.code}</span></li>
            </ul>
        </div>
    </section>

    <section class="pt-120 pb-120">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <ul class="list-unstyled mb-4">
                        {#if promo.min_spend > 0}<li>Min. belanja {rupiah(promo.min_spend)}</li>{/if}
                        {#if promo.type === 'percent' && promo.max_discount}<li>Maks. diskon {rupiah(promo.max_discount)}</li>{/if}
                        <li>
                            {promo.valid_until
                                ? 'Berlaku s/d ' + new Date(promo.valid_until).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                                : 'Tanpa batas waktu'}
                        </li>
                    </ul>

                    {#if promo.description}
                        <div class="promo-body">{@html promo.description}</div>
                    {/if}

                    <button type="button" class="promo-code mt-4" onclick={copy}>
                        <span>{promo.code}</span>
                        <small>{copied ? 'Tersalin!' : 'Salin kode'}</small>
                    </button>

                    <p class="mt-4"><a href="/booking" class="travhub-btn">Pesan Sekarang</a></p>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>

<style>
    .promo-code {
        display: flex;
        width: 100%;
        max-width: 360px;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        border: 0;
        border-radius: 10px;
        background: #f3f4f6;
        font-weight: 700;
        letter-spacing: 1px;
        cursor: pointer;
    }
    .promo-code small {
        font-weight: 500;
        letter-spacing: 0;
    }
</style>
