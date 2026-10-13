<?php
// File: includes/functions.php

/**
 * Inisialisasi koneksi database
 */
function initDatabase() {
    static $pdo = null;
    
    if ($pdo === null) {
        // Gunakan konstanta dari config.php
        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASS')) {
            require_once __DIR__ . '/config.php';
        }
        
        try {
            $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Check membership expiry and update if needed
 */
function checkMembershipExpiry($user_id) {
    $pdo = initDatabase();
    
    $stmt = $pdo->prepare("SELECT membership_status, membership_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user && $user['membership_status'] == 'premium' && $user['membership_expiry']) {
        $remaining_days = getRemainingDays($user['membership_expiry']);
        
        if ($remaining_days <= 0) {
            // Membership expired, downgrade to free
            $update_stmt = $pdo->prepare("UPDATE users SET membership_status = 'free', membership_expiry = NULL WHERE id = ?");
            $update_stmt->execute([$user_id]);
            
            // Update session
            updateMembershipStatusInSession($user_id);
            
            // Log the automatic downgrade
            logActivity($user_id, 'membership_expired', 'Membership expired, automatically downgraded to free');
            return true;
        }
    }
    
    return false;
}

/**
 * Get remaining membership days
 */
function getRemainingDays($expiry_date) {
    if (!$expiry_date || $expiry_date == '0000-00-00') {
        return 0;
    }
    
    $expiry = strtotime($expiry_date);
    $today = strtotime(date('Y-m-d'));
    
    if ($expiry === false) {
        return 0;
    }
    
    $diff = $expiry - $today;
    
    if ($diff < 0) {
        return 0;
    }
    
    return floor($diff / (60 * 60 * 24));
}

/**
 * Check if user has access to a book
 */
function hasBookAccess($user_membership, $book_price, $book_is_free) {
    if ($book_is_free || $book_price == 0) {
        return true;
    }
    
    if ($user_membership == 'premium') {
        return true;
    }
    
    return false;
}

/**
 * Redirect dengan pesan flash (support URL absolut & relatif)
 */
function redirect($url, $message = null, $type = 'info') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }

    // Jika URL bukan absolut, tambahkan SITE_URL dari config
    if (strpos($url, 'http') !== 0 && strpos($url, 'https') !== 0) {
        if (!defined('SITE_URL')) {
            require_once __DIR__ . '/config.php';
        }
        $url = SITE_URL . ltrim($url, '/');
    }

    header("Location: " . $url);
    exit();
}

/**
 * Dapatkan base URL (http/https + domain)
 */
function getBaseUrl() {
    $protocol = (
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
    ) ? 'https://' : 'http://';

    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . $host;
}

/**
 * Dapatkan full URL dengan path
 */
function getFullUrl($path = '') {
    $base = getBaseUrl();
    return $path ? $base . '/' . ltrim($path, '/') : $base;
}

/**
 * Tampilkan flash message dengan Bootstrap styling
 */
function displayFlashMessage() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';

        switch ($type) {
            case 'success': 
                $alert = 'alert-success';
                $icon = 'bi-check-circle-fill';
                break;
            case 'error':   
                $alert = 'alert-danger';
                $icon = 'bi-exclamation-triangle-fill';
                break;
            case 'warning': 
                $alert = 'alert-warning';
                $icon = 'bi-exclamation-circle-fill';
                break;
            default:        
                $alert = 'alert-info';
                $icon = 'bi-info-circle-fill';
        }

        echo '<div class="alert ' . $alert . ' alert-dismissible fade show d-flex align-items-center" role="alert">';
        echo '<i class="bi ' . $icon . ' me-2"></i>';
        echo '<div>' . htmlspecialchars($message) . '</div>';
        echo '<button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';

        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
    }
}

/**
 * Sanitize input - enhanced version
 */
function sanitize($input, $type = 'string') {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    
    $input = trim($input);
    
    switch ($type) {
        case 'int':
            return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        case 'float':
            return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        case 'email':
            return filter_var($input, FILTER_SANITIZE_EMAIL);
        case 'url':
            return filter_var($input, FILTER_SANITIZE_URL);
        case 'html':
            // For HTML content, use a library like HTMLPurifier
            // This is just basic sanitization
            return strip_tags($input, '<p><br><b><i><u><strong><em><ul><ol><li><a><img>');
        default:
            // String - escape HTML but keep special chars
            return htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
    }
}

/**
 * Validasi email dengan regex tambahan
 */
function validateEmail($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Additional regex validation
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($pattern, $email);
}

/**
 * Hash password dengan cost yang disesuaikan
 */
function hashPassword($password) {
    $options = [
        'cost' => 12,
    ];
    return password_hash($password, PASSWORD_DEFAULT, $options);
}

/**
 * Verify password dengan timing attack protection
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Generate random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Check login status
 */
function isLoggedIn() {
    // Jika session belum start, start dengan nama yang benar
    if (session_status() === PHP_SESSION_NONE) {
        session_name('EBOOKLIB_SESSION');
        session_start();
    }
    
    // Debug log
    error_log("isLoggedIn check - User ID in session: " . ($_SESSION['user_id'] ?? 'NOT SET'));
    
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is premium - FIXED
 */
function isPremium() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('EBOOKLIB_SESSION');
        session_start();
    }
    
    return isset($_SESSION['membership_status']) && $_SESSION['membership_status'] === 'premium';
}

/**
 * Format tanggal dengan berbagai format
 */
function formatDate($date, $format = 'd M Y') {
    if (empty($date) || $date == '0000-00-00' || $date == '0000-00-00 00:00:00') {
        return '-';
    }
    
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return '-';
    }
    
    $formats = [
        'short' => 'd/m/Y',
        'medium' => 'd M Y',
        'long' => 'd F Y',
        'full' => 'l, d F Y',
        'datetime' => 'd M Y H:i',
        'time' => 'H:i',
        'mysql_date' => 'Y-m-d',
        'mysql_datetime' => 'Y-m-d H:i:s'
    ];
    
    $useFormat = $formats[$format] ?? $format;
    return date($useFormat, $timestamp);
}

/**
 * Format currency Rupiah dengan opsi desimal
 */
function formatCurrency($amount, $decimal = 0) {
    if (!is_numeric($amount)) {
        return 'Rp 0';
    }
    return 'Rp ' . number_format($amount, $decimal, ',', '.');
}

/**
 * Buat folder upload jika belum ada
 */
function createUploadDirectories() {
    // Gunakan konstanta dari config.php
    if (!defined('COVER_PATH') || !defined('BOOK_PATH') || !defined('PREVIEW_PATH') || !defined('PAYMENT_PROOF_PATH')) {
        require_once __DIR__ . '/config.php';
    }
    
    $dirs = [
        COVER_PATH,
        BOOK_PATH,
        PREVIEW_PATH,
        PAYMENT_PROOF_PATH
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            
            // Tambahkan file index.php untuk keamanan
            $indexFile = $dir . 'index.php';
            if (!file_exists($indexFile)) {
                file_put_contents($indexFile, '<?php header("HTTP/1.0 403 Forbidden"); exit; ?>');
            }
            
            // Tambahkan .htaccess untuk Apache
            $htaccess = $dir . '.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Options -Indexes\nDeny from all");
            }
        }
    }
}

/**
 * Upload file dengan validasi
 */
function uploadFile($file, $targetDir, $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'], $maxSize = 5242880) {
    // 5MB default
    $errors = [];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $uploadErrors = [
            UPLOAD_ERR_INI_SIZE => 'File terlalu besar (melebihi ukuran maksimum server)',
            UPLOAD_ERR_FORM_SIZE => 'File terlalu besar (melebihi ukuran maksimum form)',
            UPLOAD_ERR_PARTIAL => 'File hanya ter-upload sebagian',
            UPLOAD_ERR_NO_FILE => 'Tidak ada file yang diupload',
            UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk',
            UPLOAD_ERR_EXTENSION => 'Upload dihentikan oleh ekstensi PHP'
        ];
        
        $errors[] = $uploadErrors[$file['error']] ?? 'Error uploading file: ' . $file['error'];
        return ['success' => false, 'errors' => $errors];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        $errors[] = 'Ukuran file terlalu besar. Maksimal ' . ($maxSize / 1024 / 1024) . 'MB';
        return ['success' => false, 'errors' => $errors];
    }
    
    // Check file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        $errors[] = 'Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowedTypes);
        return ['success' => false, 'errors' => $errors];
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . strtolower($extension);
    $targetPath = $targetDir . $filename;
    
    // Ensure directory exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'filename' => $filename,
            'path' => $targetPath,
            'original_name' => $file['name'],
            'mime_type' => $mimeType
        ];
    } else {
        $errors[] = 'Gagal menyimpan file yang diupload';
        return ['success' => false, 'errors' => $errors];
    }
}

/**
 * Log aktivitas user
 */
function logActivity($user_id, $action, $description = '') {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->execute([$user_id, $action, $description, $ip, $userAgent]);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        // Jika table tidak ada, abaikan error (untuk development)
        error_log('Failed to log activity: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user information
 */
function getUserInfo($user_id) {
    $pdo = initDatabase();
    
    $stmt = $pdo->prepare("
        SELECT id, username, email, full_name, membership_status, membership_expiry, 
               created_at, last_login, profile_picture, role 
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

/**
 * Update session user data
 */
function updateSessionUserData($user_id) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $user = getUserInfo($user_id);
    if ($user) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['membership_status'] = $user['membership_status'];
        $_SESSION['membership_expiry'] = $user['membership_expiry'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        return true;
    }
    
    return false;
}

/**
 * Update membership status di session
 */
function updateMembershipStatusInSession($user_id) {
    $pdo = initDatabase();
    
    $stmt = $pdo->prepare("SELECT membership_status, membership_expiry FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if ($user) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION['membership_status'] = $user['membership_status'];
        $_SESSION['membership_expiry'] = $user['membership_expiry'];
        return true;
    }
    
    return false;
}

/**
 * Generate pagination links
 */
function paginate($totalItems, $itemsPerPage, $currentPage, $urlPattern) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($urlPattern, $currentPage - 1) . '" aria-label="Previous">';
        $html .= '<span aria-hidden="true">&laquo;</span>';
        $html .= '</a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link" aria-hidden="true">&laquo;</span>';
        $html .= '</li>';
    }
    
    // Page numbers
    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    
    for ($i = $start; $i <= $end; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item">';
            $html .= '<a class="page-link" href="' . sprintf($urlPattern, $i) . '">' . $i . '</a>';
            $html .= '</li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item">';
        $html .= '<a class="page-link" href="' . sprintf($urlPattern, $currentPage + 1) . '" aria-label="Next">';
        $html .= '<span aria-hidden="true">&raquo;</span>';
        $html .= '</a></li>';
    } else {
        $html .= '<li class="page-item disabled">';
        $html .= '<span class="page-link" aria-hidden="true">&raquo;</span>';
        $html .= '</li>';
    }
    
    $html .= '</ul></nav>';
    return $html;
}

/**
 * Clean old files in upload directory
 */
function cleanOldFiles($directory, $daysOld = 30) {
    if (!is_dir($directory)) {
        return 0;
    }
    
    $files = glob($directory . '*');
    $deleted = 0;
    $cutoff = time() - ($daysOld * 24 * 60 * 60);
    
    foreach ($files as $file) {
        if (is_file($file) && filemtime($file) < $cutoff) {
            @unlink($file);
            $deleted++;
        }
    }
    
    return $deleted;
}

/**
 * Send email notification (basic implementation)
 */
function sendEmail($to, $subject, $body, $isHTML = true) {
    // In production, use PHPMailer or similar library
    $headers = "MIME-Version: 1.0" . "\r\n";
    
    if ($isHTML) {
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    } else {
        $headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";
    }
    
    $headers .= 'From: no-reply@ebooklibrary.com' . "\r\n";
    $headers .= 'Reply-To: no-reply@ebooklibrary.com' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    
    return @mail($to, $subject, $body, $headers);
}

/**
 * Validate Indonesian phone number
 */
function validatePhoneNumber($phone) {
    // Remove all non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Check if it's a valid Indonesian phone number
    // Format: 08xx, +628xx, 628xx
    $pattern = '/^(?:\+?62|0)(?:\d{8,15})$/';
    return preg_match($pattern, $phone);
}

/**
 * Calculate book rating
 */
function calculateBookRating($book_id) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            SELECT AVG(rating) as average, COUNT(*) as total 
            FROM reviews 
            WHERE book_id = ? AND rating > 0
        ");
        $stmt->execute([$book_id]);
        $result = $stmt->fetch();
        
        return [
            'average' => round($result['average'] ?? 0, 1),
            'total' => $result['total'] ?? 0
        ];
    } catch (PDOException $e) {
        return ['average' => 0, 'total' => 0];
    }
}

/**
 * Get reading progress
 */
function getReadingProgress($user_id, $book_id) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            SELECT last_page, total_pages, updated_at 
            FROM reading_progress 
            WHERE user_id = ? AND book_id = ?
        ");
        $stmt->execute([$user_id, $book_id]);
        $progress = $stmt->fetch();
        
        if ($progress && $progress['total_pages'] > 0) {
            $percentage = round(($progress['last_page'] / $progress['total_pages']) * 100);
            return [
                'percentage' => min($percentage, 100),
                'last_page' => $progress['last_page'],
                'total_pages' => $progress['total_pages'],
                'updated_at' => $progress['updated_at']
            ];
        }
    } catch (PDOException $e) {
        // Table might not exist yet
        error_log('Reading progress error: ' . $e->getMessage());
    }
    
    return null;
}

/**
 * Check if user can download book
 */
function canDownloadBook($user_id, $book_id) {
    $pdo = initDatabase();
    
    // Check if user is premium
    $user_stmt = $pdo->prepare("SELECT membership_status FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();
    
    if ($user && $user['membership_status'] == 'premium') {
        return true; // Premium users can download unlimited
    }
    
    // For free users, check download limit (e.g., 5 per month)
    $current_month = date('Y-m-01');
    $download_stmt = $pdo->prepare("
        SELECT COUNT(*) as download_count 
        FROM downloads 
        WHERE user_id = ? 
        AND download_date >= ?
    ");
    $download_stmt->execute([$user_id, $current_month]);
    $downloads = $download_stmt->fetch();
    
    return ($downloads['download_count'] ?? 0) < 5; // Limit 5 downloads per month for free users
}

/**
 * Log download activity
 */
function logDownload($user_id, $book_id) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO downloads (user_id, book_id, download_date) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$user_id, $book_id]);
        
        logActivity($user_id, 'book_download', 'Downloaded book ID: ' . $book_id);
        return true;
    } catch (PDOException $e) {
        error_log('Failed to log download: ' . $e->getMessage());
        return false;
    }
}

/**
 * Format file size
 */
function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}

/**
 * Get user download history
 */
function getUserDownloads($user_id, $limit = 10) {
    $pdo = initDatabase();
    
    $stmt = $pdo->prepare("
        SELECT d.*, b.title, b.author 
        FROM downloads d 
        JOIN books b ON d.book_id = b.id 
        WHERE d.user_id = ? 
        ORDER BY d.download_date DESC 
        LIMIT ?
    ");
    $stmt->execute([$user_id, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get book file information
 */
/**
 * Get book file information - FIXED VERSION
 */
function getBookFileInfo($book_id) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            SELECT b.*, 
                   (SELECT COUNT(*) FROM download_logs WHERE book_id = b.id) as download_count,
                   c.name as category_name
            FROM books b
            LEFT JOIN categories c ON b.category_id = c.id
            WHERE b.id = ?
        ");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch();
        
        if (!$book) {
            return null;
        }
        
        // DEBUG: Tampilkan data buku
        error_log("Book Data for ID $book_id: " . print_r($book, true));
        
        // Cek apakah file ada
        $book_file = $book['book_file'] ?? '';
        $file_exists = false;
        $file_size = 0;
        $file_size_formatted = 'Unknown';
        $file_path = '';
        
        if (!empty($book_file) && $book_file != '') {
            $file_path = BOOK_PATH . $book_file;
            if (file_exists($file_path) && is_file($file_path)) {
                $file_exists = true;
                $file_size = filesize($file_path);
                $file_size_formatted = formatFileSize($file_size);
            }
        }
        
        return [
            'id' => $book['id'],
            'title' => $book['title'],
            'author' => $book['author'],
            'book_file' => $book_file,
            'is_free' => $book['is_free'],
            'price' => $book['price'],
            'file_exists' => $file_exists,
            'file_path' => $file_path,
            'file_size' => $file_size,
            'file_size_formatted' => $file_size_formatted,
            'download_count' => $book['download_count'] ?? 0,
            'category_name' => $book['category_name'] ?? 'Unknown'
        ];
    } catch (PDOException $e) {
        error_log('Error getting book info: ' . $e->getMessage());
        return null;
    }
}

/**
 * Check if user can download book
 */
function canUserDownloadBook($user_id, $book_id) {
    $pdo = initDatabase();
    
    // Cek apakah user premium
    $user_stmt = $pdo->prepare("SELECT membership_status FROM users WHERE id = ?");
    $user_stmt->execute([$user_id]);
    $user = $user_stmt->fetch();
    
    if ($user && $user['membership_status'] == 'premium') {
        return true; // Premium users unlimited
    }
    
    // Cek apakah buku gratis
    $book_stmt = $pdo->prepare("SELECT is_free FROM books WHERE id = ?");
    $book_stmt->execute([$book_id]);
    $book = $book_stmt->fetch();
    
    if ($book && $book['is_free'] == 1) {
        return true; // Free book accessible to all
    }
    
    // Untuk user free dan buku premium, cek limit download
    $current_month = date('Y-m');
    
    // Cek apakah user ada di tabel download_limits
    $limit_stmt = $pdo->prepare("
        SELECT * FROM download_limits 
        WHERE user_id = ? AND current_month = ?
    ");
    $limit_stmt->execute([$user_id, $current_month]);
    $limit = $limit_stmt->fetch();
    
    if (!$limit) {
        // Jika belum ada record, buat baru
        $create_stmt = $pdo->prepare("
            INSERT INTO download_limits (user_id, monthly_limit, current_month, download_count, last_reset)
            VALUES (?, 10, ?, 0, NOW())
        ");
        $create_stmt->execute([$user_id, $current_month]);
        return true; // Boleh download karena belum ada record
    }
    
    // Cek apakah masih ada kuota
    if ($limit['download_count'] < $limit['monthly_limit']) {
        return true;
    }
    
    return false; // Kuota habis
}

/**
 * Get user download statistics
 */
function getUserDownloadStats($user_id) {
    $pdo = initDatabase();
    $current_month = date('Y-m');
    
    $stmt = $pdo->prepare("
        SELECT monthly_limit, download_count, 
               (monthly_limit - download_count) as remaining
        FROM download_limits 
        WHERE user_id = ? AND current_month = ?
    ");
    $stmt->execute([$user_id, $current_month]);
    $stats = $stmt->fetch();
    
    if (!$stats) {
        // Jika belum ada, return default
        return [
            'monthly_limit' => 10,
            'download_count' => 0,
            'remaining' => 10,
            'monthly_downloads' => 0
        ];
    }
    
    return [
        'monthly_limit' => $stats['monthly_limit'],
        'download_count' => $stats['download_count'],
        'remaining' => $stats['remaining'],
        'monthly_downloads' => $stats['download_count']
    ];
}

/**
 * Generate download token
 */
function generateDownloadToken($user_id, $book_id) {
    $pdo = initDatabase();
    
    try {
        // Generate unique token
        $token = bin2hex(random_bytes(32));
        
        // Set expiration time (10 minutes)
        $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        // Save token to database
        $stmt = $pdo->prepare("
            INSERT INTO download_tokens (user_id, book_id, token, expires_at, used)
            VALUES (?, ?, ?, ?, 0)
        ");
        $stmt->execute([$user_id, $book_id, $token, $expires_at]);
        
        return $token;
    } catch (PDOException $e) {
        error_log('Error generating download token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Verify download token
 */
function verifyDownloadToken($token) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            SELECT dt.*, b.book_file, b.title,
                   u.membership_status, b.is_free
            FROM download_tokens dt
            JOIN books b ON dt.book_id = b.id
            JOIN users u ON dt.user_id = u.id
            WHERE dt.token = ? 
            AND dt.used = 0
            AND dt.expires_at > NOW()
        ");
        $stmt->execute([$token]);
        $token_data = $stmt->fetch();
        
        return $token_data;
    } catch (PDOException $e) {
        error_log('Error verifying token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update download count for user
 */
function updateUserDownloadCount($user_id) {
    $pdo = initDatabase();
    $current_month = date('Y-m');
    
    try {
        // Cek apakah record sudah ada
        $check_stmt = $pdo->prepare("
            SELECT id FROM download_limits 
            WHERE user_id = ? AND current_month = ?
        ");
        $check_stmt->execute([$user_id, $current_month]);
        $existing = $check_stmt->fetch();
        
        if ($existing) {
            // Update count
            $update_stmt = $pdo->prepare("
                UPDATE download_limits 
                SET download_count = download_count + 1,
                    last_reset = NOW()
                WHERE user_id = ? AND current_month = ?
            ");
            $update_stmt->execute([$user_id, $current_month]);
        } else {
            // Buat record baru
            $insert_stmt = $pdo->prepare("
                INSERT INTO download_limits (user_id, monthly_limit, current_month, download_count, last_reset)
                VALUES (?, 10, ?, 1, NOW())
            ");
            $insert_stmt->execute([$user_id, $current_month]);
        }
        
        return true;
    } catch (PDOException $e) {
        error_log('Error updating download count: ' . $e->getMessage());
        return false;
    }
}

/**
 * Mark token as used
 */
function markTokenAsUsed($token_id) {
    $pdo = initDatabase();
    
    try {
        $stmt = $pdo->prepare("
            UPDATE download_tokens 
            SET used = 1 
            WHERE id = ?
        ");
        $stmt->execute([$token_id]);
        return true;
    } catch (PDOException $e) {
        error_log('Error marking token as used: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log download activity
 */
function logDownloadActivity($user_id, $book_id) {
    $pdo = initDatabase();
    
    try {
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt = $pdo->prepare("
            INSERT INTO download_logs (user_id, book_id, ip_address, user_agent)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $book_id, $ip_address, $user_agent]);
        
        // Also log to activity_logs
        logActivity($user_id, 'book_download', "Downloaded book ID: $book_id");
        
        return true;
    } catch (PDOException $e) {
        error_log('Error logging download: ' . $e->getMessage());
        return false;
    }
}
?>