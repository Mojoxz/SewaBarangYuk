<?php
require_once '../includes/header.php';
requireOwner();

// Mendapatkan jumlah permintaan penyewaan yang menunggu konfirmasi
$pending_count = fetchOne("SELECT COUNT(*) as count FROM rentals r 
                         JOIN items i ON r.item_id = i.item_id 
                         WHERE i.owner_id = ? AND r.status = 'pending'", 
                         [$_SESSION['user_id']])['count'];

// Mendapatkan jumlah penyewaan aktif
$active_count = fetchOne("SELECT COUNT(*) as count FROM rentals r 
                         JOIN items i ON r.item_id = i.item_id 
                         WHERE i.owner_id = ? AND r.status IN ('confirmed', 'active')", 
                         [$_SESSION['user_id']])['count'];

// Mendapatkan total pendapatan
$total_income = fetchOne("SELECT COALESCE(SUM(r.total_price), 0) as total FROM rentals r 
                         JOIN items i ON r.item_id = i.item_id 
                         WHERE i.owner_id = ? AND r.status IN ('confirmed', 'active', 'completed')", 
                         [$_SESSION['user_id']])['total'];

// Mendapatkan jumlah barang yang disewakan
$items_count = fetchOne("SELECT COUNT(*) as count FROM items WHERE owner_id = ?", 
                       [$_SESSION['user_id']])['count'];

// Mendapatkan penyewaan terbaru
$recent_rentals = fetchAll("SELECT r.*, i.name as item_name, u.name as renter_name 
                           FROM rentals r 
                           JOIN items i ON r.item_id = i.item_id 
                           JOIN users u ON r.renter_id = u.user_id 
                           WHERE i.owner_id = ? 
                           ORDER BY r.created_at DESC LIMIT 5", 
                           [$_SESSION['user_id']]);

// Mendapatkan notifikasi terbaru
$notifications = fetchAll("SELECT * FROM notifications 
                          WHERE user_id = ? 
                          ORDER BY created_at DESC LIMIT 5", 
                          [$_SESSION['user_id']]);
?>

<h1>Dashboard Pemilik</h1>

<div class="row mt-4">
    <div class="col-md-3 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Permintaan Baru</h6>
                        <h3 class="mb-0"><?= $pending_count ?></h3>
                    </div>
                    <i class="fas fa-shopping-cart fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="rental_requests.php" class="text-white-50 small">Lihat Detail</a>
                <i class="fas fa-angle-right text-white-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Penyewaan Aktif</h6>
                        <h3 class="mb-0"><?= $active_count ?></h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="active_rentals.php" class="text-white-50 small">Lihat Detail</a>
                <i class="fas fa-angle-right text-white-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Total Pendapatan</h6>
                        <h3 class="mb-0"><?= formatPrice($total_income) ?></h3>
                    </div>
                    <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <span class="text-white-50 small">Dari semua penyewaan</span>
                <i class="fas fa-angle-right text-white-50"></i>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50">Barang Disewakan</h6>
                        <h3 class="mb-0"><?= $items_count ?></h3>
                    </div>
                    <i class="fas fa-box fa-2x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer d-flex align-items-center justify-content-between">
                <a href="manage_items.php" class="text-white-50 small">Lihat Barang</a>
                <i class="fas fa-angle-right text-white-50"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Penyewaan Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (count($recent_rentals) > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
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
                                <td><?= formatPrice($rental['total_price']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p>Belum ada penyewaan.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Barang yang Disewakan</h5>
                    <a href="add_item.php" class="btn btn-sm btn-primary">Tambah Barang</a>
                </div>
            </div>
            <div class="card-body">
                <?php
                $items = fetchAll("SELECT * FROM items WHERE owner_id = ? ORDER BY created_at DESC LIMIT 3", [$_SESSION['user_id']]);
                
                if (count($items) > 0):
                ?>
                <div class="row">
                    <?php foreach ($items as $item): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <?php if ($item['image']): ?>
                            <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                            <?php else: ?>
                            <div class="card-img-top bg-light p-4 text-center">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text small"><?= formatPrice($item['price_per_day']) ?> / hari</p>
                                <p class="card-text small">Stok: <?= $item['stock'] ?></p>
                                <span class="badge <?= $item['is_available'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $item['is_available'] ? 'Tersedia' : 'Tidak Tersedia' ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="manage_items.php" class="btn btn-outline-primary btn-sm">Lihat Semua Barang</a>
                <?php else: ?>
                <p>Anda belum memiliki barang yang disewakan. <a href="add_item.php">Tambahkan barang sekarang</a>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Notifikasi Terbaru</h5>
            </div>
            <div class="card-body">
                <?php if (count($notifications) > 0): ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h6>
                            <small><?= date('d/m/Y', strtotime($notification['created_at'])) ?></small>
                        </div>
                        <p class="mb-1 small"><?= htmlspecialchars($notification['message']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p>Belum ada notifikasi.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Petunjuk Singkat</h5>
            </div>
            <div class="card-body">
                <h6>Cara Menyewakan Barang:</h6>
                <ol>
                    <li>Tambahkan barang yang ingin disewakan di <a href="add_item.php">sini</a>.</li>
                    <li>Kelola barang yang disewakan di <a href="manage_items.php">halaman ini</a>.</li>
                    <li>Pantau permintaan penyewaan di <a href="rental_requests.php">halaman permintaan</a>.</li>
                    <li>Konfirmasi permintaan penyewaan dan atur pengambilan barang.</li>
                    <li>Pantau penyewaan aktif di <a href="active_rentals.php">halaman ini</a>.</li>
                </ol>
                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle"></i> Sistem akan otomatis mengirimkan notifikasi ke penyewa jika mendekati tanggal pengembalian.
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>