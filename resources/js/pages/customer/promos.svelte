<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    type Promo = {
        code: string;
        title: string | null;
        type: 'percent' | 'fixed';
        value: number;
        min_spend: number;
        max_discount: number | null;
        valid_until: string | null;
    };

    let { promos }: { promos: Promo[] } = $props();

    let copied = $state<string | null>(null);

    const rupiah = (n: number) => 'Rp ' + n.toLocaleString('id-ID');

    const discountLabel = (p: Promo) =>
        p.type === 'percent' ? `${p.value}% OFF` : `${rupiah(p.value)} OFF`;

    async function copy(code: string) {
        try {
            await navigator.clipboard.writeText(code);
            copied = code;
            setTimeout(() => (copied = null), 2000);
        } catch {
            // clipboard blocked: code is still visible for manual copy
        }
    }
</script>

<AppHead title="Promo - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Promo</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Promo</span></li>
            </ul>
        </div>
    </section>

    <section class="pt-120 pb-120">
        <div class="container">
            {#if promos.length === 0}
                <p class="text-center">Belum ada promo aktif saat ini. Cek lagi nanti ya!</p>
            {:else}
                <div class="row gy-4">
                    {#each promos as promo (promo.code)}
                        <div class="col-md-6 col-lg-4">
                            <div class="promo-card">
                                <h3 class="promo-card__discount">{discountLabel(promo)}</h3>
                                {#if promo.title}<p class="fw-bold">{promo.title}</p>{/if}
                                <ul class="list-unstyled promo-card__meta">
                                    {#if promo.min_spend > 0}
                                        <li>Min. belanja {rupiah(promo.min_spend)}</li>
                                    {/if}
                                    {#if promo.type === 'percent' && promo.max_discount}
                                        <li>Maks. diskon {rupiah(promo.max_discount)}</li>
                                    {/if}
                                    <li>
                                        {promo.valid_until
                                            ? 'Berlaku s/d ' + new Date(promo.valid_until).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                                            : 'Tanpa batas waktu'}
                                    </li>
                                </ul>
                                <button type="button" class="promo-card__code" onclick={() => copy(promo.code)}>
                                    <span>{promo.code}</span>
                                    <small>{copied === promo.code ? 'Tersalin!' : 'Salin'}</small>
                                </button>
                                <a href="/promos/{promo.code}" class="d-block mt-3 text-center">Lihat detail</a>
                            </div>
                        </div>
                    {/each}
                </div>
                <p class="text-center mt-5">
                    <a href="/booking" class="travhub-btn">Pesan Sekarang</a>
                </p>
            {/if}
        </div>
    </section>

    <Footer />
</div>

<style>
    .promo-card {
        height: 100%;
        padding: 28px;
        border: 2px dashed currentColor;
        border-radius: 16px;
        background: #fff;
    }
    .promo-card__discount {
        margin-bottom: 12px;
    }
    .promo-card__meta {
        margin-bottom: 20px;
        font-size: 14px;
    }
    .promo-card__code {
        display: flex;
        width: 100%;
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
    .promo-card__code small {
        font-weight: 500;
        letter-spacing: 0;
    }
</style>
