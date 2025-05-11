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

<h1>Dashboard Penyewa</h1>

<div class="row mt-4">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                Penyewaan Aktif
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
                                    <td><?= htmlspecialchars($rental['item_name']) ?></td>
                                    <td><?= htmlspecialchars($rental['owner_name']) ?></td>
                                    <td><?= formatDate($rental['start_date']) ?></td>
                                    <td><?= formatDate($rental['end_date']) ?></td>
                                    <td>
                                        <?php if ($rental['status'] == 'confirmed'): ?>
                                            <span class="badge badge-info">Dikonfirmasi</span>
                                        <?php elseif ($rental['status'] == 'active'): ?>
                                            <span class="badge badge-success">Aktif</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($rental['days_remaining'] <= 0): ?>
                                            <span class="badge badge-danger">Jatuh tempo</span>
                                        <?php elseif ($rental['days_remaining'] <= 3): ?>
                                            <span class="badge badge-warning"><?= $rental['days_remaining'] ?> hari</span>
                                        <?php else: ?>
                                            <?= $rental['days_remaining'] ?> hari
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="my_rentals.php" class="btn btn-outline-primary btn-sm">Lihat Semua Penyewaan</a>
                <?php else: ?>
                    <p>Anda belum memiliki penyewaan aktif. <a href="view_items.php">Lihat barang yang tersedia untuk disewa</a>.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Barang Tersedia untuk Disewa
            </div>
            <div class="card-body">
                <?php
                $available_items = fetchAll("SELECT i.*, u.name as owner_name FROM items i 
                                            JOIN users u ON i.owner_id = u.user_id 
                                            WHERE i.is_available = 1 AND i.stock > 0 
                                            ORDER BY i.created_at DESC LIMIT 3");
                ?>
                
                <?php if (count($available_items) > 0): ?>
                <div class="row">
                    <?php foreach ($available_items as $item): ?>
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
                                <p class="card-text small"><?= nl2br(htmlspecialchars(substr($item['description'], 0, 50))) ?>...</p>
                                <p class="card-text">
                                    <strong>Harga:</strong> <?= formatPrice($item['price_per_day']) ?> / hari
                                </p>
                                <a href="rent_item.php?id=<?= $item['item_id'] ?>" class="btn btn-primary btn-sm">Sewa Sekarang</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <a href="view_items.php" class="btn btn-outline-primary btn-sm">Lihat Semua Barang</a>
                <?php else: ?>
                <p>Belum ada barang yang tersedia untuk disewa.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                Notifikasi Terbaru
            </div>
            <div class="card-body">
                <?php if (count($notifications) > 0): ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?= htmlspecialchars($notification['title']) ?></h5>
                            <small><?= date('d/m/Y', strtotime($notification['created_at'])) ?></small>
                        </div>
                        <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
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
                Petunjuk Singkat
            </div>
            <div class="card-body">
                <h5>Cara Menyewa Barang:</h5>
                <ol>
                    <li>Pilih barang yang ingin disewa dari halaman <a href="view_items.php">Lihat Barang</a>.</li>
                    <li>Tentukan tanggal mulai dan selesai penyewaan.</li>
                    <li>Upload foto KTP untuk verifikasi.</li>
                    <li>Klik "Sewa Sekarang" untuk mengajukan permintaan penyewaan.</li>
                    <li>Tunggu konfirmasi dari pemilik barang.</li>
                    <li>Setelah dikonfirmasi, Anda dapat mengambil barang sesuai kesepakatan.</li>
                    <li>Kembalikan barang tepat waktu untuk menghindari denda keterlambatan.</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>