<?php
// File: catalog.php - VERSI SIMPLE DAN WORKING
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Katalog Buku';
include 'includes/header.php';

// Get parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$page = isset($_GET['page']) && $_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build base query
$sql = "SELECT b.*, c.name as category_name 
        FROM books b 
        LEFT JOIN categories c ON b.category_id = c.id 
        WHERE b.status = 'active'";

$params = [];
$types = '';

// Add search filter
if (!empty($search)) {
    $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

// Add category filter
if ($category_id > 0) {
    $sql .= " AND b.category_id = ?";
    $params[] = $category_id;
}

// Add filter
if ($filter === 'free') {
    $sql .= " AND b.is_free = 1";
} elseif ($filter === 'paid') {
    $sql .= " AND b.is_free = 0";
}

// Get total count - SIMPLE VERSION
$count_sql = str_replace("SELECT b.*, c.name as category_name", "SELECT COUNT(*) as total", $sql);
$count_sql = preg_replace('/ORDER BY.*$/', '', $count_sql); // Remove ORDER BY for count

try {
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_result = $stmt->fetch();
    $total_books = $total_result['total'] ?? 0;
    $total_pages = ceil($total_books / $limit);
} catch (PDOException $e) {
    // Fallback if query fails
    $total_books = 0;
    $total_pages = 1;
    error_log("Count query error: " . $e->getMessage());
}

// Add sorting
switch ($sort) {
    case 'title_asc':
        $sql .= " ORDER BY b.title ASC";
        break;
    case 'title_desc':
        $sql .= " ORDER BY b.title DESC";
        break;
    case 'price_asc':
        $sql .= " ORDER BY b.price ASC";
        break;
    case 'price_desc':
        $sql .= " ORDER BY b.price DESC";
        break;
    case 'oldest':
        $sql .= " ORDER BY b.created_at ASC";
        break;
    case 'popular':
        // Simple popularity based on creation date
        $sql .= " ORDER BY b.created_at DESC";
        break;
    default: // newest
        $sql .= " ORDER BY b.created_at DESC";
}

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

try {
    // Prepare and execute
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $books = [];
    error_log("Books query error: " . $e->getMessage());
}

// Get categories for filter
$categories = [];
try {
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    error_log("Categories query error: " . $e->getMessage());
}
?>

<div class="container mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1><i class="bi bi-book"></i> Katalog Buku</h1>
            <p class="text-muted">Temukan dan jelajahi koleksi buku digital kami</p>
        </div>
        <div class="col-md-4 text-end">
            <?php if(isLoggedIn()): ?>
                <a href="my-books.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-bookmark-check"></i> Buku Saya
                </a>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Login untuk Download
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Section - SIMPLIFIED -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari judul, penulis, atau deskripsi..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="0">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="filter" class="form-select">
                        <option value="">Semua Buku</option>
                        <option value="free" <?php echo $filter == 'free' ? 'selected' : ''; ?>>Buku Gratis</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="title_asc" <?php echo $sort == 'title_asc' ? 'selected' : ''; ?>>Judul A-Z</option>
                        <option value="title_desc" <?php echo $sort == 'title_desc' ? 'selected' : ''; ?>>Judul Z-A</option>
                    </select>
                </div>
            </form>
            
            <?php if(!empty($search) || $category_id > 0 || !empty($filter)): ?>
            <div class="mt-3">
                <small class="text-muted">
                    Filter aktif: 
                    <?php if(!empty($search)): ?>
                        <span class="badge bg-info">Pencarian: <?php echo htmlspecialchars($search); ?></span>
                    <?php endif; ?>
                    <?php if($category_id > 0): 
                        $cat_name = 'Semua';
                        foreach($categories as $cat) {
                            if($cat['id'] == $category_id) {
                                $cat_name = $cat['name'];
                                break;
                            }
                        }
                    ?>
                        <span class="badge bg-secondary">Kategori: <?php echo htmlspecialchars($cat_name); ?></span>
                    <?php endif; ?>
                    <?php if($filter == 'free'): ?>
                        <span class="badge bg-success">Buku Gratis</span>
                    <?php endif; ?>
                    <a href="catalog.php" class="btn btn-sm btn-outline-secondary ms-2">Hapus Filter</a>
                </small>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Results Info -->
    <div class="row mb-3">
        <div class="col-md-6">
            <p class="mb-0">
                <strong><?php echo $total_books; ?> buku</strong> ditemukan
                <?php if($total_books > 0): ?>
                    (Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?>)
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group" role="group">
                <a href="?<?php 
                    $query = $_GET;
                    $query['sort'] = 'newest';
                    $query['page'] = 1;
                    echo http_build_query($query);
                ?>" class="btn btn-sm btn-outline-primary <?php echo $sort == 'newest' ? 'active' : ''; ?>">
                    Terbaru
                </a>
                <a href="?<?php 
                    $query = $_GET;
                    $query['sort'] = 'title_asc';
                    $query['page'] = 1;
                    echo http_build_query($query);
                ?>" class="btn btn-sm btn-outline-primary <?php echo $sort == 'title_asc' ? 'active' : ''; ?>">
                    Judul A-Z
                </a>
                <a href="?<?php 
                    $query = $_GET;
                    $query['filter'] = 'free';
                    $query['page'] = 1;
                    echo http_build_query($query);
                ?>" class="btn btn-sm btn-outline-success <?php echo $filter == 'free' ? 'active' : ''; ?>">
                    Buku Gratis
                </a>
            </div>
        </div>
    </div>

    <!-- Books Grid -->
    <?php if(empty($books)): ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #dee2e6;"></i>
                <h4 class="mt-3">Tidak ada buku ditemukan</h4>
                <p class="text-muted mb-4">
                    <?php if(!empty($search)): ?>
                        Tidak ada hasil untuk "<strong><?php echo htmlspecialchars($search); ?></strong>"
                    <?php elseif($category_id > 0): ?>
                        Tidak ada buku dalam kategori ini
                    <?php elseif($filter == 'free'): ?>
                        Tidak ada buku gratis tersedia
                    <?php else: ?>
                        Belum ada buku tersedia di katalog
                    <?php endif; ?>
                </p>
                <a href="catalog.php" class="btn btn-primary">
                    <i class="bi bi-arrow-clockwise"></i> Reset Pencarian
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($books as $book): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                    <div class="card book-card h-100 shadow-sm">
                        <div class="position-relative">
                            <a href="book-detail.php?id=<?php echo $book['id']; ?>">
                                <img src="<?php echo COVER_URL . ($book['cover_image'] ?: 'default.jpg'); ?>" 
                                     class="card-img-top book-cover" 
                                     alt="<?php echo htmlspecialchars($book['title']); ?>"
                                     style="height: 250px; object-fit: cover;"
                                     onerror="this.src='<?php echo COVER_URL; ?>default.jpg'">
                            </a>
                            <?php if($book['is_free']): ?>
                                <span class="position-absolute top-0 start-0 badge bg-success m-2">
                                    Gratis
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($book['category_name'] ?? 'Umum'); ?>
                                </span>
                            </div>
                            <h5 class="card-title" style="height: 60px; overflow: hidden;">
                                <a href="book-detail.php?id=<?php echo $book['id']; ?>" 
                                   class="text-decoration-none text-dark">
                                    <?php echo htmlspecialchars($book['title']); ?>
                                </a>
                            </h5>
                            <p class="card-text text-muted small">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($book['author']); ?>
                            </p>
                            <p class="card-text small flex-grow-1" style="height: 60px; overflow: hidden;">
                                <?php 
                                $desc = $book['description'] ?? 'Tidak ada deskripsi';
                                if(strlen($desc) > 80) {
                                    echo htmlspecialchars(substr($desc, 0, 80)) . '...';
                                } else {
                                    echo htmlspecialchars($desc);
                                }
                                ?>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="<?php echo $book['is_free'] ? 'text-success' : 'text-primary'; ?> fw-bold">
                                        <?php echo $book['is_free'] ? 'GRATIS' : 'Rp ' . number_format($book['price'], 0, ',', '.'); ?>
                                    </span>
                                    <small class="text-muted">
                                        <?php echo $book['total_pages'] ?? '?' ?> halaman
                                    </small>
                                </div>
                                <div class="d-grid gap-2">
                                    <a href="book-detail.php?id=<?php echo $book['id']; ?>" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i> Detail Buku
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous -->
                    <?php if($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php 
                                $query = $_GET;
                                $query['page'] = $page - 1;
                                echo http_build_query($query);
                            ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Page numbers -->
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?<?php 
                                    $query = $_GET;
                                    $query['page'] = $i;
                                    echo http_build_query($query);
                                ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php elseif($i == $page - 3 || $i == $page + 3): ?>
                            <li class="page-item disabled">
                                <span class="page-link">...</span>
                            </li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <!-- Next -->
                    <?php if($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?php 
                                $query = $_GET;
                                $query['page'] = $page + 1;
                                echo http_build_query($query);
                            ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Debug Info (Hapus setelah testing) -->
    <?php if(isset($_GET['debug'])): ?>
        <div class="card mt-4 border-danger">
            <div class="card-header bg-danger text-white">
                Debug Info
            </div>
            <div class="card-body">
                <p><strong>SQL Query:</strong> <?php echo htmlspecialchars($sql); ?></p>
                <p><strong>Parameters:</strong> <?php echo implode(', ', $params); ?></p>
                <p><strong>Total Books:</strong> <?php echo $total_books; ?></p>
                <p><strong>Books Found:</strong> <?php echo count($books); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>