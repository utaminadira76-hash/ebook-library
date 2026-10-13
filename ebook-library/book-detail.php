<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login dan memiliki membership aktif
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $isLoggedIn ? $_SESSION['user_id'] : null;

// Ambil data user untuk cek membership
$user_data = null;
$isPremiumMember = false;

if ($isLoggedIn) {
    try {
        // Query untuk mendapatkan data user lengkap dengan status membership
        $stmt = $pdo->prepare("
            SELECT u.*, 
                   CASE 
                       WHEN u.membership_status = 'premium' AND (u.membership_expiry IS NULL OR u.membership_expiry >= CURDATE()) 
                       THEN 'active' 
                       ELSE 'inactive' 
                   END as membership_active
            FROM users u 
            WHERE u.id = ?
        ");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            // Cek apakah user premium dan membership belum expired
            $isPremiumMember = ($user_data['membership_status'] == 'premium') && 
                              ($user_data['membership_expiry'] === null || 
                               strtotime($user_data['membership_expiry']) >= strtotime(date('Y-m-d')));
        }
    } catch (PDOException $e) {
        error_log("Error fetching user data: " . $e->getMessage());
        // Tetap lanjut meskipun ada error
    }
}

// Ambil data buku berdasarkan ID
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    header('Location: catalog.php');
    exit;
}

// Query untuk mendapatkan detail buku
$stmt = $pdo->prepare("
    SELECT b.*, c.name as category_name,
           COALESCE((SELECT AVG(rating) FROM reviews WHERE book_id = b.id AND is_approved = 1), 0) as avg_rating,
           COALESCE((SELECT COUNT(*) FROM reviews WHERE book_id = b.id AND is_approved = 1), 0) as review_count
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.id = ? AND b.status = 'active'
");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: catalog.php?error=book_not_found');
    exit;
}

// Tentukan apakah user dapat mengakses buku
$canAccessBook = false;
$accessMessage = '';

if ($book['is_free'] == 1) {
    // Buku gratis - semua user yang login dapat mengakses
    $canAccessBook = $isLoggedIn;
    if (!$canAccessBook) {
        $accessMessage = 'Login untuk mengakses buku ini';
    }
} else {
    // Buku premium - hanya untuk member premium
    if ($isLoggedIn && $isPremiumMember) {
        $canAccessBook = true;
    } elseif ($isLoggedIn) {
        $accessMessage = 'Upgrade ke membership premium untuk mengakses buku ini';
    } else {
        $accessMessage = 'Login untuk mengakses buku ini';
    }
}



$page_title = $book['title'];
include 'includes/header.php';
?>

<style>
.book-cover-container {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 420px;
    background: linear-gradient(145deg, #f8f9fa, #e9ecef);
}

.book-cover-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.15);
}

.book-cover {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: white;
}

.book-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

.book-title {
    font-weight: 700;
    color: #2c3e50;
    line-height: 1.2;
    margin-bottom: 10px;
}

.author-name {
    color: #6c757d;
    font-size: 1.1rem;
    font-weight: 500;
}

.book-meta {
    background: linear-gradient(135deg, #f8f9fa, #ffffff);
    border-radius: 10px;
    padding: 20px;
    margin: 20px 0;
    border-left: 4px solid #3498db;
}

.rating-display {
    font-size: 1.2rem;
    color: #ffc107;
}

.book-description {
    line-height: 1.8;
    color: #495057;
    font-size: 1.05rem;
}

.info-card {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    height: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 3px 10px rgba(0,0,0,0.05);
}

.info-card:hover {
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transform: translateY(-3px);
}

.info-card i {
    color: #3498db;
    font-size: 1.2rem;
    margin-right: 10px;
}

.action-buttons .btn {
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: 600;
    transition: all 0.3s ease;
    min-width: 160px;
}

.btn-read {
    background: linear-gradient(135deg, #3498db, #2980b9);
    border: none;
    color: white;
}

.btn-read:hover {
    background: linear-gradient(135deg, #2980b9, #1c5f8f);
    transform: translateY(-2px);
    box-shadow: 0 7px 15px rgba(52, 152, 219, 0.3);
}

.btn-preview {
    border: 2px solid #3498db;
    color: #3498db;
}

.btn-preview:hover {
    background: #3498db;
    color: white;
    transform: translateY(-2px);
}

.btn-download {
    background: linear-gradient(135deg, #27ae60, #219653);
    border: none;
    color: white;
}

.btn-download:hover {
    background: linear-gradient(135deg, #219653, #1e8449);
    transform: translateY(-2px);
    box-shadow: 0 7px 15px rgba(39, 174, 96, 0.3);
}

.membership-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    border-radius: 15px;
    overflow: hidden;
}

.membership-card .card-header {
    background: rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.2);
}

.membership-feature {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 15px;
}

.membership-feature i {
    font-size: 1.5rem;
    margin-bottom: 10px;
}

.status-badge {
    padding: 8px 20px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.9rem;
}

.breadcrumb-nav {
    background: transparent;
    padding: 0;
    margin-bottom: 30px;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
    transition: color 0.3s;
}

.breadcrumb-item a:hover {
    color: #3498db;
}
</style>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="breadcrumb-nav">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="catalog.php"><i class="fas fa-home"></i> Katalog</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($book['title']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <!-- Cover Buku -->
        <div class="col-lg-4 col-md-5 mb-4">
            <div class="book-cover-container">
                <!-- Badge Status Buku -->
                <div class="book-badge">
                    <?php if ($book['is_free'] == 1): ?>
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="fas fa-unlock me-1"></i> Gratis
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">
                            <i class="fas fa-crown me-1"></i> Premium
                        </span>
                    <?php endif; ?>
                </div>
                
                <img src="assets/uploads/covers/<?php echo htmlspecialchars($book['cover_image']); ?>" 
                     class="book-cover" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                     onerror="this.src='assets/uploads/covers/default.jpg'">
            </div>
            
            <!-- Quick Stats -->
            <div class="text-center mt-3">
                <div class="d-flex justify-content-center gap-4">
                    <div class="text-center">
                        <div class="text-muted small">Rating</div>
                        <div class="fw-bold"><?php echo number_format($book['avg_rating'], 1); ?>/5.0</div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted small">Halaman</div>
                        <div class="fw-bold"><?php echo $book['total_pages']; ?></div>
                    </div>
                    <div class="text-center">
                        <div class="text-muted small">Ulasan</div>
                        <div class="fw-bold"><?php echo $book['review_count']; ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail Buku -->
        <div class="col-lg-8 col-md-7">
            <!-- Judul dan Author -->
            <h1 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h1>
            <p class="author-name mb-4">
                <i class="fas fa-user-edit me-2"></i>Oleh: <?php echo htmlspecialchars($book['author']); ?>
            </p>
            
            <!-- Rating -->
            <div class="mb-4">
                <div class="d-flex align-items-center">
                    <div class="rating-display me-2">
                        <?php 
                        $fullStars = floor($book['avg_rating']);
                        $hasHalfStar = ($book['avg_rating'] - $fullStars) >= 0.5;
                        $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                        
                        for ($i = 0; $i < $fullStars; $i++) {
                            echo '<i class="fas fa-star"></i> ';
                        }
                        if ($hasHalfStar) {
                            echo '<i class="fas fa-star-half-alt"></i> ';
                        }
                        for ($i = 0; $i < $emptyStars; $i++) {
                            echo '<i class="far fa-star"></i> ';
                        }
                        ?>
                    </div>
                    <span class="text-muted">
                        <?php echo number_format($book['avg_rating'], 1); ?> 
                        (<?php echo $book['review_count']; ?> ulasan)
                    </span>
                </div>
            </div>
            
            <!-- Deskripsi -->
            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-align-left me-2 text-primary"></i>Deskripsi Buku</h5>
                <div class="book-description">
                    <?php echo nl2br(htmlspecialchars($book['description'])); ?>
                </div>
            </div>
            
            <!-- Informasi Buku -->
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <div class="info-card">
                        <h6 class="mb-3 text-primary"><i class="fas fa-info-circle"></i> Informasi Buku</h6>
                        <div class="mb-2">
                            <strong><i class="fas fa-tag me-2"></i>Kategori:</strong>
                            <span class="badge bg-light text-dark ms-2"><?php echo htmlspecialchars($book['category_name']); ?></span>
                        </div>
                        <div class="mb-2">
                            <strong><i class="fas fa-file-alt me-2"></i>Halaman:</strong>
                            <?php echo $book['total_pages']; ?> halaman
                        </div>
                        <div class="mb-0">
                            <strong><i class="fas fa-barcode me-2"></i>ISBN:</strong>
                            <?php echo htmlspecialchars($book['isbn']); ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="info-card">
                        <h6 class="mb-3 text-primary"><i class="fas fa-user-check"></i> Status Anda</h6>
                        <?php if ($isLoggedIn): ?>
                            <div class="mb-3">
                                <div class="text-muted small">Nama</div>
                                <div class="fw-bold"><?php echo htmlspecialchars($user_data['full_name']); ?></div>
                            </div>
                            <div class="mb-0">
                                <div class="text-muted small">Status Membership</div>
                                <div class="d-flex align-items-center">
                                    <span class="status-badge bg-<?php echo $isPremiumMember ? 'warning' : 'secondary'; ?> text-<?php echo $isPremiumMember ? 'dark' : 'white'; ?> me-2">
                                        <?php echo $isPremiumMember ? 'Premium Member' : 'Free Member'; ?>
                                    </span>
                                    <?php if ($isPremiumMember && !empty($user_data['membership_expiry'])): ?>
                                        <small class="text-muted">
                                            Berlaku hingga <?php echo date('d/m/Y', strtotime($user_data['membership_expiry'])); ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-3">
                                <i class="fas fa-user-lock fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">Anda belum login</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Tombol Aksi -->
            <div class="action-buttons mb-5">
                <?php if ($canAccessBook): ?>
                    <div class="d-flex flex-wrap gap-3">
                        <!-- Tombol Baca -->
                        <a href="assets/uploads/books/<?php echo htmlspecialchars($book['book_file']); ?>" 
                           class="btn btn-read"
                           target="_blank">
                            <i class="fas fa-book-open me-2"></i> Baca Buku
                        </a>
                        
                        <!-- Tombol Preview jika ada -->
                        <?php if (!empty($book['preview_file'])): ?>
                            <a href="assets/uploads/previews/<?php echo htmlspecialchars($book['preview_file']); ?>" 
                               class="btn btn-preview"
                               target="_blank">
                                <i class="fas fa-eye me-2"></i> Preview
                            </a>
                        <?php endif; ?>
                        
                        <!-- Tombol Download -->
                        <a href="download.php?id=<?php echo $book_id; ?>" 
                           class="btn btn-download"
                           onclick="return confirm('Apakah Anda yakin ingin mendownload buku ini?')">
                            <i class="fas fa-download me-2"></i> Download
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Pesan Akses Ditolak -->
                    <div class="alert alert-warning border-warning border-2 rounded-lg">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-2"><?php echo $accessMessage; ?></h5>
                                
                                <?php if ($isLoggedIn && !$isPremiumMember && $book['is_free'] == 0): ?>
                                    <p class="mb-3">Upgrade ke premium untuk mendapatkan akses ke semua buku premium!</p>
                                    <div class="d-flex gap-2">
                                        <a href="membership.php" class="btn btn-success px-4">
                                            <i class="fas fa-crown me-2"></i> Upgrade Premium
                                        </a>
                                        <a href="catalog.php" class="btn btn-outline-primary">
                                            <i class="fas fa-book me-2"></i> Lihat Buku Lainnya
                                        </a>
                                    </div>
                                <?php elseif (!$isLoggedIn): ?>
                                    <p class="mb-3">Login atau daftar sekarang untuk mulai membaca!</p>
                                    <div class="d-flex gap-2">
                                        <a href="login.php?redirect=book-detail.php?id=<?php echo $book_id; ?>" class="btn btn-primary px-4">
                                            <i class="fas fa-sign-in-alt me-2"></i> Login Sekarang
                                        </a>
                                        <a href="register.php" class="btn btn-outline-primary">
                                            <i class="fas fa-user-plus me-2"></i> Daftar Gratis
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sistem Membership Info -->
            <div class="card membership-card">
                <div class="card-header border-0">
                    <h5 class="mb-0"><i class="fas fa-crown me-2"></i> Sistem Membership</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="membership-feature">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-unlock text-success"></i>
                                    <h6 class="mb-0 ms-2">Buku Gratis</h6>
                                </div>
                                <p class="mb-0 small">Akses semua buku gratis untuk member yang sudah login</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="membership-feature">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-crown text-warning"></i>
                                    <h6 class="mb-0 ms-2">Buku Premium</h6>
                                </div>
                                <p class="mb-0 small">Akses tak terbatas ke semua koleksi buku premium</p>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle me-2"></i>
                        <p class="mb-0 small">
                            <strong>Satu membership, akses semua buku!</strong> Tidak perlu membeli per buku. 
                            Semua akses sesuai level membership Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Smooth hover effects
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.info-card, .book-cover-container');
    
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>