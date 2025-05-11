<?php
require_once '../includes/header.php';
requireOwner();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_per_day = $_POST['price_per_day'] ?? 0;
    $stock = $_POST['stock'] ?? 1;
    $is_available = isset($_POST['is_available']) ? 1 : 0;
    
    if (empty($name) || empty($description) || $price_per_day <= 0 || $stock < 1) {
        $error = 'Semua field harus diisi dengan benar.';
    } else {
        // Upload gambar jika ada
        $image_filename = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $upload_result = uploadFile($_FILES['image'], 'items');
            
            if ($upload_result['success']) {
                $image_filename = $upload_result['filename'];
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (empty($error)) {
            // Simpan data barang
            $sql = "INSERT INTO items (owner_id, name, description, price_per_day, stock, image, is_available) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            executeQuery($sql, [
                $_SESSION['user_id'], 
                $name, 
                $description, 
                $price_per_day, 
                $stock, 
                $image_filename, 
                $is_available
            ]);
            
            $success = 'Barang berhasil ditambahkan.';
        }
    }
}
?>

<h1>Tambah Barang Baru</h1>

<div class="card">
    <div class="card-body">
        <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <div class="text-center mb-3">
            <a href="manage_items.php" class="btn btn-primary">Kelola Barang</a>
            <a href="add_item.php" class="btn btn-outline-primary ml-2">Tambah Barang Lain</a>
        </div>
        <?php else: ?>
        
        <form method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nama Barang</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                <small class="form-text text-muted">Berikan deskripsi detail tentang barang, kondisi, dan ketentuan penyewaan.</small>
            </div>
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="price_per_day">Harga per Hari (Rp)</label>
                    <input type="number" class="form-control" id="price_per_day" name="price_per_day" min="1000" step="1000" required>
                </div>
                <div class="form-group col-md-6">
                    <label for="stock">Jumlah Stok</label>
                    <input type="number" class="form-control" id="stock" name="stock" min="1" value="1" required>
                </div>
            </div>
            <div class="form-group">
                <label for="image">Foto Barang</label>
                <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/jpg">
                <small class="form-text text-muted">Upload foto barang untuk memudahkan penyewa melihat kondisi barang.</small>
            </div>
            <div class="form-group">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="is_available" name="is_available" checked>
                    <label class="custom-control-label" for="is_available">Tersedia untuk disewa</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Tambah Barang</button>
            <a href="manage_items.php" class="btn btn-outline-secondary">Batal</a>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>