<?php
// File: admin/sidebar.php
?>
<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-speedometer2"></i>
        <span>Admin Panel</span>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a></li>
        <li><a href="books.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'books.php' ? 'active' : ''; ?>"><i class="bi bi-book"></i> <span>Manajemen Buku</span></a></li>
        <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>"><i class="bi bi-tags"></i> <span>Manajemen Kategori</span></a></li>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>"><i class="bi bi-people"></i> <span>Manajemen User</span></a></li>
        <li><a href="transactions.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>"><i class="bi bi-credit-card"></i> <span>Transaksi</span></a></li>
        <li><a href="reviews.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reviews.php' ? 'active' : ''; ?>"><i class="bi bi-star"></i> <span>Review & Rating</span></a></li>
        <li><hr></li>
        <li><a href="../index.php"><i class="bi bi-house"></i> <span>Kembali ke Site</span></a></li>
        <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
    </ul>
</div>