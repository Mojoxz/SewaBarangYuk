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

<!-- Custom styles matching login.php -->
<style>

.user-type-card {
        cursor: pointer;
        transition: all 0.3s;
        border: 2px solid transparent;
    }
    
    .user-type-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #4e73df;
    }
    
    input[type="radio"]:checked + .user-type-card {
        border-color: #4e73df;
        background-color: rgba(78, 115, 223, 0.05);
    }
    
    .user-type-card .card-body {
        padding: 1.5rem 0.5rem;
    }

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
    
    .register-header {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
        padding: 3rem 2rem;
        color: white;
        border-radius: 10px;
        margin-bottom: 30px;
        text-align: center;
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
    
    .input-group-text {
        background-color: #4e73df;
        color: white;
        border: none;
    }
    
    .register-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: white;
    }
    
    .login-link {
        color: #4e73df;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .login-link:hover {
        color: #224abe;
        text-decoration: none;
    }
    
    .form-check-input:checked {
        background-color: #4e73df;
        border-color: #4e73df;
    }
    
    .form-check-label {
        margin-left: 5px;
    }
</style>

<div class="container-fluid py-4">
    <!-- Register Header -->
    <div class="register-header shadow">
        <div class="container">
            <i class="fas fa-user-plus register-icon"></i>
            <h1 class="display-4 font-weight-bold">Buat Akun Baru</h1>
            <p class="lead">Silahkan isi form dibawah untuk mendaftar</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if ($error): ?>
            <div class="alert alert-danger shadow-sm">
                <i class="fas fa-exclamation-circle mr-2"></i> <?= $error ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success shadow-sm">
                <i class="fas fa-check-circle mr-2"></i> <?= $success ?>
            </div>
            <div class="text-center">
                <a href="login.php" class="btn btn-primary btn-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login Sekarang
                </a>
            </div>
            <?php else: ?>
            
            <div class="card hover-shadow">
                <div class="card-header">
                    <i class="fas fa-user-plus mr-2"></i> Form Registrasi
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="name"><i class="fas fa-user mr-2 text-primary"></i>Nama Lengkap</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    </div>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="email"><i class="fas fa-envelope mr-2 text-primary"></i>Email</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    </div>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="password"><i class="fas fa-lock mr-2 text-primary"></i>Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            
                            <div class="form-group col-md-6">
                                <label for="confirm_password"><i class="fas fa-lock mr-2 text-primary"></i>Konfirmasi Password</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    </div>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address"><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Alamat</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone"><i class="fas fa-phone mr-2 text-primary"></i>Nomor Telepon</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="text" class="form-control" id="phone" name="phone" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
    <label><i class="fas fa-user-tag mr-2 text-primary"></i>Daftar Sebagai</label>
    <div class="row text-center mt-3">
        <div class="col-md-4 mb-3">
            <input type="radio" class="d-none" name="user_type" id="user_type_renter" value="renter" checked>
            <label for="user_type_renter" class="card user-type-card p-3">
                <div class="card-body">
                    <i class="fas fa-home fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Penyewa</h5>
                    <p class="card-text text-muted">Menyewa barang</p>
                </div>
            </label>
        </div>
        
        <div class="col-md-4 mb-3">
            <input type="radio" class="d-none" name="user_type" id="user_type_owner" value="owner">
            <label for="user_type_owner" class="card user-type-card p-3">
                <div class="card-body">
                    <i class="fas fa-box-open fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Pemilik</h5>
                    <p class="card-text text-muted">Meminjamkan barang</p>
                </div>
            </label>
        </div>
        
        <div class="col-md-4 mb-3">
            <input type="radio" class="d-none" name="user_type" id="user_type_both" value="both">
            <label for="user_type_both" class="card user-type-card p-3">
                <div class="card-body">
                    <i class="fas fa-exchange-alt fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Keduanya</h5>
                    <p class="card-text text-muted">Sewa dan pinjamkan</p>
                </div>
            </label>
        </div>
    </div>
</div>

                        
                        <div class="text-right mt-4">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-user-plus mr-2"></i> Daftar
                            </button>
                        </div>
                    </form>
                    
                    <div class="mt-4 text-center">
                        <p>Sudah punya akun? <a href="login.php" class="login-link">Login disini</a></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>