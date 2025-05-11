<?php
require_once '../includes/header.php';
requireOwner();

// Filter
$filter = $_GET['filter'] ?? 'all';

// Dasar query
$sql = "SELECT i.*, 
        (SELECT COUNT(*) FROM rentals r WHERE r.item_id = i.item_id AND r.status IN ('confirmed', 'active')) as active_rentals
        FROM items i 
        WHERE i.owner_id = ?";

$params = [$_SESSION['user_id']];

// Tambahkan filter
if ($filter === 'available') {
    $sql .= " AND i.is_available = 1 AND i.stock > 0";
} elseif ($filter === 'unavailable') {
    $sql .= " AND (i.is_available = 0 OR i.stock = 0)";
}

$sql .= " ORDER BY i.created_at DESC";

// Dapatkan daftar barang
$items = fetchAll($sql, $params);

// Proses update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['item_id'])) {
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];
    
    if ($action === 'toggle_status') {
        // Toggle status ketersediaan
        $item = fetchOne("SELECT is_available FROM items WHERE item_id = ? AND owner_id = ?", [$item_id, $_SESSION['user_id']]);
        
        if ($item) {
            $new_status = $item['is_available'] ? 0 : 1;
            executeQuery("UPDATE items SET is_available = ? WHERE item_id = ?", [$new_status, $item_id]);
            
            // Refresh halaman
            redirect('manage_items.php' . (empty($_GET) ? '' : '?' . http_build_query($_GET)));
        }
    } elseif ($action === 'delete') {
        // Periksa apakah barang sedang disewa
        $active_rentals = fetchOne("SELECT COUNT(*) as count FROM rentals WHERE item_id = ? AND status IN ('pending', 'confirmed', 'active')", [$item_id])['count'];
        
        if ($active_rentals > 0) {
            $_SESSION['error'] = 'Barang tidak dapat dihapus karena masih ada penyewaan aktif.';
        } else {
            executeQuery("DELETE FROM items WHERE item_id = ? AND owner_id = ?", [$item_id, $_SESSION['user_id']]);
            $_SESSION['success'] = 'Barang berhasil dihapus.';
        }
        
        // Refresh halaman
        redirect('manage_items.php' . (empty($_GET) ? '' : '?' . http_build_query($_GET)));
    }
}

// Tampilkan pesan jika ada
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Kelola Barang</h1>
    <a href="add_item.php" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Barang
    </a>
</div>

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
                <a class="nav-link <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'available' ? 'active' : '' ?>" href="?filter=available">Tersedia</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $filter === 'unavailable' ? 'active' : '' ?>" href="?filter=unavailable">Tidak Tersedia</a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <?php if (count($items) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Foto</th>
                        <th>Nama Barang</th>
                        <th>Harga/Hari</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Penyewaan Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <?php if ($item['image']): ?>
                            <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="img-thumbnail" style="width: 80px; height: 80px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light text-center" style="width: 80px; height: 80px; line-height: 80px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($item['name']) ?></strong>
                            <p class="text-muted small mb-0"><?= substr(htmlspecialchars($item['description']), 0, 50) ?>...</p>
                        </td>
                        <td><?= formatPrice($item['price_per_day']) ?></td>
                        <td><?= $item['stock'] ?></td>
                        <td>
                            <span class="badge <?= $item['is_available'] && $item['stock'] > 0 ? 'badge-success' : 'badge-danger' ?>">
                                <?= $item['is_available'] && $item['stock'] > 0 ? 'Tersedia' : 'Tidak Tersedia' ?>
                            </span>
                        </td>
                        <td><?= $item['active_rentals'] ?></td>
                        <td>
                            <div class="btn-group">
                                <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn btn-sm btn-info">
                                <i class="fas fa-edit"></i> Edit
                                </a>
                                <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status barang ini?')">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="btn btn-sm <?= $item['is_available'] ? 'btn-warning' : 'btn-success' ?>">
                                        <i class="fas fa-<?= $item['is_available'] ? 'ban' : 'check' ?>"></i>
                                        <?= $item['is_available'] ? 'Non-aktifkan' : 'Aktifkan' ?>
                                    </button>
                                </form>
                                <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini? Barang dengan penyewaan aktif tidak dapat dihapus.')">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="btn btn-sm btn-danger" <?= $item['active_rentals'] > 0 ? 'disabled' : '' ?>>
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <?php if ($filter === 'all'): ?>
                Anda belum memiliki barang yang disewakan. <a href="add_item.php">Tambahkan barang sekarang</a>.
            <?php else: ?>
                Tidak ada barang dengan status yang dipilih.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>