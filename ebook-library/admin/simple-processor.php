<?php
// File: admin/simple-processor.php - VERSI PERBAIKAN
require_once __DIR__ . '/../includes/config.php'; // PERBAIKAN PATH
require_once 'check_admin.php';

// Hapus duplikat session_start() karena sudah ada di config.php

// Ambil action dan data
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$table = $_POST['table'] ?? $_GET['table'] ?? '';

// Validasi
if (empty($action) || empty($table)) {
    $_SESSION['flash_message'] = 'Parameter tidak valid';
    $_SESSION['flash_type'] = 'error';
    header("Location: index.php");
    exit();
}

// Fungsi untuk mengembalikan ke halaman sebelumnya
function goBack($page, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $page.php");
    exit();
}

// Proses berdasarkan table dan action
switch($table) {
    case 'books':
        processBooks($action);
        break;
    case 'categories':
        processCategories($action);
        break;
    case 'users':
        processUsers($action);
        break;
    default:
        goBack('index', 'Table tidak dikenali', 'error');
}

// Fungsi proses untuk books - SIMPLIFIED VERSION
function processBooks($action) {
    global $pdo;
    
    if ($action == 'add') {
        // Validasi input wajib
        if (empty($_POST['title']) || empty($_POST['author'])) {
            goBack('books', 'Judul dan penulis wajib diisi', 'error');
        }
        
        // Cek apakah file buku diupload
        if (!isset($_FILES['book_file']) || $_FILES['book_file']['error'] !== UPLOAD_ERR_OK) {
            goBack('books', 'File buku (PDF) wajib diunggah', 'error');
        }

        try {
            // Ambil data dari form
            $title = trim($_POST['title']);
            $author = trim($_POST['author']);
            $description = trim($_POST['description'] ?? '');
            $isbn = trim($_POST['isbn'] ?? '');
            $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
            $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0.00;
            $is_free = isset($_POST['is_free']) ? 1 : 0;
            $is_featured = isset($_POST['is_featured']) ? 1 : 0;
            $total_pages = !empty($_POST['total_pages']) ? (int)$_POST['total_pages'] : 0;
            $status = (isset($_POST['status']) && $_POST['status'] == 'active') ? 'active' : 'inactive';

            // Jika buku gratis, set harga ke 0
            if ($is_free) {
                $price = 0.00;
            }

            // Proses upload cover (optional)
            $cover_image = 'default.jpg';
            if (!empty($_FILES['cover_image']['name']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
                $cover_file = $_FILES['cover_image'];
                $cover_ext = strtolower(pathinfo($cover_file['name'], PATHINFO_EXTENSION));
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($cover_ext, $allowed_exts)) {
                    throw new Exception("Format cover tidak didukung. Gunakan JPG, PNG, atau GIF.");
                }
                
                if ($cover_file['size'] > 2097152) { // 2MB
                    throw new Exception('Ukuran cover maksimal 2MB.');
                }
                
                $cover_image = 'cover_' . time() . '_' . uniqid() . '.' . $cover_ext;
                $cover_destination = COVER_PATH . $cover_image;
                
                if (!move_uploaded_file($cover_file['tmp_name'], $cover_destination)) {
                    throw new Exception('Gagal mengupload cover.');
                }
            }

            // Proses upload book file (wajib)
            $book_file = $_FILES['book_file'];
            $book_ext = strtolower(pathinfo($book_file['name'], PATHINFO_EXTENSION));
            
            if ($book_ext !== 'pdf') {
                throw new Exception('Format file buku harus PDF.');
            }
            
            if ($book_file['size'] > 1073741824) { // 1GB
                throw new Exception('Ukuran file buku maksimal 1GB.');
            }
            
            $book_file_name = 'book_' . time() . '_' . uniqid() . '.pdf';
            $book_destination = BOOK_PATH . $book_file_name;
            
            if (!move_uploaded_file($book_file['tmp_name'], $book_destination)) {
                throw new Exception('Gagal mengupload file buku.');
            }

            // Proses upload preview file (optional)
            $preview_file = null;
            if (!empty($_FILES['preview_file']['name']) && $_FILES['preview_file']['error'] === UPLOAD_ERR_OK) {
                $preview = $_FILES['preview_file'];
                $preview_ext = strtolower(pathinfo($preview['name'], PATHINFO_EXTENSION));
                
                if ($preview_ext === 'pdf') {
                    $preview_file = 'preview_' . time() . '_' . uniqid() . '.pdf';
                    $preview_destination = PREVIEW_PATH . $preview_file;
                    
                    if (!move_uploaded_file($preview['tmp_name'], $preview_destination)) {
                        $preview_file = null;
                    }
                }
            }

            // Insert ke database - PASTIKAN FIELD SESUAI DENGAN TABLE
            $sql = "INSERT INTO books (title, author, description, isbn, category_id, 
                                       cover_image, book_file, preview_file, price, 
                                       is_free, is_featured, total_pages, status, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $pdo->prepare($sql);
            
            $params = [
                $title, $author, $description, $isbn, $category_id,
                $cover_image, $book_file_name, $preview_file, $price,
                $is_free, $is_featured, $total_pages, $status
            ];
            
            if ($stmt->execute($params)) {
                $book_id = $pdo->lastInsertId();
                
                goBack('books', 'Buku berhasil ditambahkan!');
            } else {
                throw new Exception('Gagal menyimpan data ke database.');
            }
            
        } catch (Exception $e) {
            goBack('books', 'Error: ' . $e->getMessage(), 'error');
        }
        
    } elseif ($action == 'delete') {
        $id = $_GET['id'] ?? 0;
        
        if (!is_numeric($id) || $id == 0) {
            goBack('books', 'ID tidak valid', 'error');
        }
        
        try {
            // Ambil data buku untuk menghapus file
            $stmt = $pdo->prepare("SELECT cover_image, book_file, preview_file FROM books WHERE id = ?");
            $stmt->execute([$id]);
            $book = $stmt->fetch();
            
            if ($book) {
                // Hapus file cover jika bukan default
                if ($book['cover_image'] && $book['cover_image'] !== 'default.jpg') {
                    $cover_path = COVER_PATH . $book['cover_image'];
                    if (file_exists($cover_path)) {
                        unlink($cover_path);
                    }
                }
                
                // Hapus file buku
                if ($book['book_file']) {
                    $book_path = BOOK_PATH . $book['book_file'];
                    if (file_exists($book_path)) {
                        unlink($book_path);
                    }
                }
                
                // Hapus file preview jika ada
                if ($book['preview_file']) {
                    $preview_path = PREVIEW_PATH . $book['preview_file'];
                    if (file_exists($preview_path)) {
                        unlink($preview_path);
                    }
                }
            }
            
            // Hapus dari database
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            if ($stmt->execute([$id])) {
                goBack('books', 'Buku berhasil dihapus');
            } else {
                goBack('books', 'Gagal menghapus buku', 'error');
            }
            
        } catch (Exception $e) {
            goBack('books', 'Error: ' . $e->getMessage(), 'error');
        }
    }
}

// Fungsi processCategories (sama dengan sebelumnya)
function processCategories($action) {
    global $pdo;
    
    if ($action == 'add') {
        $name = $_POST['name'] ?? '';
        
        if (empty($name)) {
            goBack('categories', 'Nama kategori harus diisi', 'error');
        }
        
        $stmt = $pdo->prepare("INSERT INTO categories (name, created_at) VALUES (?, NOW())");
        if ($stmt->execute([$name])) {
            goBack('categories', 'Kategori berhasil ditambahkan');
        } else {
            goBack('categories', 'Gagal menambahkan kategori', 'error');
        }
        
    } elseif ($action == 'edit') {
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        
        if (empty($id) || empty($name)) {
            goBack('categories', 'Data tidak lengkap', 'error');
        }
        
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        if ($stmt->execute([$name, $id])) {
            goBack('categories', 'Kategori berhasil diupdate');
        } else {
            goBack('categories', 'Gagal mengupdate kategori', 'error');
        }
        
    } elseif ($action == 'delete') {
        $id = $_GET['id'] ?? 0;
        
        if (!is_numeric($id) || $id == 0) {
            goBack('categories', 'ID tidak valid', 'error');
        }
        
        // Cek apakah kategori memiliki buku
        $check = $pdo->prepare("SELECT COUNT(*) as count FROM books WHERE category_id = ?");
        $check->execute([$id]);
        $result = $check->fetch();
        
        if ($result['count'] > 0) {
            goBack('categories', 'Kategori tidak bisa dihapus karena memiliki buku', 'error');
        }
        
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        if ($stmt->execute([$id])) {
            goBack('categories', 'Kategori berhasil dihapus');
        } else {
            goBack('categories', 'Gagal menghapus kategori', 'error');
        }
    }
}

// Fungsi processUsers (sama dengan sebelumnya)
function processUsers($action) {
    global $pdo;
    
    if ($action == 'add') {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (empty($username) || empty($email) || empty($password)) {
            goBack('users', 'Semua field harus diisi', 'error');
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            goBack('users', 'User berhasil ditambahkan');
        } else {
            goBack('users', 'Gagal menambahkan user', 'error');
        }
        
    } elseif ($action == 'edit') {
        $id = $_POST['id'] ?? 0;
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        
        if (empty($id) || empty($username) || empty($email)) {
            goBack('users', 'Data tidak lengkap', 'error');
        }
        
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        if ($stmt->execute([$username, $email, $id])) {
            goBack('users', 'User berhasil diupdate');
        } else {
            goBack('users', 'Gagal mengupdate user', 'error');
        }
        
    } elseif ($action == 'delete') {
        $id = $_GET['id'] ?? 0;
        
        if (!is_numeric($id) || $id == 0) {
            goBack('users', 'ID tidak valid', 'error');
        }
        
        // Cek apakah menghapus diri sendiri
        if ($id == $_SESSION['user_id']) {
            goBack('users', 'Tidak bisa menghapus akun sendiri', 'error');
        }
        
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            goBack('users', 'User berhasil dihapus');
        } else {
            goBack('users', 'Gagal menghapus user', 'error');
        }
    }
}
?>