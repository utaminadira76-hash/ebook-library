<?php
// File: admin/proses/transaction-approve.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../transactions.php?error=ID transaksi tidak valid");
    exit();
}

$id = (int)$_GET['id'];

try {
    // Get transaction details
    $stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ? AND status = 'pending'");
    $stmt->execute([$id]);
    $transaction = $stmt->fetch();
    
    if (!$transaction) {
        header("Location: ../transactions.php?error=Transaksi tidak ditemukan atau sudah diproses");
        exit();
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update transaction status
    $update_stmt = $pdo->prepare("UPDATE transactions SET status = 'success', updated_at = NOW() WHERE id = ?");
    $update_stmt->execute([$id]);
    
    // Update user membership
    if ($transaction['membership_type'] == 'yearly') {
        $expiry_date = date('Y-m-d', strtotime('+1 year'));
    } else {
        $expiry_date = date('Y-m-d', strtotime('+1 month'));
    }
    
    $user_stmt = $pdo->prepare("UPDATE users SET membership_status = 'premium', membership_expiry = ? WHERE id = ?");
    $user_stmt->execute([$expiry_date, $transaction['user_id']]);
    
    // Update transaction expiry date
    $expiry_stmt = $pdo->prepare("UPDATE transactions SET expiry_date = ? WHERE id = ?");
    $expiry_stmt->execute([$expiry_date, $id]);
    
    $pdo->commit();
    
    header("Location: ../transactions.php?success=Transaksi berhasil diapprove");
    exit();
    
} catch (PDOException $e) {
    $pdo->rollBack();
    header("Location: ../transactions.php?error=Error: " . urlencode($e->getMessage()));
    exit();
}
?>