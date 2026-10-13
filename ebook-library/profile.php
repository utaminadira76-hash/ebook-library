<?php
// File: profile.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login menggunakan fungsi isLoggedIn() yang konsisten
if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu!', 'warning');
}

// Ambil data user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // Jika user tidak ditemukan (mungkin dihapus)
    session_destroy();
    redirect('login.php', 'Sesi tidak valid. Silakan login kembali.', 'error');
}

// Cek apakah membership expired
checkMembershipExpiry($_SESSION['user_id']);

// Hitung sisa hari membership
$remaining_days = 0;
if ($user['membership_status'] == 'premium' && $user['membership_expiry']) {
    $remaining_days = getRemainingDays($user['membership_expiry']);
    
    // Jika sisa hari <= 0, reload data user setelah update
    if ($remaining_days <= 0) {
        // Reload user data
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();
        $remaining_days = 0;
    }
}

// Ambil transaksi user
$transactions_stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$transactions_stmt->execute([$_SESSION['user_id']]);
$transactions = $transactions_stmt->fetchAll();

// Ambil jumlah buku yang dapat diakses
$books_stmt = $pdo->prepare("SELECT COUNT(*) FROM books WHERE status = 'active' AND (is_free = 1 OR ? = 'premium')");
$books_stmt->execute([$user['membership_status']]);
$total_books = $books_stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - E-Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #000000;
            --accent: #3949ab;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: var(--primary) !important;
        }
        .profile-card {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .membership-badge {
            background: linear-gradient(45deg, #FFD700, #FFA500);
            color: #000;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }
        .footer {
            background-color: var(--secondary);
            color: white;
            padding: 50px 0;
            margin-top: 50px;
        }
        .stats-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary);
        }
        @media (max-width: 768px) {
            .stats-number {
                font-size: 1.5rem;
            }
        }
        .remaining-days {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book-half"></i> E-Book Library
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="catalog.php"><i class="bi bi-book"></i> Katalog</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php"><i class="bi bi-tags"></i> Kategori</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                                <?php if($user['membership_status'] == 'premium'): ?>
                                    <span class="badge bg-warning">Premium</span>
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item active" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="my-books.php"><i class="bi bi-bookmarks"></i> Buku Saya</a></li>
                                <li><a class="dropdown-item" href="transactions.php"><i class="bi bi-credit-card"></i> Transaksi</a></li>
                                <?php if($user['role'] == 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/"><i class="bi bi-speedometer2"></i> Admin Panel</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <h1 class="mb-4">Profile Saya</h1>
        
        <div class="row">
            <!-- Left Column: Profile Info -->
            <div class="col-md-4">
                <!-- Profile Card -->
                <div class="card profile-card">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="bi bi-person-circle" style="font-size: 5rem; color: var(--primary);"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($user['full_name'] ?: $user['username']); ?></h4>
                        <p class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></p>
                        
                        <!-- Membership Status Card -->
                        <div class="card mb-4">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Status Membership</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($user['membership_status'] == 'premium'): ?>
                                    <span class="membership-badge">
                                        <i class="bi bi-star-fill"></i> PREMIUM MEMBER
                                    </span>

                                    <?php if ($user['membership_expiry']): ?>
                                        <div class="mt-3">
                                            <p class="small text-muted mb-1">
                                                <i class="bi bi-calendar-check"></i> Berlaku hingga:
                                            </p>
                                            <p class="mb-2"><strong><?php echo date('d M Y', strtotime($user['membership_expiry'])); ?></strong></p>
                                            
                                            <?php if ($remaining_days > 0): ?>
                                                <p class="mb-2">
                                                    <i class="bi bi-clock"></i> Sisa waktu: 
                                                    <span class="badge bg-info"><?php echo $remaining_days; ?> hari</span>
                                                </p>
                                                <?php if ($remaining_days <= 7): ?>
                                                    <div class="alert alert-warning alert-sm p-2 mb-0 mt-2">
                                                        <small><i class="bi bi-exclamation-triangle"></i> Membership akan segera berakhir</small>
                                                    </div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <div class="alert alert-danger p-2 mt-2 mb-0">
                                                    <small><i class="bi bi-exclamation-circle"></i> Membership telah kadaluarsa</small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <span class="badge bg-secondary">FREE MEMBER</span>
                                    <p class="small text-muted mt-2 mb-3">
                                        Akses terbatas hanya untuk buku gratis
                                    </p>
                                <?php endif; ?>
                                
                                <!-- Tombol Upgrade/Perpanjang -->
                                <div class="d-grid gap-2 mt-3">
                                    <?php if ($user['membership_status'] == 'premium' && $remaining_days <= 7): ?>
                                        <a href="membership.php" class="btn btn-warning btn-sm">
                                            <i class="bi bi-arrow-clockwise"></i> Perpanjang Membership
                                        </a>
                                    <?php elseif ($user['membership_status'] == 'free' || $remaining_days <= 0): ?>
                                        <a href="membership.php" class="btn btn-warning">
                                            <i class="bi bi-star"></i> Upgrade ke Premium
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="row g-2 mb-4">
                    <div class="col-6">
                        <div class="stats-card">
                            <i class="bi bi-book display-6 text-primary mb-2"></i>
                            <div class="stats-number"><?php echo $total_books; ?></div>
                            <p class="text-muted small">Buku Tersedia</p>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stats-card">
                            <i class="bi bi-credit-card display-6 text-success mb-2"></i>
                            <div class="stats-number"><?php echo count($transactions); ?></div>
                            <p class="text-muted small">Transaksi</p>
                        </div>
                    </div>
                </div>
                
                <!-- Account Info -->
                <div class="card profile-card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-info-circle"></i> Informasi Akun</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <tr>
                                <th>Username:</th>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                            </tr>
                            <tr>
                                <th>Nama Lengkap:</th>
                                <td><?php echo htmlspecialchars($user['full_name'] ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th>Role:</th>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo $user['role']; ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Bergabung:</th>
                                <td><?php echo date('d M Y', strtotime($user['created_at'])); ?></td>
                            </tr>
                        </table>
                        
                        <div class="d-grid gap-2 mt-3">
                            <a href="edit-profile.php" class="btn btn-outline-primary">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </a>
                            <a href="change-password.php" class="btn btn-outline-secondary">
                                <i class="bi bi-key"></i> Ganti Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Main Content -->
            <div class="col-md-8">
                <!-- Membership Status Detail -->
                <div class="card profile-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-star"></i> Detail Membership</h5>
                        <?php if($user['membership_status'] == 'free' || $remaining_days <= 7): ?>
                            <a href="membership.php" class="btn btn-warning">
                                <i class="bi bi-star"></i> 
                                <?php echo $user['membership_status'] == 'premium' ? 'Perpanjang' : 'Upgrade'; ?> 
                                Membership
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if($user['membership_status'] == 'premium'): ?>
                            <div class="alert alert-success">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-check-circle display-5 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-2">Anda adalah Member Premium</h5>
                                        <p class="mb-1">Selamat! Anda dapat mengakses semua buku premium di perpustakaan kami.</p>
                                    </div>
                                </div>
                                
                                <?php if($user['membership_expiry']): ?>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="bi bi-calendar"></i> Berlaku hingga:</strong><br>
                                                <?php echo date('d M Y', strtotime($user['membership_expiry'])); ?>
                                            </p>
                                        </div>
                                        <div class="col-md-6">
                                            <p class="mb-2">
                                                <strong><i class="bi bi-clock"></i> Sisa waktu:</strong><br>
                                                <?php if ($remaining_days > 0): ?>
                                                    <span class="badge bg-info fs-6"><?php echo $remaining_days; ?> hari lagi</span>
                                                    <?php if ($remaining_days <= 7): ?>
                                                        <div class="alert alert-warning alert-sm p-2 mt-2 mb-0">
                                                            <small><i class="bi bi-exclamation-triangle"></i> Segera perpanjang untuk terus menikmati akses premium</small>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Kadaluarsa</span>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-info-circle display-5 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading mb-2">Anda adalah Member Free</h5>
                                        <p class="mb-1">Dengan akun free, Anda hanya dapat mengakses buku-buku gratis.</p>
                                        <p class="mb-2">Upgrade ke premium untuk mendapatkan akses ke semua koleksi buku premium kami.</p>
                                    </div>
                                </div>
                                <hr>
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Keuntungan Premium:</h6>
                                        <ul class="mb-0">
                                            <li>Akses ke semua buku premium</li>
                                            <li>Download buku tanpa batas</li>
                                            <li>Baca offline</li>
                                            <li>Prioritas support</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <a href="membership.php" class="btn btn-warning btn-lg mt-3">
                                            <i class="bi bi-star-fill"></i> Upgrade Sekarang
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Transactions -->
                <div class="card profile-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Transaksi Terbaru</h5>
                        <a href="transactions.php" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
                    </div>
                    <div class="card-body">
                        <?php if(empty($transactions)): ?>
                            <div class="text-center py-3">
                                <i class="bi bi-credit-card" style="font-size: 3rem; color: #ddd;"></i>
                                <p class="text-muted mt-2">Belum ada transaksi</p>
                                <?php if($user['membership_status'] == 'free'): ?>
                                    <a href="membership.php" class="btn btn-primary">Upgrade Membership</a>
                                <?php else: ?>
                                    <a href="catalog.php" class="btn btn-primary">Lihat Katalog Buku</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Kode</th>
                                            <th>Tanggal</th>
                                            <th>Jumlah</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($transactions as $transaction): ?>
                                            <?php 
                                            $status_class = [
                                                'pending' => 'warning',
                                                'success' => 'success',
                                                'failed' => 'danger',
                                                'canceled' => 'secondary'
                                            ][$transaction['status']] ?? 'secondary';
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($transaction['transaction_code']); ?></td>
                                                <td><?php echo date('d M Y', strtotime($transaction['created_at'])); ?></td>
                                                <td>Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo ucfirst($transaction['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#transactionModal<?php echo $transaction['id']; ?>">
                                                        Detail
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Transaction Detail Modal -->
                                            <div class="modal fade" id="transactionModal<?php echo $transaction['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Detail Transaksi</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th width="40%">Kode Transaksi:</th>
                                                                    <td><?php echo htmlspecialchars($transaction['transaction_code']); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Tanggal:</th>
                                                                    <td><?php echo date('d M Y H:i:s', strtotime($transaction['created_at'])); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Jumlah:</th>
                                                                    <td>Rp <?php echo number_format($transaction['amount'], 0, ',', '.'); ?></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Tipe:</th>
                                                                    <td>
                                                                        <?php echo $transaction['membership_type'] == 'yearly' ? 'Membership Tahunan' : 'Membership Bulanan'; ?>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Status:</th>
                                                                    <td>
                                                                        <span class="badge bg-<?php echo $status_class; ?>">
                                                                            <?php echo ucfirst($transaction['status']); ?>
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>E-Book Library</h5>
                    <p>Platform perpustakaan digital dengan koleksi buku terbaik dalam format digital.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Home</a></li>
                        <li><a href="catalog.php" class="text-white-50">Katalog Buku</a></li>
                        <li><a href="categories.php" class="text-white-50">Kategori</a></li>
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
</body>
</html>