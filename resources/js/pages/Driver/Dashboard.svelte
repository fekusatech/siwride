<script lang="ts">
    import DriverLayout from '@/layouts/DriverLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { Link } from '@inertiajs/svelte';

    let { articleCounts, earnings } = $props<{
        articleCounts: { pending: number; approved: number; rejected: number };
        earnings: { pending: number; paid: number };
    }>();

    function formatRp(amount: number): string {
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
</script>

<AppHead title="Driver Dashboard" />

<DriverLayout>
    <h4 class="fw-bold mb-4">Dashboard</h4>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold">{articleCounts.pending}</div>
                <div class="text-muted small">Pending Review</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-success">{articleCounts.approved}</div>
                <div class="text-muted small">Published</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold">{formatRp(earnings.pending)}</div>
                <div class="text-muted small">Referral Earnings (Pending)</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center p-3">
                <div class="fs-3 fw-bold text-success">{formatRp(earnings.paid)}</div>
                <div class="text-muted small">Referral Earnings (Paid)</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="fw-bold mb-2">Share your guides</h5>
            <p class="text-muted mb-3">
                Every approved guide gets its own public link. When someone books a tour after reading it, you earn a referral commission — no code to share, just the article link.
            </p>
            <Link href="/driver/articles/create" class="btn btn-primary">
                <i class="ti ti-plus me-1"></i> Write a New Guide
            </Link>
        </div>
    </div>
</DriverLayout>
