<?php
// File: admin/export-transactions.php
require_once 'check_admin.php';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=transactions_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['ID', 'Kode', 'User', 'Email', 'Jumlah', 'Tipe', 'Status', 'Tanggal', 'Berlaku Hingga']);

$stmt = $pdo->query("SELECT t.*, u.username, u.email 
                     FROM transactions t 
                     JOIN users u ON t.user_id = u.id 
                     ORDER BY t.created_at DESC");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $row['id'],
        $row['transaction_code'],
        $row['username'],
        $row['email'],
        $row['amount'],
        $row['membership_type'] == 'yearly' ? 'Tahunan' : 'Bulanan',
        $row['status'],
        $row['created_at'],
        $row['expiry_date'] ?? ''
    ]);
}

fclose($output);
exit();
?>