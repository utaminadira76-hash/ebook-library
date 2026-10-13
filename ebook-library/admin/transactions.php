<?php
define('ADMIN_BASE_URL', '../');
define('PAYMENTS_URL', ADMIN_BASE_URL . 'assets/uploads/payments/');
// File: admin/transactions.php
require_once 'check_admin.php';

$page_title = 'Manajemen Transaksi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <?php include 'head-assets.php'; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-top">
            <div class="container-fluid">
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
            </div>
        </nav>
        
        <?php displayFlashMessage(); ?>
        
        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Cari kode/nama..." 
                               value="<?php echo $_GET['search'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="success" <?php echo ($_GET['status'] ?? '') == 'success' ? 'selected' : ''; ?>>Success</option>
                            <option value="failed" <?php echo ($_GET['status'] ?? '') == 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="canceled" <?php echo ($_GET['status'] ?? '') == 'canceled' ? 'selected' : ''; ?>>Canceled</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="yearly" <?php echo ($_GET['type'] ?? '') == 'yearly' ? 'selected' : ''; ?>>Tahunan</option>
                            <option value="monthly" <?php echo ($_GET['type'] ?? '') == 'monthly' ? 'selected' : ''; ?>>Bulanan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="<?php echo $_GET['date'] ?? ''; ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Total Transaksi</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'success'");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Sukses</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'pending'");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Pending</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'failed'");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Gagal</div>
                </div>
            </div>
        </div>
        
        <!-- Transactions Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Daftar Transaksi</h5>
                <div>
                    <span class="text-muted me-3">
                        Total Pendapatan: 
                        <?php
                        $stmt = $pdo->query("SELECT SUM(amount) as total FROM transactions WHERE status = 'success'");
                        $total = $stmt->fetch()['total'];
                        echo formatCurrency($total ?? 0);
                        ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode</th>
                                <th>User</th>
                                <th>Jumlah</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Bukti</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Build query with filters
                            $where = [];
                            $params = [];
                            
                            if (isset($_GET['search']) && !empty($_GET['search'])) {
                                $where[] = "(t.transaction_code LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
                                $search_term = "%{$_GET['search']}%";
                                $params[] = $search_term;
                                $params[] = $search_term;
                                $params[] = $search_term;
                            }
                            
                            if (isset($_GET['status']) && !empty($_GET['status'])) {
                                $where[] = "t.status = ?";
                                $params[] = $_GET['status'];
                            }
                            
                            if (isset($_GET['type']) && !empty($_GET['type'])) {
                                $where[] = "t.membership_type = ?";
                                $params[] = $_GET['type'];
                            }
                            
                            if (isset($_GET['date']) && !empty($_GET['date'])) {
                                $where[] = "DATE(t.created_at) = ?";
                                $params[] = $_GET['date'];
                            }
                            
                            $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
                            
                            $sql = "SELECT t.*, u.username, u.email 
                                   FROM transactions t 
                                   JOIN users u ON t.user_id = u.id 
                                   $where_sql 
                                   ORDER BY t.created_at DESC";
                            
                            $stmt = $pdo->prepare($sql);
                            $stmt->execute($params);
                            $transactions = $stmt->fetchAll();
                            
                            if (empty($transactions)) {
                                echo '<tr><td colspan="9" class="text-center">Tidak ada transaksi</td></tr>';
                            } else {
                                foreach ($transactions as $t) {
                                    $status_class = [
                                        'pending' => 'warning',
                                        'success' => 'success',
                                        'failed' => 'danger',
                                        'canceled' => 'secondary'
                                    ][$t['status']] ?? 'secondary';
                                    
                                    echo '<tr>';
                                    echo '<td>' . $t['id'] . '</td>';
                                    echo '<td><strong>' . $t['transaction_code'] . '</strong></td>';
                                    echo '<td>' . htmlspecialchars($t['username']) . '<br><small>' . $t['email'] . '</small></td>';
                                    echo '<td>' . formatCurrency($t['amount']) . '</td>';
                                    echo '<td><span class="badge bg-info">' . ($t['membership_type'] == 'yearly' ? 'Tahunan' : 'Bulanan') . '</span></td>';
                                    echo '<td><span class="badge bg-' . $status_class . '">' . ucfirst($t['status']) . '</span></td>';
                                    echo '<td>' . formatDate($t['created_at'], 'd M Y H:i') . '</td>';
                                    echo '<td>';
                                    if ($t['payment_proof']) {
                                        echo '<button class="btn btn-sm btn-success view-proof" data-image="' . $t['payment_proof'] . '">Lihat</button>';
                                    } else {
                                        echo '<span class="badge bg-secondary">Tidak ada</span>';
                                    }
                                    echo '</td>';
                                    echo '<td>';
                                    echo '<div class="btn-group btn-group-sm">';
                                    if ($t['status'] == 'pending') {
                                        echo '<button class="btn btn-success approve-transaction" data-id="' . $t['id'] . '">Approve</button>';
                                        echo '<button class="btn btn-danger reject-transaction" data-id="' . $t['id'] . '">Reject</button>';
                                    }
                                    echo '<button class="btn btn-info view-details" data-id="' . $t['id'] . '">Detail</button>';
                                    echo '</div>';
                                    echo '</td>';
                                    echo '</tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Proof Image Modal -->
    <div class="modal fade" id="proofModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Bukti Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="proofImage" src="" class="img-fluid" alt="Bukti Transfer" style="max-height: 500px;">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Transaction Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Detail Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="transactionDetails">
                    <!-- Details will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
// View proof image
$('.view-proof').click(function() {
    const image = $(this).data('image');
    // PERBAIKI PATH: dari '../../assets/uploads/payments/' menjadi '../assets/uploads/payments/'
    $('#proofImage').attr('src', '<?php echo PAYMENTS_URL; ?>' + image);
    $('#proofModal').modal('show');
});
        
        // View transaction details
        $('.view-details').click(function() {
            const id = $(this).data('id');
            
            $.get('proses/transaction-details.php', {id: id}, function(data) {
                $('#transactionDetails').html(data);
                $('#detailsModal').modal('show');
            });
        });
        
        // Approve transaction
        $('.approve-transaction').click(function() {
            const id = $(this).data('id');
            if (confirm('Approve transaksi ini?')) {
                window.location.href = 'proses/transaction-approve.php?id=' + id;
            }
        });
        
        // Reject transaction
        $('.reject-transaction').click(function() {
            const id = $(this).data('id');
            if (confirm('Reject transaksi ini?')) {
                window.location.href = 'proses/transaction-reject.php?id=' + id;
            }
        });
    });
    </script>
</body>
</html>