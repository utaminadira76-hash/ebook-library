<?php
// File: process-download.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

// Ambil book_id
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    $_SESSION['flash_message'] = 'ID buku tidak valid.';
    $_SESSION['flash_type'] = 'error';
    header('Location: catalog.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Cek akses buku
$book = getBookFileInfo($book_id);
if (!$book) {
    $_SESSION['flash_message'] = 'Buku tidak ditemukan.';
    $_SESSION['flash_type'] = 'error';
    header('Location: catalog.php');
    exit;
}

if (!$book['file_exists']) {
    $_SESSION['flash_message'] = 'File buku tidak ditemukan.';
    $_SESSION['flash_type'] = 'error';
    header("Location: book-detail.php?id=$book_id");
    exit;
}

// Cek membership
$isPremium = isPremium();
$book_is_free = ($book['is_free'] == 1);

if (!$book_is_free && !$isPremium) {
    $_SESSION['flash_message'] = 'Anda perlu membership premium untuk mengakses buku ini.';
    $_SESSION['flash_type'] = 'error';
    header("Location: book-detail.php?id=$book_id");
    exit;
}

// Cek limit download untuk user free
if (!$isPremium && !canUserDownloadBook($user_id, $book_id)) {
    $stats = getUserDownloadStats($user_id);
    $_SESSION['flash_message'] = "Batas download bulanan Anda telah habis. (Limit: {$stats['monthly_limit']} buku/bulan)";
    $_SESSION['flash_type'] = 'error';
    header("Location: book-detail.php?id=$book_id");
    exit;
}

// Generate download token
$token = generateDownloadToken($user_id, $book_id);
if (!$token) {
    $_SESSION['flash_message'] = 'Gagal membuat token download.';
    $_SESSION['flash_type'] = 'error';
    header("Location: book-detail.php?id=$book_id");
    exit;
}

// Redirect ke download file dengan token
header("Location: download-file.php?token=" . $token);
exit;
?>