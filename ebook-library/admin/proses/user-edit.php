<?php
// File: admin/proses/user-edit.php
require_once '../check_admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../users.php', 'Akses tidak valid!', 'error');
}

$id = (int)$_POST['id'];
$username = sanitize($_POST['username']);
$email = sanitize($_POST['email']);
$full_name = sanitize($_POST['full_name']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$role = $_POST['role'];
$membership_status = $_POST['membership_status'];

// Validasi
$errors = [];

if (empty($username) || empty($email)) {
    $errors[] = "Username dan email harus diisi";
}

if (!validateEmail($email)) {
    $errors[] = "Email tidak valid";
}

// Cek username dan email sudah ada (kecuali untuk user ini)
$stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
$stmt->execute([$username, $email, $id]);
if ($stmt->rowCount() > 0) {
    $errors[] = "Username atau email sudah terdaftar";
}

// Jika password diisi, validasi
if (!empty($password)) {
    if (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok";
    }
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    redirect('../users.php', implode('<br>', $errors), 'error');
}

// Update database
if (!empty($password)) {
    $hashed_password = hashPassword($password);
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, 
                          role = ?, membership_status = ? WHERE id = ?");
    $success = $stmt->execute([$username, $email, $hashed_password, $full_name, $role, $membership_status, $id]);
} else {
    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, 
                          role = ?, membership_status = ? WHERE id = ?");
    $success = $stmt->execute([$username, $email, $full_name, $role, $membership_status, $id]);
}

if ($success) {
    redirect('../users.php', 'User berhasil diupdate!', 'success');
} else {
    redirect('../users.php', 'Gagal mengupdate user!', 'error');
}
?>