<?php
// File: admin/index.php
require_once 'check_admin.php';

$page_title = 'Dashboard Admin';
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
    
    <!-- Custom Admin CSS -->
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #000000;
            --accent-color: #3949ab;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: var(--secondary-color);
            color: white;
            padding: 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-brand {
            padding: 20px;
            background: var(--primary-color);
            text-align: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
            margin: 0;
        }
        
        .sidebar-menu li {
            margin: 5px 0;
        }
        
        .sidebar-menu a {
            color: #ddd;
            text-decoration: none;
            padding: 12px 20px;
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: var(--primary-color);
            color: white;
            border-left: 4px solid var(--accent-color);
        }
        
        .sidebar-menu i {
            width: 25px;
            text-align: center;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            min-height: 100vh;
        }
        
        .navbar-top {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
            margin-bottom: 10px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .stat-title {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .recent-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .table th {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-admin {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-admin:hover {
            background: var(--accent-color);
            color: white;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-brand span {
                display: none;
            }
            
            .sidebar-menu a span {
                display: none;
            }
            
            .sidebar-menu i {
                width: 100%;
                text-align: center;
                margin-right: 0;
            }
            
            .main-content {
                margin-left: 70px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-speedometer2"></i>
            <span>Admin Panel</span>
        </div>
        
        <ul class="sidebar-menu">
            <li><a href="index.php" class="active"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a></li>
            <li><a href="books.php"><i class="bi bi-book"></i> <span>Manajemen Buku</span></a></li>
            <li><a href="categories.php"><i class="bi bi-tags"></i> <span>Manajemen Kategori</span></a></li>
            <li><a href="users.php"><i class="bi bi-people"></i> <span>Manajemen User</span></a></li>
            <li><a href="transactions.php"><i class="bi bi-credit-card"></i> <span>Transaksi</span></a></li>
            <li><a href="reviews.php"><i class="bi bi-star"></i> <span>Review & Rating</span></a></li>
            <li><hr></li>
            <li><a href="../index.php"><i class="bi bi-house"></i> <span>Kembali ke Site</span></a></li>
            <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-top">
            <div class="container-fluid">
                <h4 class="mb-0">Dashboard Admin</h4>
                <div class="d-flex align-items-center">
                    <span class="me-3"><?php echo $_SESSION['full_name'] ?: $_SESSION['username']; ?></span>
                    <div class="dropdown">
                        <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        
        <!-- Flash Message -->
        <?php displayFlashMessage(); ?>
        
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-primary">
                        <i class="bi bi-book"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM books");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Total Buku</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-success">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Total User</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-warning">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM transactions WHERE status = 'success'");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Transaksi Sukses</div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="stat-card">
                    <div class="stat-icon text-info">
                        <i class="bi bi-star"></i>
                    </div>
                    <div class="stat-number">
                        <?php
                        $stmt = $pdo->query("SELECT COUNT(*) as total FROM reviews");
                        echo $stmt->fetch()['total'];
                        ?>
                    </div>
                    <div class="stat-title">Total Review</div>
                </div>
            </div>
        </div>
        
        <!-- Recent Books -->
        <div class="row">
            <div class="col-lg-8">
                <div class="recent-table">
                    <h5 class="mb-3">Buku Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Judul</th>
                                    <th>Penulis</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT b.*, c.name as category_name 
                                                     FROM books b 
                                                     LEFT JOIN categories c ON b.category_id = c.id 
                                                     ORDER BY b.created_at DESC LIMIT 5");
                                $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($books)) {
                                    echo '<tr><td colspan="6" class="text-center">Belum ada data buku</td></tr>';
                                } else {
                                    foreach ($books as $book) {
                                        $status_class = $book['status'] == 'active' ? 'badge bg-success' : 'badge bg-danger';
                                        $price = $book['is_free'] ? 'Gratis' : formatCurrency($book['price']);
                                        echo '<tr>';
                                        echo '<td>' . $book['id'] . '</td>';
                                        echo '<td>' . htmlspecialchars($book['title']) . '</td>';
                                        echo '<td>' . htmlspecialchars($book['author']) . '</td>';
                                        echo '<td>' . htmlspecialchars($book['category_name'] ?? '-') . '</td>';
                                        echo '<td>' . $price . '</td>';
                                        echo '<td><span class="' . $status_class . '">' . $book['status'] . '</span></td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="books.php" class="btn btn-admin">Lihat Semua Buku</a>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="recent-table">
                    <h5 class="mb-3">User Terbaru</h5>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT username, email, role FROM users ORDER BY created_at DESC LIMIT 5");
                                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                if (empty($users)) {
                                    echo '<tr><td colspan="3" class="text-center">Belum ada data user</td></tr>';
                                } else {
                                    foreach ($users as $user) {
                                        $role_class = $user['role'] == 'admin' ? 'badge bg-danger' : 'badge bg-primary';
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                                        echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                                        echo '<td><span class="' . $role_class . '">' . $user['role'] . '</span></td>';
                                        echo '</tr>';
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end">
                        <a href="users.php" class="btn btn-admin">Lihat Semua User</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Aktifkan sidebar menu berdasarkan halaman aktif
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = window.location.pathname.split('/').pop();
            const menuItems = document.querySelectorAll('.sidebar-menu a');
            
            menuItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href === currentPage) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
            
            // Dashboard selalu aktif jika di index.php
            if (currentPage === 'index.php' || currentPage === '') {
                document.querySelector('.sidebar-menu a[href="index.php"]').classList.add('active');
            }
        });
    </script>
</body>
</html>