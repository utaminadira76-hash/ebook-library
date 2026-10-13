<?php
// File: my-books.php - PERBAIKAN
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login menggunakan fungsi yang konsisten
if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu!', 'warning');
}

// Get user info
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'] ?? 'user';

// Cek membership status di session, jika tidak ada ambil dari database
if (!isset($_SESSION['membership_status'])) {
    $stmt = $pdo->prepare("SELECT membership_status FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();
    
    if ($user_data) {
        $_SESSION['membership_status'] = $user_data['membership_status'];
    } else {
        $_SESSION['membership_status'] = 'free';
    }
}

$membership_status = $_SESSION['membership_status'];

// Determine accessible books
if ($membership_status == 'premium' || $role == 'admin') {
    // Premium/admin can access all active books
    $sql = "SELECT b.*, c.name as category_name FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE b.status = 'active' 
            ORDER BY b.title";
} else {
    // Free users can only access free books
    $sql = "SELECT b.*, c.name as category_name FROM books b 
            LEFT JOIN categories c ON b.category_id = c.id 
            WHERE b.status = 'active' AND b.is_free = 1 
            ORDER BY b.title";
}

try {
    $books = $pdo->query($sql)->fetchAll();
} catch (PDOException $e) {
    $books = [];
    error_log("Error fetching books: " . $e->getMessage());
}

// Get user's full info for display
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_info = $stmt->fetch();

$page_title = 'Buku Saya';
include 'includes/header.php';
?>

<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1>Buku Saya</h1>
            <p class="text-muted">Koleksi buku yang bisa Anda akses</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="catalog.php" class="btn btn-outline-primary">
                <i class="bi bi-plus-circle"></i> Tambah Buku
            </a>
        </div>
    </div>

    <!-- Membership Status -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Status Keanggotaan</h5>
                    <p class="card-text text-muted mb-0">
                        <?php if($membership_status == 'premium'): ?>
                            <span class="badge bg-warning text-dark">PREMIUM MEMBER</span>
                            - Anda bisa mengakses <strong>semua buku</strong>
                        <?php else: ?>
                            <span class="badge bg-secondary">FREE MEMBER</span>
                            - Anda hanya bisa mengakses <strong>buku gratis</strong>
                        <?php endif; ?>
                    </p>
                </div>
                <?php if($membership_status == 'free'): ?>
                    <a href="membership.php" class="btn btn-warning">
                        <i class="bi bi-star"></i> Upgrade ke Premium
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <?php if(empty($books)): ?>
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-book" style="font-size: 4rem; color: #ddd;"></i>
                <h4 class="mt-3">Belum ada buku tersedia</h4>
                <p class="text-muted">
                    <?php if($membership_status == 'free'): ?>
                        Upgrade ke premium untuk mengakses lebih banyak buku
                    <?php else: ?>
                        Belum ada buku yang tersedia di sistem
                    <?php endif; ?>
                </p>
                <div class="mt-3">
                    <a href="catalog.php" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Jelajahi Buku
                    </a>
                    <?php if($membership_status == 'free'): ?>
                        <a href="membership.php" class="btn btn-warning">
                            <i class="bi bi-star"></i> Upgrade Premium
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- Stats -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Menampilkan <strong><?php echo count($books); ?> buku</strong> yang dapat Anda akses
                    (<?php echo $membership_status == 'premium' ? 'Semua buku' : 'Buku gratis saja'; ?>)
                </div>
            </div>
        </div>

        <!-- Books -->
        <div class="row">
            <?php foreach($books as $book): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card book-card h-100">
                        <div class="position-relative">
                            <a href="book-detail.php?id=<?php echo $book['id']; ?>">
                                <img src="<?php echo COVER_URL . ($book['cover_image'] ?: 'default.jpg'); ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     style="height: 200px; object-fit: cover;">
                            </a>
                            <?php if($book['is_free']): ?>
                                <span class="position-absolute top-0 start-0 badge bg-success m-2">Gratis</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">
                                <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="text-decoration-none">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </a>
                            </h6>
                            <p class="card-text small text-muted mb-2">
                                <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-secondary">
                                        <?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?>
                                    </span>
                                    <?php if(!empty($book['book_file'])): ?>
                                        <a href="<?php echo BOOK_URL . $book['book_file']; ?>" 
                                           class="btn btn-sm btn-success" target="_blank" download>
                                            <i class="bi bi-download"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Tidak ada file</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="mt-4 pt-3 border-top">
        <div class="d-flex justify-content-between">
            <div>
                <a href="profile.php" class="btn btn-outline-primary">
                    <i class="bi bi-person"></i> Ke Profile
                </a>
                <a href="catalog.php" class="btn btn-primary ms-2">
                    <i class="bi bi-book"></i> Lihat Katalog Lengkap
                </a>
            </div>
            <div>
                <?php if($role == 'admin'): ?>
                    <a href="admin/" class="btn btn-danger">
                        <i class="bi bi-speedometer2"></i> Admin Panel
                    </a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-secondary ms-2">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>