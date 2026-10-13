<?php
// File: admin/proses/user-add.php
require_once '../check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../users.php', 'Akses tidak valid!', 'error');
}

$username = sanitize($_POST['username']);
$email = sanitize($_POST['email']);
$full_name = sanitize($_POST['full_name']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];
$membership_status = $_POST['membership_status'];

// Validasi
$errors = [];

if (empty($username)) {
    $errors[] = "Username harus diisi";
}

if (empty($email) || !validateEmail($email)) {
    $errors[] = "Email tidak valid";
}

if (strlen($password) < 6) {
    $errors[] = "Password minimal 6 karakter";
}

if ($password !== $confirm_password) {
    $errors[] = "Password tidak cocok";
}

// Cek username dan email sudah ada
$stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
$stmt->execute([$username, $email]);
if ($stmt->rowCount() > 0) {
    $errors[] = "Username atau email sudah terdaftar";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    redirect('../users.php', implode('<br>', $errors), 'error');
}

// Hash password
$hashed_password = hashPassword($password);

// Simpan ke database
$stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role, membership_status, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())");
if ($stmt->execute([$username, $email, $hashed_password, $full_name, $role, $membership_status])) {
    redirect('../users.php', 'User berhasil ditambahkan!', 'success');
} else {
    redirect('../users.php', 'Gagal menambahkan user!', 'error');
}
?>