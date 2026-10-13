<?php
// File: membership.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek apakah user sudah login
if (!isLoggedIn()) {
    header("Location: login.php?redirect=membership.php");
    exit();
}

$page_title = 'Membership';

// Check membership expiry
checkMembershipExpiry($_SESSION['user_id']);

// Get current user data
$stmt = $pdo->prepare("SELECT membership_status, membership_expiry, email, username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$current_user = $stmt->fetch();

$remaining_days = 0;
if ($current_user['membership_status'] == 'premium' && $current_user['membership_expiry']) {
    $remaining_days = getRemainingDays($current_user['membership_expiry']);
}

// Check for payment messages
if (isset($_SESSION['payment_message'])) {
    $message = $_SESSION['payment_message']['text'];
    $message_type = $_SESSION['payment_message']['type'];
    unset($_SESSION['payment_message']);
} else {
    $message = '';
    $message_type = '';
}

// Handle form submission for downgrade
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'downgrade') {
        // Downgrade to free
        $stmt = $pdo->prepare("UPDATE users SET membership_status = 'free', membership_expiry = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        $_SESSION['payment_message'] = [
            'text' => 'Downgrade ke free berhasil!',
            'type' => 'info'
        ];
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT membership_status, membership_expiry FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $current_user = $stmt->fetch();
        
        // Redirect to prevent form resubmission
        header("Location: membership.php");
        exit();
    }
}

// Membership plans
$plans = [
    'free' => [
        'name' => 'Free',
        'price' => 0,
        'period' => 'Selamanya',
        'features' => [
            'Akses buku gratis',
            'Preview buku premium',
            'Batas 5 download/bulan',
            'Dukungan standar'
        ],
        'button_text' => $current_user['membership_status'] == 'free' ? 'Paket Saat Ini' : 'Downgrade',
        'button_class' => $current_user['membership_status'] == 'free' ? 'secondary' : 'outline-secondary',
        'disabled' => $current_user['membership_status'] == 'free'
    ],
    'premium' => [
        'name' => 'Premium',
        'price' => 99000,
        'period' => '1 Tahun',
        'features' => [
            'Akses semua buku',
            'Download unlimited',
            'Baca offline',
            'Priority support',
            'Akses buku baru lebih awal'
        ],
        'button_text' => $current_user['membership_status'] == 'premium' ? 
            ($remaining_days > 0 ? 'Perpanjang Sekarang' : 'Aktifkan Kembali') : 
            'Upgrade Sekarang',
        'button_class' => $current_user['membership_status'] == 'premium' ? 'warning' : 'success',
        'disabled' => false
    ]
];

// Hitung progress bar
$progress_width = 0;
if ($current_user['membership_status'] == 'premium' && $current_user['membership_expiry']) {
    $total_days = 365; // 1 tahun
    $used_days = $total_days - $remaining_days;
    $progress_width = min(100, max(0, ($used_days / $total_days) * 100));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - E-Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #1a237e;
            --secondary: #000000;
            --accent: #3949ab;
            --success: #28a745;
            --warning: #ffc107;
            --info: #17a2b8;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 70px;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: var(--primary) !important;
        }
        .membership-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .membership-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .membership-card.premium {
            border: 2px solid var(--warning);
        }
        .membership-header {
            padding: 2rem 1rem;
            color: white;
            text-align: center;
        }
        .free-header {
            background: linear-gradient(135deg, #6c757d, #495057);
        }
        .premium-header {
            background: linear-gradient(135deg, #ffc107, #e0a800);
        }
        .price-display {
            font-size: 2.5rem;
            font-weight: bold;
        }
        .feature-list {
            list-style: none;
            padding: 0;
        }
        .feature-list li {
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list li i {
            color: var(--success);
            margin-right: 10px;
        }
        .current-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        .progress-bar-container {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            margin: 10px 0;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            width: <?php echo $progress_width; ?>%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }
        .stats-card {
            border-left: 4px solid var(--primary);
            padding-left: 15px;
        }
        .modal-content {
            border-radius: 15px;
            border: none;
        }
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1rem 0;
        }
        .list-group-item:first-child {
            border-top: none;
            padding-top: 0;
        }
        .list-group-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .plan-comparison th, .plan-comparison td {
            text-align: center;
            vertical-align: middle;
        }
        .plan-comparison .feature-name {
            text-align: left;
            font-weight: 500;
        }
        .membership-cta {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem;
            border-radius: 15px;
            margin: 3rem 0;
        }
        /* Bank Information Cards */
.bank-info .card-header {
    border-bottom: 2px solid;
    padding: 12px 16px;
}

.bank-info .bank-icon {
    width: 36px;
    height: 36px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Payment Steps */
.payment-step {
    position: relative;
}

.payment-step:not(:last-child):before {
    content: '';
    position: absolute;
    left: 20px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: linear-gradient(to bottom, #dee2e6 0%, transparent 100%);
}

.step-number .badge {
    font-size: 1.1rem;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.step-number .badge:hover {
    transform: scale(1.1);
}

.step-content h6 {
    color: #2c3e50;
    position: relative;
    padding-bottom: 5px;
}

.step-content h6:after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 30px;
    height: 2px;
    background: linear-gradient(90deg, var(--primary), transparent);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .payment-step:not(:last-child):before {
        left: 19px;
    }
    
    .bank-info {
        margin-bottom: 15px;
    }
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
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($current_user['username']); ?>
                            <?php if($current_user['membership_status'] == 'premium'): ?>
                                <span class="badge bg-warning">Premium</span>
                            <?php endif; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="my-books.php"><i class="bi bi-bookmarks"></i> Buku Saya</a></li>
                            <li><a class="dropdown-item active" href="membership.php"><i class="bi bi-star"></i> Membership</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                <li class="breadcrumb-item"><a href="profile.php">Profile</a></li>
                <li class="breadcrumb-item active">Membership</li>
            </ol>
        </nav>
        
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-lg-8">
                <h1>Membership Plans</h1>
                <p class="text-muted">Pilih paket membership yang sesuai dengan kebutuhan Anda</p>
            </div>
            <div class="col-lg-4 text-end">
                <a href="profile.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Profile
                </a>
            </div>
        </div>
        
        <!-- Messages -->
        <?php if($message): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Current Status Card -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-info-circle"></i> Status Membership Saat Ini</h5>
                <div class="row">
                    <div class="col-md-6">
                        <div class="stats-card mb-3">
                            <h6 class="text-muted">Status</h6>
                            <h3>
                                <span class="badge bg-<?php echo $current_user['membership_status'] == 'premium' ? 'warning' : 'secondary'; ?>">
                                    <?php echo ucfirst($current_user['membership_status']); ?>
                                </span>
                            </h3>
                        </div>
                    </div>
                    <?php if($current_user['membership_status'] == 'premium' && $current_user['membership_expiry']): ?>
                    <div class="col-md-6">
                        <div class="stats-card mb-3">
                            <h6 class="text-muted">Berakhir Pada</h6>
                            <h4><?php echo date('d M Y', strtotime($current_user['membership_expiry'])); ?></h4>
                            <div class="progress-bar-container mt-2">
                                <div class="progress-bar-fill"></div>
                            </div>
                            <small class="text-muted">
                                <?php if($remaining_days > 0): ?>
                                    Sisa <?php echo $remaining_days; ?> hari
                                <?php else: ?>
                                    Membership telah berakhir
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Warning jika membership akan habis -->
                <?php if ($current_user['membership_status'] == 'premium' && $remaining_days > 0 && $remaining_days <= 30): ?>
                <div class="alert alert-warning mt-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">Membership Akan Berakhir</h6>
                            <p class="mb-0">Membership Anda akan berakhir dalam <strong><?php echo $remaining_days; ?> hari</strong>.
                            Perpanjang sekarang untuk tetap mendapatkan akses premium.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Membership Plans -->
        <div class="row">
            <?php foreach($plans as $plan_key => $plan): ?>
            <div class="col-md-6 mb-4">
                <div class="card membership-card h-100 <?php echo $plan_key == 'premium' ? 'premium' : ''; ?>">
                    <?php if($current_user['membership_status'] == $plan_key): ?>
                        <div class="current-badge">Paket Saat Ini</div>
                    <?php endif; ?>
                    
                    <div class="membership-header <?php echo $plan_key; ?>-header">
                        <h3 class="card-title"><?php echo $plan['name']; ?></h3>
                        <div class="price-display">
                            <?php if($plan['price'] == 0): ?>
                                Gratis
                            <?php else: ?>
                                Rp <?php echo number_format($plan['price'], 0, ',', '.'); ?>
                            <?php endif; ?>
                        </div>
                        <p class="mb-0">/ <?php echo $plan['period']; ?></p>
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-subtitle mb-4">Fitur Utama:</h5>
                        <ul class="feature-list">
                            <?php foreach($plan['features'] as $feature): ?>
                            <li><i class="bi bi-check-lg"></i> <?php echo $feature; ?></li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="mt-auto">
                            <button type="button" class="btn btn-<?php echo $plan['button_class']; ?> w-100" 
                                    <?php echo $plan['disabled'] ? 'disabled' : ''; ?>
                                    onclick="<?php echo !$plan['disabled'] && $plan_key == 'premium' ? "showPaymentModal('premium', {$plan['price']})" : 'return false;'; ?>">
                                <?php echo $plan['button_text']; ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Plan Comparison -->
        <div class="card mt-5">
            <div class="card-header">
                <h4 class="mb-0"><i class="bi bi-bar-chart"></i> Perbandingan Paket</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered plan-comparison">
                        <thead class="thead-light">
                            <tr>
                                <th class="feature-name">Fitur</th>
                                <th class="text-center">Free</th>
                                <th class="text-center">Premium</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="feature-name">Akses Buku Gratis</td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                            <tr>
                                <td class="feature-name">Akses Buku Premium</td>
                                <td class="text-center"><i class="bi bi-x-circle-fill text-danger fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                            <tr>
                                <td class="feature-name">Download Unlimited</td>
                                <td class="text-center"><i class="bi bi-x-circle-fill text-danger fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                            <tr>
                                <td class="feature-name">Baca Offline</td>
                                <td class="text-center"><i class="bi bi-x-circle-fill text-danger fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                            <tr>
                                <td class="feature-name">Priority Support</td>
                                <td class="text-center"><i class="bi bi-x-circle-fill text-danger fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                            <tr>
                                <td class="feature-name">Akses Buku Baru Lebih Awal</td>
                                <td class="text-center"><i class="bi bi-x-circle-fill text-danger fs-5"></i></td>
                                <td class="text-center"><i class="bi bi-check-circle-fill text-success fs-5"></i></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
<!-- Payment Information -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-credit-card"></i> Informasi Pembayaran</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Transfer Bank -->
            <div class="col-lg-6 mb-4">
                <h6 class="border-bottom pb-2 mb-3">Transfer Bank</h6>
                <div class="row g-3">
                    <!-- Bank BCA -->
                    <div class="col-md-6">
                        <div class="bank-info card border-primary h-100">
                            <div class="card-header bg-primary bg-opacity-10 border-primary">
                                <div class="d-flex align-items-center">
                                    <div class="bank-icon me-2">
                                        <i class="bi bi-bank fs-4 text-primary"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold">Bank BCA</h6>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <small class="text-muted d-block">No. Rekening</small>
                                    <strong class="fs-5">123 4567 890</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Atas Nama</small>
                                    <strong>E-Book Library</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank Mandiri -->
                    <div class="col-md-6">
                        <div class="bank-info card border-success h-100">
                            <div class="card-header bg-success bg-opacity-10 border-success">
                                <div class="d-flex align-items-center">
                                    <div class="bank-icon me-2">
                                        <i class="bi bi-bank fs-4 text-success"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold">Bank Mandiri</h6>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <small class="text-muted d-block">No. Rekening</small>
                                    <strong class="fs-5">098 7654 321</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Atas Nama</small>
                                    <strong>E-Book Library</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Bank BNI -->
                    <div class="col-md-6">
                        <div class="bank-info card border-warning h-100">
                            <div class="card-header bg-warning bg-opacity-10 border-warning">
                                <div class="d-flex align-items-center">
                                    <div class="bank-icon me-2">
                                        <i class="bi bi-bank fs-4 text-warning"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold">Bank BNI</h6>
                                </div>
                            </div>
                            <div class="card-body p-3">
                                <div class="mb-2">
                                    <small class="text-muted d-block">No. Rekening</small>
                                    <strong class="fs-5">112 2334 455</strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Atas Nama</small>
                                    <strong>E-Book Library</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Virtual Account Info -->
                    <div class="col-md-6">
                        <div class="alert alert-light h-100 d-flex flex-column">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle text-primary fs-4 me-2"></i>
                                <h6 class="mb-0 fw-bold">Informasi Tambahan</h6>
                            </div>
                            <div class="flex-grow-1">
                                <p class="mb-1 small">• Transfer hanya ke rekening di atas</p>
                                <p class="mb-1 small">• Gunakan kode unik jika diperlukan</p>
                                <p class="mb-0 small">• Konfirmasi setelah transfer</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Proses Pembayaran -->
            <div class="col-lg-6">
                <h6 class="border-bottom pb-2 mb-3">Proses Pembayaran</h6>
                <div class="payment-steps">
                    <!-- Step 1 -->
                    <div class="payment-step mb-4">
                        <div class="d-flex">
                            <div class="step-number me-3">
                                <span class="badge bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">1</span>
                            </div>
                            <div class="step-content flex-grow-1">
                                <h6 class="fw-bold mb-1">Pilih Paket Premium</h6>
                                <p class="mb-0 text-muted">Klik tombol <strong>"Upgrade Sekarang"</strong> atau <strong>"Perpanjang Sekarang"</strong></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2 -->
                    <div class="payment-step mb-4">
                        <div class="d-flex">
                            <div class="step-number me-3">
                                <span class="badge bg-success rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">2</span>
                            </div>
                            <div class="step-content flex-grow-1">
                                <h6 class="fw-bold mb-1">Transfer ke Rekening</h6>
                                <p class="mb-0 text-muted">Transfer sesuai nominal ke salah satu rekening bank kami yang tertera di samping</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3 -->
                    <div class="payment-step mb-4">
                        <div class="d-flex">
                            <div class="step-number me-3">
                                <span class="badge bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">3</span>
                            </div>
                            <div class="step-content flex-grow-1">
                                <h6 class="fw-bold mb-1">Upload Bukti Transfer</h6>
                                <p class="mb-0 text-muted">Upload bukti transfer melalui form konfirmasi yang muncul setelah klik tombol upgrade</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 4 -->
                    <div class="payment-step">
                        <div class="d-flex">
                            <div class="step-number me-3">
                                <span class="badge bg-info rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">4</span>
                            </div>
                            <div class="step-content flex-grow-1">
                                <h6 class="fw-bold mb-1">Verifikasi Admin</h6>
                                <p class="mb-0 text-muted">Status akan diaktifkan setelah pembayaran diverifikasi (1-24 jam pada hari kerja)</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Important Note -->
                    <div class="alert alert-info mt-4">
                        <div class="d-flex">
                            <i class="bi bi-clock-history fs-4 me-3"></i>
                            <div>
                                <h6 class="alert-heading mb-1">Waktu Verifikasi</h6>
                                <p class="mb-0 small">Proses verifikasi biasanya 1-24 jam pada hari kerja. Anda akan menerima notifikasi email setelah status diaktifkan.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Note -->
        <div class="alert alert-light border mt-3">
            <div class="row align-items-center">
                <div class="col-auto">
                    <i class="bi bi-shield-check text-success fs-3"></i>
                </div>
                <div class="col">
                    <h6 class="fw-bold mb-1">Transaksi Aman & Terpercaya</h6>
                    <p class="mb-0 small">Semua transaksi dilindungi sistem keamanan. Tidak ada biaya tambahan selain harga yang tertera.</p>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- FAQ Section -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-question-circle"></i> Pertanyaan Umum</h5>
            </div>
            <div class="card-body">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Apa perbedaan free dan premium?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Member free hanya bisa mengakses buku gratis, sedangkan member premium bisa mengakses semua buku termasuk yang berbayar dengan download unlimited.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Apakah bisa downgrade ke free?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Ya, bisa kapan saja melalui tombol downgrade. Namun akses ke buku premium akan hilang setelah downgrade.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Bagaimana jika membership habis?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Anda akan otomatis downgrade ke free dan tidak bisa mengakses buku premium lagi sampai memperpanjang membership.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Berapa lama proses verifikasi pembayaran?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Proses verifikasi biasanya memakan waktu 1-24 jam pada hari kerja. Anda akan mendapatkan notifikasi email setelah pembayaran diverifikasi.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Konfirmasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="paymentForm" action="proses/process-payment.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="membership_type" id="membershipType">
                        <input type="hidden" name="amount" id="paymentAmount">
                        
                        <div class="alert alert-info">
                            <h6>Instruksi Pembayaran:</h6>
                            <p>Transfer ke salah satu rekening berikut:</p>
                            <ul class="mb-2">
                                <li><strong>BCA:</strong> 1234567890 (E-Book Library)</li>
                                <li><strong>Mandiri:</strong> 0987654321 (E-Book Library)</li>
                                <li><strong>BNI:</strong> 1122334455 (E-Book Library)</li>
                            </ul>
                            <p class="mb-0 small text-muted">Status akan diaktifkan setelah pembayaran diverifikasi (maks 1x24 jam)</p>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Upload Bukti Transfer *</label>
                            <input type="file" name="payment_proof" class="form-control" accept=".jpg,.jpeg,.png,.gif,.pdf" required>
                            <small class="text-muted">Format: JPG, PNG, GIF, PDF (maks 5MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Tulis catatan untuk admin..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer mt-5 py-4 bg-dark text-white">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> E-Book Library. All rights reserved.</p>
            <p class="mb-0">
                <a href="#" class="text-white-50 me-3">Terms</a>
                <a href="#" class="text-white-50 me-3">Privacy</a>
                <a href="#" class="text-white-50">Support</a>
            </p>
        </div>
    </footer>
    
    <!-- Bootstrap JS (DIBAWAH MODAL) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
// Function to show payment modal
function showPaymentModal(type, amount) {
    console.log('showPaymentModal called with:', type, amount);
    
    // Set form values
    document.getElementById('membershipType').value = type;
    document.getElementById('paymentAmount').value = amount;
    
    // Show modal using Bootstrap 5
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
    paymentModal.show();
}

// Prevent form submission if file is too large
document.getElementById('paymentForm').addEventListener('submit', function(e) {
    const fileInput = this.querySelector('input[name="payment_proof"]');
    const maxSize = 5 * 1024 * 1024; // 5MB in bytes
    
    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        if (fileSize > maxSize) {
            e.preventDefault();
            alert('Ukuran file terlalu besar! Maksimal 5MB.');
            return false;
        }
    }
    
    // Show loading indicator
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';
    submitBtn.disabled = true;
    
    return true;
});

// Auto-dismiss alerts after 5 seconds - PERBAIKAN: Hanya alert dengan pesan
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        // Hanya tutup alert yang berada di bagian messages (bukan info bank)
        const messageAlerts = document.querySelectorAll('.alert[role="alert"]');
        messageAlerts.forEach(function(alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
        
        // Reset form ketika modal ditutup
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('hidden.bs.modal', function () {
                document.getElementById('paymentForm').reset();
            });
        }
    });
    </script>
</body>
</html>