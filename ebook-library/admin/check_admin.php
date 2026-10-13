<?php
// File: admin/check_admin.php - OPTIMIZED

// Pastikan tidak ada output sebelum header
if (session_status() === PHP_SESSION_NONE) {
    session_name('EBOOKLIB_SESSION');
    session_start();
}

// Cek apakah config sudah di-include
if (!defined('DB_HOST')) {
    $config_path = __DIR__ . '/../includes/config.php';
    
    if (!file_exists($config_path)) {
        die("Error: File config.php tidak ditemukan di: " . $config_path);
    }
    
    require_once $config_path;
}

// Cek koneksi database
try {
    $pdo->query("SELECT 1");
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../login.php?message=Silakan login terlebih dahulu!&type=error");
    exit();
}

// Cek apakah user adalah admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php?message=Akses ditolak! Halaman admin hanya untuk administrator.&type=error");
    exit();
}