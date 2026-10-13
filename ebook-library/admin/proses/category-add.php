<?php
// File: admin/proses/category-add.php
require_once '../check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../categories.php', 'Akses tidak valid!', 'error');
}

$name = sanitize($_POST['name']);
$description = sanitize($_POST['description']);

if (empty($name)) {
    redirect('../categories.php', 'Nama kategori harus diisi!', 'error');
}

// Cek apakah nama kategori sudah ada
$stmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
$stmt->execute([$name]);
if ($stmt->rowCount() > 0) {
    redirect('../categories.php', 'Nama kategori sudah ada!', 'error');
}

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO categories (name, description, created_at) VALUES (?, ?, NOW())");
if ($stmt->execute([$name, $description])) {
    redirect('../categories.php', 'Kategori berhasil ditambahkan!', 'success');
} else {
    redirect('../categories.php', 'Gagal menambahkan kategori!', 'error');
}
?>