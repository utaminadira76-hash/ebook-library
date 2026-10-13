<?php
session_start();

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'ebook_library';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
} catch(PDOException $e) {
    die("Database error");
}

$categories = $pdo->query("SELECT c.*, 
    (SELECT COUNT(*) FROM books WHERE category_id = c.id AND status='active') as book_count 
    FROM categories c ORDER BY name")->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kategori - E-Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { padding-top: 76px; background: #f8f9fa; }
        .category-card { transition: transform 0.3s; }
        .category-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="bi bi-book"></i> E-Book Library</a>
        </div>
    </nav>
    
    <div class="container mt-4">
        <h1 class="mb-4">Kategori Buku</h1>
        <div class="row">
            <?php foreach($categories as $cat): ?>
            <div class="col-md-4 mb-3">
                <div class="card category-card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($cat['name']); ?></h5>
                        <p class="card-text text-muted"><?php echo htmlspecialchars($cat['description'] ?? 'Tidak ada deskripsi'); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary"><?php echo $cat['book_count']; ?> buku</span>
                            <a href="catalog.php?category=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-primary">
                                Lihat Buku
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="mt-4">
            <a href="catalog.php" class="btn btn-primary">← Kembali ke Katalog</a>
        </div>
    </div>
</body>
</html>