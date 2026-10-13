<?php
// File: payment.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu!', 'error');
}

$type = $_GET['type'] ?? 'premium';
$amount = $type == 'premium' ? 99000 : 0;

$page_title = 'Pembayaran Membership';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process payment
    $transaction_code = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
    
    // Handle file upload
    $payment_proof = '';
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION));
        $filename = 'payment_' . time() . '_' . $_SESSION['user_id'] . '.' . $ext;
        $upload_path = UPLOAD_PATH . 'payments/';
        
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0777, true);
        }
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'pdf'])) {
            if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path . $filename)) {
                $payment_proof = $filename;
            }
        }
    }
    
    if ($payment_proof) {
        try {
            $stmt = $pdo->prepare("INSERT INTO transactions 
                                  (user_id, transaction_code, amount, status, payment_proof, membership_type, notes, created_at) 
                                  VALUES (?, ?, ?, 'pending', ?, 'yearly', ?, NOW())");
            
            $stmt->execute([
                $_SESSION['user_id'],
                $transaction_code,
                $amount,
                $payment_proof,
                $_POST['notes'] ?? ''
            ]);
            
            redirect('transactions.php', 'Pembayaran berhasil dikirim! Kode: ' . $transaction_code, 'success');
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "Bukti pembayaran harus diupload!";
    }
}
?>

<div class="container">
    <h1 class="mb-4">Pembayaran Membership <?php echo ucfirst($type); ?></h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Ringkasan Pembayaran</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th>Paket:</th>
                            <td><?php echo $type == 'premium' ? 'Premium (1 Tahun)' : 'Free'; ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah:</th>
                            <td class="fw-bold"><?php echo formatCurrency($amount); ?></td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td><span class="badge bg-warning">Pending</span> (setelah pembayaran)</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Instruksi Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6>Transfer ke:</h6>
                        <ul class="mb-0">
                            <li><strong>BCA:</strong> 1234567890 (E-Book Library)</li>
                            <li><strong>Mandiri:</strong> 0987654321 (E-Book Library)</li>
                            <li><strong>BNI:</strong> 1122334455 (E-Book Library)</li>
                        </ul>
                    </div>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Upload Bukti Transfer *</label>
                            <input type="file" name="payment_proof" class="form-control" required accept="image/*,.pdf">
                            <small class="text-muted">Format: JPG, PNG, PDF (maks 5MB)</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Catatan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Tulis catatan..."></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Kirim Pembayaran</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>