<?php
// File: proses/process-payment.php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Debug: Enable error reporting (hapus ini di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Cek login
if (!isLoggedIn()) {
    $_SESSION['flash_message'] = 'Silakan login terlebih dahulu!';
    $_SESSION['flash_type'] = 'error';
    header("Location: ../login.php");
    exit();
}

// Cek metode request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['flash_message'] = 'Metode tidak diizinkan!';
    $_SESSION['flash_type'] = 'error';
    header("Location: ../membership.php");
    exit();
}

// Validasi CSRF token (jika ada)
if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
    if ($_SESSION['csrf_token'] !== $_POST['csrf_token']) {
        $_SESSION['flash_message'] = 'Token keamanan tidak valid!';
        $_SESSION['flash_type'] = 'error';
        header("Location: ../membership.php");
        exit();
    }
}

// Ambil dan validasi data
$membership_type = trim($_POST['membership_type'] ?? '');
$amount = (float)($_POST['amount'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validasi tipe membership
if (!in_array($membership_type, ['monthly', 'yearly', 'premium'])) {
    $_SESSION['flash_message'] = 'Tipe membership tidak valid!';
    $_SESSION['flash_type'] = 'error';
    header("Location: ../membership.php");
    exit();
}

// Validasi jumlah pembayaran
if ($amount <= 0) {
    // Set default amount berdasarkan membership type
    switch ($membership_type) {
        case 'monthly':
            $amount = 25000;
            break;
        case 'yearly':
            $amount = 99000;
            break;
        case 'premium':
            $amount = 199000;
            break;
        default:
            $amount = 99000;
    }
}

// Generate kode transaksi yang lebih unik
$transaction_code = 'TRX-' . date('YmdHis') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));

error_log("=== PAYMENT PROCESS DEBUG ===");
error_log("UPLOAD_PATH: " . UPLOAD_PATH);
error_log("PAYMENT_PATH: " . PAYMENT_PATH);
error_log("Upload path used: " . $upload_path);
error_log("File destination: " . $file_destination);

// Handle upload bukti pembayaran
$payment_proof = '';
if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
    $file_name = $_FILES['payment_proof']['name'];
    $file_tmp = $_FILES['payment_proof']['tmp_name'];
    $file_size = $_FILES['payment_proof']['size'];
    $file_error = $_FILES['payment_proof']['error'];
    
    // Ekstrak ekstensi
    $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    
    // Validasi ekstensi file
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
    if (!in_array($ext, $allowed_ext)) {
        $_SESSION['flash_message'] = 'Format file tidak didukung! Gunakan JPG, PNG, GIF, atau PDF.';
        $_SESSION['flash_type'] = 'error';
        header("Location: ../membership.php");
        exit();
    }
    
    // Validasi ukuran file (maks 5MB)
    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file_size > $max_size) {
        $_SESSION['flash_message'] = 'Ukuran file terlalu besar! Maksimal 5MB.';
        $_SESSION['flash_type'] = 'error';
        header("Location: ../membership.php");
        exit();
    }
    
    // Generate nama file yang aman
    $filename = 'payment_' . time() . '_' . $_SESSION['user_id'] . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $upload_path = PAYMENT_PATH; // Gunakan konstanta dari config.php
    
    // Buat direktori jika belum ada
    if (!is_dir($upload_path)) {
        mkdir($upload_path, 0777, true);
    }
    
    // Full path untuk file
    $file_destination = PAYMENT_PATH . $filename;
    
    // Coba upload file
    if (move_uploaded_file($file_tmp, $file_destination)) {
        $payment_proof = $filename;
    } else {
        $_SESSION['flash_message'] = 'Gagal mengupload bukti transfer!';
        $_SESSION['flash_type'] = 'error';
        header("Location: ../membership.php");
        exit();
    }
} else {
    // Cek error spesifik upload
    $upload_errors = [
        UPLOAD_ERR_INI_SIZE => 'File terlalu besar (maksimal ' . ini_get('upload_max_filesize') . ')',
        UPLOAD_ERR_FORM_SIZE => 'File terlalu besar',
        UPLOAD_ERR_PARTIAL => 'File hanya terupload sebagian',
        UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
        UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
        UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
        UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
    ];
    
    $error_msg = isset($_FILES['payment_proof']['error']) && isset($upload_errors[$_FILES['payment_proof']['error']]) 
        ? $upload_errors[$_FILES['payment_proof']['error']] 
        : 'Bukti pembayaran harus diupload!';
    
    $_SESSION['flash_message'] = $error_msg;
    $_SESSION['flash_type'] = 'error';
    header("Location: ../membership.php");
    exit();
}

error_log("File uploaded successfully to: " . $file_destination);
error_log("File size: " . filesize($file_destination));
error_log("Payment proof filename: " . $payment_proof);

try {
    // Mulai transaksi database
    $pdo->beginTransaction();
    
    // Simpan transaksi
    $stmt = $pdo->prepare("INSERT INTO transactions 
                          (user_id, transaction_code, amount, payment_method, status, 
                          payment_proof, membership_type, notes, created_at) 
                          VALUES (?, ?, ?, 'transfer', 'pending', ?, ?, ?, NOW())");
    
    $stmt->execute([
        $_SESSION['user_id'],
        $transaction_code,
        $amount,
        $payment_proof,
        $membership_type,
        $notes ?: "Pembayaran membership " . $membership_type
    ]);
    
    // Dapatkan ID transaksi yang baru dibuat
    $transaction_id = $pdo->lastInsertId();
    
    // Kirim notifikasi email (opsional)
    // sendPaymentConfirmationEmail($_SESSION['user_id'], $transaction_id);
    
    // Commit transaksi
    $pdo->commit();
    
    // Set pesan sukses
    $_SESSION['flash_message'] = 'Pembayaran berhasil dikonfirmasi! Kode transaksi: <strong>' . $transaction_code . '</strong>. Silakan tunggu verifikasi admin.';
    $_SESSION['flash_type'] = 'success';
    $_SESSION['last_transaction_code'] = $transaction_code;
    
    // Redirect ke halaman transaksi
    header("Location: ../transactions.php");
    exit();
    
} catch (PDOException $e) {
    // Rollback jika terjadi error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Hapus file yang sudah diupload jika terjadi error database
    if (!empty($payment_proof) && file_exists(PAYMENT_PATH . $payment_proof)) {
        unlink(PAYMENT_PATH . $payment_proof);
    }
    
    $_SESSION['flash_message'] = 'Terjadi kesalahan sistem: ' . (ENVIRONMENT === 'development' ? $e->getMessage() : 'Silakan coba lagi.');
    $_SESSION['flash_type'] = 'error';
    
    error_log("Payment Process Error: " . $e->getMessage());
    
    header("Location: ../membership.php");
    exit();
}
?>