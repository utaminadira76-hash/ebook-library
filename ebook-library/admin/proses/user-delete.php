<?php
// File: admin/proses/user-delete.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('../users.php', 'ID user tidak valid!', 'error');
}

$id = (int)$_GET['id'];

// Cek apakah user adalah admin sendiri
if ($id == $_SESSION['user_id']) {
    redirect('../users.php', 'Tidak bisa menghapus akun sendiri!', 'error');
}

// Hapus user
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$id])) {
    redirect('../users.php', 'User berhasil dihapus!', 'success');
} else {
    redirect('../users.php', 'Gagal menghapus user!', 'error');
}
?>