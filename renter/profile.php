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

<!-- Custom styles for profile page -->
<style>
    .hover-shadow:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.12), 0 4px 8px rgba(0,0,0,0.06) !important;
        transition: all 0.3s ease;
    }
    
    .card {
        border: none;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .profile-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 3rem 2rem;
        color: white;
        border-radius: 10px;
        margin-bottom: 30px;
    }
    
    .profile-image-container {
        position: relative;
        margin-bottom: 20px;
    }
    
    .profile-image {
        width: 180px;
        height: 180px;
        object-fit: cover;
        border-radius: 50%;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .profile-placeholder {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 5px solid white;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: none;
        padding: 15px 20px;
        font-weight: 600;
        color: #333;
    }
    
    .btn-primary {
        background: linear-gradient(45deg, #007bff, #0062cc);
        border: none;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
        transition: all 0.3s;
        padding: 10px 20px;
        border-radius: 5px;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.4);
    }
    
    .form-control {
        border-radius: 5px;
        padding: 10px 15px;
        border: 1px solid #e3e6f0;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        border-color: #bac8f3;
    }
    
    .badge-pill {
        border-radius: 30px;
        padding: 8px 15px;
        font-size: 0.9rem;
    }
    
    .user-info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .user-info-list li {
        padding: 12px 0;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
    }
    
    .user-info-list li:last-child {
        border-bottom: none;
    }
    
    .user-info-list i {
        width: 30px;
        color: #4e73df;
        font-size: 1.1rem;
    }
    
    .input-group-text {
        background-color: #4e73df;
        color: white;
        border: none;
    }
    
    /* Section dividers - now using standard form-group with hr */
</style>

<div class="container-fluid py-4">
    <!-- Profile Header -->
    <div class="profile-header shadow">
        <div class="container">
            <h1 class="display-4 font-weight-bold">Profil Saya</h1>
            <p class="lead">Kelola informasi akun dan preferensi Anda</p>
        </div>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-danger shadow-sm">
        <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success shadow-sm">
        <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <!-- Profile Info Card -->
            <div class="card hover-shadow">
                <div class="card-body text-center">
                    <div class="profile-image-container">
                        <?php if ($user['profile_image']): ?>
                        <img src="../assets/images/uploads/profiles/<?= $user['profile_image'] ?>" class="profile-image" alt="Profile Image">
                        <?php else: ?>
                        <div class="profile-placeholder mx-auto">
                            <i class="fas fa-user fa-5x text-muted"></i>
                        </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-weight-bold text-primary"><?= htmlspecialchars($user['name']) ?></h3>
                    <span class="badge badge-pill badge-primary mb-3">
                        <?php
                        switch ($user['user_type']) {
                            case 'renter':
                                echo '<i class="fas fa-user mr-1"></i> Penyewa';
                                break;
                            case 'owner':
                                echo '<i class="fas fa-briefcase mr-1"></i> Pemilik';
                                break;
                            case 'both':
                                echo '<i class="fas fa-users mr-1"></i> Penyewa & Pemilik';
                                break;
                        }
                        ?>
                    </span>
                </div>
                <div class="card-body pt-0">
                    <ul class="user-info-list">
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span><?= htmlspecialchars($user['email']) ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span><?= htmlspecialchars($user['phone']) ?></span>
                        </li>
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= htmlspecialchars($user['address']) ?></span>
                        </li>
                        <li>
                            <i class="fas fa-calendar-alt"></i>
                            <span>Bergabung: <?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <!-- Edit Profile Card -->
            <div class="card hover-shadow">
                <div class="card-header">
                    <i class="fas fa-user-edit mr-2"></i> Edit Profil
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="name"><i class="fas fa-user mr-2 text-primary"></i>Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email"><i class="fas fa-envelope mr-2 text-primary"></i>Email</label>
                            <input type="email" class="form-control bg-light" id="email" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                            <small class="form-text text-muted">Email tidak dapat diubah.</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="address"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address']) ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone mr-2 text-primary"></i>Nomor Telepon</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile_image"><i class="fas fa-camera mr-2 text-primary"></i>Foto Profil</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="profile_image" name="profile_image" accept="image/jpeg,image/png,image/jpg">
                                <label class="custom-file-label" for="profile_image">Pilih file foto...</label>
                            </div>
                            <small class="form-text text-muted">Upload foto profil baru (opsional)</small>
                        </div>
                        
                        <div class="form-group mt-4 mb-4">
                            <label><i class="fas fa-lock mr-2 text-primary"></i>Ubah Password</label>
                            <hr class="mt-0">
                        </div>
                        
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="new_password">Password Baru</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="new_password" name="new_password">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="confirm_password">Konfirmasi Password Baru</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        </div>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted mb-4">Biarkan kosong jika tidak ingin mengubah password.</small>
                        
                        <div class="text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk menampilkan nama file pada custom file input -->
<script>
    document.querySelector('.custom-file-input').addEventListener('change', function(e) {
        var fileName = e.target.files[0].name;
        var nextSibling = e.target.nextElementSibling;
        nextSibling.innerText = fileName;
    });
</script>

<?php require_once '../includes/footer.php'; ?>