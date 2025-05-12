<?php
require_once '../includes/header.php';
requireOwner();

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

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h2 mb-4 text-primary">Profil Saya</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Foto Profil</h5>
                </div>
                <div class="card-body text-center">
                    <div class="profile-image-container mb-3">
                        <?php if ($user['profile_image']): ?>
                        <img src="../assets/images/uploads/profiles/<?= $user['profile_image'] ?>" class="img-fluid rounded-circle profile-avatar" alt="Profile Image">
                        <?php else: ?>
                        <div class="profile-avatar-placeholder rounded-circle mx-auto d-flex align-items-center justify-content-center">
                            <i class="fas fa-user fa-5x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <h4 class="mb-2"><?= htmlspecialchars($user['name']) ?></h4>
                    <p class="text-muted mb-2">
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
                    <small class="text-muted">Bergabung sejak: <?= date('d/m/Y', strtotime($user['created_at'])) ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Edit Profil</h5>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= $success ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>
                    
                    <form method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="font-weight-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email" class="font-weight-bold">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                    <small class="form-text text-muted">Email tidak dapat diubah.</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address" class="font-weight-bold">Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone" class="font-weight-bold">Nomor Telepon</label>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="profile_image" class="font-weight-bold">Foto Profil</label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg">
                                        <label class="custom-file-label" for="profile_image">Pilih file</label>
                                    </div>
                                    <small class="form-text text-muted">Upload foto profil baru (maks. 2MB)</small>
                                </div>
                            </div>
                        </div>
                        
                        <hr class="my-4">
                        
                        <h5 class="text-primary mb-3">Ubah Password</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="current_password" class="font-weight-bold">Password Saat Ini</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="new_password" class="font-weight-bold">Password Baru</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="confirm_password" class="font-weight-bold">Konfirmasi Password Baru</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted mb-3">Biarkan kosong jika tidak ingin mengubah password.</small>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save mr-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .profile-image-container {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
    }
    .profile-avatar {
        width: 200px;
        height: 200px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    .profile-avatar-placeholder {
        width: 200px;
        height: 200px;
        background-color: #f8f9fa;
        border: 2px dashed #dee2e6;
    }
    .card-header {
        background-color: #007bff !important;
    }
</style>

<script>
    // Custom file input label
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerHTML = fileName;
    });
</script>

<?php require_once '../includes/footer.php'; ?>