<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
    
    let contactData = {
        name: '',
        email: '',
        phone: '',
        subject: '',
        message: ''
    };

    let isSubmitting = false;

    const handleSubmit = async (e: Event) => {
        e.preventDefault();
        isSubmitting = true;
        setTimeout(() => {
            alert('Message sent successfully! We will contact you shortly.');
            isSubmitting = false;
        }, 1200);
    };
</script>

<AppHead title="Contact - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Contact</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Contact</span></li>
            </ul>
        </div>
    </section>

    <section class="contact-info">
        <div class="container">
            <ul class="contact-info__list">
                <li class="contact-info__item">
                    <div class="contact-info__icon"><i class="fa fa-map-marker-alt"></i></div>
                    <h3 class="contact-info__title">Address</h3>
                    <p class="contact-info__text">Bali, Indonesia</p>
                </li>
                <li class="contact-info__item">
                    <div class="contact-info__icon"><i class="fa fa-phone-alt"></i></div>
                    <h3 class="contact-info__title">Call</h3>
                    <p class="contact-info__text"><a href="tel:+62812345678">+62 812-3456-78</a></p>
                </li>
                <li class="contact-info__item">
                    <div class="contact-info__icon"><i class="fa fa-envelope"></i></div>
                    <h3 class="contact-info__title">Email</h3>
                    <p class="contact-info__text"><a href="mailto:info@siwride.com">info@siwride.com</a></p>
                </li>
            </ul>
        </div>
    </section>

    <section class="contact-page">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 wow fadeInLeft" data-wow-delay="100ms">
                    <div class="sec-title">
                        <div class="sec-title__tagline bw-split-in-right">
                            Get in touch<img src="/assets/images/shapes/sec-title-shape.png" alt="Siwride" />
                        </div>
                        <h3 class="sec-title__title bw-split-in-left">Send Us a Message</h3>
                        <p class="sec-title__text bw-split-in-up-fast">
                            Have a question about booking, vehicles, or custom trips? Leave your details and we’ll reply as soon as possible.
                        </p>
                    </div>
                </div>
                <div class="col-lg-6 wow fadeInRight" data-wow-delay="200ms">
                    <form class="contact-page__form form-one" on:submit={handleSubmit}>
                        <div class="form-one__group">
                            <div class="form-one__control">
                                <label for="contact_name">Full Name *</label>
                                <input
                                    id="contact_name"
                                    type="text"
                                    bind:value={contactData.name}
                                    required
                                    minlength="3"
                                    maxlength="60"
                                    placeholder="Enter your full name"
                                />
                            </div>
                            <div class="form-one__control">
                                <label for="contact_email">Email Address *</label>
                                <input
                                    id="contact_email"
                                    type="email"
                                    bind:value={contactData.email}
                                    required
                                    maxlength="80"
                                    placeholder="your.email@example.com"
                                />
                            </div>
                            <div class="form-one__control">
                                <label for="contact_phone">WhatsApp / Phone</label>
                                <input
                                    id="contact_phone"
                                    type="tel"
                                    bind:value={contactData.phone}
                                    maxlength="16"
                                    placeholder="+62 812 3456 7890"
                                />
                            </div>
                            <div class="form-one__control">
                                <label for="contact_subject">Subject</label>
                                <input
                                    id="contact_subject"
                                    type="text"
                                    bind:value={contactData.subject}
                                    maxlength="120"
                                    placeholder="Booking question / Partnership / Support"
                                />
                            </div>
                            <div class="form-one__control form-one__control--full">
                                <label for="contact_message">Message *</label>
                                <textarea
                                    id="contact_message"
                                    bind:value={contactData.message}
                                    rows="6"
                                    required
                                    maxlength="800"
                                    placeholder="Tell us how we can help you..."
                                ></textarea>
                            </div>
                            <div class="form-one__control form-one__control--full">
                                <button type="submit" class="travhub-btn" disabled={isSubmitting}>
                                    <span>{isSubmitting ? 'Sending...' : 'Send Message'}</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>
