<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Header from '@/components/Template/Header.svelte';
    import Footer from '@/components/Template/Footer.svelte';
    import Preloader from '@/components/Template/Preloader.svelte';
</script>

<AppHead title="Our Vehicles - Siwride" />

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
            <h2 class="page-header__title bw-split-in-right">Our Fleet</h2>
            <ul class="travhub-breadcrumb list-unstyled">
                <li><a href="/">Home</a></li>
                <li><span>Vehicles</span></li>
            </ul>
        </div>
    </section>

    <!-- Main Content -->
    <section class="about-section pt-120 pb-120">
        <div class="container text-center">
            <div class="sec-title mb-5">
                <div class="sec-title__tagline bw-split-in-right">Vehicle Options<img src="/assets/images/shapes/sec-title-shape.png" alt="travhub"></div>
                <h3 class="sec-title__title bw-split-in-left">Explore Our Premium Fleet</h3>
                <p class="sec-title__text bw-split-in-up-fast" style="max-width: 700px; margin: 15px auto 0;">
                    From comfortable solo rides to large group excursions, Siwride provides top-tier vehicles equipped for safety and comfort across Bali.
                </p>
            </div>
            
            <div class="row gutter-y-30 mt-5 pt-3 justify-content-center">
                
                <!-- Standard Cars -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="100ms">
                    <div class="vehicle-feature-card text-center h-100 p-4" style="background-color: #fff;">
                        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 20px; height: 180px;">
                            <img src="https://placehold.co/600x400/eef2f5/888?text=Standard+Cars+Image" alt="Standard Cars" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                        <h4 class="mb-3" style="font-size: 24px; font-weight: 700;">Standard Cars</h4>
                        <p class="mb-4" style="color: #666; font-size: 15px;">Comfortable and affordable standard vehicles for everyday travel.</p>
                        <a href="/vehicles/standard-cars" class="travhub-btn" style="font-size: 14px; padding: 10px 20px;"><span>View Details</span></a>
                    </div>
                </div>

                <!-- Premium Cars -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="200ms">
                    <div class="vehicle-feature-card text-center h-100 p-4" style="background-color: #fff;">
                        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 20px; height: 180px;">
                            <img src="https://placehold.co/600x400/eef2f5/888?text=Premium+Cars+Image" alt="Premium Cars" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                        <h4 class="mb-3" style="font-size: 24px; font-weight: 700;">Premium Cars</h4>
                        <p class="mb-4" style="color: #666; font-size: 15px;">Luxury vehicles for a premium and prestigious travel experience.</p>
                        <a href="/vehicles/premium-cars" class="travhub-btn" style="font-size: 14px; padding: 10px 20px;"><span>View Details</span></a>
                    </div>
                </div>

                <!-- Vans & Minibuses -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="300ms">
                    <div class="vehicle-feature-card text-center h-100 p-4" style="background-color: #fff;">
                        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 20px; height: 180px;">
                            <img src="https://placehold.co/600x400/eef2f5/888?text=Vans+%26+Minibuses+Image" alt="Vans & Minibuses" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                        <h4 class="mb-3" style="font-size: 24px; font-weight: 700;">Vans & Minibuses</h4>
                        <p class="mb-4" style="color: #666; font-size: 15px;">Spacious vehicles tailored for larger families and group tours.</p>
                        <a href="/vehicles/vans-minibuses" class="travhub-btn" style="font-size: 14px; padding: 10px 20px;"><span>View Details</span></a>
                    </div>
                </div>

                <!-- Buses -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="400ms">
                    <div class="vehicle-feature-card text-center h-100 p-4" style="background-color: #fff;">
                        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 20px; height: 180px;">
                            <img src="https://placehold.co/600x400/eef2f5/888?text=Buses+Image" alt="Buses" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                        <h4 class="mb-3" style="font-size: 24px; font-weight: 700;">Buses</h4>
                        <p class="mb-4" style="color: #666; font-size: 15px;">High-capacity buses reserved for monumental groups and large excursions.</p>
                        <a href="/vehicles/buses" class="travhub-btn" style="font-size: 14px; padding: 10px 20px;"><span>View Details</span></a>
                    </div>
                </div>

                <!-- Special Vehicles -->
                <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="500ms">
                    <div class="vehicle-feature-card text-center h-100 p-4" style="background-color: #fff;">
                        <div style="border-radius: 10px; overflow: hidden; margin-bottom: 20px; height: 180px;">
                            <img src="https://placehold.co/600x400/eef2f5/888?text=Special+Vehicles+Image" alt="Special Vehicles" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                        <h4 class="mb-3" style="font-size: 24px; font-weight: 700;">Special Vehicles</h4>
                        <p class="mb-4" style="color: #666; font-size: 15px;">Specialized and stylized transport for weddings or customized requirements.</p>
                        <a href="/vehicles/special-vehicles" class="travhub-btn" style="font-size: 14px; padding: 10px 20px;"><span>View Details</span></a>
                    </div>
                </div>
            </div>
            
            <div class="cta-banner mt-5 pt-4">
                 <a href="/booking" class="travhub-btn mt-3 mr-3">
                    <span>Initiate Booking</span>
                </a>
            </div>
        </div>
    </section>
    
    <Footer />
</div>

<style>
    .vehicle-feature-card {
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid #f0f0f0;
    }
    
    .vehicle-feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        border-color: var(--travhub-base);
    }
</style>
