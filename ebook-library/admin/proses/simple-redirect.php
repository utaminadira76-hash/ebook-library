<?php
// File: admin/proses/simple-redirect.php
// Helper untuk redirect sederhana
function simpleRedirect($url, $message = '', $type = 'success') {
    if (!empty($message)) {
        $url .= (strpos($url, '?') === false ? '?' : '&') . $type . '=' . urlencode($message);
    }
    header("Location: $url");
    exit();
}
?>