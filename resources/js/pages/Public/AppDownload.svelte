<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    let { version } = $props<{
        version: {
            version_name: string;
            version_code: number;
            apk_url: string | null;
            whats_new: string | null;
            size_mb: number | null;
            updated_at: string | null;
        } | null;
    }>();

    const updatedLabel = $derived(
        version?.updated_at
            ? new Date(version.updated_at).toLocaleDateString('id-ID', {
                  day: 'numeric',
                  month: 'long',
                  year: 'numeric',
              })
            : null,
    );
</script>

<AppHead title="Download Aplikasi - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />

    <!-- Page Header -->
    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">
                Download Aplikasi Siwride
            </h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Download App</span></li>
            </ul>
        </div>
    </section>

    <section class="app-download-section" style="padding: 90px 0; background: #f8f9fa;">
        <div class="container" style="max-width: 760px;">
            {#if version}
                <div class="app-download-card wow fadeInUp">
                    <div class="app-download-card__head">
                        <div>
                            <span class="app-download-card__eyebrow">
                                <i class="fas fa-mobile-alt"></i> Android
                            </span>
                            <h3 class="app-download-card__version">
                                v{version.version_name}
                                <small>build {version.version_code}</small>
                            </h3>
                        </div>
                        {#if updatedLabel}
                            <span class="app-download-card__date">Diperbarui {updatedLabel}</span>
                        {/if}
                    </div>

                    <p class="app-download-card__tagline">
                        Pesan driver, lihat estimasi harga, dan pantau perjalanan Anda langsung dari
                        HP &mdash; aplikasi resmi customer Siwride untuk Android.
                    </p>

                    {#if version.apk_url}
                        <a
                            href={version.apk_url}
                            class="travhub-btn app-download-card__cta"
                            download
                        >
                            <i class="fas fa-download"></i> Download APK
                        </a>
                        {#if version.size_mb}
                            <div class="app-download-card__cta-note">Ukuran file &asymp; {version.size_mb} MB</div>
                        {/if}
                    {:else}
                        <div class="app-download-card__unavailable">
                            <i class="fas fa-exclamation-circle"></i> File belum tersedia, hubungi tim Siwride.
                        </div>
                    {/if}

                    <div class="app-download-card__notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>Build untuk instalasi manual (sideload) &mdash; belum tersedia di Google Play Store.</span>
                    </div>
                </div>

                {#if version.whats_new}
                    <div class="app-download-notes wow fadeInUp" data-wow-delay="100ms">
                        <h4><i class="fas fa-list-check"></i> Yang baru di versi ini</h4>
                        <p>{version.whats_new}</p>
                    </div>
                {/if}

                <div class="app-download-steps wow fadeInUp" data-wow-delay="200ms">
                    <h4><i class="fas fa-circle-info"></i> Cara pasang di Android</h4>
                    <ol>
                        <li>Tap tombol <strong>Download APK</strong> di atas.</li>
                        <li>
                            Jika muncul peringatan izin, aktifkan
                            <strong>Install unknown apps</strong> untuk browser Anda &mdash; ini wajar
                            untuk aplikasi di luar Play Store.
                        </li>
                        <li>Buka file APK yang sudah diunduh, lalu tap <strong>Install</strong>.</li>
                        <li>Buka aplikasi Siwride dan mulai pesan perjalanan Anda.</li>
                    </ol>
                </div>
            {:else}
                <div class="app-download-empty wow fadeInUp">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Aplikasi Android segera hadir</h3>
                    <p>
                        Kami sedang menyiapkan aplikasi mobile Siwride. Sementara itu, Anda tetap
                        bisa memesan perjalanan lewat website ini.
                    </p>
                    <a href="/booking" class="travhub-btn">Pesan lewat website</a>
                </div>
            {/if}
        </div>
    </section>

    <Footer />
</div>

<style>
    .app-download-card {
        background: #fff;
        border-radius: 20px;
        padding: 36px;
        box-shadow: 0 10px 40px rgba(17, 24, 39, 0.06);
        margin-bottom: 24px;
    }
    .app-download-card__head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .app-download-card__eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.4px;
        text-transform: uppercase;
        color: var(--travhub-base, #d11f1f);
        background: rgba(209, 31, 31, 0.08);
        padding: 5px 12px;
        border-radius: 99px;
    }
    .app-download-card__version {
        margin: 10px 0 0;
        font-size: 30px;
        font-weight: 800;
        color: #1a1a1a;
    }
    .app-download-card__version small {
        font-size: 14px;
        font-weight: 500;
        color: #8a8a8a;
        margin-left: 6px;
    }
    .app-download-card__date {
        font-size: 13px;
        color: #8a8a8a;
        margin-top: 6px;
        white-space: nowrap;
    }
    .app-download-card__tagline {
        color: #666;
        margin: 18px 0 26px;
        max-width: 56ch;
        line-height: 1.7;
    }
    .app-download-card__cta {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .app-download-card__cta-note {
        margin-top: 10px;
        font-size: 13px;
        color: #8a8a8a;
    }
    .app-download-card__unavailable {
        color: #925d00;
        background: #fff1da;
        border-radius: 12px;
        padding: 12px 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    .app-download-card__notice {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        margin-top: 24px;
        padding-top: 22px;
        border-top: 1px solid #eee;
        color: #666;
        font-size: 13.5px;
    }
    .app-download-card__notice i {
        color: var(--travhub-base, #d11f1f);
        margin-top: 2px;
    }

    .app-download-notes,
    .app-download-steps {
        background: #fff;
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 20px;
        box-shadow: 0 4px 20px rgba(17, 24, 39, 0.04);
    }
    .app-download-notes h4,
    .app-download-steps h4 {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 14px;
        color: #1a1a1a;
    }
    .app-download-notes h4 i,
    .app-download-steps h4 i {
        color: var(--travhub-base, #d11f1f);
    }
    .app-download-notes p {
        color: #666;
        line-height: 1.8;
        white-space: pre-line;
        margin: 0;
    }
    .app-download-steps ol {
        margin: 0;
        padding-left: 20px;
        color: #666;
        line-height: 1.9;
    }
    .app-download-steps li {
        margin-bottom: 6px;
    }
    .app-download-steps li strong {
        color: #1a1a1a;
    }

    .app-download-empty {
        text-align: center;
        background: #fff;
        border-radius: 20px;
        padding: 60px 32px;
        box-shadow: 0 10px 40px rgba(17, 24, 39, 0.06);
    }
    .app-download-empty i {
        font-size: 40px;
        color: var(--travhub-base, #d11f1f);
        margin-bottom: 18px;
        display: block;
    }
    .app-download-empty h3 {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 10px;
        color: #1a1a1a;
    }
    .app-download-empty p {
        color: #666;
        max-width: 44ch;
        margin: 0 auto 24px;
        line-height: 1.7;
    }
</style>
