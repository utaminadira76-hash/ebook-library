<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hapus semua session
session_destroy();

// Redirect ke home
redirect('index.php', 'Anda telah logout.', 'info');
?>