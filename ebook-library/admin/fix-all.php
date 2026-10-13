<?php
// File: admin/fix-all.php
echo "<h1>Perbaikan Sistem Admin</h1>";

// Cek file yang ada
$required_files = [
    'check_admin.php',
    'index.php',
    'books.php',
    'categories.php',
    'users.php',
    'sidebar.php',
    'head-assets.php',
    'admin-style.css'
];

echo "<h3>1. Cek File yang Diperlukan:</h3>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✓ $file</p>";
    } else {
        echo "<p style='color: red;'>✗ $file - TIDAK ADA</p>";
    }
}

// Cek folder proses
echo "<h3>2. Cek Folder Proses:</h3>";
if (is_dir('proses')) {
    echo "<p style='color: green;'>✓ Folder proses ada</p>";
    
    $process_files = ['book-add.php', 'book-delete.php', 'category-add.php', 'category-edit.php', 'category-delete.php', 'user-add.php', 'user-edit.php', 'user-delete.php'];
    
    foreach ($process_files as $file) {
        if (file_exists('proses/' . $file)) {
            echo "<p style='color: green;'>✓ proses/$file</p>";
        } else {
            echo "<p style='color: orange;'>⚠ proses/$file - Tidak ada (akan dibuat)</p>";
        }
    }
} else {
    echo "<p style='color: red;'>✗ Folder proses tidak ada</p>";
    mkdir('proses', 0777, true);
    echo "<p style='color: green;'>✓ Folder proses dibuat</p>";
}

// Cek koneksi database
echo "<h3>3. Cek Koneksi Database:</h3>";
try {
    require_once '../includes/config.php';
    $pdo->query("SELECT 1");
    echo "<p style='color: green;'>✓ Koneksi database OK</p>";
    
    // Cek tabel
    $tables = ['users', 'books', 'categories'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p style='color: green;'>✓ Tabel $table ada</p>";
        } else {
            echo "<p style='color: red;'>✗ Tabel $table tidak ada</p>";
        }
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Database error: " . $e->getMessage() . "</p>";
}

echo "<h3>4. Solusi Cepat:</h3>";
echo "<p>Jika masih ada masalah redirect, gunakan link berikut:</p>";
echo "<ul>";
echo "<li><a href='books-simple.php'>Manajemen Buku (Simple)</a></li>";
echo "<li><a href='categories-simple.php'>Manajemen Kategori (Simple)</a></li>";
echo "<li><a href='users-simple.php'>Manajemen User (Simple)</a></li>";
echo "</ul>";

echo "<h3>5. Link Testing:</h3>";
echo "<ul>";
echo "<li><a href='../fix-database.php'>Perbaiki Database</a></li>";
echo "<li><a href='index.php'>Dashboard Admin</a></li>";
echo "<li><a href='books.php'>Manajemen Buku</a></li>";
echo "</ul>";
?>