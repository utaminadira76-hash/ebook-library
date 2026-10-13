<?php
// File: 404.php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Tidak Ditemukan - E-Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #000000);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }
        .error-container {
            max-width: 600px;
            padding: 40px;
            background: rgba(255,255,255,0.1);
            border-radius: 15px;
            backdrop-filter: blur(10px);
        }
        .error-code {
            font-size: 8rem;
            font-weight: bold;
            color: #ff6b6b;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">404</div>
        <h1 class="mb-4">Halaman Tidak Ditemukan</h1>
        <p class="lead mb-4">Maaf, halaman yang Anda cari tidak ditemukan atau telah dipindahkan.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="index.php" class="btn btn-primary">
                <i class="bi bi-house"></i> Kembali ke Home
            </a>
            <a href="javascript:history.back()" class="btn btn-outline-light">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</body>
</html>