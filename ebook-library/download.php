<?php
// File: download.php - VERSI SEDERHANA YANG BEKERJA
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session dengan benar
if (session_status() === PHP_SESSION_NONE) {
    session_name('EBOOKLIB_SESSION');
    session_start();
}

require_once 'includes/config.php';
require_once 'includes/functions.php';

echo "<!DOCTYPE html><html><head><title>Download Debug</title></head><body>";
echo "<div style='background:#f0f0f0; padding:20px; margin:20px; border:2px solid red;'>";
echo "<h2>DEBUG DOWNLOAD SYSTEM</h2>";

// Cek login dengan cara sederhana
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo "<p style='color:red;'><strong>ERROR:</strong> Anda belum login!</p>";
    echo "<p>Session data: <pre>";
    print_r($_SESSION);
    echo "</pre></p>";
    
    // Redirect ke login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    echo "<p>Redirecting to login in 3 seconds...</p>";
    echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 3000);</script>";
    echo "</div></body></html>";
    exit;
}

echo "<p><strong>✓ User logged in:</strong> User ID: " . $_SESSION['user_id'] . "</p>";

// Ambil book_id
$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    echo "<p style='color:red;'><strong>ERROR:</strong> ID buku tidak valid!</p>";
    echo "<p>GET parameters: <pre>";
    print_r($_GET);
    echo "</pre></p>";
    echo "<p><a href='catalog.php'>Kembali ke Katalog</a></p>";
    echo "</div></body></html>";
    exit;
}

echo "<p><strong>✓ Book ID:</strong> $book_id</p>";

// Cek buku di database
try {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$book_id]);
    $book = $stmt->fetch();
    
    if (!$book) {
        echo "<p style='color:red;'><strong>ERROR:</strong> Buku ID $book_id tidak ditemukan di database!</p>";
        echo "<p><a href='catalog.php'>Lihat buku yang tersedia</a></p>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<p><strong>✓ Buku ditemukan:</strong> " . htmlspecialchars($book['title']) . "</p>";
    echo "<p><strong>File buku:</strong> " . htmlspecialchars($book['book_file']) . "</p>";
    
    // Cek apakah file ada
    $file_path = BOOK_PATH . $book['book_file'];
    if (empty($book['book_file']) || !file_exists($file_path)) {
        echo "<p style='color:red;'><strong>ERROR:</strong> File buku tidak ditemukan di server!</p>";
        echo "<p>File path: " . htmlspecialchars($file_path) . "</p>";
        
        // Tampilkan file yang tersedia
        echo "<h3>File PDF yang tersedia:</h3>";
        $pdf_files = glob(BOOK_PATH . "*.pdf");
        if (count($pdf_files) > 0) {
            echo "<ul>";
            foreach ($pdf_files as $file) {
                echo "<li>" . basename($file) . " (" . formatFileSize(filesize($file)) . ")</li>";
            }
            echo "</ul>";
            
            // Opsi untuk assign file
            echo "<p><a href='assign-book-file.php?book_id=$book_id'>Assign file ke buku ini</a></p>";
        } else {
            echo "<p>Tidak ada file PDF di folder books!</p>";
        }
        
        echo "</div></body></html>";
        exit;
    }
    
    echo "<p><strong>✓ File ditemukan:</strong> " . htmlspecialchars($file_path) . " (" . formatFileSize(filesize($file_path)) . ")</p>";
    
    // Cek membership
    $is_premium = ($_SESSION['membership_status'] ?? 'free') === 'premium';
    $is_free_book = ($book['is_free'] == 1);
    
    echo "<p><strong>Status Membership:</strong> " . ($is_premium ? 'PREMIUM' : 'FREE') . "</p>";
    echo "<p><strong>Status Buku:</strong> " . ($is_free_book ? 'GRATIS' : 'PREMIUM') . "</p>";
    
    // Cek akses
    if (!$is_free_book && !$is_premium) {
        echo "<p style='color:red;'><strong>ERROR:</strong> Anda perlu membership premium untuk mengakses buku ini!</p>";
        echo "<p><a href='membership.php'>Upgrade ke Premium</a></p>";
        echo "</div></body></html>";
        exit;
    }
    
    echo "<p><strong>✓ Akses diberikan!</strong></p>";
    
    // DOWNLOAD FILE
    $file_name = basename($book['book_file']);
    $file_size = filesize($file_path);
    
    echo "<p><strong>Memulai download...</strong></p>";
    
    // Set headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $file_name . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . $file_size);
    
    // Clear buffer dan kirim file
    ob_clean();
    flush();
    
    readfile($file_path);
    exit;
    
} catch(Exception $e) {
    echo "<p style='color:red;'><strong>ERROR DATABASE:</strong> " . $e->getMessage() . "</p>";
    echo "</div></body></html>";
    exit;
}

echo "</div></body></html>";
?>