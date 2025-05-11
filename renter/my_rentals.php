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
?>

<h1>Penyewaan Saya</h1>

<div class="card mb-4">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'all' ? 'active' : '' ?>" href="?status=all">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'pending' ? 'active' : '' ?>" href="?status=pending">Menunggu</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'confirmed' ? 'active' : '' ?>" href="?status=confirmed">Dikonfirmasi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'active' ? 'active' : '' ?>" href="?status=active">Aktif</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'completed' ? 'active' : '' ?>" href="?status=completed">Selesai</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'cancelled' ? 'active' : '' ?>" href="?status=cancelled">Dibatalkan</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter == 'late' ? 'active' : '' ?>" href="?status=late">Terlambat</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <?php if (count($rentals) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Barang</th>
                            <th>Pemilik</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rentals as $rental): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <?php if ($rental['item_image']): ?>
                                    <img src="../assets/images/uploads/items/<?= $rental['item_image'] ?>" class="mr-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    <?php else: ?>
                                    <div class="bg-light mr-2 text-center" style="width: 50px; height: 50px;">
                                        <i class="fas fa-image text-muted" style="line-height: 50px;"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <?= htmlspecialchars($rental['item_name']) ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?= htmlspecialchars($rental['owner_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($rental['owner_phone']) ?></small>
                            </td>
                            <td><?= formatDate($rental['start_date']) ?></td>
                            <td><?= formatDate($rental['end_date']) ?></td>
                            <td><?= formatPrice($rental['total_price']) ?></td>
                            <td>
                                <?php
                                switch ($rental['status']) {
                                    case 'pending':
                                        echo '<span class="badge badge-warning">Menunggu Konfirmasi</span>';
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
                            <td>
                                <?php if (in_array($rental['status'], ['confirmed', 'active'])): ?>
                                    <?php if ($rental['days_remaining'] <= 0): ?>
                                        <span class="text-danger">Jatuh tempo</span>
                                    <?php elseif ($rental['days_remaining'] <= 3): ?>
                                        <span class="text-warning"><?= $rental['days_remaining'] ?> hari lagi</span>
                                    <?php else: ?>
                                        <?= $rental['days_remaining'] ?> hari lagi
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small class="text-muted">Dibuat pada <?= date('d/m/Y', strtotime($rental['created_at'])) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <?php if ($status_filter == 'all'): ?>
                    Anda belum memiliki penyewaan. <a href="view_items.php">Lihat barang yang tersedia untuk disewa</a>.
                <?php else: ?>
                    Tidak ada penyewaan dengan status yang dipilih.
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>