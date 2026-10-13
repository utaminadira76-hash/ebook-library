<?php
// File: admin/proses/book-update.php
require_once '../check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../books.php', 'Akses tidak valid!', 'error');
}

$id = (int)$_POST['id'];
$title = sanitize($_POST['title']);
$author = sanitize($_POST['author']);
$description = sanitize($_POST['description']);
$isbn = sanitize($_POST['isbn']);
$category_id = $_POST['category_id'] ?: NULL;
$price = $_POST['price'] ?: 0;
$total_pages = $_POST['total_pages'] ?: 0;
$publish_date = $_POST['publish_date'] ?: NULL;
$is_free = isset($_POST['is_free']) ? 1 : 0;
$is_featured = isset($_POST['is_featured']) ? 1 : 0;
$status = isset($_POST['status']) ? 'active' : 'inactive';

// Validasi
if (empty($title) || empty($author)) {
    redirect('../books.php', 'Judul dan penulis harus diisi!', 'error');
}

// Ambil data buku lama
$stmt = $pdo->prepare("SELECT cover_image, book_file, preview_file FROM books WHERE id = ?");
$stmt->execute([$id]);
$old_book = $stmt->fetch();

if (!$old_book) {
    redirect('../books.php', 'Buku tidak ditemukan!', 'error');
}

$cover_image = $old_book['cover_image'];
$book_file = $old_book['book_file'];
$preview_file = $old_book['preview_file'];

// Update cover image jika diupload
if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $cover_ext = pathinfo($_FILES['cover_image']['name'], PATHINFO_EXTENSION);
    $cover_name = uniqid() . '.' . $cover_ext;
    $cover_tmp = $_FILES['cover_image']['tmp_name'];
    $cover_path = COVER_PATH . $cover_name;
    
    $allowed_images = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array(strtolower($cover_ext), $allowed_images)) {
        if (move_uploaded_file($cover_tmp, $cover_path)) {
            // Hapus file cover lama jika bukan default
            if ($cover_image && $cover_image !== 'default.jpg') {
                $old_cover_path = COVER_PATH . $cover_image;
                if (file_exists($old_cover_path)) {
                    unlink($old_cover_path);
                }
            }
            $cover_image = $cover_name;
        }
    }
}

// Update book file jika diupload
if (isset($_FILES['book_file']) && $_FILES['book_file']['error'] === UPLOAD_ERR_OK) {
    $book_ext = pathinfo($_FILES['book_file']['name'], PATHINFO_EXTENSION);
    $book_name = uniqid() . '.' . $book_ext;
    $book_tmp = $_FILES['book_file']['tmp_name'];
    $book_path = BOOK_PATH . $book_name;
    
    if (strtolower($book_ext) === 'pdf') {
        if (move_uploaded_file($book_tmp, $book_path)) {
            // Hapus file buku lama
            if ($book_file) {
                $old_book_path = BOOK_PATH . $book_file;
                if (file_exists($old_book_path)) {
                    unlink($old_book_path);
                }
            }
            $book_file = $book_name;
        }
    } else {
        redirect('../books.php', 'File buku harus format PDF!', 'error');
    }
}

// Update preview file jika diupload
if (isset($_FILES['preview_file']) && $_FILES['preview_file']['error'] === UPLOAD_ERR_OK) {
    $preview_ext = pathinfo($_FILES['preview_file']['name'], PATHINFO_EXTENSION);
    $preview_name = uniqid() . '_preview.' . $preview_ext;
    $preview_tmp = $_FILES['preview_file']['tmp_name'];
    $preview_path = PREVIEW_PATH . $preview_name;
    
    if (strtolower($preview_ext) === 'pdf') {
        if (move_uploaded_file($preview_tmp, $preview_path)) {
            // Hapus file preview lama
            if ($preview_file) {
                $old_preview_path = PREVIEW_PATH . $preview_file;
                if (file_exists($old_preview_path)) {
                    unlink($old_preview_path);
                }
            }
            $preview_file = $preview_name;
        }
    }
}

// Update database
try {
    $stmt = $pdo->prepare("UPDATE books SET 
                          title = ?, author = ?, description = ?, isbn = ?, category_id = ?,
                          cover_image = ?, book_file = ?, preview_file = ?, price = ?, 
                          is_free = ?, is_featured = ?, total_pages = ?, publish_date = ?,
                          status = ?, updated_at = NOW()
                          WHERE id = ?");
    
    $stmt->execute([
        $title, $author, $description, $isbn, $category_id,
        $cover_image, $book_file, $preview_file, $price, $is_free,
        $is_featured, $total_pages, $publish_date, $status, $id
    ]);
    
    redirect('../books.php', 'Buku berhasil diupdate!', 'success');
    
} catch (PDOException $e) {
    redirect('../books.php', 'Error: ' . $e->getMessage(), 'error');
}
?>