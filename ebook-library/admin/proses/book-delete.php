<?php
// File: admin/proses/book-delete.php
require_once '../check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../books.php?error=ID buku tidak valid");
    exit();
}

// [KODE SEBELUMNYA TETAP SAMA...]

// DI AKHIR FILE:
if ($stmt->execute([$book_id])) {
    header("Location: ../books.php?success=Buku berhasil dihapus");
    exit();
} else {
    header("Location: ../books.php?error=Gagal menghapus buku");
    exit();
}
?>