<script lang="ts">
    const year = new Date().getFullYear();
</script>

<footer class="footer">
    <div class="page-container">
        <div class="row">
            <div class="col-md-6 text-center text-md-start">
                {year} © Boron - By
                <span
                    class="fw-bold text-decoration-underline text-uppercase text-reset fs-12"
                    >Coderthemes</span
                >
            </div>
            <div class="col-md-6">
                <div class="text-md-end footer-links d-none d-md-block">
                    <a href="#/" onclick={(e) => e.preventDefault()}>About</a>
                    <a href="#/" onclick={(e) => e.preventDefault()}>Support</a>
                    <a href="#/" onclick={(e) => e.preventDefault()}
                        >Contact Us</a
                    >
                </div>
            </div>
        </div>
    </div>
</footer>
