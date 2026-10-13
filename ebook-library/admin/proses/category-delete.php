<?php
// File: admin/proses/category-delete.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('../categories.php', 'ID kategori tidak valid!', 'error');
}

$id = (int)$_GET['id'];

// Cek apakah kategori memiliki buku
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM books WHERE category_id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch();

if ($result['count'] > 0) {
    redirect('../categories.php', 'Kategori tidak bisa dihapus karena memiliki buku!', 'error');
}

// Hapus kategori
$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
if ($stmt->execute([$id])) {
    redirect('../categories.php', 'Kategori berhasil dihapus!', 'success');
} else {
    redirect('../categories.php', 'Gagal menghapus kategori!', 'error');
}
?>