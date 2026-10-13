<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$page_title = 'Register';
include 'includes/header.php';

if (isLoggedIn()) {
    redirect('index.php', 'Anda sudah login!', 'info');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    
    $errors = [];
    
    // Validasi
    if (empty($username)) {
        $errors[] = "Username harus diisi!";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi!";
    } elseif (!validateEmail($email)) {
        $errors[] = "Format email tidak valid!";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Password tidak cocok!";
    }
    
    // Cek username dan email sudah terdaftar
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Username atau email sudah terdaftar!";
        }
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        $hashed_password = hashPassword($password);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, created_at) VALUES (?, ?, ?, ?, NOW())");
        
        if ($stmt->execute([$username, $email, $hashed_password, $full_name])) {
            redirect('login.php', 'Registrasi berhasil! Silakan login.', 'success');
        } else {
            $errors[] = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-person-plus"></i> Daftar Akun Baru</h4>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username *</label>
                            <input type="text" class="form-control" id="username" name="username" required
                                   value="<?php echo isset($_POST['username']) ? $_POST['username'] : ''; ?>">
                            <div class="form-text">Minimal 3 karakter, hanya huruf, angka, dan underscore</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="full_name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">Email *</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password *</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="form-text">Minimal 6 karakter</div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            Saya menyetujui <a href="terms.php" target="_blank">Syarat dan Ketentuan</a>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-plus"></i> Daftar Sekarang
                    </button>
                </form>
                
                <hr>
                
                <div class="text-center">
                    <p class="mb-0">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>