<?php
// File: admin/proses/transaction-reject.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../transactions.php?error=ID transaksi tidak valid");
    exit();
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("UPDATE transactions SET status = 'failed', updated_at = NOW() WHERE id = ?");
if ($stmt->execute([$id])) {
    header("Location: ../transactions.php?success=Transaksi berhasil direject");
} else {
    header("Location: ../transactions.php?error=Gagal mereject transaksi");
}
exit();
?>