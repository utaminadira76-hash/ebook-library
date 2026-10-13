    </div> <!-- End container -->
    
    <!-- Footer -->
    <footer class="footer mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>E-Book Library</h5>
                    <p>Platform perpustakaan digital dengan koleksi buku terbaik dalam format digital.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo SITE_URL; ?>" class="text-white-50">Home</a></li>
                        <li><a href="<?php echo SITE_URL; ?>books.php" class="text-white-50">Katalog Buku</a></li>
                        <li><a href="<?php echo SITE_URL; ?>categories.php" class="text-white-50">Kategori</a></li>
                        <li><a href="<?php echo SITE_URL; ?>about.php" class="text-white-50">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Kontak</h5>
                    <p><i class="bi bi-envelope"></i> info@ebooklibrary.com</p>
                    <p><i class="bi bi-telephone"></i> +62 812 3456 7890</p>
                </div>
            </div>
            <hr class="bg-light">
            <div class="text-center">
                <p>&copy; <?php echo date('Y'); ?> E-Book Library. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Enable Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>