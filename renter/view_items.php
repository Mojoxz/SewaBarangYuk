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

<div class="container-fluid py-4">
    <!-- Hero Section -->
    <div class="jumbotron bg-primary text-white rounded-lg shadow mb-4">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Temukan Barang Untuk Disewa</h1>
            <p class="lead">Berbagai pilihan barang berkualitas siap untuk memenuhi kebutuhan Anda</p>
            <form method="get" class="mt-4">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" name="search" placeholder="Cari barang yang Anda butuhkan..." value="<?= htmlspecialchars($search) ?>" aria-label="Search">
                    <div class="input-group-append">
                        <button class="btn btn-light" type="submit">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <?php if (!empty($search)): ?>
                        <div class="mb-2">
                            <span class="badge badge-pill badge-primary p-2">
                                <i class="fas fa-search mr-1"></i> "<?= htmlspecialchars($search) ?>"
                                <a href="?<?= http_build_query(array_diff_key($_GET, ['search' => ''])) ?>" class="text-white ml-2">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        </div>
                    <?php endif; ?>
                    <h5 class="mb-0 text-muted">
                        <i class="fas fa-list mr-2"></i> Menampilkan <?= count($items) ?> barang tersedia
                    </h5>
                </div>
                <div class="col-md-6">
                    <form method="get" class="form-inline justify-content-md-end">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="search" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                        
                        <div class="form-group mr-2">
                            <label for="sort" class="mr-2"><i class="fas fa-sort mr-1"></i> Urutkan:</label>
                            <select id="sort" name="sort" class="form-control" onchange="this.form.submit()">
                                <option value="newest" <?= $sort == 'newest' ? 'selected' : '' ?>>Terbaru</option>
                                <option value="oldest" <?= $sort == 'oldest' ? 'selected' : '' ?>>Terlama</option>
                                <option value="price_low" <?= $sort == 'price_low' ? 'selected' : '' ?>>Harga Terendah</option>
                                <option value="price_high" <?= $sort == 'price_high' ? 'selected' : '' ?>>Harga Tertinggi</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Listing Section -->
    <?php if (count($items) > 0): ?>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 col-lg-3 mb-4">
                    <div class="card h-100 hover-shadow item-card">
                        <div class="position-relative">
                            <?php if ($item['image']): ?>
                                <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="fas fa-box fa-4x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="position-absolute bottom-0 right-0 m-2">
                                <div class="price-tag">
                                    <span class="price-amount"><?= formatPrice($item['price_per_day']) ?></span>
                                    <span class="price-period">/hari</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title font-weight-bold text-primary"><?= htmlspecialchars($item['name']) ?></h5>
                            <p class="card-text text-muted small">
                                <?= nl2br(htmlspecialchars(substr($item['description'], 0, 100))) ?>
                                <?= strlen($item['description']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <!-- Display price in card body for mobile view -->
                            <div class="d-block d-md-none mb-3">
                                <div class="price-card">
                                    <i class="fas fa-tag mr-1"></i>
                                    <span class="font-weight-bold"><?= formatPrice($item['price_per_day']) ?></span>
                                    <span class="small text-muted">/hari</span>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="badge badge-pill badge-light p-2">
                                    <i class="fas fa-cubes mr-1"></i> Stok: <?= $item['stock'] ?>
                                </span>
                                <button class="btn btn-sm btn-outline-primary" title="Tambah ke favorit">
                                    <i class="far fa-heart"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top-0">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <small class="text-muted">
                                        <i class="fas fa-user-circle mr-1"></i> <?= htmlspecialchars($item['owner_name']) ?>
                                    </small>
                                </div>
                                <a href="rent_item.php?id=<?= $item['item_id'] ?>" class="btn btn-primary btn-rental">
                                    <i class="fas fa-shopping-cart mr-1"></i> Sewa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info shadow-sm">
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                <h4>Maaf, tidak ditemukan barang yang sesuai</h4>
                <p class="mb-0">Coba kata kunci lain atau lihat semua barang yang tersedia</p>
                <a href="?" class="btn btn-outline-primary mt-3">Lihat Semua Barang</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Custom styles -->
<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
        transition: all 0.3s ease;
    }
    
    .item-card {
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
    }
    
    .jumbotron {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 3rem 2rem;
    }
    
    .badge-pill {
        border-radius: 30px;
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
    
    .price-card {
        background-color: #f8f9fa;
        border-left: 4px solid #28a745;
        padding: 8px 12px;
        border-radius: 4px;
    }
    
    .position-absolute.bottom-0 {
        bottom: 0;
    }
    
    .position-absolute.right-0 {
        right: 0;
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
</style>

<?php require_once '../includes/footer.php'; ?>