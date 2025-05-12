<?php
require_once '../includes/header.php';
requireRenter();

$item_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Ambil detail barang
$item = fetchOne("SELECT i.*, u.name as owner_name, u.phone as owner_phone 
                  FROM items i 
                  JOIN users u ON i.owner_id = u.user_id 
                  WHERE i.item_id = ? AND i.is_available = 1 AND i.stock > 0", [$item_id]);

if (!$item) {
    redirect('view_items.php');
}

// Proses permintaan penyewaan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    
    if (empty($start_date) || empty($end_date)) {
        $error = 'Tanggal mulai dan selesai harus diisi.';
    } elseif (strtotime($start_date) < strtotime('today')) {
        $error = 'Tanggal mulai tidak boleh kurang dari hari ini.';
    } elseif (strtotime($end_date) < strtotime($start_date)) {
        $error = 'Tanggal selesai tidak boleh kurang dari tanggal mulai.';
    } elseif (!isset($_FILES['id_card']) || $_FILES['id_card']['error'] != 0) {
        $error = 'Foto KTP harus diunggah.';
    } else {
        // Upload foto KTP
        $upload_result = uploadFile($_FILES['id_card'], 'id_cards');
        
        if (!$upload_result['success']) {
            $error = $upload_result['message'];
        } else {
            // Hitung total harga
            $total_price = $item['price_per_day'] * $days;
            
            // Buat entri penyewaan baru
            $sql = "INSERT INTO rentals (item_id, renter_id, start_date, end_date, total_price, id_card_image) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            executeQuery($sql, [$item_id, $_SESSION['user_id'], $start_date, $end_date, $total_price, $upload_result['filename']]);
            
            $rental_id = lastInsertId();
            
            // Buat notifikasi untuk pemilik
            createNotification(
                $item['owner_id'],
                "Permintaan Penyewaan Baru",
                "Ada permintaan penyewaan baru untuk barang '{$item['name']}' dari {$_SESSION['user_name']}.",
                "new_order",
                $rental_id
            );
            
            $success = "Permintaan penyewaan berhasil dikirim! Mohon tunggu konfirmasi dari pemilik barang.";
        }
    }
}
?>

<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb bg-light rounded-pill px-3 py-2 shadow-sm">
            <li class="breadcrumb-item"><a href="view_items.php" class="text-decoration-none"><i class="fas fa-home"></i> Daftar Barang</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sewa Barang</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <!-- Detail Barang Card -->
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h2 class="h5 mb-0"><i class="fas fa-info-circle mr-2"></i>Detail Barang</h2>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="item-image-container rounded overflow-hidden shadow-sm">
                                <?php if ($item['image']): ?>
                                <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="img-fluid" alt="<?= htmlspecialchars($item['name']) ?>">
                                <?php else: ?>
                                <div class="bg-light p-5 text-center">
                                    <i class="fas fa-box fa-5x text-muted"></i>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Owner Info Card -->
                            <div class="card mt-3 border-0 bg-light shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 text-muted">Disewakan oleh</h6>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle mr-3 bg-primary text-white">
                                            <span><?= substr($item['owner_name'], 0, 1) ?></span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 font-weight-bold"><?= htmlspecialchars($item['owner_name']) ?></h6>
                                            <?php if (!empty($item['owner_phone'])): ?>
                                            <small class="text-muted"><i class="fas fa-phone-alt mr-1"></i> <?= htmlspecialchars($item['owner_phone']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-7">
                            <h3 class="text-primary font-weight-bold mb-3"><?= htmlspecialchars($item['name']) ?></h3>
                            
                            <div class="mb-4 price-highlight">
                                <span class="font-weight-bold">Harga Sewa:</span>
                                <div class="price-tag">
                                    <span class="price-amount"><?= formatPrice($item['price_per_day']) ?></span>
                                    <span class="price-period">/hari</span>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <span class="badge badge-success mr-2"><i class="fas fa-check-circle mr-1"></i> Tersedia</span>
                                <span class="badge badge-info"><i class="fas fa-cubes mr-1"></i> Stok: <?= $item['stock'] ?></span>
                            </div>
                            
                            <div class="description-section">
                                <h5 class="mb-2">Deskripsi</h5>
                                <div class="p-3 bg-light rounded">
                                    <p class="mb-0"><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                                </div>
                            </div>
                            
                            <!-- Fitur dan Manfaat -->
                            <div class="mt-4">
                                <h5 class="mb-3">Mengapa Menyewa dari Kami?</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="feature-icon mr-2">
                                                <i class="fas fa-shipping-fast text-success"></i>
                                            </div>
                                            <div>Pengiriman Cepat</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="feature-icon mr-2">
                                                <i class="fas fa-shield-alt text-primary"></i>
                                            </div>
                                            <div>Barang Berkualitas</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="feature-icon mr-2">
                                                <i class="fas fa-wallet text-warning"></i>
                                            </div>
                                            <div>Hemat Biaya</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center mb-3">
                                            <div class="feature-icon mr-2">
                                                <i class="fas fa-headset text-danger"></i>
                                            </div>
                                            <div>Dukungan 24/7</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Form Penyewaan Card -->
            <div class="card border-0 shadow-sm rounded-lg">
                <div class="card-header bg-primary text-white py-3">
                    <h3 class="h5 mb-0"><i class="fas fa-calendar-alt mr-2"></i>Form Penyewaan</h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        <div><?= $error ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success d-flex align-items-center">
                        <i class="fas fa-check-circle mr-2"></i>
                        <div><?= $success ?></div>
                    </div>
                    <div class="success-animation">
                        <div class="checkmark-circle">
                            <div class="checkmark draw"></div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <a href="my_rentals.php" class="btn btn-success btn-lg">
                            <i class="fas fa-list-alt mr-2"></i>Lihat Penyewaan Saya
                        </a>
                    </div>
                    <?php else: ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="start_date" class="font-weight-bold">
                                <i class="fas fa-calendar mr-1"></i> Tanggal Mulai
                            </label>
                            <input type="date" class="form-control form-control-lg" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date" class="font-weight-bold">
                                <i class="fas fa-calendar-check mr-1"></i> Tanggal Selesai
                            </label>
                            <input type="date" class="form-control form-control-lg" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="id_card" class="font-weight-bold">
                                <i class="fas fa-id-card mr-1"></i> Foto KTP (untuk verifikasi)
                            </label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="id_card" name="id_card" accept="image/jpeg,image/png,image/jpg" required>
                                <label class="custom-file-label" for="id_card">Pilih file...</label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i> Upload foto KTP untuk verifikasi identitas
                            </small>
                        </div>
                        
                        <div class="rental-summary mt-4 mb-4">
                            <h5 class="mb-3">Ringkasan Sewa</h5>
                            <div class="p-3 bg-light rounded">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Harga per hari:</span>
                                    <span class="font-weight-bold"><?= formatPrice($item['price_per_day']) ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Durasi sewa:</span>
                                    <span class="font-weight-bold" id="rental_days">0 hari</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">Total:</span>
                                    <span class="total-price" id="total_price">Rp 0</span>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block">
                            <i class="fas fa-shopping-cart mr-2"></i>Sewa Sekarang
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Script untuk menghitung total harga secara dinamis
    const pricePerDay = <?= $item['price_per_day'] ?>;
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const totalPriceElement = document.getElementById('total_price');
    const rentalDaysElement = document.getElementById('rental_days');
    
    function updateTotalPrice() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate >= startDate) {
                // Tambahkan 1 karena inklusive hari terakhir
                const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const totalPrice = pricePerDay * days;
                totalPriceElement.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
                rentalDaysElement.textContent = days + ' hari';
            }
        }
    }
    
    startDateInput.addEventListener('change', updateTotalPrice);
    endDateInput.addEventListener('change', updateTotalPrice);
    
    // Script untuk custom file input
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>

<style>
    /* Custom styles */
    .rounded-lg {
        border-radius: 0.5rem !important;
    }
    
    .item-image-container {
        position: relative;
        overflow: hidden;
        height: 250px;
    }
    
    .item-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .price-tag {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border-radius: 8px;
        padding: 8px 15px;
        display: inline-block;
        margin-left: 10px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }
    
    .price-amount {
        font-size: 1.2rem;
        font-weight: bold;
        color: white;
    }
    
    .price-period {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.85);
    }
    
    .price-highlight {
        display: flex;
        align-items: center;
    }
    
    .total-price {
        font-size: 1.4rem;
        font-weight: bold;
        color: #28a745;
    }
    
    .feature-icon {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background-color: rgba(0,0,0,0.05);
    }
    
    .feature-icon i {
        font-size: 1rem;
    }
    
    .avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        font-weight: bold;
    }
    
    .card-header {
        border-bottom: none;
    }
    
    .form-control-lg {
        border-radius: 0.4rem;
    }
    
    .btn-lg {
        border-radius: 0.4rem;
        padding: 0.75rem 1rem;
    }
    
    /* Checkmark animation for success */
    .success-animation {
        margin: 20px auto;
        text-align: center;
    }
    
    .checkmark-circle {
        width: 80px;
        height: 80px;
        position: relative;
        display: inline-block;
        vertical-align: top;
        margin-left: auto;
        margin-right: auto;
    }
    
    .checkmark-circle .background {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: #28a745;
        position: absolute;
    }
    
    .checkmark-circle .checkmark {
        border-radius: 5px;
    }
    
    .checkmark-circle .checkmark.draw:after {
        animation-delay: 300ms;
        animation-duration: 1s;
        animation-timing-function: ease;
        animation-name: checkmark;
        transform: scaleX(-1) rotate(135deg);
        animation-fill-mode: forwards;
    }
    
    .checkmark-circle .checkmark:after {
        opacity: 1;
        height: 40px;
        width: 20px;
        transform-origin: left top;
        border-right: 7px solid #28a745;
        border-top: 7px solid #28a745;
        content: '';
        left: 16px;
        top: 44px;
        position: absolute;
    }
    
    @keyframes checkmark {
        0% {
            height: 0;
            width: 0;
            opacity: 1;
        }
        20% {
            height: 0;
            width: 20px;
            opacity: 1;
        }
        40% {
            height: 40px;
            width: 20px;
            opacity: 1;
        }
        100% {
            height: 40px;
            width: 20px;
            opacity: 1;
        }
    }
    
    .rounded-pill {
        border-radius: 50rem !important;
    }
    
    .shadow-sm {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
    }
</style>

<?php require_once '../includes/footer.php'; ?>