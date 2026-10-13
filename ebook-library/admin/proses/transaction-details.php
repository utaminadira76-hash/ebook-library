<?php
// File: admin/proses/transaction-details.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID tidak valid');
}

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("SELECT t.*, u.username, u.email, u.full_name, u.membership_status 
                      FROM transactions t 
                      JOIN users u ON t.user_id = u.id 
                      WHERE t.id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    die('Transaksi tidak ditemukan');
}

$status_class = [
    'pending' => 'warning',
    'success' => 'success',
    'failed' => 'danger',
    'canceled' => 'secondary'
][$transaction['status']] ?? 'secondary';
?>

<table class="table table-sm">
    <tr>
        <th width="40%">Kode Transaksi:</th>
        <td><strong><?php echo $transaction['transaction_code']; ?></strong></td>
    </tr>
    <tr>
        <th>User:</th>
        <td>
            <?php echo htmlspecialchars($transaction['username']); ?><br>
            <small><?php echo htmlspecialchars($transaction['email']); ?></small>
        </td>
    </tr>
    <tr>
        <th>Nama Lengkap:</th>
        <td><?php echo htmlspecialchars($transaction['full_name'] ?: '-'); ?></td>
    </tr>
    <tr>
        <th>Status Membership:</th>
        <td>
            <span class="badge bg-<?php echo $transaction['membership_status'] == 'premium' ? 'warning' : 'secondary'; ?>">
                <?php echo $transaction['membership_status'] == 'premium' ? 'Premium' : 'Free'; ?>
            </span>
        </td>
    </tr>
    <tr>
        <th>Jumlah:</th>
        <td class="fw-bold"><?php echo formatCurrency($transaction['amount']); ?></td>
    </tr>
    <tr>
        <th>Tipe Membership:</th>
        <td><?php echo $transaction['membership_type'] == 'yearly' ? 'Tahunan' : 'Bulanan'; ?></td>
    </tr>
    <tr>
        <th>Status:</th>
        <td><span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst($transaction['status']); ?></span></td>
    </tr>
    <tr>
        <th>Metode Pembayaran:</th>
        <td><?php echo ucfirst($transaction['payment_method']); ?></td>
    </tr>
    <tr>
        <th>Tanggal Transaksi:</th>
        <td><?php echo formatDate($transaction['created_at'], 'd M Y H:i:s'); ?></td>
    </tr>
    <tr>
        <th>Tanggal Update:</th>
        <td><?php echo formatDate($transaction['updated_at'], 'd M Y H:i:s'); ?></td>
    </tr>
    <?php if ($transaction['expiry_date']): ?>
    <tr>
        <th>Berlaku Hingga:</th>
        <td><?php echo formatDate($transaction['expiry_date']); ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($transaction['notes']): ?>
    <tr>
        <th>Catatan:</th>
        <td><?php echo nl2br(htmlspecialchars($transaction['notes'])); ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($transaction['payment_proof']): ?>
    <tr>
        <th>Bukti Transfer:</th>
        <td>
            <a href="../../assets/uploads/payments/<?php echo $transaction['payment_proof']; ?>" 
               target="_blank" class="btn btn-sm btn-success">
                Lihat Gambar
            </a>
        </td>
    </tr>
    <?php endif; ?>
</table>