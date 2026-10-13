
<?php
require_once 'check_admin.php';
$page_title = 'Manajemen Review & Rating';

// Ambil data review
$stmt = $pdo->query("SELECT r.*, u.username, b.title 
                     FROM reviews r 
                     JOIN users u ON r.user_id = u.id 
                     JOIN books b ON r.book_id = b.id 
                     ORDER BY r.created_at DESC");
$reviews = $stmt->fetchAll();

include 'head-assets.php';
include 'sidebar.php';
?>

<div class="main-content">
    <nav class="navbar navbar-expand-lg navbar-top">
        <div class="container-fluid">
            <h4 class="mb-0"><?php echo $page_title; ?></h4>
        </div>
    </nav>

    <?php displayFlashMessage(); ?>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Review</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Buku</th>
                            <th>Rating</th>
                            <th>Komentar</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="8" class="text-center">Belum ada review</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td><?php echo $r['id']; ?></td>
                                    <td><?php echo htmlspecialchars($r['username']); ?></td>
                                    <td><?php echo htmlspecialchars($r['title']); ?></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?php echo $i <= $r['rating'] ? '-fill' : ''; ?> 
                                                text-warning"></i>
                                        <?php endfor; ?>
                                        (<?php echo $r['rating']; ?>/5)
                                    </td>
                                    <td>
                                        <div class="comment-preview">
                                            <?php echo nl2br(htmlspecialchars(substr($r['comment'], 0, 100))); ?>
                                            <?php if (strlen($r['comment']) > 100): ?>...<?php endif; ?>
                                        </div>
                                        <?php if (strlen($r['comment']) > 100): ?>
                                            <button class="btn btn-sm btn-link view-comment" 
                                                    data-comment="<?php echo htmlspecialchars($r['comment']); ?>">
                                                Lihat lengkap
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $r['is_approved'] ? 'success' : 'warning'; ?>">
                                            <?php echo $r['is_approved'] ? 'Approved' : 'Pending'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo formatDate($r['created_at']); ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <?php if (!$r['is_approved']): ?>
                                                <button class="btn btn-success approve-review" 
                                                        data-id="<?php echo $r['id']; ?>">
                                                    Approve
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-danger delete-review" 
                                                    data-id="<?php echo $r['id']; ?>">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Komentar -->
<div class="modal fade" id="commentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Komentar Lengkap</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="fullComment">
                Loading...
            </div>
        </div>
    </div>
</div>

<script>
// Tampilkan komentar lengkap
document.querySelectorAll('.view-comment').forEach(btn => {
    btn.addEventListener('click', function() {
        const comment = this.dataset.comment;
        document.getElementById('fullComment').innerHTML = 
            `<p>${comment.replace(/\n/g, '<br>')}</p>`;
        new bootstrap.Modal(document.getElementById('commentModal')).show();
    });
});

// Approve review
document.querySelectorAll('.approve-review').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        if (confirm('Approve review ini?')) {
            fetch(`proses/review-approve.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        }
    });
});

// Hapus review
document.querySelectorAll('.delete-review').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        if (confirm('Hapus review ini?')) {
            fetch(`proses/review-delete.php?id=${id}`)
                .then(() => location.reload());
        }
    });
});
</script>

<?php include 'footer.php'; ?>