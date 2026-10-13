<?php
// File: includes/config.php

// ============================================
// KONFIGURASI DASAR SISTEM
// ============================================

// Environment (development/production)
define('ENVIRONMENT', 'development'); // Ganti ke 'production' di server live

// Aktifkan error reporting hanya untuk development
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// ============================================
// KONFIGURASI DATABASE
// ============================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ebook_library');

// ============================================
// KONFIGURASI SITUS
// ============================================
define('SITE_URL', 'http://localhost/ebook-library/');
define('SITE_NAME', 'E-Book Library');
define('SITE_ADMIN_EMAIL', 'admin@ebooklibrary.com');

// ============================================
// KONFIGURASI PATH UNTUK FILE UPLOAD
// ============================================

// Base path untuk upload (gunakan absolute path)
define('UPLOAD_BASE', $_SERVER['DOCUMENT_ROOT'] . '/ebook-library/assets/uploads/');
define('UPLOAD_PATH', UPLOAD_BASE); // Alias untuk kompatibilitas

// Subdirectories untuk berbagai tipe file
define('COVER_PATH', UPLOAD_BASE . 'covers/');
define('BOOK_PATH', UPLOAD_BASE . 'books/');
define('PREVIEW_PATH', UPLOAD_BASE . 'previews/');
define('PAYMENT_PATH', UPLOAD_BASE . 'payments/');
define('PROFILE_PATH', UPLOAD_BASE . 'profiles/');

// ============================================
// KONFIGURASI URL UNTUK AKSES FILE
// ============================================
define('UPLOAD_URL', SITE_URL . 'assets/uploads/');
define('COVER_URL', UPLOAD_URL . 'covers/');
define('BOOK_URL', UPLOAD_URL . 'books/');
define('PREVIEW_URL', UPLOAD_URL . 'previews/');
define('PAYMENT_URL', UPLOAD_URL . 'payments/');
define('PROFILE_URL', UPLOAD_URL . 'profiles/');

// ============================================
// KONFIGURASI KEHAMANAN
// ============================================
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOCKOUT_TIME', 900); // 15 menit dalam detik
define('SESSION_TIMEOUT', 1800); // 30 menit dalam detik
define('CSRF_TOKEN_LIFETIME', 3600); // 1 jam dalam detik

// ============================================
// KONFIGURASI UPLOAD FILE
// ============================================
define('MAX_UPLOAD_SIZE', 5242880); // 5MB dalam bytes
define('ALLOWED_COVER_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_BOOK_TYPES', ['pdf', 'epub', 'mobi']);
define('ALLOWED_PREVIEW_TYPES', ['pdf']);
define('ALLOWED_PAYMENT_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);
define('ALLOWED_PROFILE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// ============================================
// KONFIGURASI MEMBERSHIP
// ============================================
define('MEMBERSHIP_PRICE_MONTHLY', 25000);
define('MEMBERSHIP_PRICE_YEARLY', 99000);
define('MEMBERSHIP_PRICE_PREMIUM', 199000);
define('TRIAL_DAYS', 7); // Masa trial gratis

// ============================================
// KONFIGURASI EMAIL (jika menggunakan email)
// ============================================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-email-password');
define('SMTP_SECURE', 'tls');

// ============================================
// KONEKSI DATABASE
// ============================================
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch(PDOException $e) {
    if (ENVIRONMENT === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        error_log("Database connection error: " . $e->getMessage());
        die("System error. Please try again later.");
    }
}

// ============================================
// INISIALISASI SESSION - PERBAIKAN TOTAL
// ============================================

// Hentikan semua session yang ada
if (session_status() !== PHP_SESSION_NONE) {
    session_abort();
}

// Gunakan hanya satu session name
session_name('EBOOKLIB_SESSION');

// Set cookie parameters
session_set_cookie_params([
    'lifetime' => 0, // Sampai browser ditutup
    'path' => '/',
    'domain' => '',
    'secure' => false, // Set true jika HTTPS
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Start session
session_start();

// Regenerate ID untuk keamanan
if (!isset($_SESSION['created'])) {
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// Debug: log session
error_log("Session started: " . session_id() . " - Name: " . session_name());

// ============================================
// FUNGSI HELPER UNTUK PATH
// ============================================

/**
 * Mendapatkan absolute path untuk direktori upload
 */
function get_upload_path($type = '') {
    switch($type) {
        case 'cover':
            return COVER_PATH;
        case 'book':
            return BOOK_PATH;
        case 'preview':
            return PREVIEW_PATH;
        case 'payment':
            return PAYMENT_PATH;
        case 'profile':
            return PROFILE_PATH;
        default:
            return UPLOAD_PATH;
    }
}

/**
 * Mendapatkan URL untuk file upload
 */
function get_upload_url($type = '') {
    switch($type) {
        case 'cover':
            return COVER_URL;
        case 'book':
            return BOOK_URL;
        case 'preview':
            return PREVIEW_URL;
        case 'payment':
            return PAYMENT_URL;
        case 'profile':
            return PROFILE_URL;
        default:
            return UPLOAD_URL;
    }
}

/**
 * Membuat direktori upload jika belum ada
 */
function init_upload_dirs() {
    $directories = [
        UPLOAD_BASE,
        COVER_PATH,
        BOOK_PATH,
        PREVIEW_PATH,
        PAYMENT_PATH,
        PROFILE_PATH
    ];
    
    foreach ($directories as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
            // Buat file .htaccess untuk keamanan
            $htaccess = $dir . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Order deny,allow\nDeny from all\n");
            }
        }
    }
}

// Inisialisasi direktori upload saat pertama kali
init_upload_dirs();

// ============================================
// SET TIMEZONE
// ============================================
date_default_timezone_set('Asia/Jakarta');

// ============================================
// AUTO-LOAD HELPER FUNCTIONS
// ============================================
require_once __DIR__ . '/functions.php';
?>