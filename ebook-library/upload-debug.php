<?php
// File: upload-debug.php
require_once 'includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    
    echo "<h2>Upload Test Result</h2>";
    echo "<pre>";
    echo "File Name: " . $file['name'] . "\n";
    echo "File Size: " . ($file['size'] / 1024) . " KB\n";
    echo "File Type: " . $file['type'] . "\n";
    echo "Temp Name: " . $file['tmp_name'] . "\n";
    echo "Error Code: " . $file['error'] . "\n\n";
    
    // Error code meanings
    $error_codes = [
        0 => 'UPLOAD_ERR_OK',
        1 => 'UPLOAD_ERR_INI_SIZE (file too big for php.ini)',
        2 => 'UPLOAD_ERR_FORM_SIZE (file too big for HTML form)',
        3 => 'UPLOAD_ERR_PARTIAL',
        4 => 'UPLOAD_ERR_NO_FILE',
        6 => 'UPLOAD_ERR_NO_TMP_DIR',
        7 => 'UPLOAD_ERR_CANT_WRITE',
        8 => 'UPLOAD_ERR_EXTENSION'
    ];
    
    if (isset($error_codes[$file['error']])) {
        echo "Error Meaning: " . $error_codes[$file['error']] . "\n";
    }
    
    // Cek MIME type sebenarnya
    if (function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        echo "Actual MIME Type: " . $mime . "\n";
    }
    
    echo "\n=== PHP Configuration ===\n";
    echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
    echo "post_max_size: " . ini_get('post_max_size') . "\n";
    echo "memory_limit: " . ini_get('memory_limit') . "\n";
    
    echo "\n=== File Validation ===\n";
    
    // Cek jika file terlalu besar untuk PHP
    $max_upload = return_bytes(ini_get('upload_max_filesize'));
    $max_post = return_bytes(ini_get('post_max_size'));
    $memory_limit = return_bytes(ini_get('memory_limit'));
    
    echo "PHP upload_max_filesize in bytes: " . $max_upload . "\n";
    echo "PHP post_max_size in bytes: " . $max_post . "\n";
    
    if ($file['size'] > $max_upload) {
        echo "❌ File is larger than upload_max_filesize\n";
    } elseif ($file['size'] > $max_post) {
        echo "❌ File is larger than post_max_size\n";
    } else {
        echo "✅ File size is within PHP limits\n";
    }
    
    // Coba move uploaded file
    $test_filename = 'debug_' . time() . '_' . $file['name'];
    $destination = COVER_PATH . $test_filename;
    
    echo "\n=== Move Upload Test ===\n";
    echo "Destination: " . $destination . "\n";
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        echo "✅ Successfully moved file\n";
        echo "File saved as: " . $test_filename . "\n";
        echo "Actual saved size: " . filesize($destination) . " bytes\n";
        
        // Tampilkan gambar
        echo "\n<img src='" . COVER_URL . $test_filename . "' style='max-width: 300px;'>";
        
        // Cleanup
        unlink($destination);
    } else {
        echo "❌ Failed to move uploaded file\n";
        $error = error_get_last();
        echo "Error: " . ($error['message'] ?? 'Unknown') . "\n";
    }
    
    echo "</pre>";
    echo "<br><a href='upload-debug.php'>Test Another File</a>";
    exit;
}

// Helper function
function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $val = (int)$val;
    
    switch($last) {
        case 'g': $val *= 1024;
        case 'm': $val *= 1024;
        case 'k': $val *= 1024;
    }
    return $val;
}
?>

<h2>Upload Debug Tool</h2>
<p>Upload gambar B (261KB) yang gagal untuk testing:</p>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit">Test Upload</button>
</form> 