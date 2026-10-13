<?php
// File: admin/book-edit.php
require_once 'check_admin.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('books.php', 'ID buku tidak valid!', 'error');
}

$book_id = (int)$_GET['id'];

// Ambil data buku
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    redirect('books.php', 'Buku tidak ditemukan!', 'error');
}

// Ambil kategori
$categories_stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
$categories = $categories_stmt->fetchAll();

$page_title = 'Edit Buku';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <?php include 'head-assets.php'; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-top">
            <div class="container-fluid">
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
                <a href="books.php" class="btn btn-admin-outline">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
        </nav>
        
        <?php displayFlashMessage(); ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Edit Buku: <?php echo htmlspecialchars($book['title']); ?></h5>
            </div>
            <div class="card-body">
                <form action="proses/book-update.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?php echo $book['id']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Judul Buku *</label>
                            <input type="text" name="title" class="form-control" required 
                                   value="<?php echo htmlspecialchars($book['title']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Penulis *</label>
                            <input type="text" name="author" class="form-control" required
                                   value="<?php echo htmlspecialchars($book['author']); ?>">
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Kategori</label>
                            <select name="category_id" class="form-select">
                                <option value="">Pilih Kategori</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $book['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>ISBN</label>
                            <input type="text" name="isbn" class="form-control"
                                   value="<?php echo htmlspecialchars($book['isbn'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"><?php 
                            echo htmlspecialchars($book['description'] ?? ''); 
                        ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" 
                                   value="<?php echo $book['price']; ?>" min="0" step="1000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Total Halaman</label>
                            <input type="number" name="total_pages" class="form-control" 
                                   value="<?php echo $book['total_pages']; ?>" min="0">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label>Tanggal Terbit</label>
                            <input type="date" name="publish_date" class="form-control"
                                   value="<?php echo $book['publish_date'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="is_free" class="form-check-input" id="isFree"
                                    <?php echo $book['is_free'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isFree">Buku Gratis</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured"
                                    <?php echo $book['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="isFeatured">Featured</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" name="status" class="form-check-input" id="status"
                                    <?php echo $book['status'] == 'active' ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status">Aktif</label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Cover Buku (Biarkan kosong jika tidak ingin mengubah)</label>
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <?php if ($book['cover_image'] && $book['cover_image'] != 'default.jpg'): ?>
                            <div class="mt-2">
                                <img src="../assets/uploads/covers/<?php echo $book['cover_image']; ?>" 
                                     width="100" alt="Cover saat ini">
                                <p class="text-muted small mt-1">Cover saat ini</p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>File Buku (PDF - Biarkan kosong jika tidak ingin mengubah)</label>
                            <input type="file" name="book_file" class="form-control" accept=".pdf">
                            <small class="text-muted">File saat ini: <?php echo $book['book_file']; ?></small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>File Preview (PDF - Biarkan kosong jika tidak ingin mengubah)</label>
                        <input type="file" name="preview_file" class="form-control" accept=".pdf">
                        <?php if ($book['preview_file']): ?>
                        <small class="text-muted">File saat ini: <?php echo $book['preview_file']; ?></small>
                        <?php endif; ?>
                    </div>
                    
                    <div class="text-end">
                        <button type="submit" class="btn btn-admin">
                            <i class="bi bi-check-circle"></i> Update Buku
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle harga berdasarkan checkbox gratis
        document.getElementById('isFree').addEventListener('change', function() {
            const priceInput = document.querySelector('input[name="price"]');
            if (this.checked) {
                priceInput.value = 0;
                priceInput.readOnly = true;
            } else {
                priceInput.readOnly = false;
            }
        });
    </script>
</body>
</html>