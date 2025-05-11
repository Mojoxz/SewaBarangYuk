<?php
require_once 'includes/header.php';

// Mendapatkan beberapa barang teratas yang tersedia
$items = fetchAll("SELECT i.*, u.name as owner_name FROM items i 
                   JOIN users u ON i.owner_id = u.user_id 
                   WHERE i.is_available = 1 AND i.stock > 0 
                   ORDER BY i.created_at DESC LIMIT 6");
?>

<!-- Hero Section dengan Background Image -->
<div class="hero-section py-5" style="background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('assets/images/hero-bg.jpg'); background-size: cover; background-position: center; color: white;">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 offset-lg-2 text-center py-4">
                <h1 class="display-4 fw-bold mb-4">Selamat Datang di Sistem Penyewaan</h1>
                <p class="lead fs-4 mb-4">Platform terpercaya untuk menyewa dan menyewakan barang di wilayah Gresik dan sekitarnya.</p>
                <p class="mb-4 fs-5">Sewa barang berkualitas dengan harga terjangkau atau sewakan barang Anda untuk mendapatkan penghasilan tambahan.</p>
                <?php if (!isLoggedIn()): ?>
                <div class="d-grid gap-2 d-md-block">
                    <a class="btn btn-primary btn-lg px-4 py-2 me-md-2" href="register.php" role="button">
                        <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                    </a>
                    <a class="btn btn-outline-light btn-lg px-4 py-2" href="login.php" role="button">
                        <i class="fas fa-sign-in-alt me-2"></i>Masuk
                    </a>
                </div>
                <?php else: ?>
                <a class="btn btn-primary btn-lg px-4 py-2" href="<?= isRenter() ? 'renter/view_items.php' : 'owner/manage_items.php' ?>" role="button">
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
</div>

<!-- Features Section -->
<div class="bg-light py-5">
    <div class="container">
        <div class="row text-center mb-5">
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm bg-white">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-hand-holding-usd fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4">Hemat Biaya</h3>
                    <p class="text-muted">Sewa hanya saat Anda membutuhkan tanpa perlu membeli dengan harga penuh.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm bg-white">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-shield-alt fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4">Aman & Terpercaya</h3>
                    <p class="text-muted">Semua penyewa dan pemilik barang diverifikasi untuk menjamin keamanan transaksi.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="feature-box p-4 h-100 rounded shadow-sm bg-white">
                    <div class="feature-icon mb-3">
                        <i class="fas fa-sync fa-3x text-primary"></i>
                    </div>
                    <h3 class="h4">Proses Mudah</h3>
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
            <h2 class="section-title position-relative pb-2 mb-3">
                <span class="bg-white pe-3">Barang Terbaru</span>
                <div class="section-line"></div>
            </h2>
        </div>
    </div>
    
    <?php if (count($items) > 0): ?>
    <div class="row">
        <?php foreach ($items as $item): ?>
        <div class="col-md-4 mb-4">
            <div class="card item-card h-100 border-0 shadow-sm transition-hover">
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
                    <h5 class="card-title text-truncate"><?= htmlspecialchars($item['name']) ?></h5>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-primary fw-bold"><?= formatPrice($item['price_per_day']) ?> / hari</span>
                        <span class="text-muted"><i class="fas fa-cubes me-1"></i> Stok: <?= $item['stock'] ?></span>
                    </div>
                    <p class="card-text item-description"><?= nl2br(htmlspecialchars(substr($item['description'], 0, 100))) ?>...</p>
                    <div class="owner-info d-flex align-items-center mb-3">
                        <div class="owner-avatar me-2">
                            <i class="fas fa-user-circle fa-2x text-secondary"></i>
                        </div>
                        <div class="owner-name text-muted small">
                            Pemilik: <?= htmlspecialchars($item['owner_name']) ?>
                        </div>
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
        <a href="renter/view_items.php" class="btn btn-outline-primary px-4">
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
            <div class="col-lg-6 offset-lg-3 text-center">
                <h2 class="mb-3">Cara Kerja</h2>
                <p class="lead text-muted">Proses penyewaan yang mudah dan transparan</p>
            </div>
        </div>
        <div class="row text-center">
            <div class="col-md-3 mb-4">
                <div class="step-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <i class="fas fa-search fa-2x text-primary"></i>
                </div>
                <h4 class="h5">1. Cari</h4>
                <p class="text-muted">Temukan barang yang Anda butuhkan</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <i class="fas fa-calendar-alt fa-2x text-primary"></i>
                </div>
                <h4 class="h5">2. Pilih Tanggal</h4>
                <p class="text-muted">Tentukan durasi penyewaan</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <i class="fas fa-credit-card fa-2x text-primary"></i>
                </div>
                <h4 class="h5">3. Bayar</h4>
                <p class="text-muted">Lakukan pembayaran dengan aman</p>
            </div>
            <div class="col-md-3 mb-4">
                <div class="step-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                    <i class="fas fa-box-open fa-2x text-primary"></i>
                </div>
                <h4 class="h5">4. Terima</h4>
                <p class="text-muted">Ambil atau terima pengiriman barang</p>
            </div>
        </div>
    </div>
</div>

<!-- Testimoni Section -->
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-6 offset-lg-3 text-center">
            <h2 class="mb-3">Testimoni Pelanggan</h2>
            <p class="lead text-muted">Apa kata mereka tentang layanan kami</p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="testimonial-rating mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="testimonial-text mb-3">"Sistem penyewaan yang mudah digunakan dan sangat membantu kebutuhan saya. Proses cepat dan tidak ribet."</p>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Ahmad Fauzi</h6>
                            <small class="text-muted">Penyewa</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="testimonial-rating mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                    </div>
                    <p class="testimonial-text mb-3">"Sebagai pemilik barang, platform ini sangat membantu saya mendapatkan penghasilan tambahan dari barang yang jarang dipakai."</p>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">Siti Nurhaliza</h6>
                            <small class="text-muted">Pemilik Barang</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card testimonial-card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="testimonial-rating mb-2">
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star text-warning"></i>
                        <i class="fas fa-star-half-alt text-warning"></i>
                    </div>
                    <p class="testimonial-text mb-3">"Harga sewa yang terjangkau dan kualitas barang yang disewakan sangat baik. Transaksi aman dan proses cepat."</p>
                    <div class="testimonial-author d-flex align-items-center">
                        <div class="author-avatar me-3">
                            <i class="fas fa-user-circle fa-2x text-primary"></i>
                        </div>
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
<div class="cta-section text-white text-center py-5" style="background: linear-gradient(90deg, #0062cc, #0093ff);">
    <div class="container py-3">
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

<style>
/* === GLOBAL STYLES === */
:root {
    --primary-color: #3498db;
    --primary-dark: #2980b9;
    --secondary-color: #2ecc71;
    --accent-color: #f39c12;
    --text-color: #333;
    --light-text: #666;
    --light-bg: #f8f9fa;
    --border-radius: 10px;
    --card-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    --hover-shadow: 0 12px 22px rgba(0, 0, 0, 0.15);
    --transition: all 0.3s ease;
}

body {
    color: var(--text-color);
    scroll-behavior: smooth;
}

.curved-section {
    position: relative;
    overflow: hidden;
}

.curved-section::before {
    content: '';
    position: absolute;
    top: -50px;
    left: 0;
    width: 100%;
    height: 50px;
    background: inherit;
    border-radius: 0 0 50% 50%;
}

.curved-section::after {
    content: '';
    position: absolute;
    bottom: -50px;
    left: 0;
    width: 100%;
    height: 50px;
    background: inherit;
    border-radius: 50% 50% 0 0;
}

.glass-effect {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hover-float {
    transition: var(--transition);
}

.hover-float:hover {
    transform: translateY(-10px);
    box-shadow: var(--hover-shadow);
}

.text-gradient {
    background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.bg-gradient {
    background: linear-gradient(120deg, var(--primary-color), var(--secondary-color));
}

.section-heading {
    position: relative;
    display: inline-block;
    margin-bottom: 30px;
    padding-bottom: 15px;
    font-weight: 700;
}

.section-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 50px;
    height: 3px;
    background: var(--primary-color);
}

.section-heading.text-center::after {
    left: 50%;
    transform: translateX(-50%);
}

/* === HERO SECTION === */
.hero-section {
    position: relative;
    background: linear-gradient(135deg, rgba(29, 29, 29, 0.8), rgba(29, 29, 29, 0.6)), url('assets/images/hero-bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: white;
    overflow: hidden;
    min-height: 600px;
    display: flex;
    align-items: center;
    margin-top: -20px;
}

.hero-shape {
    position: absolute;
    bottom: -50px;
    left: 0;
    width: 100%;
    height: 100px;
    background: #fff;
    clip-path: polygon(0 50%, 100% 0, 100% 100%, 0% 100%);
}

.hero-content {
    padding: 6rem 0;
    position: relative;
    z-index: 5;
}

.hero-content h1 {
    font-size: 3.5rem;
    font-weight: 800;
    margin-bottom: 1.5rem;
    text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.3);
    animation: fadeInDown 1s;
}

.hero-content p {
    font-size: 1.25rem;
    margin-bottom: 2rem;
    max-width: 650px;
    text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.3);
    animation: fadeInUp 1s 0.3s;
    animation-fill-mode: both;
}

.hero-btn {
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
    animation: fadeInUp 1s 0.6s;
    animation-fill-mode: both;
}

.hero-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
}

.hero-card {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

/* === FEATURES SECTION === */
.features-section {
    padding: 80px 0;
    background-color: #fff;
    position: relative;
    z-index: 1;
}

.feature-card {
    border-radius: var(--border-radius);
    padding: 30px;
    height: 100%;
    transition: var(--transition);
    border: none;
    overflow: hidden;
    position: relative;
    z-index: 1;
}

.feature-card::before {
    content: '';
    position: absolute;
    top: -10px;
    right: -10px;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(52, 152, 219, 0.1);
    z-index: -1;
}

.feature-icon {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: rgba(52, 152, 219, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
    color: var(--primary-color);
    transform: rotate(10deg);
    transition: var(--transition);
}

.feature-card:hover .feature-icon {
    transform: rotate(0deg);
    background: var(--primary-color);
    color: white;
}

.feature-card h3 {
    font-weight: 700;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.feature-card p {
    color: var(--light-text);
    margin-bottom: 0;
}

/* === ITEMS SECTION === */
.items-section {
    padding: 80px 0;
    background-color: var(--light-bg);
    position: relative;
}

.item-card {
    border: none;
    border-radius: var(--border-radius);
    overflow: hidden;
    box-shadow: var(--card-shadow);
    transition: var(--transition);
    height: 100%;
    background: white;
}

.item-card:hover {
    transform: translateY(-10px);
    box-shadow: var(--hover-shadow);
}

.item-img-container {
    position: relative;
    height: 220px;
    overflow: hidden;
}

.item-img-container img {
    height: 100%;
    width: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.item-card:hover .item-img-container img {
    transform: scale(1.1);
}

.item-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0) 50%);
    opacity: 0;
    transition: var(--transition);
}

.item-card:hover .item-overlay {
    opacity: 1;
}

.item-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
    transform: rotate(3deg);
    box-shadow: 0 3px 10px rgba(0,0,0,0.1);
}

.item-price {
    position: absolute;
    bottom: 15px;
    left: 15px;
    background: var(--primary-color);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-weight: 700;
    box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    z-index: 2;
}

.item-card-body {
    padding: 20px;
}

.item-title {
    font-weight: 700;
    margin-bottom: 12px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-description {
    color: var(--light-text);
    height: 65px;
    overflow: hidden;
    margin-bottom: 15px;
    font-size: 0.9rem;
}

.item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 15px;
    border-top: 1px solid rgba(0,0,0,0.05);
    color: var(--light-text);
    font-size: 0.85rem;
}

.item-category {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 15px;
    font-size: 0.75rem;
    margin-bottom: 10px;
    background: rgba(52, 152, 219, 0.1);
    color: var(--primary-color);
}

.item-btn {
    border-radius: 30px;
    padding: 8px 20px;
    font-weight: 600;
    transition: var(--transition);
    margin-top: 10px;
    width: 100%;
}

.owner-info {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.owner-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(52, 152, 219, 0.1);
    color: var(--primary-color);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
}

/* === HOW IT WORKS SECTION === */
.how-works-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #4e54c8, #8f94fb);
    color: white;
    position: relative;
    overflow: hidden;
}

.step-box {
    text-align: center;
    position: relative;
    z-index: 1;
}

.step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    color: var(--primary-dark);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0 auto 20px;
    position: relative;
    z-index: 2;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.step-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 2rem;
    position: relative;
    z-index: 2;
    border: 2px dashed rgba(255, 255, 255, 0.5);
}

.step-title {
    font-weight: 700;
    margin-bottom: 10px;
}

.step-description {
    opacity: 0.9;
    max-width: 300px;
    margin: 0 auto;
}

.step-connector {
    position: absolute;
    top: 25px;
    left: 50%;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
    z-index: 0;
}

/* === TESTIMONIALS SECTION === */
.testimonials-section {
    padding: 80px 0;
    background-color: #fff;
    position: relative;
}

.testimonial-card {
    border-radius: var(--border-radius);
    overflow: hidden;
    padding: 30px;
    box-shadow: var(--card-shadow);
    height: 100%;
    transition: var(--transition);
    border: 1px solid rgba(0,0,0,0.05);
    position: relative;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--hover-shadow);
}

.testimonial-card::before {
    content: '\201C';
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 5rem;
    color: rgba(52, 152, 219, 0.1);
    font-family: Georgia, serif;
    line-height: 1;
}

.testimonial-text {
    font-style: italic;
    margin-bottom: 20px;
    position: relative;
    z-index: 1;
}

.testimonial-author {
    display: flex;
    align-items: center;
}

.author-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin-right: 15px;
    flex-shrink: 0;
    background: rgba(52, 152, 219, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-color);
}

.author-name {
    font-weight: 700;
    margin-bottom: 5px;
}

.author-title {
    color: var(--light-text);
    font-size: 0.85rem;
}

.star-rating {
    color: #f1c40f;
    margin-bottom: 15px;
}

/* === CTA SECTION === */
.cta-section {
    padding: 100px 0;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(46, 204, 113, 0.9)), url('assets/images/cta-bg.jpg');
    background-size: cover;
    background-position: center;
    background-attachment: fixed;
    color: white;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: url('assets/images/pattern.png');
    opacity: 0.1;
}

.cta-content {
    position: relative;
    z-index: 1;
    max-width: 700px;
    margin: 0 auto;
}

.cta-title {
    font-weight: 800;
    font-size: 2.5rem;
    margin-bottom: 20px;
    text-shadow: 0 2px 10px rgba(0,0,0,0.2);
}

.cta-description {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.cta-btn {
    border-radius: 50px;
    padding: 12px 30px;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    transition: all 0.3s ease;
    margin: 0 5px;
}

.cta-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.3);
}

.floating-shapes div {
    position: absolute;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: floatBubble 8s linear infinite;
}

.shape1 {
    top: 20%;
    left: 10%;
    animation-delay: 0s !important;
}

.shape2 {
    top: 80%;
    left: 20%;
    width: 80px !important;
    height: 80px !important;
    animation-delay: 1s !important;
}

.shape3 {
    top: 40%;
    left: 80%;
    width: 40px !important;
    height: 40px !important;
    animation-delay: 2s !important;
}

.shape4 {
    top: 10%;
    left: 70%;
    width: 70px !important;
    height: 70px !important;
    animation-delay: 3s !important;
}

.shape5 {
    top: 50%;
    left: 60%;
    animation-delay: 4s !important;
}

.shape6 {
    top: 90%;
    left: 90%;
    width: 50px !important;
    height: 50px !important;
    animation-delay: 5s !important;
}

/* === ANIMATIONS === */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes fadeInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes floatBubble {
    0% {
        transform: translateY(0) rotate(0);
        opacity: 1;
    }
    50% {
        opacity: 0.5;
    }
    100% {
        transform: translateY(-120vh) rotate(360deg);
        opacity: 0;
    }
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0.5);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(52, 152, 219, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(52, 152, 219, 0);
    }
}

/* === RESPONSIVE STYLES === */
@media (max-width: 991.98px) {
    .hero-content h1 {
        font-size: 2.5rem;
    }
    
    .step-connector {
        display: none;
    }
    
    .step-box {
        margin-bottom: 40px;
    }
}

@media (max-width: 767.98px) {
    .hero-content h1 {
        font-size: 2rem;
    }
    
    .hero-content p {
        font-size: 1rem;
    }
    
    .item-img-container {
        height: 180px;
    }
    
    .cta-title {
        font-size: 2rem;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>