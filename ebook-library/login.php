<?php
// File: login.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect('index.php', 'Anda sudah login!', 'info');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } else {
        // Cari user di database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Set session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['full_name'] = $user['full_name'];
            
            // Update last login
            $updateStmt = $pdo->prepare("UPDATE users SET updated_at = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Redirect berdasarkan role
            if ($user['role'] === 'admin') {
                redirect('admin/', 'Login berhasil! Selamat datang admin.', 'success');
            } else {
                redirect('index.php', 'Login berhasil! Selamat datang ' . $user['username'], 'success');
            }
        } else {
            $error = "Email atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Book Library</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a237e, #000000);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .login-header {
            background: #1a237e;
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-card">
                    <div class="login-header text-center">
                        <h3><i class="bi bi-book"></i> E-Book Library</h3>
                        <p class="mb-0">Login ke Sistem</p>
                    </div>
                    <div class="p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" required 
                                       value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Login</button>
                        </form>
                        
                        <hr>
                        <div class="text-center">
                            <p class="mb-2">Login dengan akun admin:</p>
                            <p class="mb-1"><strong>Email:</strong> admin@ebooklibrary.com</p>
                            <p class="mb-3"><strong>Password:</strong> admin123</p>
                            <p>Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                            <p><a href="index.php">Kembali ke Home</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>