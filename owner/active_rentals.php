<?php
require_once '../includes/header.php';
requireOwner();

// Filter status
$status_filter = $_GET['status'] ?? 'active';

// Query dasar
$sql = "SELECT r.*, i.name as item_name, i.image as item_image, 
        u.name as renter_name, u.phone as renter_phone, u.email as renter_email, u.address as renter_address,
        DATEDIFF(r.end_date, CURDATE()) as days_remaining
        FROM rentals r 
        JOIN items i ON r.item_id = i.item_id 
        JOIN users u ON r.renter_id = u.user_id
        WHERE i.owner_id = ?";

$params = [$_SESSION['user_id']];

// Tambahkan filter status
if ($status_filter === 'active') {
    $sql .= " AND r.status IN ('confirmed', 'active')";
} elseif ($status_filter === 'late') {
    $sql .= " AND r.status = 'late'";
} elseif ($status_filter === 'completed') {
    $sql .= " AND r.status = 'completed'";
} elseif ($status_filter === 'all') {
    $sql .= " AND r.status IN ('confirmed', 'active', 'late', 'completed')";
}

$sql .= " ORDER BY r.end_date ASC";

// Ambil data penyewaan
$rentals = fetchAll($sql, $params);

// Proses aksi-aksi penyewaan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];
    $action = $_POST['action'];
    
    // Dapatkan data rental
    $rental = fetchOne("SELECT r.*, i.name as item_name, i.owner_id, i.item_id, u.name as renter_name, u.user_id as renter_id 
                       FROM rentals r 
                       JOIN items i ON r.item_id = i.item_id 
                       JOIN users u ON r.renter_id = u.user_id
                       WHERE r.rental_id = ?", [$rental_id]);
    
    if ($rental && $rental['owner_id'] == $_SESSION['user_id']) {
        if ($action === 'start') {
            // Update status rental menjadi active
            executeQuery("UPDATE rentals SET status = 'active' WHERE rental_id = ?", [$rental_id]);
            
            // Buat notifikasi untuk penyewa
            createNotification(
                $rental['renter_id'],
                "Penyewaan Dimulai",
                "Penyewaan Anda untuk barang '{$rental['item_name']}' telah dimulai.",
                "confirmation",
                $rental_id
            );
            
            $_SESSION['success'] = 'Status penyewaan berhasil diubah menjadi aktif.';
        } elseif ($action === 'complete') {
            // Update status rental menjadi completed
            executeQuery("UPDATE rentals SET status = 'completed', return_status = 1 WHERE rental_id = ?", [$rental_id]);
            
            // Tambah stok barang
            executeQuery("UPDATE items SET stock = stock + 1 WHERE item_id = ?", [$rental['item_id']]);
            
            // Buat notifikasi untuk penyewa
            createNotification(
                $rental['renter_id'],
                "Penyewaan Selesai",
                "Penyewaan Anda untuk barang '{$rental['item_name']}' telah selesai. Terima kasih telah menggunakan layanan kami.",
                "return",
                $rental_id
            );
            
            $_SESSION['success'] = 'Penyewaan berhasil diselesaikan.';
        }
        
        // Refresh halaman
        redirect('active_rentals.php' . (empty($_GET) ? '' : '?' . http_build_query($_GET)));
    }
}

// Tampilkan pesan jika ada
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<h1>Penyewaan Aktif</h1>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs">
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === 'active' ? 'active' : '' ?>" href="?status=active">Aktif</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === 'late' ? 'active' : '' ?>" href="?status=late">Terlambat</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === 'completed' ? 'active' : '' ?>" href="?status=completed">Selesai</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $status_filter === 'all' ? 'active' : '' ?>" href="?status=all">Semua</a>
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
                        <th>Penyewa</th>
                        <th>Periode</th>
                        <th>Status</th>
                        <th>Sisa Waktu</th>
                        <th>Total</th>
                        <th>Aksi</th>
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
                            <div>
                                <?= htmlspecialchars($rental['renter_name']) ?><br>
                                <small class="text-muted"><?= htmlspecialchars($rental['renter_phone']) ?></small>
                            </div>
                        </td>
                        <td>
                            <?= formatDate($rental['start_date']) ?> s/d <?= formatDate($rental['end_date']) ?>
                        </td>
                        <td>
                            <?php
                            switch ($rental['status']) {
                                case 'confirmed':
                                    echo '<span class="badge badge-info">Dikonfirmasi</span>';
                                    break;
                                case 'active':
                                    echo '<span class="badge badge-success">Aktif</span>';
                                    break;
                                case 'late':
                                    echo '<span class="badge badge-danger">Terlambat</span>';
                                    break;
                                case 'completed':
                                    echo '<span class="badge badge-primary">Selesai</span>';
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
                            <?php elseif ($rental['status'] === 'late'): ?>
                                <span class="text-danger"><?= abs($rental['days_remaining']) ?> hari terlambat</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?= formatPrice($rental['total_price']) ?></td>
                        <td>
                            <?php if ($rental['status'] === 'confirmed'): ?>
                            <form method="post" class="d-inline">
                                <input type="hidden" name="rental_id" value="<?= $rental['rental_id'] ?>">
                                <input type="hidden" name="action" value="start">
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-play"></i> Mulai
                                </button>
                            </form>
                            <?php elseif (in_array($rental['status'], ['active', 'late'])): ?>
                            <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin barang telah dikembalikan?')">
                                <input type="hidden" name="rental_id" value="<?= $rental['rental_id'] ?>">
                                <input type="hidden" name="action" value="complete">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="fas fa-check"></i> Selesai
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-muted">Tidak ada aksi</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <?php if ($status_filter === 'all'): ?>
                Belum ada penyewaan.
            <?php else: ?>
                Tidak ada penyewaan dengan status yang dipilih.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>