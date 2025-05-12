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

<style>
    :root {
        --primary: #3498db;
        --success: #2ecc71;
        --danger: #e74c3c;
        --warning: #f39c12;
        --light-bg: #f8f9fa;
    }

    body {
        background-color: var(--light-bg);
    }

    .rental-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        padding: 1rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .rental-header h1 {
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .rental-card {
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .rental-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }

    .rental-card .card-header {
        cursor: pointer;
    }

    .rental-card .card-header .btn-link {
        color: #2c3e50;
        text-decoration: none;
        font-weight: 600;
    }

    .rental-details img {
        max-height: 200px;
        object-fit: cover;
        border-radius: 8px;
    }

    .rental-actions .btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .rental-actions .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .badge-pending {
        background-color: var(--warning);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 2rem;
        background-color: white;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .empty-state i {
        font-size: 3rem;
        color: #6c757d;
        margin-bottom: 1rem;
    }

    .custom-control-label {
        cursor: pointer;
    }
</style>

<div class="container-fluid">
    <div class="rental-header">
        <h1>Permintaan Penyewaan</h1>
    </div>

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
                <div class="card rental-card">
                    <div class="card-header" id="heading<?= $index ?>">
                        <div>
                            <button class="btn btn-link text-left" type="button" data-toggle="collapse" data-target="#collapse<?= $index ?>" aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>" aria-controls="collapse<?= $index ?>">
                                <span class="font-weight-bold"><?= htmlspecialchars($rental['item_name']) ?></span> - 
                                <span><?= htmlspecialchars($rental['renter_name']) ?></span>
                            </button>
                        </div>
                        <div>
                            <span class="badge badge-pending">Menunggu Konfirmasi</span>
                        </div>
                    </div>

                    <div id="collapse<?= $index ?>" class="collapse <?= $index === 0 ? 'show' : '' ?>" aria-labelledby="heading<?= $index ?>" data-parent="#rentalAccordion">
                        <div class="card-body rental-details">
                            <div class="row">
                                <div class="col-md-4">
                                    <h5 class="mb-3">Detail Penyewa</h5>
                                    <div class="bg-light p-3 rounded mb-3">
                                        <p class="mb-1">
                                            <strong>Nama:</strong> <?= htmlspecialchars($rental['renter_name']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>No. HP:</strong> <?= htmlspecialchars($rental['renter_phone']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Email:</strong> <?= htmlspecialchars($rental['renter_email']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Alamat:</strong> <?= htmlspecialchars($rental['renter_address']) ?>
                                        </p>
                                    </div>
                                    
                                    <h5 class="mb-3">KTP Penyewa</h5>
                                    <?php if ($rental['id_card_image']): ?>
                                    <img src="../assets/images/uploads/id_cards/<?= $rental['id_card_image'] ?>" class="img-fluid rounded shadow-sm">
                                    <?php else: ?>
                                    <div class="alert alert-warning">Tidak ada foto KTP</div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4">
                                    <h5 class="mb-3">Detail Barang</h5>
                                    <?php if ($rental['item_image']): ?>
                                    <img src="../assets/images/uploads/items/<?= $rental['item_image'] ?>" class="img-fluid rounded shadow-sm mb-3">
                                    <?php endif; ?>
                                    <div class="bg-light p-3 rounded">
                                        <p class="mb-1">
                                            <strong>Barang:</strong> <?= htmlspecialchars($rental['item_name']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Tanggal Mulai:</strong> <?= formatDate($rental['start_date']) ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Tanggal Selesai:</strong> <?= formatDate($rental['end_date']) ?>
                                        </p>
                                        <p class="mb-0">
                                            <strong>Total Harga:</strong> <?= formatPrice($rental['total_price']) ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4 rental-actions">
                                    <h5 class="mb-3">Tindakan</h5>
                                    <form method="post" class="mb-3" onsubmit="return confirm('Apakah Anda yakin ingin mengkonfirmasi permintaan penyewaan ini?')">
                                        <input type="hidden" name="rental_id" value="<?= $rental['rental_id'] ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn btn-success btn-block">
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
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h4>Tidak ada permintaan penyewaan</h4>
                <p class="text-muted">Belum ada permintaan penyewaan yang menunggu konfirmasi.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>