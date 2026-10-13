<?php
// File: admin/proses/book-add.php - VERSION FIXED
require_once '../check_admin.php';

// Enable detailed error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../redirect-handler.php?page=books&message=Akses tidak valid&type=error");
    exit();
}

// Include database and config
require_once '../config/database.php';
require_once '../../../includes/config.php'; // Path yang benar ke config utama

// Start output buffering untuk debugging
ob_start();
echo "=== DEBUG: Book Add Process Started ===<br>\n";

// Log semua data yang diterima
error_log("=== BOOK ADD DEBUG ===");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Inisialisasi variabel
$success = false;
$error_message = '';

try {
    // Validasi input wajib
    if (empty($_POST['title']) || empty($_POST['author'])) {
        throw new Exception('Judul dan penulis wajib diisi');
    }

    // Cek apakah file buku diupload
    if (empty($_FILES['book_file']['name'])) {
        throw new Exception('File buku (PDF) wajib diunggah');
    }

    // Ambil data dari form
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $description = trim($_POST['description'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $price = floatval($_POST['price'] ?? 0);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $total_pages = !empty($_POST['total_pages']) ? (int)$_POST['total_pages'] : 0;
    $status = isset($_POST['status']) ? 'active' : 'inactive';

    // Jika buku gratis, set harga ke 0
    if ($is_free) {
        $price = 0;
    }

    echo "=== DEBUG: Form data processed ===<br>\n";
    error_log("Processed data - Title: $title, Author: $author, Price: $price");

    // Proses upload cover image
    $cover_image = 'default.jpg'; // Default
    
    if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $cover_file = $_FILES['cover_image'];
        
        echo "=== DEBUG: Processing cover upload ===<br>\n";
        error_log("Cover file: " . print_r($cover_file, true));
        
        // Validasi tipe file (gunakan multiple methods)
        $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        // Method 1: MIME type from PHP
        $php_mime = $cover_file['type'];
        
        // Method 2: File extension
        $file_ext = strtolower(pathinfo($cover_file['name'], PATHINFO_EXTENSION));
        
        // Method 3: Actual MIME type detection
        if (function_exists('finfo_file')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $actual_mime = finfo_file($finfo, $cover_file['tmp_name']);
            finfo_close($finfo);
        } else {
            $actual_mime = mime_content_type($cover_file['tmp_name']);
        }
        
        error_log("Cover MIME: PHP=$php_mime, Actual=$actual_mime, Ext=$file_ext");
        
        // Validasi: Cek ekstensi dan MIME type
        if (!in_array($file_ext, $allowed_exts)) {
            throw new Exception("Format cover tidak didukung. Gunakan JPG, PNG, atau GIF. File Anda: .$file_ext");
        }
        
        // Validasi ukuran file (max 2MB = 2097152 bytes)
        if ($cover_file['size'] > 2097152) {
            throw new Exception('Ukuran cover maksimal 2MB. File Anda: ' . 
                              round($cover_file['size'] / 1024 / 1024, 2) . 'MB');
        }
        
        // Cek error upload
        if ($cover_file['error'] !== UPLOAD_ERR_OK) {
            $upload_errors = [
                0 => 'Tidak ada error',
                1 => 'File terlalu besar (php.ini)',
                2 => 'File terlalu besar (HTML form)',
                3 => 'File hanya terupload sebagian',
                4 => 'Tidak ada file yang diupload',
                6 => 'Folder temporary tidak ada',
                7 => 'Gagal menulis ke disk',
                8 => 'Ekstensi PHP menghentikan upload'
            ];
            throw new Exception('Error upload cover: ' . ($upload_errors[$cover_file['error']] ?? 'Unknown error'));
        }
        
        // Generate nama file unik
        $cover_image = 'cover_' . time() . '_' . uniqid() . '.' . $file_ext;
        $cover_destination = COVER_PATH . $cover_image;
        
        error_log("Cover destination: $cover_destination");
        
        // Pastikan folder ada dan writable
        if (!is_dir(COVER_PATH)) {
            mkdir(COVER_PATH, 0755, true);
        }
        
        if (!is_writable(COVER_PATH)) {
            throw new Exception('Folder cover tidak dapat ditulisi. Permission: ' . substr(sprintf('%o', fileperms(COVER_PATH)), -4));
        }
        
        // Move uploaded file
        if (move_uploaded_file($cover_file['tmp_name'], $cover_destination)) {
            error_log("Cover uploaded successfully: $cover_destination");
            echo "=== DEBUG: Cover uploaded successfully ===<br>\n";
            
            // Verifikasi file
            if (file_exists($cover_destination)) {
                $actual_size = filesize($cover_destination);
                error_log("Cover file verified. Size: $actual_size bytes");
            } else {
                error_log("WARNING: Cover file not found after upload!");
            }
        } else {
            $error = error_get_last();
            throw new Exception('Gagal mengupload cover: ' . ($error['message'] ?? 'Unknown error'));
        }
    } else {
        error_log("No cover uploaded or upload error: " . ($_FILES['cover_image']['error'] ?? 'No file'));
        echo "=== DEBUG: Using default cover ===<br>\n";
    }

    // Proses upload book file (PDF) - SIMPLIFIED VERSION
    $book_file_name = null;
    if ($_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
        $book_file = $_FILES['book_file'];
        
        // Validasi hanya ekstensi PDF
        $book_ext = strtolower(pathinfo($book_file['name'], PATHINFO_EXTENSION));
        if ($book_ext !== 'pdf') {
            throw new Exception('Format file buku harus PDF');
        }
        
        // Generate nama file unik
        $book_file_name = 'book_' . time() . '_' . uniqid() . '.pdf';
        $book_destination = BOOK_PATH . $book_file_name;
        
        if (!move_uploaded_file($book_file['tmp_name'], $book_destination)) {
            throw new Exception('Gagal mengupload file buku');
        }
        
        error_log("Book file uploaded: $book_destination");
    }

    // Insert ke database dengan logging detail
    $sql = "INSERT INTO books (title, author, description, isbn, category_id, 
                               cover_image, book_file, price, is_free, 
                               is_featured, total_pages, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    
    $params = [
        $title, $author, $description, $isbn, $category_id,
        $cover_image, $book_file_name, $price, $is_free,
        $is_featured, $total_pages, $status
    ];
    
    error_log("Inserting book with params: " . print_r($params, true));
    
    if ($stmt->execute($params)) {
        $book_id = $pdo->lastInsertId();
        $success = true;
        error_log("Book inserted successfully. ID: $book_id");
        echo "=== DEBUG: Book inserted to database. ID: $book_id ===<br>\n";
    } else {
        throw new Exception('Gagal menyimpan data ke database: ' . implode(', ', $stmt->errorInfo()));
    }

} catch (Exception $e) {
    $error_message = $e->getMessage();
    $success = false;
    error_log("ERROR in book-add.php: " . $error_message);
    echo "=== DEBUG ERROR: " . $error_message . " ===<br>\n";
}

// Redirect dengan status
$debug_output = ob_get_clean();
error_log("Debug output: " . $debug_output);

if ($success) {
    // Simpan debug info ke session untuk dilihat nanti
    $_SESSION['upload_debug'] = $debug_output;
    header("Location: ../redirect-handler.php?page=books&message=Buku berhasil ditambahkan&type=success");
} else {
    $message = !empty($error_message) ? $error_message : 'Gagal menambahkan buku';
    // Simpan debug info dan error
    $_SESSION['upload_debug'] = $debug_output;
    $_SESSION['upload_error'] = $message;
    header("Location: ../redirect-handler.php?page=books&message=" . urlencode($message) . "&type=error");
}
exit();
?>