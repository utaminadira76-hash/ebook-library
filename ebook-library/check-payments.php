<?php
// File: check-payments.php - Untuk debugging upload payments
require_once 'includes/config.php';

echo "<h2>=== DEBUG PAYMENT SYSTEM ===</h2>";

// 1. Cek Konstanta
echo "<h3>1. Konstanta PATH:</h3>";
echo "UPLOAD_BASE: " . UPLOAD_BASE . "<br>";
echo "UPLOAD_PATH: " . UPLOAD_PATH . "<br>";
echo "PAYMENT_PATH: " . PAYMENT_PATH . "<br>";
echo "Realpath PAYMENT_PATH: " . realpath(PAYMENT_PATH) . "<br>";

// 2. Cek Folder
echo "<h3>2. Status Folder:</h3>";
if (is_dir(PAYMENT_PATH)) {
    echo "✅ Folder payments ADA<br>";
    if (is_writable(PAYMENT_PATH)) {
        echo "✅ Folder payments WRITABLE<br>";
    } else {
        echo "❌ Folder payments TIDAK writable<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms(PAYMENT_PATH)), -4) . "<br>";
    }
} else {
    echo "❌ Folder payments TIDAK ADA<br>";
}

// 3. List Files
echo "<h3>3. File di Folder payments:</h3>";
$files = scandir(PAYMENT_PATH);
echo "<pre>";
print_r($files);
echo "</pre>";

// 4. Cek Database
echo "<h3>4. Data di Tabel transactions:</h3>";
try {
    $stmt = $pdo->query("SELECT id, transaction_code, payment_proof, status, created_at FROM transactions ORDER BY id DESC LIMIT 10");
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($transactions) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr style='background: #f2f2f2;'><th>ID</th><th>Kode</th><th>Payment Proof</th><th>Status</th><th>Tanggal</th><th>File Exists?</th></tr>";
        foreach ($transactions as $t) {
            $file_exists = ($t['payment_proof'] && file_exists(PAYMENT_PATH . $t['payment_proof'])) ? '✅' : '❌';
            echo "<tr>";
            echo "<td>{$t['id']}</td>";
            echo "<td>{$t['transaction_code']}</td>";
            echo "<td>{$t['payment_proof']}</td>";
            echo "<td>{$t['status']}</td>";
            echo "<td>{$t['created_at']}</td>";
            echo "<td>{$file_exists}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No transactions found in database";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

// 5. Test Upload Path
echo "<h3>5. Test Path untuk Admin:</h3>";
echo "Path dari admin ke payments: '../assets/uploads/payments/'<br>";
echo "Test file exists: ";
$test_file = 'payment_1765857673_2_428c15aa.png';
$test_path = '../assets/uploads/payments/' . $test_file;
if (file_exists($test_path)) {
    echo "✅ File '$test_file' ADA di '$test_path'<br>";
    echo "<img src='$test_path' width='200'><br>";
} else {
    echo "❌ File '$test_file' TIDAK ADA di '$test_path'<br>";
    echo "Absolute path: " . realpath(dirname(__FILE__) . '/../assets/uploads/payments/') . "<br>";
}
?>