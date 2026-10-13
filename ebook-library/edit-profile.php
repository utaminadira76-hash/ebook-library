<?php
// File: edit-profile.php
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
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '', 'email');
    
    // Validasi
    if (empty($full_name)) {
        $errors[] = "Nama lengkap wajib diisi";
    }
    
    if (empty($email)) {
        $errors[] = "Email wajib diisi";
    } elseif (!validateEmail($email)) {
        $errors[] = "Format email tidak valid";
    }
    
    // Cek jika email sudah digunakan oleh user lain
    if ($email != $user['email']) {
        $check_email = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->execute([$email, $_SESSION['user_id']]);
        if ($check_email->fetch()) {
            $errors[] = "Email sudah digunakan oleh user lain";
        }
    }
    
    // Jika tidak ada error, update data
    if (empty($errors)) {
        try {
            $update_stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = NOW() WHERE id = ?");
            $update_stmt->execute([$full_name, $email, $_SESSION['user_id']]);
            
            // Update session data
            $_SESSION['email'] = $email;
            $_SESSION['full_name'] = $full_name;
            
            $success = "Profil berhasil diperbarui!";
            
            // Log aktivitas
            logActivity($_SESSION['user_id'], 'profile_update', 'Mengupdate informasi profil');
            
        } catch (PDOException $e) {
            $errors[] = "Gagal memperbarui profil: " . $e->getMessage();
        }
    }
}

$page_title = 'Edit Profil';
include 'includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-person-gear"></i> Edit Profil</h4>
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
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                            <div class="form-text">Username tidak dapat diubah</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst($user['role']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status Membership</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo ucfirst($user['membership_status']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Bergabung Sejak</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('d F Y', strtotime($user['created_at'])); ?>" readonly>
                        </div>
                        
                        <div class="d-flex justify-content-between mt-4">
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Profil
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mt-4">
                <div class="card-header bg-warning">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Informasi Penting</h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Pastikan email yang Anda masukkan valid dan aktif</li>
                        <li>Perubahan email akan mempengaruhi proses login Anda</li>
                        <li>Setelah mengubah email, Anda perlu menggunakan email baru untuk login</li>
                        <li>Untuk mengubah password, gunakan menu <strong>Ganti Password</strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>