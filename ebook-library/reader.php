<?php
// File: reader.php (PDF viewer)
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Cek login
if (!isLoggedIn()) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$book_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($book_id <= 0) {
    header('Location: catalog.php');
    exit;
}

// Cek akses buku
$book = getBookFileInfo($book_id);
if (!$book || !$book['file_exists']) {
    header('Location: book-detail.php?id=' . $book_id . '&error=file_not_found');
    exit;
}

// Cek membership
$isPremium = isPremium();
$book_is_free = ($book['is_free'] == 1);

if (!$book_is_free && !$isPremium) {
    header('Location: book-detail.php?id=' . $book_id . '&error=premium_required');
    exit;
}

$page_title = 'Baca: ' . $book['title'];
include 'includes/header.php';
?>

<style>
.reader-container {
    height: calc(100vh - 150px);
    border: 1px solid #dee2e6;
    border-radius: 8px;
    overflow: hidden;
}

.reader-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 10px 20px;
}

.reader-toolbar {
    background: #fff;
    border-bottom: 1px solid #dee2e6;
    padding: 10px;
}

.reader-frame {
    width: 100%;
    height: 100%;
    border: none;
}

.page-nav {
    font-size: 0.9rem;
}

.page-nav input {
    width: 80px;
    display: inline-block;
}

.tool-btn {
    margin: 0 2px;
    padding: 5px 10px;
}

@media (max-width: 768px) {
    .reader-container {
        height: calc(100vh - 200px);
    }
}
</style>

<div class="container-fluid px-0">
    <!-- Header -->
    <div class="reader-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    <i class="fas fa-book-open text-primary me-2"></i>
                    <?php echo htmlspecialchars($book['title']); ?>
                </h5>
                <small class="text-muted">Penulis: <?php echo htmlspecialchars($book['author']); ?></small>
            </div>
            <div>
                <a href="book-detail.php?id=<?php echo $book_id; ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
    
    <!-- Toolbar -->
    <div class="reader-toolbar">
        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="btn-group btn-group-sm">
                    <a href="download.php?id=<?php echo $book_id; ?>" class="btn btn-success tool-btn">
                        <i class="fas fa-download me-1"></i> Download
                    </a>
                    <button class="btn btn-outline-primary tool-btn" id="zoomIn">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button class="btn btn-outline-primary tool-btn" id="zoomOut">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button class="btn btn-outline-primary tool-btn" id="fitWidth">
                        <i class="fas fa-arrows-alt-h"></i>
                    </button>
                    <button class="btn btn-outline-primary tool-btn" id="fitPage">
                        <i class="fas fa-expand"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-4 text-center">
                <div class="page-nav">
                    <button class="btn btn-sm btn-outline-secondary" id="prevPage">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <span class="mx-2">
                        Halaman: <span id="currentPage">1</span> / <span id="totalPages">?</span>
                    </span>
                    <button class="btn btn-sm btn-outline-secondary" id="nextPage">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
            
            <div class="col-md-4 text-end">
                <div class="btn-group btn-group-sm">
                    <a href="assets/uploads/books/<?php echo htmlspecialchars($book['book_file']); ?>" 
                       class="btn btn-outline-info tool-btn" target="_blank">
                        <i class="fas fa-external-link-alt me-1"></i> Buka di Tab Baru
                    </a>
                    <button class="btn btn-outline-secondary tool-btn" id="fullscreen">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PDF Viewer -->
    <div class="reader-container">
        <iframe id="pdfViewer" class="reader-frame" 
                src="assets/uploads/books/<?php echo htmlspecialchars($book['book_file']); ?>#toolbar=0&navpanes=0">
            Browser Anda tidak mendukung tampilan PDF. 
            <a href="assets/uploads/books/<?php echo htmlspecialchars($book['book_file']); ?>">Download file</a>.
        </iframe>
    </div>
    
    <!-- Info -->
    <div class="container mt-3">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6><i class="fas fa-info-circle text-primary me-2"></i>Info Buku</h6>
                        <ul class="list-unstyled mb-0">
                            <li><small><strong>Judul:</strong> <?php echo htmlspecialchars($book['title']); ?></small></li>
                            <li><small><strong>Penulis:</strong> <?php echo htmlspecialchars($book['author']); ?></small></li>
                            <li><small><strong>Ukuran:</strong> <?php echo $book['file_size_formatted']; ?></small></li>
                            <li><small><strong>Total Halaman:</strong> <?php echo $book['total_pages'] ?: 'Tidak diketahui'; ?></small></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h6><i class="fas fa-user-check text-primary me-2"></i>Status Anda</h6>
                        <ul class="list-unstyled mb-0">
                            <li><small><strong>Nama:</strong> <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></small></li>
                            <li><small><strong>Membership:</strong> 
                                <span class="badge bg-<?php echo $isPremium ? 'warning' : 'secondary'; ?>">
                                    <?php echo $isPremium ? 'Premium' : 'Free'; ?>
                                </span>
                            </small></li>
                            <?php if ($isPremium && !empty($_SESSION['membership_expiry'])): ?>
                                <li><small><strong>Berlaku hingga:</strong> <?php echo date('d/m/Y', strtotime($_SESSION['membership_expiry'])); ?></small></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const viewer = document.getElementById('pdfViewer');
    const currentPageSpan = document.getElementById('currentPage');
    const totalPagesSpan = document.getElementById('totalPages');
    let currentPage = 1;
    let totalPages = 1;
    let zoomLevel = 1.0;
    
    // PDF.js untuk mendapatkan total halaman (jika browser support)
    try {
        const pdfUrl = viewer.src.split('#')[0];
        pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
            totalPages = pdf.numPages;
            totalPagesSpan.textContent = totalPages;
        }).catch(err => {
            console.log('PDF.js error:', err);
            totalPagesSpan.textContent = '?';
        });
    } catch (e) {
        totalPagesSpan.textContent = '?';
    }
    
    // Navigation buttons
    document.getElementById('prevPage').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            currentPageSpan.textContent = currentPage;
            viewer.contentWindow.PDFViewerApplication.page = currentPage;
        }
    });
    
    document.getElementById('nextPage').addEventListener('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            currentPageSpan.textContent = currentPage;
            viewer.contentWindow.PDFViewerApplication.page = currentPage;
        }
    });
    
    // Zoom controls
    document.getElementById('zoomIn').addEventListener('click', function() {
        zoomLevel = Math.min(zoomLevel + 0.1, 3.0);
        viewer.contentWindow.PDFViewerApplication.zoomIn();
    });
    
    document.getElementById('zoomOut').addEventListener('click', function() {
        zoomLevel = Math.max(zoomLevel - 0.1, 0.5);
        viewer.contentWindow.PDFViewerApplication.zoomOut();
    });
    
    document.getElementById('fitWidth').addEventListener('click', function() {
        viewer.contentWindow.PDFViewerApplication.fitToWidth();
    });
    
    document.getElementById('fitPage').addEventListener('click', function() {
        viewer.contentWindow.PDFViewerApplication.fitToPage();
    });
    
    // Fullscreen
    document.getElementById('fullscreen').addEventListener('click', function() {
        const container = document.querySelector('.reader-container');
        
        if (!document.fullscreenElement) {
            container.requestFullscreen().catch(err => {
                console.log('Fullscreen error:', err);
            });
        } else {
            document.exitFullscreen();
        }
    });
    
    // Listen for page changes in PDF viewer
    viewer.addEventListener('load', function() {
        try {
            // Try to get PDF viewer API
            const pdfViewer = viewer.contentWindow.PDFViewerApplication;
            if (pdfViewer) {
                pdfViewer.eventBus.on('pagechanging', function(evt) {
                    currentPage = evt.pageNumber;
                    currentPageSpan.textContent = currentPage;
                });
            }
        } catch (e) {
            console.log('PDF viewer API not accessible');
        }
    });
});
</script>

<!-- PDF.js library (optional) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.min.js"></script>

<?php include 'includes/footer.php'; ?>