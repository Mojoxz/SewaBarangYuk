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
                    <img src="../assets/images/uploads/items/<?= $item['image'] ?>" class="img-thumbnail" style="max-width: 200px;">
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

<?php require_once '../includes/footer.php'; ?>