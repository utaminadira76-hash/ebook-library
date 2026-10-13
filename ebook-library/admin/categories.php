<?php
// File: admin/categories.php
require_once 'check_admin.php';

$page_title = 'Manajemen Kategori';
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
                <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <i class="bi bi-plus-circle"></i> Tambah Kategori
                </button>
            </div>
        </nav>
        
        <?php displayFlashMessage(); ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Daftar Kategori</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Kategori</th>
                                        <th>Deskripsi</th>
                                        <th>Jumlah Buku</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT c.*, 
                                                        (SELECT COUNT(*) FROM books WHERE category_id = c.id) as book_count 
                                                        FROM categories c ORDER BY c.name");
                                    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                    
                                    if (empty($categories)) {
                                        echo '<tr><td colspan="5" class="text-center">Belum ada kategori</td></tr>';
                                    } else {
                                        foreach ($categories as $cat) {
                                            echo '<tr>';
                                            echo '<td>' . $cat['id'] . '</td>';
                                            echo '<td><strong>' . htmlspecialchars($cat['name']) . '</strong></td>';
                                            echo '<td>' . htmlspecialchars(substr($cat['description'] ?? '', 0, 50)) . '...</td>';
                                            echo '<td><span class="badge bg-primary">' . $cat['book_count'] . ' buku</span></td>';
                                            echo '<td>';
                                            echo '<div class="btn-group btn-group-sm">';
                                            echo '<button class="btn btn-warning edit-category" 
                                                    data-id="' . $cat['id'] . '"
                                                    data-name="' . htmlspecialchars($cat['name']) . '"
                                                    data-description="' . htmlspecialchars($cat['description'] ?? '') . '">
                                                    <i class="bi bi-pencil"></i></button>';
                                            echo '<button class="btn btn-danger delete-category" 
                                                    data-id="' . $cat['id'] . '" 
                                                    data-name="' . htmlspecialchars($cat['name']) . '">
                                                    <i class="bi bi-trash"></i></button>';
                                            echo '</div>';
                                            echo '</td>';
                                            echo '</tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Informasi</h5>
                    </div>
                    <div class="card-body">
                        <p>Kategori digunakan untuk mengelompokkan buku berdasarkan topik atau genre.</p>
                        <p><strong>Tips:</strong></p>
                        <ul>
                            <li>Buat kategori yang jelas dan deskriptif</li>
                            <li>Hindari kategori yang tumpang tindih</li>
                            <li>Setiap buku bisa memiliki satu kategori</li>
                            <li>Kategori tidak bisa dihapus jika memiliki buku</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Kategori -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kategori Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses/category-add.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label>Nama Kategori *</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-admin">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit Kategori -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses/category-edit.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editCategoryId">
                        <div class="mb-3">
                            <label>Nama Kategori *</label>
                            <input type="text" name="name" id="editCategoryName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="description" id="editCategoryDescription" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-admin">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit kategori
        document.querySelectorAll('.edit-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const description = this.dataset.description;
                
                document.getElementById('editCategoryId').value = id;
                document.getElementById('editCategoryName').value = name;
                document.getElementById('editCategoryDescription').value = description;
                
                const modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
                modal.show();
            });
        });
        
        // Hapus kategori
        document.querySelectorAll('.delete-category').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;
                
                if (confirm(`Apakah Anda yakin ingin menghapus kategori "${name}"?`)) {
                    window.location.href = `proses/category-delete.php?id=${id}`;
                }
            });
        });
    </script>
</body>
</html>