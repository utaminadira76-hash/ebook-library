<?php
// File: my-downloads.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stats = getUserDownloadStats($user_id);

$page_title = 'Riwayat Download';
include 'includes/header.php';

// Get download history
$pdo = initDatabase();
$stmt = $pdo->prepare("
    SELECT dl.*, b.title, b.author, b.cover_image 
    FROM download_logs dl 
    JOIN books b ON dl.book_id = b.id 
    WHERE dl.user_id = ? 
    ORDER BY dl.downloaded_at DESC 
    LIMIT 50
");
$stmt->execute([$user_id]);
$downloads = $stmt->fetchAll();
?>

<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-download me-2"></i>Riwayat Download</h2>
    
    <!-- Stats Card -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body text-center">
                    <h5 class="card-title">Total Download</h5>
                    <h2 class="display-6"><?php echo $stats['total_downloads']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body text-center">
                    <h5 class="card-title">Download Bulan Ini</h5>
                    <h2 class="display-6"><?php echo $stats['monthly_downloads']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body text-center">
                    <h5 class="card-title">Batas Bulanan</h5>
                    <h2 class="display-6"><?php echo $stats['monthly_limit']; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-<?php echo $stats['remaining'] > 0 ? 'warning' : 'danger'; ?>">
                <div class="card-body text-center">
                    <h5 class="card-title">Sisa Download</h5>
                    <h2 class="display-6"><?php echo $stats['remaining']; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Download History -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Riwayat Download Terakhir</h5>
        </div>
        <div class="card-body">
            <?php if (empty($downloads)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-download fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada riwayat download</p>
                    <a href="catalog.php" class="btn btn-primary">Jelajahi Katalog</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Buku</th>
                                <th>Tanggal Download</th>
                                <th>IP Address</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($downloads as $index => $download): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="assets/uploads/covers/<?php echo htmlspecialchars($download['cover_image']); ?>" 
                                                 class="rounded me-2" 
                                                 width="40" 
                                                 height="55"
                                                 style="object-fit: cover;"
                                                 onerror="this.src='assets/uploads/covers/default.jpg'">
                                            <div>
                                                <strong><?php echo htmlspecialchars($download['title']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($download['author']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatDate($download['downloaded_at'], 'datetime'); ?></td>
                                    <td><code><?php echo htmlspecialchars($download['ip_address']); ?></code></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="book-detail.php?id=<?php echo $download['book_id']; ?>" 
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="download.php?id=<?php echo $download['book_id']; ?>" 
                                               class="btn btn-outline-success">
                                                <i class="fas fa-redo"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>