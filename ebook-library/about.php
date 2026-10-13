<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Tentang Kami';
include 'includes/header.php';
?>

<div class="container py-5">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 mb-3">Tentang E-Book Library</h1>
            <p class="lead">Platform perpustakaan digital terdepan yang menghubungkan pembaca dengan dunia pengetahuan.</p>
        </div>
    </div>

    <!-- Tentang Kami -->
    <div class="row mb-5">
        <div class="col-lg-6">
            <h2 class="mb-4">Siapa Kami?</h2>
            <p>E-Book Library adalah platform perpustakaan digital yang didirikan dengan misi untuk menyediakan akses mudah terhadap berbagai macam buku digital berkualitas. Kami percaya bahwa setiap orang berhak mendapatkan akses ke pengetahuan tanpa batas.</p>
            <p>Dengan koleksi yang terus bertambah, kami berkomitmen untuk menjadi mitra belajar dan sumber inspirasi bagi masyarakat Indonesia dalam mengembangkan minat baca dan literasi digital.</p>
        </div>
        <div class="col-lg-6">
            <img src="https://images.unsplash.com/photo-1507842217343-583bb7270b66?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                 class="img-fluid rounded shadow" 
                 alt="Library Interior">
        </div>
    </div>

    <!-- Visi & Misi -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-center mb-4">Visi & Misi</h2>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-eye fs-1 text-primary"></i>
                    </div>
                    <h4 class="card-title">Visi</h4>
                    <p class="card-text">Menjadi platform perpustakaan digital terdepan di Indonesia yang mendorong budaya membaca dan pembelajaran seumur hidup melalui teknologi.</p>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-bullseye fs-1 text-primary"></i>
                    </div>
                    <h4 class="card-title">Misi</h4>
                    <ul class="text-start ps-3">
                        <li>Menyediakan akses mudah ke buku digital berkualitas</li>
                        <li>Mendorong minat baca masyarakat Indonesia</li>
                        <li>Mendukung pendidikan dan penelitian melalui koleksi digital</li>
                        <li>Mengembangkan teknologi untuk pengalaman membaca yang lebih baik</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Tim Pengembang -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-center mb-4">Tim Pengembang</h2>
            <p class="text-center mb-5">Dikelola oleh tim yang berdedikasi untuk memberikan pengalaman terbaik bagi pengguna.</p>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body p-4">
                    <img src="https://ui-avatars.com/api/?name=Admin+One&background=random&size=100" 
                         class="rounded-circle mb-3" 
                         alt="Team Member">
                    <h5 class="card-title">Admin Satu</h5>
                    <p class="card-text text-muted">Project Manager</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body p-4">
                    <img src="https://ui-avatars.com/api/?name=Dev+Two&background=random&size=100" 
                         class="rounded-circle mb-3" 
                         alt="Team Member">
                    <h5 class="card-title">Developer Dua</h5>
                    <p class="card-text text-muted">Backend Developer</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body p-4">
                    <img src="https://ui-avatars.com/api/?name=Design+Three&background=random&size=100" 
                         class="rounded-circle mb-3" 
                         alt="Team Member">
                    <h5 class="card-title">Designer Tiga</h5>
                    <p class="card-text text-muted">UI/UX Designer</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card border-0 shadow-sm text-center h-100">
                <div class="card-body p-4">
                    <img src="https://ui-avatars.com/api/?name=Support+Four&background=random&size=100" 
                         class="rounded-circle mb-3" 
                         alt="Team Member">
                    <h5 class="card-title">Support Empat</h5>
                    <p class="card-text text-muted">Customer Support</p>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ -->
    <div class="row mb-5">
        <div class="col-lg-12">
            <h2 class="text-center mb-4">Pertanyaan Umum (FAQ)</h2>
            <div class="accordion" id="faqAccordion">
                <!-- FAQ 1 -->
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Apa itu E-Book Library?
                        </button>
                    </h3>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            E-Book Library adalah platform perpustakaan digital yang menyediakan akses ke berbagai buku digital (ebook) dalam berbagai kategori. Anda dapat membaca buku secara online atau mendownloadnya untuk dibaca offline.
                        </div>
                    </div>
                </div>
                <!-- FAQ 2 -->
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Bagaimana cara mengakses buku premium?
                        </button>
                    </h3>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Untuk mengakses buku premium, Anda perlu menjadi member premium dengan melakukan upgrade membership melalui halaman membership. Setelah menjadi member premium, Anda dapat mengakses semua buku premium selama masa berlaku membership.
                        </div>
                    </div>
                </div>
                <!-- FAQ 3 -->
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Apakah ada biaya untuk mendaftar?
                        </button>
                    </h3>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Pendaftaran akun dasar adalah GRATIS. Dengan akun gratis, Anda dapat mengakses buku-buku gratis yang tersedia. Untuk mengakses buku premium, Anda perlu upgrade ke membership premium.
                        </div>
                    </div>
                </div>
                <!-- FAQ 4 -->
                <div class="accordion-item">
                    <h3 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Bagaimana jika lupa password?
                        </button>
                    </h3>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Anda dapat menggunakan fitur "Lupa Password" di halaman login. Sistem akan mengirimkan email dengan instruksi untuk mereset password Anda.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kebijakan & Kontak -->
    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h4 class="card-title mb-3"><i class="bi bi-shield-check text-primary"></i> Kebijakan & Privasi</h4>
                    <p>Kami menghargai privasi Anda. Data pribadi yang Anda berikan hanya digunakan untuk keperluan layanan dan tidak akan dibagikan kepada pihak ketiga tanpa izin.</p>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-decoration-none">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-decoration-none">Syarat dan Ketentuan</a></li>
                        <li><a href="#" class="text-decoration-none">Kebijakan Cookie</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h4 class="card-title mb-3"><i class="bi bi-envelope text-primary"></i> Hubungi Kami</h4>
                    <p>Punya pertanyaan atau butuh bantuan? Hubungi kami melalui:</p>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> info@ebooklibrary.com</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> +62 812 3456 7890</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Jl. Perpustakaan No. 123, Jakarta</li>
                        <li><i class="bi bi-clock me-2"></i> Senin - Jumat: 09.00 - 17.00 WIB</li>
                    </ul>
                    <div class="mt-3">
                        <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm me-2"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="btn btn-outline-primary btn-sm"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>