<script lang="ts">
    import { page, useForm } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';

    const status = $derived(page.props.status as string | null);

    let form = useForm({
        type: 'travel',
        name: '',
        email: '',
        phone: '',
        description: '',
        password: '',
        password_confirmation: '',
    });

    function submit(event: Event) {
        event.preventDefault();
        form.post('/become-partner', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<AppHead title="Become a Partner - Siwride" />

<Preloader />

<div class="page-wrapper">
    <Header />

    <section class="page-header">
        <div class="page-header__bg"></div>
        <div class="page-header__shape-one"></div>
        <div class="page-header__shape-two"></div>
        <div class="container">
            <h2 class="page-header__title bw-split-in-right">Become a Partner</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Become a Partner</span></li>
            </ul>
        </div>
    </section>

    <section class="contact-page" style="padding: 90px 0 100px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-8 col-lg-10">
                    <div class="sec-title text-center mb-5">
                        <div class="sec-title__tagline bw-split-in-right">
                            Join Our Network<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                        </div>
                        <h3 class="sec-title__title bw-split-in-left">Partner Registration</h3>
                        <p class="sec-title__text bw-split-in-up-fast">
                            List your travel business or guiding services with Siwride and reach travelers across Bali.
                        </p>
                    </div>

                    {#if status}
                        <div class="alert alert-success mb-4" role="alert">{status}</div>
                    {/if}

                    {#if Object.keys(form.errors).length > 0}
                        <div class="alert alert-danger mb-4" role="alert">
                            {#each Object.values(form.errors) as error}
                                <div>{error}</div>
                            {/each}
                        </div>
                    {/if}

                    <form class="form-one" onsubmit={submit}>
                        <div class="form-one__group">
                            <div class="form-one__control form-one__control--full">
                                <label for="type">Partner Type *</label>
                                <select id="type" bind:value={form.type} required>
                                    <option value="travel">Travel Partner — agency or tour operator</option>
                                    <option value="guide">Tour Guide — licensed local guide</option>
                                </select>
                            </div>
                            <div class="form-one__control">
                                <label for="name">Full Name / Business Name *</label>
                                <input id="name" type="text" bind:value={form.name} required placeholder="Enter your name or business name" />
                            </div>
                            <div class="form-one__control">
                                <label for="email">Email Address *</label>
                                <input id="email" type="email" bind:value={form.email} required placeholder="your.email@example.com" />
                            </div>
                            <div class="form-one__control">
                                <label for="phone">WhatsApp / Phone *</label>
                                <input id="phone" type="tel" bind:value={form.phone} required placeholder="+62 812 3456 7890" />
                            </div>
                            <div class="form-one__control">
                                <label for="password">Password *</label>
                                <input id="password" type="password" bind:value={form.password} required placeholder="Create a password" />
                            </div>
                            <div class="form-one__control">
                                <label for="password_confirmation">Confirm Password *</label>
                                <input id="password_confirmation" type="password" bind:value={form.password_confirmation} required placeholder="Repeat your password" />
                            </div>
                            <div class="form-one__control form-one__control--full">
                                <label for="description">About You</label>
                                <textarea id="description" rows="4" bind:value={form.description} placeholder="Tell travelers about your services, experience, and the areas you cover."></textarea>
                            </div>
                            <div class="form-one__control form-one__control--full text-center">
                                <button type="submit" class="travhub-btn" disabled={form.processing}>
                                    <span>{form.processing ? 'Submitting...' : 'Submit Application'}</span>
                                </button>
                            </div>
                        </div>
                    </form>

                    <p class="text-center mt-4 mb-0">
                        Want to drive instead? <a href="/driver/register">Apply as a driver</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <Footer />
</div>
