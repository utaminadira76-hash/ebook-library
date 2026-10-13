<?php
// File: setup-uploads.php
require_once 'includes/config.php';

echo "<h1>Setup Folder Uploads</h1>";

// Buat folder uploads
$folders = [
    COVER_PATH,
    BOOK_PATH,
    PREVIEW_PATH
];

foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        if (mkdir($folder, 0777, true)) {
            echo "<p style='color: green;'>✓ Folder dibuat: " . $folder . "</p>";
            
            // Buat file .htaccess untuk keamanan
            $htaccess = $folder . '.htaccess';
            file_put_contents($htaccess, "Options -Indexes\nDeny from all");
            
            // Buat file index.html kosong
            file_put_contents($folder . 'index.html', '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>Directory access is forbidden</h1></body></html>');
        } else {
            echo "<p style='color: red;'>✗ Gagal membuat folder: " . $folder . "</p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ Folder sudah ada: " . $folder . "</p>";
    }
}

// Buat default cover image
$default_cover = COVER_PATH . 'default.jpg';
if (!file_exists($default_cover)) {
    // Buat gambar sederhana
    $image = imagecreatetruecolor(300, 400);
    $bg_color = imagecolorallocate($image, 26, 35, 126); // Biru gelap
    $text_color = imagecolorallocate($image, 255, 255, 255);
    
    imagefill($image, 0, 0, $bg_color);
    imagestring($image, 5, 80, 180, "E-BOOK", $text_color);
    imagestring($image, 5, 70, 200, "COVER", $text_color);
    
    imagejpeg($image, $default_cover, 90);
    imagedestroy($image);
    
    echo "<p style='color: green;'>✓ File default.jpg berhasil dibuat</p>";
} else {
    echo "<p style='color: blue;'>✓ File default.jpg sudah ada</p>";
}

echo "<h2 style='color: green;'>SETUP UPLOADS SELESAI!</h2>";
echo "<p><a href='index.php' class='btn btn-primary'>Kembali ke Home</a></p>";
echo "<p><a href='admin/' class='btn btn-success'>Login ke Admin Panel</a></p>";
?>