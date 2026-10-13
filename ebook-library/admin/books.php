<?php
// File: admin/books.php
require_once 'check_admin.php';

// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Tangkap pesan dari session
if (isset($_SESSION['flash_message'])) {
    $message = $_SESSION['flash_message'];
    $type = $_SESSION['flash_type'] ?? 'info';
    
    echo '<div class="alert alert-' . ($type == 'error' ? 'danger' : $type) . ' alert-dismissible fade show" role="alert">';
    echo htmlspecialchars($message);
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
    echo '</div>';
    
    // Hapus session message
    unset($_SESSION['flash_message'], $_SESSION['flash_type']);
}

$page_title = 'Manajemen Buku';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    
    <!-- Custom Admin CSS (sama seperti dashboard) -->
    <link rel="stylesheet" href="admin-style.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-top">
            <div class="container-fluid">
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
                <div class="d-flex align-items-center">
                    <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addBookModal">
                        <i class="bi bi-plus-circle"></i> Tambah Buku
                    </button>
                </div>
            </div>
        </nav>
        
        <!-- Flash Message -->
        <?php 
        // Tampilkan pesan dari session lagi di sini jika diperlukan
        // Tapi karena sudah ditampilkan di atas, kita bisa hapus atau biarkan kosong
        ?>
        
        <!-- Content -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="booksTable" class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cover</th>
                                <th>Judul</th>
                                <th>Penulis</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT b.*, c.name as category_name 
                                                 FROM books b 
                                                 LEFT JOIN categories c ON b.category_id = c.id 
                                                 ORDER BY b.created_at DESC");
                            $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($books as $book) {
                                $status_class = $book['status'] == 'active' ? 'success' : 'danger';
                                $price = $book['is_free'] ? 'Gratis' : formatCurrency($book['price']);
                                $cover = $book['cover_image'] ? '../assets/uploads/covers/' . $book['cover_image'] : '../assets/uploads/covers/default.jpg';
                                
                                echo '<tr>';
                                echo '<td>' . $book['id'] . '</td>';
                                echo '<td><img src="' . $cover . '" width="50" height="70" style="object-fit: cover;" alt="Cover"></td>';
                                echo '<td>' . htmlspecialchars($book['title']) . '</td>';
                                echo '<td>' . htmlspecialchars($book['author']) . '</td>';
                                echo '<td>' . htmlspecialchars($book['category_name'] ?? '-') . '</td>';
                                echo '<td>' . $price . '</td>';
                                echo '<td><span class="badge bg-' . $status_class . '">' . $book['status'] . '</span></td>';
                                echo '<td>' . formatDate($book['created_at']) . '</td>';
                                echo '<td>';
                                echo '<div class="btn-group btn-group-sm">';
                                echo '<a href="book-edit.php?id=' . $book['id'] . '" class="btn btn-warning"><i class="bi bi-pencil"></i></a>';
                                echo '<a href="simple-processor.php?action=delete&table=books&id=' . $book['id'] . '" 
                                       class="btn btn-danger btn-sm delete-book" 
                                       onclick="return confirm(\'Hapus buku ' . addslashes($book['title']) . '?\')">
                                       <i class="bi bi-trash"></i>
                                     </a>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah Buku -->
    <div class="modal fade" id="addBookModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Buku Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="simple-processor.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="table" value="books">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Judul Buku *</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Penulis *</label>
                                <input type="text" name="author" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                                    $categories = $stmt->fetchAll();
                                    foreach ($categories as $cat) {
                                        echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>ISBN</label>
                                <input type="text" name="isbn" class="form-control">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Harga (Rp)</label>
                                <input type="number" name="price" class="form-control" value="0" min="0" step="1000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Total Halaman</label>
                                <input type="number" name="total_pages" class="form-control" min="0">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_free" class="form-check-input" id="isFree">
                                    <label class="form-check-label" for="isFree">Buku Gratis</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="is_featured" class="form-check-input" id="isFeatured">
                                    <label class="form-check-label" for="isFeatured">Featured</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" name="status" class="form-check-input" id="status" checked value="active">
                                    <label class="form-check-label" for="status">Aktif</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Cover Buku</label>
                                <input type="file" name="cover_image" class="form-control" accept="image/*">
                                <small class="text-muted">Ukuran maksimal 2MB, format: JPG, PNG</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>File Buku (PDF) *</label>
                                <input type="file" name="book_file" class="form-control" accept=".pdf" required>
                                <small class="text-muted">Hanya file PDF, maksimal 20MB</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>File Preview (PDF - Opsional)</label>
                            <input type="file" name="preview_file" class="form-control" accept=".pdf">
                            <small class="text-muted">Beberapa halaman pertama untuk preview</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-admin">Simpan Buku</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        // Inisialisasi DataTable
        $(document).ready(function() {
            $('#booksTable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
                }
            });
            
            // Toggle harga berdasarkan checkbox gratis
            $('input[name="is_free"]').change(function() {
                if ($(this).is(':checked')) {
                    $('input[name="price"]').val(0).prop('readonly', true);
                } else {
                    $('input[name="price"]').prop('readonly', false);
                }
            });
            
            // Perbaikan: Hapus event handler lama untuk delete
            // karena sekarang menggunakan onclick langsung di link
        });
    </script>
</body>
</html>