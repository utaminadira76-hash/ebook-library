<?php
// assign-book-file.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

echo "<h2>Assign File ke Buku</h2>";

if ($book_id <= 0) {
    echo "ID buku tidak valid!";
    exit;
}

// Dapatkan info buku
$stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch();

if (!$book) {
    echo "Buku tidak ditemukan!";
    exit;
}

echo "<p>Buku: <strong>" . htmlspecialchars($book['title']) . "</strong></p>";

// Daftar file PDF yang tersedia
$pdf_files = glob(BOOK_PATH . "*.pdf");
if (count($pdf_files) === 0) {
    echo "<p>Tidak ada file PDF di folder books!</p>";
    echo "<p>Folder: " . BOOK_PATH . "</p>";
    exit;
}

echo "<h3>Pilih File PDF:</h3>";
echo "<form method='POST' action=''>";
echo "<input type='hidden' name='book_id' value='$book_id'>";

foreach ($pdf_files as $file) {
    $filename = basename($file);
    $filesize = formatFileSize(filesize($file));
    $selected = ($book['book_file'] == $filename) ? 'checked' : '';
    
    echo "<div>";
    echo "<input type='radio' name='pdf_file' value='$filename' $required $selected>";
    echo " $filename ($filesize)";
    echo "</div>";
}

echo "<br><button type='submit' name='assign'>Assign File ke Buku</button>";
echo "</form>";

// Proses assign
if (isset($_POST['assign'])) {
    $selected_file = $_POST['pdf_file'] ?? '';
    $book_id = $_POST['book_id'];
    
    if (!empty($selected_file)) {
        $update = $pdo->prepare("UPDATE books SET book_file = ? WHERE id = ?");
        $update->execute([$selected_file, $book_id]);
        
        echo "<p style='color:green;'>✓ File berhasil diassign ke buku!</p>";
        echo "<p><a href='download.php?id=$book_id'>Coba Download Sekarang</a></p>";
    }
}

function formatFileSize($bytes) {
    if ($bytes == 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
}
?>