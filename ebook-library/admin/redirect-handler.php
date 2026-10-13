<?php
// File: admin/redirect-handler.php
// File ini hanya untuk handle redirect setelah proses

// Mulai session
session_start();

// Ambil parameter
$page = $_GET['page'] ?? 'index';
$message = $_GET['message'] ?? '';
$type = $_GET['type'] ?? 'success';

// Simpan di session untuk flash message
if ($message) {
    $_SESSION['flash_message'] = urldecode($message);
    $_SESSION['flash_type'] = $type;
}

// Redirect ke halaman yang sesuai
switch($page) {
    case 'books':
        header("Location: books.php");
        break;
    case 'categories':
        header("Location: categories.php");
        break;
    case 'users':
        header("Location: users.php");
        break;
    case 'dashboard':
        header("Location: index.php");
        break;
    default:
        header("Location: index.php");
}

exit();
?>