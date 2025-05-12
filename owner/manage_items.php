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

<style>
    :root {
        --primary: #3498db;
        --primary-dark: #2980b9;
        --success: #2ecc71;
        --warning: #f39c12;
        --danger: #e74c3c;
        --light-bg: #f8f9fa;
    }

    body {
        background-color: var(--light-bg);
    }

    .items-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .items-header h1 {
        margin: 0;
        color: #2c3e50;
        font-weight: 600;
    }

    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background-color: white;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        padding: 1rem 1.25rem;
    }

    .nav-tabs .nav-link {
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link.active {
        background-color: var(--primary);
        color: white !important;
        border-color: var(--primary);
    }

    .nav-tabs .nav-link:hover {
        background-color: rgba(52,152,219,0.1);
        color: var(--primary);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(52,152,219,0.05);
        transition: background-color 0.3s ease;
    }

    .table td, .table th {
        vertical-align: middle;
        padding: 1rem;
    }

    .item-image {
        width: 100px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
        transition: transform 0.3s ease;
    }

    .item-image:hover {
        transform: scale(1.05);
    }

    .item-placeholder {
        width: 100px;
        height: 100px;
        background-color: var(--light-bg);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    .btn-group .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .badge-available {
        background-color: var(--success);
        color: white;
    }

    .badge-unavailable {
        background-color: var(--danger);
        color: white;
    }

    .table thead {
        background-color: var(--light-bg);
    }

    .action-column {
        width: 150px;
        text-align: center;
    }

    .dropdown-item.disabled {
        color: #6c757d;
        pointer-events: none;
        background-color: #f8f9fa;
    }

    .dropdown-item i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }

    .dropdown-menu {
        min-width: 250px;
    }

    .table td.text-center {
        vertical-align: middle;
    }
</style>

<div class="container-fluid">
    <div class="items-header">
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
                            <th class="action-column">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td>
                                <?php if ($item['image']): ?>
                                <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="item-image" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="item-placeholder">
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
                                <span class="badge <?= $item['is_available'] && $item['stock'] > 0 ? 'badge-available' : 'badge-unavailable' ?>">
                                    <?= $item['is_available'] && $item['stock'] > 0 ? 'Tersedia' : 'Tidak Tersedia' ?>
                                </span>
                            </td>
                            <td><?= $item['active_rentals'] ?></td>
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="actionDropdown<?= $item['item_id'] ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Aksi
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="actionDropdown<?= $item['item_id'] ?>">
                                        <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="dropdown-item">
                                            <i class="fas fa-edit mr-2"></i> Edit Barang
                                        </a>
                                        
                                        <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengubah status barang ini?')">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="dropdown-item <?= $item['active_rentals'] > 0 ? 'disabled' : '' ?>">
                                                <i class="fas fa-<?= $item['is_available'] ? 'ban' : 'check' ?> mr-2"></i> 
                                                <?= $item['is_available'] ? 'Non-aktifkan Barang' : 'Aktifkan Barang' ?>
                                                <?= $item['active_rentals'] > 0 ? ' (Tidak Bisa)' : '' ?>
                                            </button>
                                        </form>
                                        
                                        <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini? Barang dengan penyewaan aktif tidak dapat dihapus.')">
                                            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="dropdown-item <?= $item['active_rentals'] > 0 ? 'disabled' : '' ?>">
                                                <i class="fas fa-trash mr-2"></i> 
                                                Hapus Barang
                                                <?= $item['active_rentals'] > 0 ? ' (Tidak Bisa)' : '' ?>
                                            </button>
                                        </form>
                                    </div>
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
</div>

<?php require_once '../includes/footer.php'; ?>