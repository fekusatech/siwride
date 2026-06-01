<script lang="ts">
    import { page } from '@inertiajs/svelte';
    const settings = $derived(page.props.settings as any);
</script>

<div class="topbar-one">
    <div class="container">
        <div class="topbar-one__inner">
            <ul class="list-unstyled topbar-one__info">
                <li class="topbar-one__info__item">
                    <i class="flaticon-pin-1 topbar-one__info__icon"></i>
                    {settings.company_address || 'Jl. Mahendradatta selatan GG. Robby William no 99, Denpasar Barat, Bali'}
                </li>
                <li class="topbar-one__info__item">
                    <i class="flaticon-mail topbar-one__info__icon"></i>
                    <a href="mailto:{settings.company_email || 'info@siwride.com'}">{settings.company_email || 'info@siwride.com'}</a>
                </li>
                <li class="topbar-one__info__item topbar-one__info__item--last">
                    <i
                        class="flaticon-three-o-clock-clock topbar-one__info__icon"
                    ></i>
                    {settings.company_working_hours || '24/7 Service Available'}
                </li>
            </ul>
        </div>
    </div>
</div>
