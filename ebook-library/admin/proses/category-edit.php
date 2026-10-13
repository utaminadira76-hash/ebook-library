<?php
// File: admin/proses/category-edit.php
require_once '../check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../categories.php', 'Akses tidak valid!', 'error');
}

$id = (int)$_POST['id'];
$name = sanitize($_POST['name']);
$description = sanitize($_POST['description']);

if (empty($name) || empty($id)) {
    redirect('../categories.php', 'Data tidak valid!', 'error');
}

// Cek apakah nama kategori sudah ada (kecuali untuk kategori ini)
$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? AND id != ?");
$stmt->execute([$name, $id]);
if ($stmt->rowCount() > 0) {
    redirect('../categories.php', 'Nama kategori sudah ada!', 'error');
}

// Update database
$stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
if ($stmt->execute([$name, $description, $id])) {
    redirect('../categories.php', 'Kategori berhasil diupdate!', 'success');
} else {
    redirect('../categories.php', 'Gagal mengupdate kategori!', 'error');
}
?>