<?php
require_once 'includes/header.php';

// Mendapatkan beberapa barang teratas yang tersedia
$items = fetchAll("SELECT i.*, u.name as owner_name FROM items i 
                   JOIN users u ON i.owner_id = u.user_id 
                   WHERE i.is_available = 1 AND i.stock > 0 
                   ORDER BY i.created_at DESC LIMIT 6");
?>

<!-- Custom styles matching login.php -->
<style>
    /* Reuse styles from login.php */
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
        transition: all 0.3s ease;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .hero-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 5rem 2rem;
        color: white;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
    }
    
    .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: none;
        padding: 15px 20px;
        font-weight: 600;
        color: #333;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0062cc);
        border: none;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        transition: all 0.3s;
        padding: 10px 20px;
        border-radius: 5px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
    }
    
    .form-control {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid #e3e6f0;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        border-color: #bac8f3;
    }
    
    .input-group-text {
        background-color: #4e73df;
        color: white;
        border: none;
    }
    
    .hero-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: white;
    }
    
    .login-link {
        color: #4e73df;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .login-link:hover {
        color: #224abe;
        text-decoration: none;
    }
    
    /* Additional styles for index page */
    .feature-card {
        padding: 2rem;
        height: 100%;
        transition: all 0.3s;
    }
    
    .feature-icon {
        font-size: 2.5rem;
        color: #4e73df;
        margin-bottom: 1rem;
    }
    
    .step-circle {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(78, 115, 223, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
    }
    
    .step-icon {
        font-size: 2rem;
        color: #4e73df;
    }
    
    .item-img-container {
        height: 200px;
        overflow: hidden;
    }
    
    .item-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .item-card:hover .item-img-container img {
        transform: scale(1.05);
    }
    
    .item-availability {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .section-title {
        position: relative;
        padding-bottom: 10px;
        margin-bottom: 2rem;
    }
    
    .section-title:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 3px;
        background: #4e73df;
    }
    
    .text-center .section-title:after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .testimonial-card {
        padding: 1.5rem;
        height: 100%;
    }
    
    .testimonial-rating {
        color: #f1c40f;
        margin-bottom: 1rem;
    }
    
    .cta-section {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 4rem 0;
        color: white;
    }
</style>

<!-- Hero Section -->
<div class="container-fluid py-4">
    <div class="hero-header shadow">
        <div class="container">
            <i class="fas fa-handshake hero-icon"></i>
            <h1 class="display-4 font-weight-bold">Selamat Datang di SewaBarangYuk</h1>
            <p class="lead">Platform terpercaya untuk menyewa dan menyewakan barang di wilayah Krembung dan sekitarnya.</p>
            <p class="mb-4">Sewa barang berkualitas dengan harga terjangkau atau sewakan barang Anda untuk mendapatkan penghasilan tambahan.</p>
            <?php if (!isLoggedIn()): ?>
            <div class="d-grid gap-2 d-md-block">
                <a class="btn btn-primary btn-lg px-4 py-2 me-md-2" href="register.php">
                    <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                </a>
                <a class="btn btn-outline-light btn-lg px-4 py-2" href="login.php">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </a>
            </div>
            <?php else: ?>
            <a class="btn btn-primary btn-lg px-4 py-2" href="<?= isRenter() ? 'renter/view_items.php' : 'owner/manage_items.php' ?>">
                <?php if (isRenter()): ?>
                    <i class="fas fa-search me-2"></i>Lihat Barang
                <?php else: ?>
                    <i class="fas fa-cog me-2"></i>Kelola Barang
                <?php endif; ?>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-4 mb-4">
                <div class="card feature-card hover-shadow">
                    <div class="feature-icon">
                        <i class="fas fa-hand-holding-usd"></i>
                    </div>
                    <h3>Hemat Biaya</h3>
                    <p class="text-muted">Sewa hanya saat Anda membutuhkan tanpa perlu membeli dengan harga penuh.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card hover-shadow">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Aman & Terpercaya</h3>
                    <p class="text-muted">Semua penyewa dan pemilik barang diverifikasi untuk menjamin keamanan transaksi.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card feature-card hover-shadow">
                    <div class="feature-icon">
                        <i class="fas fa-sync"></i>
                    </div>
                    <h3>Proses Mudah</h3>
                    <p class="text-muted">Cari, pesan, dan bayar dalam hitungan menit. Tanpa ribet!</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Barang Terbaru Section -->
<div class="container py-5">
    <div class="row mb-4">
        <div class="col">
            <h2 class="section-title">Barang Terbaru</h2>
        </div>
    </div>
    
    <?php if (count($items) > 0): ?>
    <div class="row">
        <?php foreach ($items as $item): ?>
        <div class="col-md-4 mb-4">
            <div class="card item-card hover-shadow">
                <div class="item-img-container">
                    <?php if ($item['image']): ?>
                    <img src="assets/images/uploads/items/<?= $item['image'] ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>">
                    <?php else: ?>
                    <div class="card-img-top bg-light p-5 text-center">
                        <i class="fas fa-image fa-4x text-muted"></i>
                    </div>
                    <?php endif; ?>
                    <div class="item-availability">
                        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Tersedia</span>
                    </div>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-primary fw-bold"><?= formatPrice($item['price_per_day']) ?> / hari</span>
                        <span class="text-muted"><i class="fas fa-cubes me-1"></i> Stok: <?= $item['stock'] ?></span>
                    </div>
                    <p class="card-text text-muted"><?= nl2br(htmlspecialchars(substr($item['description'], 0, 100))) ?>...</p>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-user-circle me-2 text-secondary"></i>
                        <small class="text-muted">Pemilik: <?= htmlspecialchars($item['owner_name']) ?></small>
                    </div>
                    <?php if (isRenter()): ?>
                    <a href="renter/rent_item.php?id=<?= $item['item_id'] ?>" class="btn btn-primary w-100">
                        <i class="fas fa-shopping-cart me-1"></i> Sewa Sekarang
                    </a>
                    <?php elseif (!isLoggedIn()): ?>
                    <a href="login.php" class="btn btn-outline-primary w-100">
                        <i class="fas fa-sign-in-alt me-1"></i> Login untuk Menyewa
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="text-center mt-4">
        <a href="renter/view_items.php" class="btn btn-outline-primary">
            <i class="fas fa-th-list me-2"></i>Lihat Semua Barang
        </a>
    </div>
    
    <?php else: ?>
    <div class="alert alert-info shadow-sm">
        <i class="fas fa-info-circle me-2"></i> Belum ada barang yang tersedia untuk disewa.
    </div>
    <?php endif; ?>
</div>

<!-- Cara Kerja Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row mb-4">
            <div class="col text-center">
                <h2 class="section-title">Cara Kerja</h2>
                <p class="text-muted">Proses penyewaan yang mudah dan transparan</p>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="step-circle">
                    <i class="fas fa-search step-icon"></i>
                </div>
                <h4>1. Cari</h4>
                <p class="text-muted">Temukan barang yang Anda butuhkan</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle">
                    <i class="fas fa-calendar-alt step-icon"></i>
                </div>
                <h4>2. Pilih Tanggal</h4>
                <p class="text-muted">Tentukan durasi penyewaan</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle">
                    <i class="fas fa-credit-card step-icon"></i>
                </div>
                <h4>3. Bayar</h4>
                <p class="text-muted">Lakukan pembayaran dengan aman</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle">
                    <i class="fas fa-box-open step-icon"></i>
                </div>
                <h4>4. Terima</h4>
                <p class="text-muted">Ambil atau terima pengiriman barang</p>
            </div>
        </div>
    </div>
</div>

<!-- Testimoni Section -->
<div class="container py-5">
    <div class="row mb-4">
        <div class="col text-center">
            <h2 class="section-title">Testimoni Pelanggan</h2>
            <p class="text-muted">Apa kata mereka tentang layanan kami</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card hover-shadow">
                <div class="card-body">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-3">"SewaBarangYuk yang mudah digunakan dan sangat membantu kebutuhan saya. Proses cepat dan tidak ribet."</p>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
                        <div>
                            <h6 class="mb-0">Ahmad Fauzi</h6>
                            <small class="text-muted">Penyewa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card hover-shadow">
                <div class="card-body">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="mb-3">"Sebagai pemilik barang, platform ini sangat membantu saya mendapatkan penghasilan tambahan dari barang yang jarang dipakai."</p>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
                        <div>
                            <h6 class="mb-0">Siti Nurhaliza</h6>
                            <small class="text-muted">Pemilik Barang</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card hover-shadow">
                <div class="card-body">
                    <div class="testimonial-rating">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star-half-alt"></i>
                    </div>
                    <p class="mb-3">"Harga sewa yang terjangkau dan kualitas barang yang disewakan sangat baik. Transaksi aman dan proses cepat."</p>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-circle fa-2x me-3 text-primary"></i>
                        <div>
                            <h6 class="mb-0">Budi Santoso</h6>
                            <small class="text-muted">Penyewa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="cta-section text-white text-center py-5">
    <div class="container">
        <h2 class="mb-4">Siap Memulai?</h2>
        <p class="lead mb-4">Daftar sekarang dan mulai menyewa atau menyewakan barang Anda!</p>
        <?php if (!isLoggedIn()): ?>
        <div class="d-grid gap-2 d-md-block">
            <a href="register.php" class="btn btn-light btn-lg me-md-2">
                <i class="fas fa-user-plus me-2"></i>Daftar
            </a>
            <a href="login.php" class="btn btn-outline-light btn-lg">
                <i class="fas fa-sign-in-alt me-2"></i>Masuk
            </a>
        </div>
        <?php else: ?>
        <a href="<?= isRenter() ? 'renter/view_items.php' : 'owner/manage_items.php' ?>" class="btn btn-light btn-lg">
            <?= isRenter() ? '<i class="fas fa-search me-2"></i>Cari Barang' : '<i class="fas fa-plus-circle me-2"></i>Tambah Barang' ?>
        </a>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>