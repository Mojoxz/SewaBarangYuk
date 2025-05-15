<?php
require_once '../includes/header.php';
requireRenter();

// Mendapatkan penyewaan aktif
$active_rentals = fetchAll("SELECT r.*, i.name as item_name, i.image as item_image, u.name as owner_name, 
                           DATEDIFF(r.end_date, CURDATE()) as days_remaining
                           FROM rentals r 
                           JOIN items i ON r.item_id = i.item_id 
                           JOIN users u ON i.owner_id = u.user_id
                           WHERE r.renter_id = ? AND r.status IN ('confirmed', 'active')
                           ORDER BY r.end_date ASC", [$_SESSION['user_id']]);

// Mendapatkan notifikasi terbaru
$notifications = fetchAll("SELECT * FROM notifications 
                          WHERE user_id = ? 
                          ORDER BY created_at DESC LIMIT 5", [$_SESSION['user_id']]);
?>

<!-- CSS tambahan untuk mempercantik tampilan -->
<style>
    .dashboard-title {
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        border-left: 5px solid #3498db;
        padding-left: 15px;
    }
    
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        margin-bottom: 1.5rem;
        border: none;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .card-header {
        background: linear-gradient(to right, #3498db, #2980b9);
        color: white;
        font-weight: 600;
        border-radius: 10px 10px 0 0 !important;
        padding: 0.8rem 1.25rem;
    }
    
    .badge-info {
        background-color: #3498db;
    }
    
    .badge-success {
        background-color: #2ecc71;
    }
    
    .badge-danger {
        background-color: #e74c3c;
    }
    
    .badge-warning {
        background-color: #f39c12;
        color: white;
    }
    
    .table th {
        border-top: none;
        border-bottom: 2px solid #e9ecef;
        font-weight: 600;
        color: #34495e;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .btn-primary {
        background-color: #3498db;
        border-color: #3498db;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
        border-color: #2980b9;
    }
    
    .btn-outline-primary {
        color: #3498db;
        border-color: #3498db;
    }
    
    .btn-outline-primary:hover {
        background-color: #3498db;
        color: white;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
        border-top: none;
        border-bottom: 1px solid #e9ecef;
        padding: 1rem;
        transition: background-color 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: rgba(52, 152, 219, 0.05);
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
    
    .list-group-item h5 {
        color: #2c3e50;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .list-group-item small {
        color: #7f8c8d;
        background-color: #f8f9fa;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 0.75rem;
    }
    
    .instruction-card li {
        margin-bottom: 10px;
        color: #34495e;
    }
    
    .item-card {
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .item-card .card-img-top {
        height: 180px;
        object-fit: cover;
        transition: transform 0.5s ease;
        flex-shrink: 0;
    }
    
    .item-card:hover .card-img-top {
        transform: scale(1.05);
    }
    
    .item-card .card-body {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .item-card .card-text {
        flex-grow: 1;
        margin-bottom: 10px;
    }
    
    .item-price {
        font-weight: 700;
        color: #2980b9;
    }
    
    .empty-state {
        padding: 3rem 2rem;
        text-align: center;
        color: #7f8c8d;
        background-color: #f9f9f9;
        border-radius: 8px;
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #bdc3c7;
        background: #e6e6e6;
        width: 80px;
        height: 80px;
        line-height: 80px;
        border-radius: 50%;
        display: inline-block;
    }
    
    .dashboard-stats {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .stats-item {
        text-align: center;
        padding: 15px 10px;
        border-right: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .stats-item:last-child {
        border-right: none;
    }
    
    .stats-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #3498db;
        margin-bottom: 8px;
        display: block;
    }
    
    .stats-label {
        color: #7f8c8d;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>

<div class="container py-4">
    <h1 class="dashboard-title">Dashboard Penyewa</h1>
    
    <!-- Dashboard Stats -->
    <div class="row dashboard-stats">
        <div class="col-md-4 stats-item">
            <div class="stats-number"><?= count($active_rentals) ?></div>
            <div class="stats-label">Penyewaan Aktif</div>
        </div>
        <div class="col-md-4 stats-item">
            <div class="stats-number"><?= count($notifications) ?></div>
            <div class="stats-label">Notifikasi Baru</div>
        </div>
        <div class="col-md-4 stats-item">
            <div class="stats-number">
                <?php
                $earliest_due = null;
                foreach ($active_rentals as $rental) {
                    if ($rental['days_remaining'] > 0 && ($earliest_due === null || $rental['days_remaining'] < $earliest_due)) {
                        $earliest_due = $rental['days_remaining'];
                    }
                }
                echo $earliest_due !== null ? $earliest_due : "-";
                ?>
            </div>
            <div class="stats-label">Hari Hingga Jatuh Tempo</div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-clipboard-list mr-2"></i> Penyewaan Aktif</span>
                    <a href="my_rentals.php" class="btn btn-light btn-sm"><i class="fas fa-list mr-1"></i> Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php if (count($active_rentals) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Barang</th>
                                        <th>Pemilik</th>
                                        <th>Tanggal Mulai</th>
                                        <th>Tanggal Selesai</th>
                                        <th>Status</th>
                                        <th>Sisa Hari</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_rentals as $rental): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($rental['item_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($rental['owner_name']) ?></td>
                                        <td><?= formatDate($rental['start_date']) ?></td>
                                        <td><?= formatDate($rental['end_date']) ?></td>
                                        <td>
                                            <?php if ($rental['status'] == 'confirmed'): ?>
                                                <span class="badge badge-info"><i class="fas fa-check-circle mr-1"></i> Dikonfirmasi</span>
                                            <?php elseif ($rental['status'] == 'active'): ?>
                                                <span class="badge badge-success"><i class="fas fa-play-circle mr-1"></i> Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($rental['days_remaining'] <= 0): ?>
                                                <span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i> Jatuh tempo</span>
                                            <?php elseif ($rental['days_remaining'] <= 3): ?>
                                                <span class="badge badge-warning"><i class="fas fa-clock mr-1"></i> <?= $rental['days_remaining'] ?> hari</span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-calendar-alt mr-1"></i> <?= $rental['days_remaining'] ?> hari</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-box-open"></i>
                            <p>Anda belum memiliki penyewaan aktif.</p>
                            <a href="view_items.php" class="btn btn-primary mt-2"><i class="fas fa-search mr-1"></i> Lihat barang yang tersedia</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-box mr-2"></i> Barang Tersedia untuk Disewa</span>
                    <a href="view_items.php" class="btn btn-light btn-sm"><i class="fas fa-th-large mr-1"></i> Lihat Semua</a>
                </div>
                <div class="card-body">
                    <?php
                    $available_items = fetchAll("SELECT i.*, u.name as owner_name FROM items i 
                                                JOIN users u ON i.owner_id = u.user_id 
                                                WHERE i.is_available = 1 AND i.stock > 0 
                                                ORDER BY i.created_at DESC LIMIT 3");
                    ?>
                    
                    <?php if (count($available_items) > 0): ?>
                    <div class="row row-cols-1 row-cols-md-3">
                        <?php foreach ($available_items as $item): ?>
                        <div class="col mb-4">
                            <div class="card item-card">
                                <?php if ($item['image']): ?>
                                <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="card-img-top bg-light p-4 text-center">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title text-truncate"><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="card-text small text-muted flex-grow-1"><?= nl2br(htmlspecialchars(substr($item['description'], 0, 50))) ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <p class="card-text item-price mb-0">
                                            <i class="fas fa-tag mr-1"></i> <?= formatPrice($item['price_per_day']) ?>
                                        </p>
                                        <span class="badge badge-light">per hari</span>
                                    </div>
                                    <div>
                                        <a href="rent_item.php?id=<?= $item['item_id'] ?>" class="btn btn-primary btn-block"><i class="fas fa-handshake mr-1"></i> Sewa Sekarang</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <p>Belum ada barang yang tersedia untuk disewa.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-bell mr-2"></i> Notifikasi Terbaru</span>
                    <?php if (count($notifications) > 0): ?>
                    <span class="badge badge-light"><?= count($notifications) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-0">
                    <?php if (count($notifications) > 0): ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item list-group-item-action flex-column align-items-start">
                            <div class="d-flex w-100 justify-content-between">
                                <h5 class="mb-1"><i class="fas fa-circle text-primary mr-2" style="font-size: 10px;"></i><?= htmlspecialchars($notification['title']) ?></h5>
                                <small><i class="far fa-clock mr-1"></i><?= date('d/m/Y', strtotime($notification['created_at'])) ?></small>
                            </div>
                            <p class="mb-1 text-muted"><?= htmlspecialchars($notification['message']) ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="far fa-bell-slash"></i>
                        <p>Belum ada notifikasi.</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="card instruction-card">
                <div class="card-header">
                    <i class="fas fa-info-circle mr-2"></i> Petunjuk Singkat
                </div>
                <div class="card-body">
                    <h5 class="mb-3">Cara Menyewa Barang:</h5>
                    <div class="instruction-steps">
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">1</span>
                            </div>
                            <div>
                                Pilih barang yang ingin disewa dari halaman <a href="view_items.php">Lihat Barang</a>.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">2</span>
                            </div>
                            <div>
                                Tentukan tanggal mulai dan selesai penyewaan.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">3</span>
                            </div>
                            <div>
                                Upload foto KTP untuk verifikasi.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">4</span>
                            </div>
                            <div>
                                Klik "Sewa Sekarang" untuk mengajukan permintaan penyewaan.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">5</span>
                            </div>
                            <div>
                                Tunggu konfirmasi dari pemilik barang.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">6</span>
                            </div>
                            <div>
                                Setelah dikonfirmasi, Anda dapat mengambil barang sesuai kesepakatan.
                            </div>
                        </div>
                        <div class="d-flex mb-2">
                            <div class="mr-3 text-center">
                                <span class="badge badge-primary rounded-circle p-2" style="width: 25px; height: 25px;">7</span>
                            </div>
                            <div>
                                Kembalikan barang tepat waktu untuk menghindari denda keterlambatan.
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3" role="alert">
                        <i class="fas fa-lightbulb mr-2"></i> Tip: Selalu periksa kondisi barang sebelum mengambil dan saat mengembalikan untuk menghindari perselisihan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

