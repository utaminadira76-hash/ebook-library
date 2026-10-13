<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Home';
include 'includes/header.php';

// Ambil buku terbaru
$stmt = $pdo->query("SELECT b.*, c.name as category_name FROM books b 
                     LEFT JOIN categories c ON b.category_id = c.id 
                     WHERE b.status = 'active' 
                     ORDER BY b.created_at DESC LIMIT 6");
$new_books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil buku gratis
$stmt = $pdo->query("SELECT b.*, c.name as category_name FROM books b 
                     LEFT JOIN categories c ON b.category_id = c.id 
                     WHERE b.status = 'active' AND b.is_free = TRUE 
                     ORDER BY RAND() LIMIT 4");
$free_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!-- Hero Section -->
<div class="hero bg-dark text-white rounded-3 p-5 mb-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h1 class="display-4 fw-bold">Temukan Ribuan E-Book Berkualitas</h1>
            <p class="lead">Akses buku digital dari berbagai genre. Baca di mana saja, kapan saja.</p>
            <div class="mt-4">
                <a href="catalog.php" class="btn btn-light btn-lg me-3">
                    <i class="bi bi-search"></i> Jelajahi Koleksi
                </a>
                <?php if (!isLoggedIn()): ?>
                    <a href="register.php" class="btn btn-outline-light btn-lg">
                        <i class="bi bi-person-plus"></i> Daftar Gratis
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6 text-center">
            <i class="bi bi-book" style="font-size: 15rem; opacity: 0.7;"></i>
        </div>
    </div>
</div>

<!-- Stats -->
<div class="row text-center mb-5">
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h1 class="display-6 text-primary">
                    <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM books WHERE status = 'active'");
                    echo $stmt->fetch()['total'];
                    ?>
                </h1>
                <p class="text-muted">Total Buku</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h1 class="display-6 text-primary">
                    <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM books WHERE is_free = TRUE AND status = 'active'");
                    echo $stmt->fetch()['total'];
                    ?>
                </h1>
                <p class="text-muted">Buku Gratis</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h1 class="display-6 text-primary">
                    <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM categories");
                    echo $stmt->fetch()['total'];
                    ?>
                </h1>
                <p class="text-muted">Kategori</p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <h1 class="display-6 text-primary">
                    <?php 
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                    echo $stmt->fetch()['total'];
                    ?>
                </h1>
                <p class="text-muted">Pengguna</p>
            </div>
        </div>
    </div>
</div>

<!-- Buku Terbaru -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-clock-history"></i> Buku Terbaru</h2>
        <a href="catalog.php?sort=newest" class="btn btn-outline-primary">Lihat Semua</a>
    </div>
    
    <div class="row">
        <?php foreach ($new_books as $book): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card h-100">
                <div class="position-relative">
                    <img src="assets/uploads/covers/<?php echo $book['cover_image'] ?: 'default.jpg'; ?>" 
                         class="card-img-top book-cover" alt="<?php echo $book['title']; ?>">
                    <?php if ($book['is_free']): ?>
                        <span class="position-absolute top-0 end-0 badge bg-success m-2">Gratis</span>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo $book['title']; ?></h5>
                    <p class="card-text text-muted"><?php echo $book['author']; ?></p>
                    <p class="card-text small"><?php echo substr($book['description'], 0, 100) . '...'; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-secondary"><?php echo $book['category_name']; ?></span>
                        <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">Detail</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Buku Gratis -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="bi bi-gift"></i> Buku Gratis</h2>
        <a href="catalog.php?filter=free" class="btn btn-outline-primary">Lihat Semua</a>
    </div>
    
    <div class="row">
        <?php foreach ($free_books as $book): ?>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <img src="assets/uploads/covers/<?php echo $book['cover_image'] ?: 'default.jpg'; ?>" 
                     class="card-img-top book-cover" alt="<?php echo $book['title']; ?>">
                <div class="card-body">
                    <h6 class="card-title"><?php echo $book['title']; ?></h6>
                    <p class="card-text small text-muted"><?php echo $book['author']; ?></p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="badge bg-secondary"><?php echo $book['category_name']; ?></span>
                        <a href="book-detail.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-success">Baca Gratis</a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Call to Action -->
<div class="card bg-primary text-white">
    <div class="card-body text-center p-5">
        <h2 class="card-title">Bergabunglah dengan Komunitas Pembaca</h2>
        <p class="card-text lead mb-4">Dapatkan akses ke ribuan e-book premium dengan berlangganan.</p>
        <a href="membership.php" class="btn btn-light btn-lg">Lihat Paket Membership</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>