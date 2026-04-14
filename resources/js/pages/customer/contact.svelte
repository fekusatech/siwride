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
</script>

<AppHead title="Contact - Siwride" />

<Preloader />
<div class="custom-cursor__cursor"></div>
<div class="custom-cursor__cursor-two"></div>

<div class="page-wrapper">
    <Header />
    
    <!-- Contact Section -->
    <section class="page-header" style="background: linear-gradient(135deg, var(--travhub-base) 0%, #1a1a1a 100%); padding: 80px 0; text-align: center;">
        <div class="container">
            <h1 style="color: white; font-size: 48px; font-weight: 700; margin-bottom: 20px;">Contact Us</h1>
            <p style="color: rgba(255,255,255,0.8); font-size: 18px; max-width: 600px; margin: 0 auto;">Get in touch with us for any questions or booking inquiries</p>
        </div>
    </section>

    <section class="contact-section" style="padding: 80px 0; background: #f8f9fa;">
        <div class="container">
            <div class="row">
                <div class="col-lg-4" style="margin-bottom: 40px;">
                    <div class="contact-info" style="background: white; border-radius: 15px; padding: 40px 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); height: 100%;">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fa fa-map-marker-alt" style="color: white; font-size: 36px;"></i>
                            </div>
                            <h3 style="font-size: 22px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">Office Address</h3>
                            <p style="color: #666; line-height: 1.6;">Jl. Sudirman No. 123<br>Jakarta Selatan, DKI Jakarta 12190<br>Indonesia</p>
                        </div>
                        
                        <div style="text-align: center; margin-bottom: 30px;">
                            <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fa fa-phone-alt" style="color: white; font-size: 36px;"></i>
                            </div>
                            <h3 style="font-size: 22px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">Phone</h3>
                            <p style="color: #666; line-height: 1.6;">+62 812-3456-789<br>+62 21-1234-567</p>
                        </div>
                        
                        <div style="text-align: center;">
                            <div style="width: 80px; height: 80px; background: var(--travhub-base); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                <i class="fa fa-envelope" style="color: white; font-size: 36px;"></i>
                            </div>
                            <h3 style="font-size: 22px; font-weight: 700; color: var(--travhub-black); margin-bottom: 15px;">Email</h3>
                            <p style="color: #666; line-height: 1.6;">info@siwride.com<br>support@siwride.com</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8" style="margin-bottom: 40px;">
                    <div class="contact-form" style="background: white; border-radius: 15px; padding: 50px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                        <h3 style="font-size: 28px; font-weight: 700; color: var(--travhub-black); margin-bottom: 30px; text-align: center;">Send Us a Message</h3>
                        
                        <form>
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Full Name *</label>
                                    <input 
                                        type="text" 
                                        bind:value={contactData.name}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="Enter your full name"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Email Address *</label>
                                    <input 
                                        type="email" 
                                        bind:value={contactData.email}
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="your.email@example.com"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Phone Number</label>
                                    <input 
                                        type="tel" 
                                        bind:value={contactData.phone}
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="+62 812-3456-789"
                                    />
                                </div>
                                <div class="col-md-6" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Subject</label>
                                    <input 
                                        type="text" 
                                        bind:value={contactData.subject}
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease;"
                                        placeholder="Enter subject"
                                    />
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12" style="margin-bottom: 25px;">
                                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--travhub-black);">Message *</label>
                                    <textarea 
                                        bind:value={contactData.message}
                                        rows="6"
                                        required
                                        style="width: 100%; padding: 15px; border: 2px solid #e9ecef; border-radius: 10px; font-size: 16px; transition: border-color 0.3s ease; resize: vertical;"
                                        placeholder="Tell us how we can help you..."
                                    ></textarea>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button 
                                        type="submit"
                                        class="contact-submit-btn"
                                        style="background: var(--travhub-base); color: white; padding: 18px 50px; border: none; border-radius: 50px; font-size: 18px; font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 10px 25px rgba(0,0,0,0.2);"
                                        on:mouseover={(e) => {
                                            e.currentTarget.style.transform = 'translateY(-3px)';
                                            e.currentTarget.style.boxShadow = '0 15px 35px rgba(0,0,0,0.3)';
                                        }}
                                        on:mouseout={(e) => {
                                            e.currentTarget.style.transform = 'translateY(0)';
                                            e.currentTarget.style.boxShadow = '0 10px 25px rgba(0,0,0,0.2)';
                                        }}
                                    >
                                        Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    input:focus, textarea:focus {
        outline: none;
        border-color: var(--travhub-base) !important;
    }
    
    button:hover {
        background: var(--travhub-black) !important;
    }
</style>
