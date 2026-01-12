<!-- Footer -->
    <footer class="pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="footer-brand d-flex align-items-center mb-3">
                        <i class="fas fa-plane me-2 text-primary"></i>
                        <span style="font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 1.5rem;">
                            <!-- <span class="text-primary">Tyagi</span>  -->
                            <span class="text-light">Travel In Peace</span>
                        </span>
                    </div>
                    <p class="text-light-50">Offering premium travel services across Himachal Pradesh and beyond. Your comfort is our priority.</p>
                    <div class="social-icons">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php#home">Home</a></li>
                        <li><a href="index.php#about">About Us</a></li>
                        <li><a href="tours.php">Tours</a></li>
                        <li><a href="vehicles.php">Vehicles</a></li>
                        <li><a href="index.php#contact">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Popular Destinations</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="tours.php?category=Weekend%20Escape">Shimla</a></li>
                        <li><a href="tours.php?category=Adventure">Manali</a></li>
                        <li><a href="tours.php?category=Cultural">Dharamshala</a></li>
                        <li><a href="tours.php?category=Family">Dalhousie</a></li>
                        <li><a href="tours.php?category=Trekking">Spiti Valley</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4">
                    <h5 class="text-white mb-4">Newsletter</h5>
                    <p class="text-light-50">Subscribe to get updates on new tours and offers</p>
                    <form class="newsletter-form">
                        <div class="input-group">
                            <input type="email" class="form-control" placeholder="Your email">
                            <button class="btn btn-primary">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <hr class="mt-4 mb-4 bg-light opacity-10">
            
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0 text-light-50">© <?php echo date('Y'); ?> Travel In Peace. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0 text-light-50">
                        <a href="#" class="text-light-50">Terms & Conditions</a> |
                        <a href="#" class="text-light-50">Privacy Policy</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <a href="#" class="back-to-top"><i class="fas fa-arrow-up"></i></a>


    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <?php if ($currentPage !== 'weather.php'): ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js" defer></script>
    <?php endif; ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/gsap.min.js" defer></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.9.1/ScrollTrigger.min.js" defer></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js" defer></script>
    <script src="js/responsive-helper.js" defer></script>
    <?php if ($currentPage !== 'weather.php'): ?>
    <script src="js/weather-service.js" defer></script>
    <script src="js/three-scene.js" defer></script>
    <?php endif; ?>
    <script src="js/animations.js" defer></script>
    <script src="js/theme-switcher.js" defer></script>
    <script src="js/main.js" defer></script>
    <?php if (isset($extraScripts)) echo $extraScripts; ?>
</body>
</html>
