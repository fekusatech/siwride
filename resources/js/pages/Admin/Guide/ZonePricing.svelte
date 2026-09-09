<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';

    type Zone = { id: number; name: string; is_active: boolean };
    type Rule = {
        pickup_zone: string | null;
        dropoff_zone: string | null;
        base_price: number;
        minimum_price: number;
        distance_km: number;
        is_active: boolean;
    };
    type VehicleCategory = { title: string; price_per_km: number };

    let { zones, rules, vehicle_categories: vehicleCategories } = $props<{
        zones: Zone[];
        rules: Rule[];
        vehicle_categories: VehicleCategory[];
    }>();

    const activeZones = $derived(zones.filter((z: Zone) => z.is_active));
    const inactiveZones = $derived(zones.filter((z: Zone) => !z.is_active));
    const activeRules = $derived(rules.filter((r: Rule) => r.is_active));

    // Every ordered pair of active zones that a customer could plausibly
    // want a price for, minus the ones actually covered by a rule.
    const missingRouteCount = $derived.by(() => {
        const covered = new Set(activeRules.map((r: Rule) => `${r.pickup_zone}→${r.dropoff_zone}`));
        let total = 0;
        let missing = 0;
        for (const a of activeZones) {
            for (const b of activeZones) {
                if (a.id === b.id) continue;
                total++;
                if (!covered.has(`${a.name}→${b.name}`)) missing++;
            }
        }
        return { total, missing };
    });

    const exampleRule = $derived(
        rules.find((r: Rule) => r.pickup_zone?.includes('Airport') && r.base_price > 0) ?? rules[0],
    );
    const exampleVehicle = $derived(vehicleCategories[0]);
    const exampleTotal = $derived(
        exampleRule && exampleVehicle
            ? Math.max(
                  exampleRule.minimum_price,
                  exampleRule.base_price + exampleRule.distance_km * exampleVehicle.price_per_km,
              )
            : null,
    );

    function idr(value: number): string {
        return 'Rp ' + Math.round(value).toLocaleString('id-ID');
    }
</script>

<AppHead title="Panduan Zona & Harga" />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <h4 class="mb-1">Panduan Zona &amp; Harga</h4>
            <p class="text-muted mb-0">
                Cara kerja harga airport transfer, dan status zona/aturan harga yang benar-benar
                aktif sekarang.
            </p>
        </div>

        {#if missingRouteCount.missing > 0}
            <div class="alert alert-danger d-flex gap-2 mb-4" role="alert">
                <i class="ti ti-alert-triangle fs-20 flex-shrink-0 mt-1"></i>
                <div>
                    <strong
                        >{missingRouteCount.missing} dari {missingRouteCount.total} kombinasi rute
                        antar-zona aktif belum punya aturan harga.</strong
                    >
                    Trip dengan jemput/antar di zona-zona itu akan gagal dihitung harganya ("outside
                    active pricing zones" / "no active pricing rule"). Lengkapi di
                    <a href="/admin/zones/pricing">Zone Pricing</a>.
                </div>
            </div>
        {/if}

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-map-pin text-primary"></i> Zone
                        </h6>
                        <p class="text-muted small mb-0">
                            Area geografis berbentuk polygon (digambar di peta admin). Setiap titik
                            jemput/antar dicocokkan ke sebuah Zone lewat koordinatnya. Zone yang
                            nonaktif diabaikan &mdash; area itu dianggap di luar layanan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="d-flex align-items-center gap-2 mb-2">
                            <i class="ti ti-cash text-primary"></i> Zone Pricing Rule
                        </h6>
                        <p class="text-muted small mb-0">
                            Aturan harga untuk satu pasangan <code>pickup_zone &rarr; dropoff_zone</code>.
                            Kalau pasangan zona tidak punya rule, sistem tidak bisa kasih harga sama
                            sekali untuk rute itu.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3">Rumus Harga</h6>
                <div class="bg-light rounded-3 p-3 mb-3" style="font-family: monospace; font-size: 13px;">
                    harga = max( minimum_price, base_price + jarak_km &times; kendaraan.price_per_km )
                </div>

                {#if exampleRule && exampleVehicle && exampleTotal !== null}
                    <p class="text-muted small mb-2">
                        Contoh nyata dari data yang sekarang tersimpan &mdash; rute
                        <strong>{exampleRule.pickup_zone} &rarr; {exampleRule.dropoff_zone}</strong>,
                        kendaraan <strong>{exampleVehicle.title}</strong>:
                    </p>
                    <div class="row g-2 small">
                        <div class="col-auto text-muted">base_price</div>
                        <div class="col-auto ms-auto fw-medium">{idr(exampleRule.base_price)}</div>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-auto text-muted">+ jarak ({exampleRule.distance_km} km) &times; price_per_km</div>
                        <div class="col-auto ms-auto fw-medium">
                            {idr(exampleRule.distance_km * exampleVehicle.price_per_km)}
                        </div>
                    </div>
                    <div class="row g-2 pt-2 mt-2 border-top">
                        <div class="col-auto fw-bold">Total (dibandingkan minimum {idr(exampleRule.minimum_price)})</div>
                        <div class="col-auto ms-auto fw-bold text-primary fs-18">{idr(exampleTotal)}</div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">
                        Catatan: jarak di atas pakai angka fallback tersimpan di rule. Saat booking
                        beneran, app menghitung jarak asli antara dua titik koordinat yang dipilih
                        customer (garis lurus &times; 1.3 sebagai perkiraan jarak jalan).
                    </p>
                {:else}
                    <p class="text-muted small mb-0">
                        Belum ada zona/aturan harga/kategori kendaraan untuk dijadikan contoh &mdash;
                        tambahkan dulu di <a href="/admin/zones">Zones</a> dan
                        <a href="/admin/zones/pricing">Zone Pricing</a>.
                    </p>
                {/if}
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Kelola Zona &mdash; <a href="/admin/zones">/admin/zones</a></h6>
                        <ol class="ps-3 small text-muted mb-0" style="line-height: 1.9;">
                            <li>Klik <strong>Draw Zone</strong>, gambar area di peta (klik beberapa
                                titik membentuk polygon).</li>
                            <li>Isi nama zona yang jelas (mis. "Airport &amp; Tuban"), simpan.</li>
                            <li>Centang/hilangkan <strong>Active</strong> untuk mengaktifkan atau
                                menonaktifkan zona &mdash; zona nonaktif tampil abu-abu di peta.</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <h6 class="mb-3">Kelola Harga &mdash; <a href="/admin/zones/pricing">/admin/zones/pricing</a></h6>
                        <ol class="ps-3 small text-muted mb-0" style="line-height: 1.9;">
                            <li>Pilih <strong>zona jemput</strong> dan <strong>zona antar</strong> dari
                                dropdown (harus dua zona yang sudah dibuat).</li>
                            <li>Isi <strong>base_price</strong> (ongkos dasar/tetap per rute) dan
                                <strong>minimum_price</strong> (harga terendah, jaga-jaga trip pendek).</li>
                            <li>Simpan. Rule otomatis dipakai begitu ada trip dengan zona jemput
                                &amp; antar yang cocok.</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <h6 class="mb-3">
                    Zona saat ini
                    <span class="badge bg-success-subtle text-success ms-1">{activeZones.length} aktif</span>
                    {#if inactiveZones.length > 0}
                        <span class="badge bg-warning-subtle text-warning">{inactiveZones.length} nonaktif</span>
                    {/if}
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr><th>Nama</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                            {#each zones as zone}
                                <tr>
                                    <td>{zone.name}</td>
                                    <td>
                                        {#if zone.is_active}
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        {:else}
                                            <span class="badge bg-warning-subtle text-warning">Nonaktif</span>
                                        {/if}
                                    </td>
                                </tr>
                            {:else}
                                <tr><td colspan="2" class="text-muted text-center py-4">Belum ada zona.</td></tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="mb-3">
                    Aturan harga saat ini
                    <span class="badge bg-secondary-subtle text-secondary ms-1">{rules.length} rule</span>
                </h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Jemput</th>
                                <th>Antar</th>
                                <th class="text-end">base_price</th>
                                <th class="text-end">minimum_price</th>
                                <th class="text-end">jarak</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each rules as rule}
                                <tr>
                                    <td>{rule.pickup_zone ?? '—'}</td>
                                    <td>{rule.dropoff_zone ?? '—'}</td>
                                    <td class="text-end">{idr(rule.base_price)}</td>
                                    <td class="text-end">{idr(rule.minimum_price)}</td>
                                    <td class="text-end">{rule.distance_km} km</td>
                                    <td>
                                        {#if rule.is_active}
                                            <span class="badge bg-success-subtle text-success">Aktif</span>
                                        {:else}
                                            <span class="badge bg-warning-subtle text-warning">Nonaktif</span>
                                        {/if}
                                    </td>
                                </tr>
                            {:else}
                                <tr><td colspan="6" class="text-muted text-center py-4">Belum ada aturan harga.</td></tr>
                            {/each}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>
