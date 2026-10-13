<?php
// File: transactions.php - VERSI DIPERBAIKI
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    header("Location: login.php");
    exit();
}

$page_title = 'Riwayat Transaksi';

// Dapatkan data user
$user_id = $_SESSION['user_id'];

// Get total transactions count
$count_stmt = $pdo->prepare("SELECT COUNT(*) as total FROM transactions WHERE user_id = ?");
$count_stmt->execute([$user_id]);
$total_transactions = $count_stmt->fetch()['total'];

// Pagination
$limit = 10;
$total_pages = ceil($total_transactions / $limit);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

// Get paginated transactions
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $user_id, PDO::PARAM_INT);
$stmt->bindValue(2, $limit, PDO::PARAM_INT);
$stmt->bindValue(3, $offset, PDO::PARAM_INT);
$stmt->execute();
$transactions = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
.transaction-card {
    border-left: 4px solid;
    transition: all 0.3s ease;
}
.transaction-card.success { border-left-color: #28a745; }
.transaction-card.pending { border-left-color: #ffc107; }
.transaction-card.failed { border-left-color: #dc3545; }
.transaction-card.canceled { border-left-color: #6c757d; }
.transaction-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
</style>

<div class="container mt-4">
    <h1 class="mb-4">Riwayat Transaksi</h1>
    
    <?php if (isset($_SESSION['last_transaction_code'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle-fill"></i> 
        Pembayaran berhasil! Kode transaksi: <strong><?php echo $_SESSION['last_transaction_code']; ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['last_transaction_code']); endif; ?>
    
    <?php if (empty($transactions)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Belum ada riwayat transaksi.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($transactions as $transaction): 
                $status_class = [
                    'success' => 'success',
                    'pending' => 'warning',
                    'failed' => 'danger',
                    'canceled' => 'secondary'
                ][$transaction['status']] ?? 'secondary';
            ?>
            <div class="col-md-6 mb-3">
                <div class="card transaction-card <?php echo $transaction['status']; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0"><?php echo $transaction['transaction_code']; ?></h6>
                            <span class="badge bg-<?php echo $status_class; ?>">
                                <?php echo ucfirst($transaction['status']); ?>
                            </span>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Jumlah:</small>
                            <strong><?php echo formatCurrency($transaction['amount']); ?></strong>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Tipe:</small>
                            <span class="badge bg-info">
                                <?php echo $transaction['membership_type'] == 'yearly' ? 'Tahunan' : 'Bulanan'; ?>
                            </span>
                        </div>
                        
                        <div class="mb-2">
                            <small class="text-muted">Tanggal:</small>
                            <?php echo formatDate($transaction['created_at'], 'd M Y H:i'); ?>
                        </div>
                        
                        <?php if ($transaction['payment_proof']): ?>
                        <div class="mb-2">
                            <small class="text-muted">Bukti:</small>
                            <a href="assets/uploads/payments/<?php echo $transaction['payment_proof']; ?>" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i> Lihat
                            </a>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($transaction['notes'])): ?>
                        <div class="mb-0">
                            <small class="text-muted">Catatan:</small>
                            <p class="mb-0 small"><?php echo htmlspecialchars($transaction['notes']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                            <i class="bi bi-chevron-left"></i> Sebelumnya
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                            Selanjutnya <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>