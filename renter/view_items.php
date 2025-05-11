<?php
require_once '../includes/header.php';
requireRenter();

// Filter pencarian
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$category = $_GET['category'] ?? '';

// Query dasar
$sql_base = "SELECT i.*, u.name as owner_name FROM items i 
             JOIN users u ON i.owner_id = u.user_id 
             WHERE i.is_available = 1 AND i.stock > 0";

// Tambahkan kondisi pencarian jika ada
if (!empty($search)) {
    $sql_base .= " AND (i.name LIKE ? OR i.description LIKE ?)";
    $params = ["%$search%", "%$search%"];
} else {
    $params = [];
}

// Urutkan hasil
switch ($sort) {
    case 'price_low':
        $sql_base .= " ORDER BY i.price_per_day ASC";
        break;
    case 'price_high':
        $sql_base .= " ORDER BY i.price_per_day DESC";
        break;
    case 'oldest':
        $sql_base .= " ORDER BY i.created_at ASC";
        break;
    case 'newest':
    default:
        $sql_base .= " ORDER BY i.created_at DESC";
        break;
}

// Ambil daftar barang
$items = fetchAll($sql_base, $params);
?>

<h1>Barang Tersedia</h1>

<div class="row mb-4">
    <div class="col-md-6">
        <form method="get" class="form-inline">
            <div class="input-group w-100">
                <input type="text" class="form-control" name="search" placeholder="Cari barang..." value="<?= htmlspecialchars($search) ?>">
                <div class="input-group-append">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-md-6 text-right">
        <div class="form-inline justify-content-end">
            <label for="sort" class="mr-2">Urutkan:</label>
            <select id="sort" class="form-control" onchange="this.form.submit()">
                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Terbaru</option>
                <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Terlama</option>
                <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
            </select>
        </div>
    </div>
</div>

<?php if (count($items) > 0): ?>
<div class="row">
    <?php foreach ($items as $item): ?>
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <?php if ($item['image']): ?>
            <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
            <?php else: ?>
            <div class="card-img-top bg-light p-5 text-center">
                <i class="fas fa-image fa-5x text-muted"></i>
            </div>
            <?php endif; ?>
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                <p class="card-text"><?= nl2br(htmlspecialchars(substr($item['description'], 0, 100))) ?>...</p>
                <p class="card-text">
                    <strong>Harga:</strong> <?= formatPrice($item['price_per_day']) ?> / hari<br>
                    <strong>Stok:</strong> <?= $item['stock'] ?>
                </p>
                <a href="rent_item.php?id=<?= $item['item_id'] ?>" class="btn btn-primary">Sewa Sekarang</a>
            </div>
            <div class="card-footer text-muted">
                Disewakan oleh: <?= htmlspecialchars($item['owner_name']) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    Tidak ada barang yang tersedia sesuai dengan kriteria pencarian Anda.
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>