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

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="h5 mb-0">Detail Barang</h2>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <?php if ($item['image']): ?>
                        <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($item['name']) ?>">
                        <?php else: ?>
                        <div class="bg-light p-5 text-center">
                            <i class="fas fa-image fa-5x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <h3><?= htmlspecialchars($item['name']) ?></h3>
                        <p><?= nl2br(htmlspecialchars($item['description'])) ?></p>
                        <p><strong>Harga Sewa:</strong> <?= formatPrice($item['price_per_day']) ?> / hari</p>
                        <p><strong>Stok Tersedia:</strong> <?= $item['stock'] ?></p>
                        <p><strong>Disewakan oleh:</strong> <?= htmlspecialchars($item['owner_name']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="h5 mb-0">Form Penyewaan</h3>
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <div class="text-center mt-3">
                    <a href="my_rentals.php" class="btn btn-primary">Lihat Penyewaan Saya</a>
                </div>
                <?php else: ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="id_card">Foto KTP (untuk verifikasi)</label>
                        <input type="file" class="form-control-file" id="id_card" name="id_card" accept="image/jpeg,image/png,image/jpg" required>
                        <small class="form-text text-muted">Upload foto KTP untuk verifikasi identitas</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Total Harga:</label>
                        <div class="font-weight-bold" id="total_price">Rp 0</div>
                        <small class="form-text text-muted">Total akan dihitung berdasarkan durasi sewa</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Sewa Sekarang</button>
                </form>
                <?php endif; ?>
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
    
    function updateTotalPrice() {
        if (startDateInput.value && endDateInput.value) {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            
            if (endDate >= startDate) {
                // Tambahkan 1 karena inklusive hari terakhir
                const days = Math.floor((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1;
                const totalPrice = pricePerDay * days;
                totalPriceElement.textContent = 'Rp ' + totalPrice.toLocaleString('id-ID');
            }
        }
    }
    
    startDateInput.addEventListener('change', updateTotalPrice);
    endDateInput.addEventListener('change', updateTotalPrice);
</script>

<?php require_once '../includes/footer.php'; ?>