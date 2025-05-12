<?php
require_once '../includes/header.php';
requireOwner();

// Initialize all variables with default values
$pending_count = 0;
$active_count = 0;
$total_income = 0;
$items_count = 0;
$recent_rentals = [];
$notifications = [];

try {
    // Mendapatkan jumlah permintaan penyewaan yang menunggu konfirmasi
    $pending_result = fetchOne("SELECT COUNT(*) as count FROM rentals r 
                             JOIN items i ON r.item_id = i.item_id 
                             WHERE i.owner_id = ? AND r.status = 'pending'", 
                             [$_SESSION['user_id']]);
    $pending_count = $pending_result ? $pending_result['count'] : 0;

    // Mendapatkan jumlah penyewaan aktif
    $active_result = fetchOne("SELECT COUNT(*) as count FROM rentals r 
                             JOIN items i ON r.item_id = i.item_id 
                             WHERE i.owner_id = ? AND r.status IN ('confirmed', 'active')", 
                             [$_SESSION['user_id']]);
    $active_count = $active_result ? $active_result['count'] : 0;

    // Mendapatkan total pendapatan
    $income_result = fetchOne("SELECT COALESCE(SUM(r.total_price), 0) as total FROM rentals r 
                           JOIN items i ON r.item_id = i.item_id 
                           WHERE i.owner_id = ? AND r.status IN ('confirmed', 'active', 'completed')", 
                           [$_SESSION['user_id']]);
    $total_income = $income_result ? $income_result['total'] : 0;

    // Mendapatkan jumlah barang yang disewakan
    $items_result = fetchOne("SELECT COUNT(*) as count FROM items WHERE owner_id = ?", 
                           [$_SESSION['user_id']]);
    $items_count = $items_result ? $items_result['count'] : 0;

    // Mendapatkan penyewaan terbaru
    $recent_rentals = fetchAll("SELECT r.*, i.name as item_name, u.name as renter_name 
                               FROM rentals r 
                               JOIN items i ON r.item_id = i.item_id 
                               JOIN users u ON r.renter_id = u.user_id 
                               WHERE i.owner_id = ? 
                               ORDER BY r.created_at DESC LIMIT 5", 
                               [$_SESSION['user_id']]) ?: [];

    // Mendapatkan notifikasi terbaru
    $notifications = fetchAll("SELECT * FROM notifications 
                              WHERE user_id = ? 
                              ORDER BY created_at DESC LIMIT 5", 
                              [$_SESSION['user_id']]) ?: [];

} catch (Exception $e) {
    // Log error but don't show to user
    error_log("Dashboard error: " . $e->getMessage());
}
?>

<style>
    /* Improved Dashboard Styles */
    :root {
        --primary: #3498db;
        --primary-dark: #2980b9;
        --success: #2ecc71;
        --success-dark: #27ae60;
        --warning: #f39c12;
        --warning-dark: #d35400;
        --info: #1abc9c;
        --info-dark: #16a085;
        --danger: #e74c3c;
    }
    
    .dashboard-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.25rem;
    }
    
    .card-body {
        padding: 1.5rem;
    }
    
    .stat-card {
        border-left: 4px solid;
        color: white;
    }
    
    .stat-card.bg-primary {
        background-color: var(--primary) !important;
        border-left-color: var(--primary-dark);
    }
    
    .stat-card.bg-success {
        background-color: var(--success) !important;
        border-left-color: var(--success-dark);
    }
    
    .stat-card.bg-warning {
        background-color: var(--warning) !important;
        border-left-color: var(--warning-dark);
    }
    
    .stat-card.bg-info {
        background-color: var(--info) !important;
        border-left-color: var(--info-dark);
    }
    
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.85em;
    }
    
    .badge-warning {
        background-color: var(--warning);
    }
    
    .badge-info {
        background-color: var(--primary);
    }
    
    .badge-success {
        background-color: var(--success);
    }
    
    .badge-primary {
        background-color: var(--info);
    }
    
    .badge-danger {
        background-color: var(--danger);
    }
    
    .table th {
        border-top: none;
        font-weight: 600;
        color: #6c757d;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        padding: 1rem;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .item-card {
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .item-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .item-card .card-img-top {
        height: 150px;
        object-fit: cover;
    }
    
    .quick-guide ol {
        padding-left: 1.2rem;
    }
    
    .quick-guide li {
        margin-bottom: 0.5rem;
    }
    
    h1 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
    }
    
    h5 {
        font-weight: 600;
        color: #34495e;
    }
    
    .empty-state {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #dee2e6;
    }
    
    .stat-icon {
        font-size: 2rem;
        opacity: 0.8;
    }
    
    .stat-value {
        font-size: 1.75rem;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <h1>Dashboard Pemilik</h1>

    <div class="row">
        <!-- Stat Cards -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-primary text-white h-100 dashboard-card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Permintaan Baru</h6>
                            <h3 class="stat-value mb-0"><?= htmlspecialchars($pending_count) ?></h3>
                        </div>
                        <i class="fas fa-shopping-cart stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer bg-primary-dark d-flex align-items-center justify-content-between">
                    <a href="rental_requests.php" class="text-white small stretched-link">Lihat Detail</a>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-success text-white h-100 dashboard-card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Penyewaan Aktif</h6>
                            <h3 class="stat-value mb-0"><?= htmlspecialchars($active_count) ?></h3>
                        </div>
                        <i class="fas fa-check-circle stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer bg-success-dark d-flex align-items-center justify-content-between">
                    <a href="active_rentals.php" class="text-white small stretched-link">Lihat Detail</a>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-warning text-white h-100 dashboard-card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Total Pendapatan</h6>
                            <h3 class="stat-value mb-0"><?= formatPrice($total_income) ?></h3>
                        </div>
                        <i class="fas fa-money-bill-wave stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer bg-warning-dark d-flex align-items-center justify-content-between">
                    <span class="text-white small">Dari semua penyewaan</span>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card bg-info text-white h-100 dashboard-card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-2">Barang Disewakan</h6>
                            <h3 class="stat-value mb-0"><?= htmlspecialchars($items_count) ?></h3>
                        </div>
                        <i class="fas fa-box stat-icon"></i>
                    </div>
                </div>
                <div class="card-footer bg-info-dark d-flex align-items-center justify-content-between">
                    <a href="manage_items.php" class="text-white small stretched-link">Lihat Barang</a>
                    <i class="fas fa-angle-right"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Recent Rentals Card -->
            <div class="card mb-4 dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Penyewaan Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recent_rentals)): ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Barang</th>
                                    <th>Penyewa</th>
                                    <th>Tanggal</th>
                                    <th>Status</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_rentals as $rental): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rental['item_name']) ?></td>
                                    <td><?= htmlspecialchars($rental['renter_name']) ?></td>
                                    <td><?= formatDate($rental['start_date']) ?> s/d <?= formatDate($rental['end_date']) ?></td>
                                    <td>
                                        <?php
                                        switch ($rental['status']) {
                                            case 'pending':
                                                echo '<span class="badge badge-warning">Menunggu</span>';
                                                break;
                                            case 'confirmed':
                                                echo '<span class="badge badge-info">Dikonfirmasi</span>';
                                                break;
                                            case 'active':
                                                echo '<span class="badge badge-success">Aktif</span>';
                                                break;
                                            case 'completed':
                                                echo '<span class="badge badge-primary">Selesai</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="badge badge-danger">Dibatalkan</span>';
                                                break;
                                            case 'late':
                                                echo '<span class="badge badge-danger">Terlambat</span>';
                                                break;
                                        }
                                        ?>
                                    </td>
                                    <td class="font-weight-bold"><?= formatPrice($rental['total_price']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h5>Belum ada penyewaan</h5>
                        <p>Tidak ada riwayat penyewaan terbaru</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Items Card -->
            <div class="card dashboard-card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Barang yang Disewakan</h5>
                        <a href="add_item.php" class="btn btn-sm btn-primary">Tambah Barang</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($items_count)): ?>
                    <div class="row">
                        <?php 
                        $items = fetchAll("SELECT * FROM items WHERE owner_id = ? ORDER BY created_at DESC LIMIT 3", [$_SESSION['user_id']]) ?: [];
                        foreach ($items as $item): 
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 item-card">
                                <?php if (!empty($item['image'])): ?>
                                <img src="../assets/images/uploads/items/<?= htmlspecialchars($item['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="card-img-top bg-light p-4 text-center d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="card-text small text-muted"><?= formatPrice($item['price_per_day']) ?> / hari</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge <?= $item['is_available'] ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $item['is_available'] ? 'Tersedia' : 'Tidak Tersedia' ?>
                                        </span>
                                        <small class="text-muted">Stok: <?= $item['stock'] ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="text-center mt-3">
                        <a href="manage_items.php" class="btn btn-outline-primary">Lihat Semua Barang</a>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <h5>Belum ada barang</h5>
                        <p>Anda belum memiliki barang yang disewakan</p>
                        <a href="add_item.php" class="btn btn-primary mt-2">Tambahkan Barang</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Notifications Card -->
            <div class="card mb-4 dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Notifikasi Terbaru</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($notifications)): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                                <small class="text-muted"><?= date('d/m/Y', strtotime($notification['created_at'])) ?></small>
                            </div>
                            <p class="mb-1 small text-muted"><?= htmlspecialchars($notification['message']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <h5>Tidak ada notifikasi</h5>
                        <p>Belum ada notifikasi terbaru</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Guide Card -->
            <div class="card dashboard-card">
                <div class="card-header">
                    <h5 class="mb-0">Petunjuk Singkat</h5>
                </div>
                <div class="card-body quick-guide">
                    <h6 class="font-weight-bold mb-3">Cara Menyewakan Barang:</h6>
                    <ol class="pl-3">
                        <li class="mb-2">Tambahkan barang yang ingin disewakan di <a href="add_item.php">sini</a>.</li>
                        <li class="mb-2">Kelola barang yang disewakan di <a href="manage_items.php">halaman ini</a>.</li>
                        <li class="mb-2">Pantau permintaan penyewaan di <a href="rental_requests.php">halaman permintaan</a>.</li>
                        <li class="mb-2">Konfirmasi permintaan penyewaan dan atur pengambilan barang.</li>
                        <li>Pantau penyewaan aktif di <a href="active_rentals.php">halaman ini</a>.</li>
                    </ol>
                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle mr-2"></i> Sistem akan otomatis mengirimkan notifikasi ke penyewa jika mendekati tanggal pengembalian.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>