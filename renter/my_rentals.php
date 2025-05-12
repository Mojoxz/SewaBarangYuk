<?php
require_once '../includes/header.php';
requireRenter();

// Filter status
$status_filter = $_GET['status'] ?? 'all';

// Query dasar
$sql = "SELECT r.*, i.name as item_name, i.image as item_image, u.name as owner_name, 
        u.phone as owner_phone, DATEDIFF(r.end_date, CURDATE()) as days_remaining
        FROM rentals r 
        JOIN items i ON r.item_id = i.item_id 
        JOIN users u ON i.owner_id = u.user_id
        WHERE r.renter_id = ?";

$params = [$_SESSION['user_id']];

// Tambahkan filter status jika ada
if ($status_filter !== 'all') {
    $sql .= " AND r.status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY r.created_at DESC";

// Ambil data penyewaan
$rentals = fetchAll($sql, $params);

// Hitung jumlah untuk setiap status
$status_counts = [
    'all' => 0,
    'pending' => 0,
    'confirmed' => 0,
    'active' => 0,
    'completed' => 0,
    'cancelled' => 0,
    'late' => 0
];

foreach ($rentals as $rental) {
    $status_counts['all']++;
    $status_counts[$rental['status']]++;
}

// Status badge styling
$status_badges = [
    'pending' => ['text' => 'Menunggu Konfirmasi', 'class' => 'badge-warning'],
    'confirmed' => ['text' => 'Dikonfirmasi', 'class' => 'badge-info'],
    'active' => ['text' => 'Aktif', 'class' => 'badge-success'],
    'completed' => ['text' => 'Selesai', 'class' => 'badge-primary'],
    'cancelled' => ['text' => 'Dibatalkan', 'class' => 'badge-danger'],
    'late' => ['text' => 'Terlambat', 'class' => 'badge-danger']
];
?>

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="jumbotron bg-primary text-white rounded-lg shadow mb-4">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Penyewaan Saya</h1>
            <p class="lead">Kelola semua aktivitas penyewaan Anda di satu tempat</p>
            
            <!-- Status filters -->
            <div class="status-filters mt-4">
                <div class="row">
                    <div class="col-12">
                        <ul class="nav nav-pills nav-fill bg-white rounded-pill shadow-sm p-1">
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'all' ? 'active' : '' ?> rounded-pill px-3" href="?status=all">
                                    <i class="fas fa-list-ul mr-1"></i> Semua
                                    <?php if($status_counts['all'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['all'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'pending' ? 'active' : '' ?> rounded-pill px-3" href="?status=pending">
                                    <i class="fas fa-clock mr-1"></i> Menunggu
                                    <?php if($status_counts['pending'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['pending'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'confirmed' ? 'active' : '' ?> rounded-pill px-3" href="?status=confirmed">
                                    <i class="fas fa-check-circle mr-1"></i> Dikonfirmasi
                                    <?php if($status_counts['confirmed'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['confirmed'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'active' ? 'active' : '' ?> rounded-pill px-3" href="?status=active">
                                    <i class="fas fa-play-circle mr-1"></i> Aktif
                                    <?php if($status_counts['active'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['active'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'completed' ? 'active' : '' ?> rounded-pill px-3" href="?status=completed">
                                    <i class="fas fa-check-double mr-1"></i> Selesai
                                    <?php if($status_counts['completed'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['completed'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'cancelled' ? 'active' : '' ?> rounded-pill px-3" href="?status=cancelled">
                                    <i class="fas fa-times-circle mr-1"></i> Dibatalkan
                                    <?php if($status_counts['cancelled'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['cancelled'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= $status_filter == 'late' ? 'active' : '' ?> rounded-pill px-3" href="?status=late">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Terlambat
                                    <?php if($status_counts['late'] > 0): ?>
                                    <span class="badge badge-pill badge-light ml-1"><?= $status_counts['late'] ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 text-muted">
                        <i class="fas fa-clipboard-list mr-2"></i> Menampilkan <?= count($rentals) ?> penyewaan
                        <?php if ($status_filter != 'all'): ?>
                            <span class="badge badge-pill <?= $status_badges[$status_filter]['class'] ?> ml-2">
                                <?= $status_badges[$status_filter]['text'] ?>
                            </span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="col-md-6 text-right">
                    <a href="view_items.php" class="btn btn-primary btn-rental">
                        <i class="fas fa-plus mr-1"></i> Sewa Barang Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Rentals List -->
    <?php if (count($rentals) > 0): ?>
        <div class="row">
            <?php foreach ($rentals as $rental): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card h-100 hover-shadow item-card">
                        <div class="position-relative">
                            <?php if ($rental['item_image']): ?>
                                <img src="../assets/images/uploads/items/<?= $rental['item_image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($rental['item_name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-box fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="position-absolute top-0 left-0 m-2">
                                <span class="badge badge-pill <?= $status_badges[$rental['status']]['class'] ?> px-3 py-2 shadow-sm">
                                    <?= $status_badges[$rental['status']]['text'] ?>
                                </span>
                            </div>
                            <div class="position-absolute bottom-0 right-0 m-2">
                                <div class="price-tag">
                                    <span class="price-amount"><?= formatPrice($rental['total_price']) ?></span>
                                    <span class="price-period">total</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold text-primary"><?= htmlspecialchars($rental['item_name']) ?></h5>
                            
                            <div class="rental-info mt-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user-circle text-muted mr-2"></i>
                                    <div>
                                        <div class="font-weight-bold"><?= htmlspecialchars($rental['owner_name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($rental['owner_phone']) ?></small>
                                    </div>
                                </div>
                                
                                <div class="d-flex align-items-center mb-2">
                                    <i class="far fa-calendar-alt text-muted mr-2"></i>
                                    <div>
                                        <div class="rental-dates">
                                            <span class="font-weight-bold"><?= formatDate($rental['start_date']) ?></span>
                                            <span class="mx-2">hingga</span>
                                            <span class="font-weight-bold"><?= formatDate($rental['end_date']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3">
                                <?php if (in_array($rental['status'], ['confirmed', 'active'])): ?>
                                    <?php if ($rental['days_remaining'] <= 0): ?>
                                        <div class="alert alert-danger p-2 mb-0">
                                            <i class="fas fa-exclamation-triangle mr-1"></i> Jatuh tempo
                                        </div>
                                    <?php elseif ($rental['days_remaining'] <= 3): ?>
                                        <div class="alert alert-warning p-2 mb-0">
                                            <i class="fas fa-hourglass-half mr-1"></i> <?= $rental['days_remaining'] ?> hari lagi
                                        </div>
                                    <?php else: ?>
                                        <div class="alert alert-info p-2 mb-0">
                                            <i class="far fa-clock mr-1"></i> <?= $rental['days_remaining'] ?> hari lagi
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">
                                        <i class="far fa-calendar mr-1"></i> Dibuat pada <?= date('d/m/Y', strtotime($rental['created_at'])) ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-tag mr-1"></i> ID: <?= $rental['rental_id'] ?>
                                    </small>
                                </div>
                                <?php if ($rental['status'] == 'pending'): ?>
                                <a href="#" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-times mr-1"></i> Batalkan
                                </a>
                                <?php elseif ($rental['status'] == 'active'): ?>
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-check mr-1"></i> Selesai
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info shadow-sm">
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x mb-3 text-muted"></i>
                <?php if ($status_filter == 'all'): ?>
                    <h4>Anda belum memiliki penyewaan</h4>
                    <p class="mb-0">Mulai menyewa barang yang Anda butuhkan sekarang</p>
                    <a href="view_items.php" class="btn btn-primary btn-rental mt-3">
                        <i class="fas fa-search mr-1"></i> Lihat Barang yang Tersedia
                    </a>
                <?php else: ?>
                    <h4>Tidak ada penyewaan dengan status "<?= $status_badges[$status_filter]['text'] ?>"</h4>
                    <p class="mb-0">Coba lihat status lain atau lihat semua penyewaan Anda</p>
                    <a href="?status=all" class="btn btn-primary btn-rental mt-3">
                        <i class="fas fa-list mr-1"></i> Lihat Semua Penyewaan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Custom styles -->
<style>
    .jumbotron {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 3rem 2rem;
    }
    
    .status-filters .nav-pills .nav-link {
        color: #495057;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .status-filters .nav-pills .nav-link.active {
        background-color: #4e73df;
        color: white;
    }
    
    .status-filters .nav-pills .nav-link:hover:not(.active) {
        background-color: #f8f9fa;
    }
    
    .badge-pill {
        border-radius: 30px;
    }
    
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
        transition: all 0.3s ease;
    }
    
    .item-card {
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    
    /* Styling harga */
    .price-tag {
        background-color: #28a745;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 8px;
        padding: 8px 12px;
        font-weight: bold;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
        display: inline-block;
    }
    
    .price-amount {
        font-size: 1.1rem;
        color: white;
    }
    
    .price-period {
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.85);
    }
    
    .position-absolute.bottom-0 {
        bottom: 0;
    }
    
    .position-absolute.right-0 {
        right: 0;
    }
    
    .position-absolute.top-0 {
        top: 0;
    }
    
    .position-absolute.left-0 {
        left: 0;
    }
    
    .btn-rental {
        background: linear-gradient(45deg, #007bff, #0062cc);
        border: none;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        transition: all 0.3s;
    }
    
    .btn-rental:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
    }
    
    .rental-info {
        font-size: 0.9rem;
    }
    
    .rental-dates {
        font-size: 0.85rem;
    }
    
    .alert {
        border-radius: 8px;
        border: none;
    }
</style>

<?php require_once '../includes/footer.php'; ?>