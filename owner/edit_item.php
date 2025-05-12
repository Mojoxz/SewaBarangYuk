<?php
require_once '../includes/header.php';
requireOwner();

$item_id = $_GET['id'] ?? 0;
$error = '';
$success = '';

// Ambil data barang
$item = fetchOne("SELECT * FROM items WHERE item_id = ? AND owner_id = ?", [$item_id, $_SESSION['user_id']]);

if (!$item) {
    redirect('manage_items.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_per_day = $_POST['price_per_day'] ?? 0;
    $stock = $_POST['stock'] ?? 1;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name) || empty($description) || $price_per_day <= 0 || $stock < 1) {
        $error = 'Semua field harus diisi dengan benar.';
    } else {
        // Upload gambar baru jika ada
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadFile($_FILES['image'], 'items');
            
            if ($upload_result['success']) {
                // Update termasuk gambar baru
                $sql = "UPDATE items SET name = ?, description = ?, price_per_day = ?, stock = ?, image = ?, is_available = ? WHERE item_id = ?";
                executeQuery($sql, [
                    $name, 
                    $description, 
                    $price_per_day, 
                    $stock, 
                    $upload_result['filename'], 
                    $is_available, 
                    $item_id
                ]);
            } else {
                $error = $upload_result['message'];
            }
        } else {
            // Update tanpa mengubah gambar
            $sql = "UPDATE items SET name = ?, description = ?, price_per_day = ?, stock = ?, is_available = ? WHERE item_id = ?";
            executeQuery($sql, [
                $name, 
                $description, 
                $price_per_day, 
                $stock, 
                $is_available, 
                $item_id
            ]);
        }
        
        if (empty($error)) {
            $success = 'Barang berhasil diperbarui.';
            // Refresh data barang
            $item = fetchOne("SELECT * FROM items WHERE item_id = ?", [$item_id]);
        }
    }
}
?>

<style>
    :root {
        --primary: #3498db;
        --primary-dark: #2980b9;
        --success: #2ecc71;
        --success-dark: #27ae60;
        --warning: #f39c12;
        --warning-dark: #d35400;
        --info: #1abc9c;
        --info-dark: #16a085;
        --danger: #e74c3c;
    }
    
    body {
        background-color: #f4f6f9;
    }
    
    .card {
        border: none;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        margin-bottom: 1.5rem;
    }
    
    .card-body {
        padding: 2rem;
    }
    
    .btn-primary {
        background-color: var(--primary);
        border-color: var(--primary);
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }
    
    .btn-outline-secondary {
        border-color: #6c757d;
        color: #6c757d;
        transition: all 0.3s ease;
    }
    
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
    
    h1 {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        font-weight: 600;
        color: #34495e;
    }
    
    .form-control {
        border-color: #ced4da;
        transition: border-color 0.3s ease, box-shadow 0.3s ease;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    
    .custom-control-input:checked ~ .custom-control-label::before {
        background-color: var(--primary);
        border-color: var(--primary);
    }
    
    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }
    
    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }
    
    .btn-outline-primary {
        color: var(--primary);
        border-color: var(--primary);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary);
        color: white;
    }
    
    .img-thumbnail {
        border-radius: 10px;
        max-width: 250px;
        margin-bottom: 1rem;
    }
</style>

<div class="container">
    <h1>Edit Barang</h1>

    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama Barang</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="4" required><?= htmlspecialchars($item['description']) ?></textarea>
                    <small class="form-text text-muted">Berikan deskripsi detail tentang barang, kondisi, dan ketentuan penyewaan.</small>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="price_per_day">Harga per Hari (Rp)</label>
                        <input type="number" class="form-control" id="price_per_day" name="price_per_day" min="1000" step="1000" value="<?= $item['price_per_day'] ?>" required>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="stock">Jumlah Stok</label>
                        <input type="number" class="form-control" id="stock" name="stock" min="1" value="<?= $item['stock'] ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Foto Barang Saat Ini</label>
                    <div>
                        <?php if ($item['image']): ?>
                        <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="img-thumbnail" alt="Foto Barang">
                        <?php else: ?>
                        <p class="text-muted">Tidak ada foto</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="image">Upload Foto Baru (Opsional)</label>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                    <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah foto.</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" <?= $item['is_available'] ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_available">Tersedia untuk disewa</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="manage_items.php" class="btn btn-outline-secondary">Kembali</a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>