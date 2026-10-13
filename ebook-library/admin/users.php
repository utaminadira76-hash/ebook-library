<?php
// File: admin/users.php
require_once 'check_admin.php';

$page_title = 'Manajemen User';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <?php include 'head-assets.php'; ?>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-top">
            <div class="container-fluid">
                <h4 class="mb-0"><?php echo $page_title; ?></h4>
                <button class="btn btn-admin" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-plus-circle"></i> Tambah User
                </button>
            </div>
        </nav>
        
        <?php displayFlashMessage(); ?>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Daftar User</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Nama Lengkap</th>
                                <th>Role</th>
                                <th>Membership</th>
                                <th>Tanggal Daftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
                            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($users as $user) {
                                $role_badge = $user['role'] == 'admin' ? 'danger' : 'primary';
                                $membership_badge = $user['membership_status'] == 'premium' ? 'warning' : 'secondary';
                                
                                echo '<tr>';
                                echo '<td>' . $user['id'] . '</td>';
                                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                                echo '<td>' . htmlspecialchars($user['email']) . '</td>';
                                echo '<td>' . htmlspecialchars($user['full_name'] ?? '-') . '</td>';
                                echo '<td><span class="badge bg-' . $role_badge . '">' . $user['role'] . '</span></td>';
                                echo '<td><span class="badge bg-' . $membership_badge . '">' . $user['membership_status'] . '</span></td>';
                                echo '<td>' . formatDate($user['created_at']) . '</td>';
                                echo '<td>';
                                echo '<div class="btn-group btn-group-sm">';
                                echo '<button class="btn btn-warning edit-user" 
                                        data-id="' . $user['id'] . '"
                                        data-username="' . htmlspecialchars($user['username']) . '"
                                        data-email="' . htmlspecialchars($user['email']) . '"
                                        data-full_name="' . htmlspecialchars($user['full_name'] ?? '') . '"
                                        data-role="' . $user['role'] . '"
                                        data-membership="' . $user['membership_status'] . '">
                                        <i class="bi bi-pencil"></i></button>';
                                echo '<button class="btn btn-danger delete-user" 
                                        data-id="' . $user['id'] . '" 
                                        data-username="' . htmlspecialchars($user['username']) . '">
                                        <i class="bi bi-trash"></i></button>';
                                echo '</div>';
                                echo '</td>';
                                echo '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal Tambah User -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses/user-add.php" method="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username *</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Konfirmasi Password *</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role" class="form-select">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Membership Status</label>
                                <select name="membership_status" class="form-select">
                                    <option value="free">Free</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-admin">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Edit User -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="proses/user-edit.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="editUserId">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Username *</label>
                                <input type="text" name="username" id="editUsername" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Email *</label>
                                <input type="email" name="email" id="editEmail" class="form-control" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" id="editFullName" class="form-control">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Password (Kosongkan jika tidak diubah)</label>
                                <input type="password" name="password" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Konfirmasi Password</label>
                                <input type="password" name="confirm_password" class="form-control">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Role</label>
                                <select name="role" id="editRole" class="form-select">
                                    <option value="user">User</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Membership Status</label>
                                <select name="membership_status" id="editMembership" class="form-select">
                                    <option value="free">Free</option>
                                    <option value="premium">Premium</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-admin">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Edit user
        document.querySelectorAll('.edit-user').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const username = this.dataset.username;
                const email = this.dataset.email;
                const full_name = this.dataset.full_name;
                const role = this.dataset.role;
                const membership = this.dataset.membership;
                
                document.getElementById('editUserId').value = id;
                document.getElementById('editUsername').value = username;
                document.getElementById('editEmail').value = email;
                document.getElementById('editFullName').value = full_name;
                document.getElementById('editRole').value = role;
                document.getElementById('editMembership').value = membership;
                
                const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                modal.show();
            });
        });
        
        // Hapus user
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const username = this.dataset.username;
                
                if (confirm(`Apakah Anda yakin ingin menghapus user "${username}"?`)) {
                    window.location.href = `proses/user-delete.php?id=${id}`;
                }
            });
        });
    </script>
</body>
</html>