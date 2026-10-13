<?php
// File: change-password.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    redirect('login.php', 'Silakan login terlebih dahulu!', 'warning');
}

// Ambil data user saat ini
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    redirect('login.php', 'User tidak ditemukan!', 'error');
}

$errors = [];
$success = '';

// Proses form jika ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi
    if (empty($current_password)) {
        $errors[] = "Password saat ini wajib diisi";
    }
    
    if (empty($new_password)) {
        $errors[] = "Password baru wajib diisi";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "Password baru minimal 8 karakter";
    }
    
    if (empty($confirm_password)) {
        $errors[] = "Konfirmasi password wajib diisi";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    // Verifikasi password saat ini
    if (!empty($current_password) && !verifyPassword($current_password, $user['password'])) {
        $errors[] = "Password saat ini salah";
    }
    
    // Cek jika password baru sama dengan password lama
    if (!empty($new_password) && verifyPassword($new_password, $user['password'])) {
        $errors[] = "Password baru harus berbeda dengan password saat ini";
    }
    
    // Jika tidak ada error, update password
    if (empty($errors)) {
        try {
            $hashed_password = hashPassword($new_password);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->execute([$hashed_password, $_SESSION['user_id']]);
            
            $success = "Password berhasil diubah!";
            
            // Log aktivitas
            logActivity($_SESSION['user_id'], 'password_change', 'Mengubah password');
            
            // Optional: Kirim email notifikasi
            // sendEmail($user['email'], 'Password Berhasil Diubah', 'Password akun Anda telah berhasil diubah.');
            
        } catch (PDOException $e) {
            $errors[] = "Gagal mengubah password: " . $e->getMessage();
        }
    }
}

$page_title = 'Ganti Password';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-key-fill"></i> Ganti Password</h4>
                </div>
                <div class="card-body">
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <h5><i class="bi bi-exclamation-triangle-fill"></i> Terjadi kesalahan:</h5>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('current_password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimal 8 karakter. Gunakan kombinasi huruf, angka, dan simbol</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                       minlength="8" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Profil
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-key"></i> Ganti Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-shield-check"></i> Tips Keamanan Password</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Gunakan minimal 8 karakter</li>
                        <li>Kombinasikan huruf besar, huruf kecil, angka, dan simbol</li>
                        <li>Jangan gunakan password yang sama dengan akun lain</li>
                        <li>Jangan gunakan informasi pribadi seperti nama atau tanggal lahir</li>
                        <li>Ganti password secara berkala setiap 3-6 bulan</li>
                        <li>Jangan bagikan password Anda kepada siapapun</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fungsi untuk toggle show/hide password
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php include 'includes/footer.php'; ?>