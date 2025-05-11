<?php
require_once 'includes/header.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = $_POST['address'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $user_type = $_POST['user_type'] ?? 'renter';
    
    if (empty($name) || empty($email) || empty($password) || empty($address) || empty($phone)) {
        $error = 'Semua field harus diisi.';
    } elseif ($password !== $confirm_password) {
        $error = 'Password dan konfirmasi password tidak cocok.';
    } else {
        $result = register($name, $email, $password, $address, $phone, $user_type);
        
        if ($result['success']) {
            $success = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = $result['message'];
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Register</div>
            <div class="card-body">
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <div class="text-center">
                    <a href="login.php" class="btn btn-primary">Login Sekarang</a>
                </div>
                <?php else: ?>
                
                <form method="post">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>
                    <div class="form-group">
                        <label for="address">Alamat</label>
                        <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Daftar Sebagai</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_renter" value="renter" checked>
                            <label class="form-check-label" for="user_type_renter">
                                Penyewa
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_owner" value="owner">
                            <label class="form-check-label" for="user_type_owner">
                                Pemilik Barang
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="user_type" id="user_type_both" value="both">
                            <label class="form-check-label" for="user_type_both">
                                Keduanya
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </form>
                
                <div class="mt-3">
                    Sudah punya akun? <a href="login.php">Login disini</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>