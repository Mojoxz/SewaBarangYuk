<?php
require_once '../includes/header.php';
requireOwner();

// Dapatkan permintaan penyewaan yang belum dikonfirmasi
$pending_rentals = fetchAll("SELECT r.*, i.name as item_name, i.image as item_image, 
                           u.name as renter_name, u.phone as renter_phone, u.email as renter_email, u.address as renter_address
                           FROM rentals r 
                           JOIN items i ON r.item_id = i.item_id 
                           JOIN users u ON r.renter_id = u.user_id
                           WHERE i.owner_id = ? AND r.status = 'pending'
                           ORDER BY r.created_at DESC", [$_SESSION['user_id']]);

// Proses aksi konfirmasi atau tolak
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['rental_id'])) {
    $rental_id = $_POST['rental_id'];
    $action = $_POST['action'];
    
    // Dapatkan data rental
    $rental = fetchOne("SELECT r.*, i.name as item_name, i.owner_id, i.stock, u.name as renter_name, u.user_id as renter_id 
                       FROM rentals r 
                       JOIN items i ON r.item_id = i.item_id 
                       JOIN users u ON r.renter_id = u.user_id
                       WHERE r.rental_id = ?", [$rental_id]);
    
    if ($rental && $rental['owner_id'] == $_SESSION['user_id']) {
        if ($action === 'confirm') {
            // Periksa stok
            if ($rental['stock'] < 1) {
                $_SESSION['error'] = 'Stok barang tidak mencukupi.';
            } else {
                // Update status rental menjadi confirmed
                executeQuery("UPDATE rentals SET status = 'confirmed' WHERE rental_id = ?", [$rental_id]);
                
                // Kurangi stok
                executeQuery("UPDATE items SET stock = stock - 1 WHERE item_id = ?", [$rental['item_id']]);
                
                // Buat notifikasi untuk penyewa
                createNotification(
                    $rental['renter_id'],
                    "Permintaan Penyewaan Dikonfirmasi",
                    "Permintaan penyewaan Anda untuk barang '{$rental['item_name']}' telah dikonfirmasi.",
                    "confirmation",
                    $rental_id
                );
                
                $_SESSION['success'] = 'Permintaan penyewaan berhasil dikonfirmasi.';
            }
        } elseif ($action === 'reject') {
            // Update status rental menjadi cancelled
            executeQuery("UPDATE rentals SET status = 'cancelled' WHERE rental_id = ?", [$rental_id]);
            
            // Buat notifikasi untuk penyewa
            createNotification(
                $rental['renter_id'],
                "Permintaan Penyewaan Ditolak",
                "Maaf, permintaan penyewaan Anda untuk barang '{$rental['item_name']}' tidak dapat dipenuhi.",
                "confirmation",
                $rental_id
            );
            
            $_SESSION['success'] = 'Permintaan penyewaan berhasil ditolak.';
        }
        
        // Refresh halaman
        redirect('rental_requests.php');
    }
}

// Tampilkan pesan jika ada
$error = $_SESSION['error'] ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<h1>Permintaan Penyewaan</h1>

<?php if ($error): ?>
<div class="alert alert-danger"><?= $error ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (count($pending_rentals) > 0): ?>
        <div class="accordion" id="rentalAccordion">
            <?php foreach ($pending_rentals as $index => $rental): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center" id="heading<?= $index ?>">
                    <div>
                        <button class="btn btn-link text-left" type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                            <span class="font-weight-bold"><?= htmlspecialchars($rental['item_name']) ?></span> - 
                            <span><?= htmlspecialchars($rental['renter_name']) ?></span>
                        </button>
                    </div>
                    <div>
                        <span class="badge badge-warning">Menunggu Konfirmasi</span>
                    </div>
                </div>

                <div id="collapse<?= $index ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-parent="#rentalAccordion">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h5>Detail Penyewa</h5>
                                <p>
                                    <strong>Nama:</strong> <?= htmlspecialchars($rental['renter_name']) ?><br>
                                    <strong>No. HP:</strong> <?= htmlspecialchars($rental['renter_phone']) ?><br>
                                    <strong>Email:</strong> <?= htmlspecialchars($rental['renter_email']) ?><br>
                                    <strong>Alamat:</strong> <?= htmlspecialchars($rental['renter_address']) ?>
                                </p>
                                
                                <h5>KTP Penyewa</h5>
                                <?php if ($rental['id_card_image']): ?>
                                <img src="../assets/images/uploads/id_cards/<?= $rental['id_card_image'] ?>" class="img-fluid img-thumbnail">
                                <?php else: ?>
                                <p class="text-muted">Tidak ada foto KTP</p>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-4">
                                <h5>Detail Barang</h5>
                                <?php if ($rental['item_image']): ?>
                                <img src="../assets/images/uploads/items/<?= $rental['item_image'] ?>" class="img-thumbnail mb-3" style="max-width: 150px;">
                                <?php endif; ?>
                                <p>
                                    <strong>Barang:</strong> <?= htmlspecialchars($rental['item_name']) ?><br>
                                    <strong>Tanggal Mulai:</strong> <?= formatDate($rental['start_date']) ?><br>
                                    <strong>Tanggal Selesai:</strong> <?= formatDate($rental['end_date']) ?><br>
                                    <strong>Total Harga:</strong> <?= formatPrice($rental['total_price']) ?>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h5>Tindakan</h5>
                                <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin mengkonfirmasi permintaan penyewaan ini?')">
                                    <input type="hidden" name="rental_id" value="<?= $rental['rental_id'] ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-success btn-block mb-2">
                                        <i class="fas fa-check"></i> Konfirmasi Penyewaan
                                    </button>
                                </form>
                                <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menolak permintaan penyewaan ini?')">
                                    <input type="hidden" name="rental_id" value="<?= $rental['rental_id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-times"></i> Tolak Penyewaan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            Tidak ada permintaan penyewaan yang menunggu konfirmasi.
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>