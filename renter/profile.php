<?php
require_once '../includes/header.php';
requireRenter();

$user = getCurrentUser();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($name) || empty($address) || empty($phone)) {
        $error = 'Nama, alamat, dan nomor telepon harus diisi.';
    } else {
        // Update profil
        $sql = "UPDATE users SET name = ?, address = ?, phone = ? WHERE user_id = ?";
        executeQuery($sql, [$name, $address, $phone, $_SESSION['user_id']]);
        
        // Update password jika diisi
        if (!empty($current_password) && !empty($new_password)) {
            if (password_verify($current_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    executeQuery("UPDATE users SET password = ? WHERE user_id = ?", [$hashed_password, $_SESSION['user_id']]);
                } else {
                    $error = 'Password baru dan konfirmasi password tidak cocok.';
                }
            } else {
                $error = 'Password saat ini tidak valid.';
            }
        }
        
        // Upload foto profil jika ada
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $upload_result = uploadFile($_FILES['profile_image'], 'profiles');
            if ($upload_result['success']) {
                executeQuery("UPDATE users SET profile_image = ? WHERE user_id = ?", [$upload_result['filename'], $_SESSION['user_id']]);
            } else {
                $error = $upload_result['message'];
            }
        }
        
        if (empty($error)) {
            $success = 'Profil berhasil diperbarui.';
            // Refresh data user
            $user = getCurrentUser();
            $_SESSION['user_name'] = $user['name'];
        }
    }
}
?>

<h1>Profil Saya</h1>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                Foto Profil
            </div>
            <div class="card-body text-center">
                <?php if ($user['profile_image']): ?>
                <img src="../assets/images/uploads/profiles/<?= $user['profile_image'] ?>" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile Image">
                <?php else: ?>
                <div class="bg-light rounded-circle mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                    <i class="fas fa-user fa-5x text-muted"></i>
                </div>
                <?php endif; ?>
                <h4><?= htmlspecialchars($user['name']) ?></h4>
                <p class="text-muted">
                    <?php
                    switch ($user['user_type']) {
                        case 'renter':
                            echo 'Penyewa';
                            break;
                        case 'owner':
                            echo 'Pemilik';
                            break;
                        case 'both':
                            echo 'Penyewa & Pemilik';
                            break;
                    }
                    ?>
                </p>
                <p>Bergabung sejak: <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                Edit Profil
            </div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                        <small class="form-text text-muted">Email tidak dapat diubah.</small>
                    </div>
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="profile_image">Foto Profil</label>
                        <input type="file" class="form-control-file" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg">
                        <small class="form-text text-muted">Upload foto profil baru (opsional)</small>
                    </div>
                    
                    <hr>
                    <h5>Ubah Password</h5>
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                    </div>
                    <small class="form-text text-muted mb-3">Biarkan kosong jika tidak ingin mengubah password.</small>
                    
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>